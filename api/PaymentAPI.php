<?php
/**
 * RESTful API cho Payment
 * Endpoint: /api/payment
 * ✅ ĐÃ SỬA: Dùng email thay vì customer_id
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

$currentUser = AuthMiddleware::requireAuth();

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'POST':
        if ($action === 'create') {
            createPayment($db, $currentUser);
        } elseif ($action === 'confirm') {
            confirmPayment($db, $currentUser);
        } else {
            sendResponse(400, 'error', 'Action không hợp lệ');
        }
        break;
        
    case 'GET':
        if ($id) {
            getPayment($db, $currentUser, $id);
        } else {
            getPayments($db, $currentUser);
        }
        break;
        
    default:
        sendResponse(405, 'error', 'Method not allowed');
}

/**
 * Tạo thanh toán cho đơn hàng
 * ✅ ĐÃ SỬA: Dùng email thay vì customer_id
 */
function createPayment($db, $currentUser) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['order_id']) || empty($data['payment_method'])) {
            sendResponse(400, 'error', 'Thiếu order_id hoặc payment_method');
            return;
        }

        $orderId = (int)$data['order_id'];
        $paymentMethod = $data['payment_method'];

        $allowedMethods = ['cod', 'transfer', 'momo', 'zalopay'];
        if (!in_array($paymentMethod, $allowedMethods)) {
            sendResponse(400, 'error', 'Phương thức thanh toán không hợp lệ');
            return;
        }

        // Lấy thông tin đơn hàng
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            sendResponse(404, 'error', 'Không tìm thấy đơn hàng');
            return;
        }

        // ✅ SỬA: Dùng email thay vì customer_id
        if ($currentUser['role'] !== 'admin' && $order['email'] != ($currentUser['email'] ?? '')) {
            sendResponse(403, 'error', 'Bạn không có quyền thanh toán đơn hàng này');
            return;
        }

        // Không cho thanh toán lại đơn đã thanh toán
        if ($order['status'] === 'completed' || $order['status'] === 'cancelled') {
            sendResponse(400, 'error', 'Đơn hàng không thể thanh toán (trạng thái: ' . $order['status'] . ')');
            return;
        }

        // Kiểm tra đã có payment chưa
        $stmt = $db->prepare("SELECT * FROM payments WHERE order_id = ? AND status != 'failed'");
        $stmt->execute([$orderId]);
        $existingPayment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingPayment) {
            sendResponse(400, 'error', 'Đơn hàng đã có giao dịch thanh toán');
            return;
        }

        // Tạo mã thanh toán
        $paymentCode = 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

        // Mô phỏng: COD = pending, online = completed
        $status = 'pending';
        $paidAt = null;
        $transactionId = null;

        if ($paymentMethod === 'cod') {
            $status = 'pending';
        } else {
            $status = 'completed';
            $paidAt = date('Y-m-d H:i:s');
            $transactionId = 'TXN-' . time() . '-' . rand(1000, 9999);
        }

        // Insert payment
        $stmt = $db->prepare("
            INSERT INTO payments (payment_code, order_id, user_id, amount, payment_method, status, transaction_id, paid_at, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $paymentCode,
            $orderId,
            $currentUser['user_id'],
            $order['total_amount'],
            $paymentMethod,
            $status,
            $transactionId,
            $paidAt,
            $data['notes'] ?? ''
        ]);

        $paymentId = $db->lastInsertId();

        // Cập nhật đơn hàng: ghi nhận payment_method
        $stmt = $db->prepare("UPDATE orders SET payment_method = ? WHERE id = ?");
        $stmt->execute([$paymentMethod, $orderId]);

        // Nếu thanh toán online thành công → cập nhật trạng thái đơn
        if ($status === 'completed') {
            $stmt = $db->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$orderId]);
        }

        sendResponse(201, 'success', 'Tạo thanh toán thành công', [
            'payment_id' => $paymentId,
            'payment_code' => $paymentCode,
            'status' => $status,
            'amount' => (float)$order['total_amount'],
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Xác nhận thanh toán COD
 */
function confirmPayment($db, $currentUser) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['payment_id'])) {
            sendResponse(400, 'error', 'Thiếu payment_id');
            return;
        }

        if ($currentUser['role'] !== 'admin') {
            sendResponse(403, 'error', 'Chỉ Admin mới xác nhận được thanh toán COD');
            return;
        }

        $stmt = $db->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$data['payment_id']]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            sendResponse(404, 'error', 'Không tìm thấy giao dịch');
            return;
        }

        if ($payment['status'] === 'completed') {
            sendResponse(400, 'error', 'Giao dịch đã được xác nhận');
            return;
        }

        $now = date('Y-m-d H:i:s');

        // Cập nhật payment
        $stmt = $db->prepare("UPDATE payments SET status = 'completed', paid_at = ? WHERE id = ?");
        $stmt->execute([$now, $payment['id']]);

        // Cập nhật đơn hàng
        $stmt = $db->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $stmt->execute([$payment['order_id']]);

        sendResponse(200, 'success', 'Xác nhận thanh toán thành công');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Lấy chi tiết thanh toán
 * ✅ ĐÃ SỬA: Dùng o.name thay vì o.order_code
 */
function getPayment($db, $currentUser, $id) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, o.name as order_name, o.total_amount as order_total
            FROM payments p
            INNER JOIN orders o ON p.order_id = o.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            sendResponse(404, 'error', 'Không tìm thấy giao dịch');
            return;
        }

        if ($currentUser['role'] !== 'admin' && $payment['user_id'] != $currentUser['user_id']) {
            sendResponse(403, 'error', 'Bạn không có quyền xem giao dịch này');
            return;
        }

        sendResponse(200, 'success', 'Lấy thông tin thanh toán', ['data' => $payment]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Lấy danh sách thanh toán
 * ✅ ĐÃ SỬA: Dùng o.name thay vì o.order_code
 */
function getPayments($db, $currentUser) {
    try {
        $where = "1=1";
        $params = [];

        if ($currentUser['role'] !== 'admin') {
            $where .= " AND p.user_id = ?";
            $params[] = $currentUser['user_id'];
        }

        $stmt = $db->prepare("
            SELECT p.*, o.name as order_name
            FROM payments p
            INNER JOIN orders o ON p.order_id = o.id
            WHERE $where
            ORDER BY p.created_at DESC
            LIMIT 50
        ");
        $stmt->execute($params);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, 'success', 'Lấy danh sách thanh toán', [
            'count' => count($payments),
            'data' => $payments
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

function sendResponse($statusCode, $status, $message, $data = []) {
    http_response_code($statusCode);
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit();
}
?>