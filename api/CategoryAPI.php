<?php
/**
 * RESTful API cho Category
 * Endpoint: /api/categories
 */

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load dependencies
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/CategoryModel.php';

// Initialize database and model
$database = new Database();
$db = $database->getConnection();
$categoryModel = new CategoryModel($db);

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

// Route based on HTTP method
switch ($method) {
    case 'GET':
        if ($id) {
            getCategory($categoryModel, $id);
        } else {
            getCategories($categoryModel);
        }
        break;

    case 'POST':
        createCategory($categoryModel);
        break;

    case 'PUT':
        if ($id) {
            updateCategory($categoryModel, $id);
        } else {
            sendResponse(400, 'error', 'Missing category ID');
        }
        break;

    case 'DELETE':
        if ($id) {
            deleteCategory($categoryModel, $id);
        } else {
            sendResponse(400, 'error', 'Missing category ID');
        }
        break;

    default:
        sendResponse(405, 'error', 'Method not allowed');
        break;
}

// ==================== HELPER FUNCTIONS ====================

/**
 * GET /api/categories - Lấy tất cả danh mục
 */
function getCategories($model) {
    try {
        $categories = $model->getCategories();
        sendResponse(200, 'success', 'Lấy danh sách danh mục thành công', [
            'count' => count($categories),
            'data' => $categories
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * GET /api/categories/{id} - Lấy chi tiết danh mục
 */
function getCategory($model, $id) {
    try {
        $category = $model->getCategoryById($id);
        
        if ($category) {
            sendResponse(200, 'success', 'Lấy danh mục thành công', [
                'data' => $category
            ]);
        } else {
            sendResponse(404, 'error', 'Không tìm thấy danh mục');
        }
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * POST /api/categories - Tạo danh mục mới
 * ✅ SỬA: Gọi $model->create() thay vì addCategory()
 */
function createCategory($model) {
    try {
        $data = getJsonInput();
        
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        
        // Validation
        if (empty($name)) {
            sendResponse(400, 'error', 'Tên danh mục không được để trống');
            return;
        }
        
        // ✅ GỌI ĐÚNG METHOD: create()
        $result = $model->create($name, $description);
        
        if ($result) {
            sendResponse(201, 'success', 'Tạo danh mục thành công');
        } else {
            sendResponse(500, 'error', 'Không thể tạo danh mục');
        }
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * PUT /api/categories/{id} - Cập nhật danh mục
 * ✅ SỬA: Gọi $model->update() thay vì updateCategory()
 */
function updateCategory($model, $id) {
    try {
        // Check if category exists
        $existing = $model->getCategoryById($id);
        if (!$existing) {
            sendResponse(404, 'error', 'Không tìm thấy danh mục');
            return;
        }
        
        $data = getJsonInput();
        
        $name = $data['name'] ?? $existing->name;
        $description = $data['description'] ?? $existing->description;
        
        // Validation
        if (empty($name)) {
            sendResponse(400, 'error', 'Tên danh mục không được để trống');
            return;
        }
        
        // ✅ GỌI ĐÚNG METHOD: update()
        $result = $model->update($id, $name, $description);
        
        if ($result) {
            sendResponse(200, 'success', 'Cập nhật danh mục thành công');
        } else {
            sendResponse(500, 'error', 'Không thể cập nhật danh mục');
        }
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * DELETE /api/categories/{id} - Xóa danh mục
 * ✅ SỬA: Gọi $model->delete() thay vì deleteCategory()
 */
function deleteCategory($model, $id) {
    try {
        // Check if category exists
        $existing = $model->getCategoryById($id);
        if (!$existing) {
            sendResponse(404, 'error', 'Không tìm thấy danh mục');
            return;
        }
        
        // ✅ GỌI ĐÚNG METHOD: delete()
        if ($model->delete($id)) {
            sendResponse(200, 'success', 'Xóa danh mục thành công');
        } else {
            sendResponse(500, 'error', 'Không thể xóa danh mục');
        }
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

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
    exit();
}
?>