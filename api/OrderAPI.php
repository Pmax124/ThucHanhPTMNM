<?php
/**
 * RESTful API cho Order Management
 * Endpoint: /api/orders
 * ✅ ĐÃ SỬA: Thêm cột phone vào INSERT
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'statistics' || $action === 'revenue') {
            AuthMiddleware::requireAdmin();
            if ($action === 'statistics') {
                getStatistics($db);
            } else {
                getRevenue($db);
            }
        } elseif ($id) {
            $currentUser = AuthMiddleware::requireAuth();
            getOrder($db, $id, $currentUser);
        } else {
            $currentUser = AuthMiddleware::requireAuth();
            getOrders($db, $currentUser);
        }
        break;
        
    case 'POST':
        $currentUser = AuthMiddleware::requireAuth();
        if ($action === 'checkout') {
            checkoutFromCart($db, $currentUser);
        } else {
            createOrder($db, $currentUser);
        }
        break;
        
    case 'PUT':
        AuthMiddleware::requireAdmin();
        if ($id) {
            updateOrder($db, $id);
        } else {
            sendResponse(400, 'error', 'Missing order ID');
        }
        break;
        
    case 'DELETE':
        AuthMiddleware::requireAdmin();
        if ($id) {
            deleteOrder($db, $id);
        } else {
            sendResponse(400, 'error', 'Missing order ID');
        }
        break;
        
    default:
        sendResponse(405, 'error', 'Method not allowed');
}

/**
 * Lấy danh sách đơn hàng
 */
