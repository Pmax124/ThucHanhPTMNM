<?php
/**
 * RESTful API cho Product
 * Endpoint: /api/products
 * 
 * GET    /api/products          → Lấy danh sách
 * GET    /api/products/{id}     → Lấy chi tiết
 * POST   /api/products          → Tạo mới
 * PUT    /api/products/{id}     → Cập nhật
 * DELETE /api/products/{id}     → Xóa
 * GET    /api/products/search   → Tìm kiếm/lọc
 */

// ===== CORS HEADERS =====
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ===== LOAD DEPENDENCIES =====
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/ProductModel.php';

$database = new Database();
$db = $database->getConnection();
$productModel = new ProductModel($db);

// ===== PARSE REQUEST =====
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Lấy ID từ URL (ví dụ: /api/products/5 → id = 5)
$pathParts = explode('/', trim($path, '/'));
$id = isset($pathParts[2]) && is_numeric($pathParts[2]) ? (int)$pathParts[2] : null;

// Lấy action từ query string (cho search)
$action = $_GET['action'] ?? '';

// ===== ROUTING =====
try {
    switch ($method) {
        case 'GET':
            if ($action === 'search') {
                handleSearch($productModel);
            } elseif ($id !== null) {
                handleGetOne($productModel, $id);
            } else {
                handleGetAll($productModel);
            }
            break;

        case 'POST':
            handleCreate($productModel);
            break;

        case 'PUT':
            if ($id === null) {
                sendResponse(400, 'error', 'Missing product ID');
            }
            handleUpdate($productModel, $id);
            break;

        case 'DELETE':
            if ($id === null) {
                sendResponse(400, 'error', 'Missing product ID');
            }
            handleDelete($productModel, $id);
            break;

        default:
            sendResponse(405, 'error', 'Method not allowed');
    }
} catch (Exception $e) {
    sendResponse(500, 'error', 'Server error: ' . $e->getMessage());
}

// ===== HANDLER FUNCTIONS =====

/**
 * GET /api/products - Lấy tất cả sản phẩm
 */
function handleGetAll($model) {
    $products = $model->getProducts();
    sendResponse(200, 'success', 'Lấy danh sách sản phẩm thành công', [
        'count' => count($products),
        'data' => $products
    ]);
}

/**
 * GET /api/products/{id} - Lấy 1 sản phẩm
 */
function handleGetOne($model, $id) {
    $product = $model->getProductById($id);
    if ($product) {
        sendResponse(200, 'success', 'Lấy sản phẩm thành công', ['data' => $product]);
    } else {
        sendResponse(404, 'error', 'Không tìm thấy sản phẩm');
    }
}

/**
 * GET /api/products?action=search&keyword=...&category_id=...&price_min=...&price_max=...
 */
function handleSearch($model) {
    $keyword = $_GET['keyword'] ?? '';
    $category_id = (int)($_GET['category_id'] ?? 0);
    $price_min = (float)($_GET['price_min'] ?? 0);
    $price_max = (float)($_GET['price_max'] ?? 0);

    $products = $model->getProductsFiltered($keyword, $category_id, $price_min, $price_max);
    sendResponse(200, 'success', 'Tìm kiếm thành công', [
        'count' => count($products),
        'data' => $products
    ]);
}

/**
 * POST /api/products - Tạo sản phẩm mới
 */
function handleCreate($model) {
    $data = getJsonInput();

    $result = $model->addProduct(
        $data['name'] ?? '',
        $data['description'] ?? '',
        $data['price'] ?? 0,
        $data['category_id'] ?? 0,
        $data['image'] ?? ''
    );

    if ($result === true) {
        sendResponse(201, 'success', 'Tạo sản phẩm thành công');
    } elseif (is_array($result)) {
        // Validation errors
        sendResponse(400, 'error', 'Dữ liệu không hợp lệ', ['errors' => $result]);
    } else {
        sendResponse(500, 'error', 'Không thể tạo sản phẩm');
    }
}

/**
 * PUT /api/products/{id} - Cập nhật sản phẩm
 */
function handleUpdate($model, $id) {
    // Kiểm tra sản phẩm tồn tại
    $existing = $model->getProductById($id);
    if (!$existing) {
        sendResponse(404, 'error', 'Không tìm thấy sản phẩm');
        return;
    }

    $data = getJsonInput();

    $result = $model->updateProduct(
        $id,
        $data['name'] ?? $existing->name,
        $data['description'] ?? $existing->description,
        $data['price'] ?? $existing->price,
        $data['category_id'] ?? $existing->category_id,
        $data['image'] ?? $existing->image
    );

    if ($result) {
        sendResponse(200, 'success', 'Cập nhật sản phẩm thành công');
    } else {
        sendResponse(500, 'error', 'Không thể cập nhật sản phẩm');
    }
}

/**
 * DELETE /api/products/{id} - Xóa sản phẩm
 */
function handleDelete($model, $id) {
    $existing = $model->getProductById($id);
    if (!$existing) {
        sendResponse(404, 'error', 'Không tìm thấy sản phẩm');
        return;
    }

    if ($model->deleteProduct($id)) {
        sendResponse(200, 'success', 'Xóa sản phẩm thành công');
    } else {
        sendResponse(500, 'error', 'Không thể xóa sản phẩm');
    }
}

// ===== HELPER FUNCTIONS =====

/**
 * Đọc JSON từ request body
 */
function getJsonInput() {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput, true);
    return is_array($data) ? $data : [];
}

/**
 * Gửi response JSON chuẩn
 */
function sendResponse($statusCode, $status, $message, $data = []) {
    http_response_code($statusCode);
    $response = array_merge([
        'status' => $status,
        'message' => $message
    ], $data);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>