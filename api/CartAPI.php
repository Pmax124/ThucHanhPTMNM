<?php
/**
 * RESTful API cho Cart Management
 * Endpoint: /api/cart
 * ✅ Chỉ dành cho User đã đăng nhập
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

// ✅ BẮT BUỘC ĐĂNG NHẬP
$currentUser = AuthMiddleware::requireAuth();

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'count') {
            getCartCount($db, $currentUser);
        } elseif ($action === 'total') {
            getCartTotal($db, $currentUser);
        } else {
            getCart($db, $currentUser);
        }
        break;
        
    case 'POST':
        if ($action === 'add') {
            addToCart($db, $currentUser);
        } elseif ($action === 'clear') {
            clearCart($db, $currentUser);
        } else {
            sendResponse(400, 'error', 'Action không hợp lệ');
        }
        break;
        
    case 'PUT':
        if ($id) {
            updateCartItem($db, $currentUser, $id);
        } else {
            sendResponse(400, 'error', 'Missing cart item ID');
        }
        break;
        
    case 'DELETE':
        if ($id) {
            removeFromCart($db, $currentUser, $id);
        } elseif ($action === 'clear') {
            clearCart($db, $currentUser);
        } else {
            sendResponse(400, 'error', 'Missing cart item ID');
        }
        break;
        
    default:
        sendResponse(405, 'error', 'Method not allowed');
}

/**
 * Lấy giỏ hàng của user
 */
function getCart($db, $currentUser) {
    try {
        $stmt = $db->prepare("
            SELECT 
                c.id,
                c.product_id,
                c.quantity,
                c.added_at,
                p.name as product_name,
                p.price,
                p.image,
                p.stock,
                (p.price * c.quantity) as subtotal,
                cat.name as category_name
            FROM cart c
            INNER JOIN product p ON c.product_id = p.id
            LEFT JOIN category cat ON p.category_id = cat.id
            WHERE c.user_id = ?
            ORDER BY c.added_at DESC
        ");
        $stmt->execute([$currentUser['user_id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tính tổng
        $totalAmount = 0;
        $totalItems = 0;
        foreach ($items as $item) {
            $totalAmount += $item['subtotal'];
            $totalItems += $item['quantity'];
        }

        sendResponse(200, 'success', 'Lấy giỏ hàng thành công', [
            'count' => count($items),
            'total_items' => $totalItems,
            'total_amount' => $totalAmount,
            'data' => $items
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Đếm số lượng sản phẩm trong giỏ
 */
function getCartCount($db, $currentUser) {
    try {
        $stmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$currentUser['user_id']]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        sendResponse(200, 'success', 'Lấy số lượng thành công', [
            'count' => (int)$count
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Tính tổng tiền giỏ hàng
 */
function getCartTotal($db, $currentUser) {
    try {
        $stmt = $db->prepare("
            SELECT 
                COALESCE(SUM(p.price * c.quantity), 0) as total,
                COALESCE(SUM(c.quantity), 0) as items
            FROM cart c
            INNER JOIN product p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$currentUser['user_id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        sendResponse(200, 'success', 'Tính tổng tiền thành công', [
            'total' => (float)$result['total'],
            'items' => (int)$result['items']
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Thêm sản phẩm vào giỏ hàng
 */
function addToCart($db, $currentUser) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['product_id']) || empty($data['quantity'])) {
            sendResponse(400, 'error', 'Thiếu product_id hoặc quantity');
            return;
        }

        $productId = (int)$data['product_id'];
        $quantity = (int)$data['quantity'];

        // ✅ Kiểm tra số lượng > 0
        if ($quantity <= 0) {
            sendResponse(400, 'error', 'Số lượng phải lớn hơn 0');
            return;
        }

        // ✅ Kiểm tra sản phẩm tồn tại
        $stmt = $db->prepare("SELECT id, name, price, stock FROM product WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            sendResponse(404, 'error', 'Sản phẩm không tồn tại');
            return;
        }

        // ✅ Kiểm tra còn hàng
        if ($product['stock'] !== null && $product['stock'] < $quantity) {
            sendResponse(400, 'error', "Sản phẩm '{$product['name']}' chỉ còn {$product['stock']} trong kho");
            return;
        }

        // Kiểm tra đã có trong giỏ chưa
        $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$currentUser['user_id'], $productId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Cập nhật số lượng
            $newQuantity = $existing['quantity'] + $quantity;
            
            // Kiểm tra stock
            if ($product['stock'] !== null && $product['stock'] < $newQuantity) {
                sendResponse(400, 'error', "Không đủ hàng. Chỉ còn {$product['stock']} sản phẩm");
                return;
            }
            
            $stmt = $db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newQuantity, $existing['id']]);
            
            sendResponse(200, 'success', 'Đã cập nhật số lượng trong giỏ hàng', [
                'cart_id' => $existing['id'],
                'quantity' => $newQuantity
            ]);
        } else {
            // Thêm mới
            $stmt = $db->prepare("INSERT INTO cart (user_id, product_id, quantity, added_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$currentUser['user_id'], $productId, $quantity]);
            
            sendResponse(201, 'success', 'Đã thêm vào giỏ hàng', [
                'cart_id' => $db->lastInsertId(),
                'quantity' => $quantity
            ]);
        }
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Cập nhật số lượng sản phẩm trong giỏ
 */
function updateCartItem($db, $currentUser, $cartId) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['quantity'])) {
            sendResponse(400, 'error', 'Thiếu quantity');
            return;
        }

        $quantity = (int)$data['quantity'];

        // ✅ Kiểm tra số lượng > 0
        if ($quantity <= 0) {
            sendResponse(400, 'error', 'Số lượng phải lớn hơn 0');
            return;
        }

        // Kiểm tra cart item thuộc về user
        $stmt = $db->prepare("SELECT c.*, p.stock FROM cart c INNER JOIN product p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
        $stmt->execute([$cartId, $currentUser['user_id']]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cartItem) {
            sendResponse(404, 'error', 'Không tìm thấy sản phẩm trong giỏ');
            return;
        }

        // Kiểm tra stock
        if ($cartItem['stock'] !== null && $cartItem['stock'] < $quantity) {
            sendResponse(400, 'error', "Chỉ còn {$cartItem['stock']} sản phẩm trong kho");
            return;
        }

        $stmt = $db->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$quantity, $cartId]);

        sendResponse(200, 'success', 'Cập nhật số lượng thành công', [
            'quantity' => $quantity
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Xóa 1 sản phẩm khỏi giỏ
 */
function removeFromCart($db, $currentUser, $cartId) {
    try {
        $stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cartId, $currentUser['user_id']]);

        if ($stmt->rowCount() === 0) {
            sendResponse(404, 'error', 'Không tìm thấy sản phẩm trong giỏ');
            return;
        }

        sendResponse(200, 'success', 'Đã xóa khỏi giỏ hàng');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Xóa toàn bộ giỏ hàng
 */
function clearCart($db, $currentUser) {
    try {
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$currentUser['user_id']]);

        sendResponse(200, 'success', 'Đã xóa toàn bộ giỏ hàng', [
            'deleted_count' => $stmt->rowCount()
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