function getOrders($db, $currentUser) {
    try {
        $status = $_GET['status'] ?? '';
        $keyword = $_GET['search'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $where = ["1=1"];
        $params = [];

        if ($currentUser['role'] !== 'admin') {
            $where[] = "o.email = ?";
            $params[] = $currentUser['email'] ?? '';
        }

        if ($status) {
            $where[] = "o.status = ?";
            $params[] = $status;
        }

        if ($keyword) {
            $where[] = "(o.name LIKE ? OR o.email LIKE ? OR o.address LIKE ?)";
            $kw = "%$keyword%";
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        $whereClause = implode(" AND ", $where);

        $sql = "SELECT o.* FROM orders o WHERE $whereClause ORDER BY o.created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "SELECT COUNT(*) as total FROM orders o WHERE $whereClause";
        $stmt2 = $db->prepare($countSql);
        $stmt2->execute($params);
        $total = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];

        sendResponse(200, 'success', 'Lấy danh sách đơn hàng thành công', [
            'count' => count($orders),
            'total' => (int)$total,
            'page' => $page,
            'total_pages' => ceil($total / $limit),
            'data' => $orders
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Lấy chi tiết đơn hàng
 */
function getOrder($db, $id, $currentUser) {
    try {
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            sendResponse(404, 'error', 'Không tìm thấy đơn hàng');
            return;
        }

        if ($currentUser['role'] !== 'admin' && $order['email'] != ($currentUser['email'] ?? '')) {
            sendResponse(403, 'error', 'Bạn không có quyền xem đơn hàng này');
            return;
        }

        try {
            $stmt = $db->prepare("
                SELECT od.*, p.image as product_image 
                FROM order_details od
                LEFT JOIN product p ON od.product_id = p.id
                WHERE od.order_id = ?
            ");
            $stmt->execute([$id]);
            $order['details'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $order['details'] = [];
        }

        sendResponse(200, 'success', 'Lấy thông tin đơn hàng thành công', [
            'data' => $order
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Tạo đơn hàng thủ công
 * ✅ ĐÃ SỬA: Thêm cột phone
 */
function createOrder($db, $currentUser) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $db->prepare("SELECT * FROM account WHERE id = ?");
        $stmt->execute([$currentUser['user_id']]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $customerName = $data['customer_name'] ?? $userInfo['fullname'] ?? $userInfo['username'] ?? '';
        $customerEmail = $data['customer_email'] ?? $userInfo['email'] ?? '';
        $customerPhone = $data['customer_phone'] ?? $userInfo['phone'] ?? '';  // ✅ THÊM
        $customerCity = $data['city'] ?? '';
        $customerAddress = $data['customer_address'] ?? $userInfo['address'] ?? '';

        if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($customerAddress)) {
            sendResponse(400, 'error', 'Thiếu thông tin bắt buộc (name, email, phone, address)');
            return;
        }

        if (empty($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
            sendResponse(400, 'error', 'Đơn hàng phải có ít nhất 1 sản phẩm');
            return;
        }

        $totalAmount = 0;
        foreach ($data['items'] as $item) {
            $totalAmount += ($item['price'] * $item['quantity']);
        }

        $discountAmount = $data['discount_amount'] ?? 0;
        $finalTotal = $totalAmount - $discountAmount;

        // ✅ INSERT có cột phone
        $stmt = $db->prepare("
            INSERT INTO orders (
                name, email, phone, city, address, note,
                payment_method, total_amount, discount_amount, 
                voucher_code, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $customerName,
            $customerEmail,
            $customerPhone,  // ✅ THÊM
            $customerCity,
            $customerAddress,
            $data['notes'] ?? $data['note'] ?? '',
            $data['payment_method'] ?? 'cod',
            $finalTotal,
            $discountAmount,
            $data['voucher_code'] ?? '',
            'pending'
        ]);

        $orderId = $db->lastInsertId();

        try {
            $stmtDetail = $db->prepare("
                INSERT INTO order_details (order_id, product_id, product_name, quantity, price, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($data['items'] as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $stmtDetail->execute([
                    $orderId,
                    $item['product_id'] ?? null,
                    $item['product_name'],
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                ]);
            }
        } catch (Exception $e) {
            // Bỏ qua
        }

        sendResponse(201, 'success', 'Tạo đơn hàng thành công', [
            'order_id' => $orderId,
            'order_code' => 'ORD-' . $orderId
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * ✅ MỚI: Tạo đơn hàng từ giỏ hàng
 * ✅ ĐÃ SỬA: Thêm cột phone
 */
function checkoutFromCart($db, $currentUser) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $db->prepare("
            SELECT c.*, p.name, p.price, p.stock 
            FROM cart c 
            INNER JOIN product p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$currentUser['user_id']]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cartItems)) {
            sendResponse(400, 'error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi đặt hàng.');
            return;
        }

        foreach ($cartItems as $item) {
            if ($item['stock'] !== null && $item['stock'] < $item['quantity']) {
                sendResponse(400, 'error', "Sản phẩm '{$item['name']}' không đủ hàng (còn {$item['stock']}, cần {$item['quantity']})");
                return;
            }
        }

        $stmt = $db->prepare("SELECT * FROM account WHERE id = ?");
        $stmt->execute([$currentUser['user_id']]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        $customerName = $data['customer_name'] ?? $userInfo['fullname'] ?? $userInfo['username'] ?? '';
        $customerEmail = $data['customer_email'] ?? $userInfo['email'] ?? '';
        $customerPhone = $data['customer_phone'] ?? $userInfo['phone'] ?? '';  // ✅ THÊM
        $customerCity = $data['city'] ?? '';
        $customerAddress = $data['customer_address'] ?? $userInfo['address'] ?? '';

        if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($customerAddress)) {
            sendResponse(400, 'error', 'Vui lòng cập nhật đầy đủ thông tin (họ tên, email, SĐT, địa chỉ)');
            return;
        }

        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        $discountAmount = $data['discount_amount'] ?? 0;
        $finalTotal = $totalAmount - $discountAmount;

        // ✅ INSERT có cột phone
        $stmt = $db->prepare("
            INSERT INTO orders (
                name, email, phone, city, address, note,
                payment_method, total_amount, discount_amount, 
                voucher_code, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $customerName,
            $customerEmail,
            $customerPhone,  // ✅ THÊM
            $customerCity,
            $customerAddress,
            $data['notes'] ?? $data['note'] ?? '',
            $data['payment_method'] ?? 'cod',
            $finalTotal,
            $discountAmount,
            $data['voucher_code'] ?? '',
            'pending'
        ]);

        $orderId = $db->lastInsertId();

        try {
            $stmtDetail = $db->prepare("
                INSERT INTO order_details (order_id, product_id, product_name, quantity, price, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $stmtDetail->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $subtotal
                ]);

                if ($item['stock'] !== null) {
                    $stmt = $db->prepare("UPDATE product SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['product_id']]);
                }
            }
        } catch (Exception $e) {
            // Bỏ qua
        }

        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$currentUser['user_id']]);

        sendResponse(201, 'success', 'Đặt hàng thành công! Giỏ hàng đã được làm trống.', [
            'order_id' => $orderId,
            'order_code' => 'ORD-' . $orderId,
            'total_amount' => $finalTotal,
            'items_count' => count($cartItems)
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Cập nhật đơn hàng - CHỈ ADMIN
 * ✅ ĐÃ SỬA: Thêm phone vào allowedFields
 */
function updateOrder($db, $id) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            sendResponse(404, 'error', 'Không tìm thấy đơn hàng');
            return;
        }

        $fields = [];
        $params = [];

        // ✅ Thêm phone vào allowedFields
        $allowedFields = ['status', 'payment_method', 'note', 'discount_amount', 'voucher_code', 'name', 'email', 'phone', 'city', 'address'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            sendResponse(400, 'error', 'Không có dữ liệu để cập nhật');
            return;
        }

        $fields[] = "updated_at = NOW()";
        $params[] = $id;

        $sql = "UPDATE orders SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        sendResponse(200, 'success', 'Cập nhật đơn hàng thành công');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Xóa đơn hàng - CHỈ ADMIN
 */
function deleteOrder($db, $id) {
    try {
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            sendResponse(404, 'error', 'Không tìm thấy đơn hàng');
            return;
        }

        try {
            $stmt = $db->prepare("DELETE FROM order_details WHERE order_id = ?");
            $stmt->execute([$id]);
        } catch (Exception $e) {
            // Bỏ qua
        }

        $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);

        sendResponse(200, 'success', 'Xóa đơn hàng thành công');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Thống kê đơn hàng - CHỈ ADMIN
 */
function getStatistics($db) {
    try {
        $stmt = $db->query("
            SELECT 
                status,
                COUNT(*) as count,
                COALESCE(SUM(total_amount), 0) as total_revenue
            FROM orders
            GROUP BY status
        ");
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, 'success', 'Lấy thống kê thành công', [
            'count' => count($stats),
            'data' => $stats
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Thống kê doanh thu - CHỈ ADMIN
 */
function getRevenue($db) {
    try {
        $period = $_GET['period'] ?? 'month';

        $dateFormat = '%Y-%m';
        if ($period === 'day') {
            $dateFormat = '%Y-%m-%d';
        } elseif ($period === 'week') {
            $dateFormat = '%Y-%u';
        } elseif ($period === 'year') {
            $dateFormat = '%Y';
        }

        $stmt = $db->prepare("
            SELECT 
                DATE_FORMAT(created_at, ?) as period,
                COUNT(*) as order_count,
                COALESCE(SUM(total_amount), 0) as revenue,
                COALESCE(AVG(total_amount), 0) as avg_order_value
            FROM orders
            WHERE status = 'completed'
            GROUP BY period
            ORDER BY period DESC
            LIMIT 12
        ");
        $stmt->execute([$dateFormat]);
        $revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, 'success', 'Lấy doanh thu thành công', [
            'period' => $period,
            'data' => $revenue
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

function sendResponse($statusCode, $status, $message, $data = []) {
    http_response_code($statusCode);
    $response = array_merge(['status' => $status, 'message' => $message], $data);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}
?>