<?php
/**
 * RESTful API cho Product Management
 * Endpoint: /api/products
 * ✅ ĐÃ THÊM: Tìm kiếm, lọc theo danh mục, sắp xếp giá, lọc khoảng giá, validation
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

switch ($method) {
    case 'GET':
        if ($id) {
            getProduct($db, $id);
        } else {
            getProducts($db);  // ✅ Hỗ trợ search, filter, sort
        }
        break;
        
    case 'POST':
        AuthMiddleware::requireAdmin();
        createProduct($db);
        break;
        
    case 'PUT':
        AuthMiddleware::requireAdmin();
        if ($id) {
            updateProduct($db, $id);
        } else {
            sendResponse(400, 'error', 'Missing product ID');
        }
        break;
        
    case 'DELETE':
        AuthMiddleware::requireAdmin();
        if ($id) {
            deleteProduct($db, $id);
        } else {
            sendResponse(400, 'error', 'Missing product ID');
        }
        break;
        
    default:
        sendResponse(405, 'error', 'Method not allowed');
}

/**
 * ✅ Lấy danh sách sản phẩm với TÌM KIẾM, LỌC, SẮP XẾP
 * 
 * Các tham số hỗ trợ:
 * - search: Tìm theo tên hoặc mô tả
 * - category_id: Lọc theo danh mục
 * - min_price: Giá tối thiểu
 * - max_price: Giá tối đa
 * - sort: price_asc, price_desc, name_asc, name_desc, newest, oldest
 * - page: Số trang (mặc định 1)
 * - limit: Số sản phẩm mỗi trang (mặc định 20, tối đa 100)
 * 
 * Ví dụ:
 * GET /api/products?search=iphone&category_id=1&sort=price_asc&min_price=1000000&max_price=50000000&page=1
 */
function getProducts($db) {
    try {
        // ✅ Lấy các tham số
        $search = trim($_GET['search'] ?? '');
        $categoryId = $_GET['category_id'] ?? '';
        $sort = $_GET['sort'] ?? '';
        $minPrice = $_GET['min_price'] ?? '';
        $maxPrice = $_GET['max_price'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $where = ["1=1"];
        $params = [];

        // ✅ Tìm kiếm theo tên hoặc mô tả
        if ($search !== '') {
            $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $kw = "%$search%";
            $params[] = $kw;
            $params[] = $kw;
        }

        // ✅ Lọc theo danh mục
        if ($categoryId !== '' && is_numeric($categoryId)) {
            // Kiểm tra danh mục có tồn tại không
            $stmtCheck = $db->prepare("SELECT id FROM category WHERE id = ?");
            $stmtCheck->execute([$categoryId]);
            if ($stmtCheck->fetch()) {
                $where[] = "p.category_id = ?";
                $params[] = (int)$categoryId;
            }
        }

        // ✅ Lọc theo khoảng giá
        if ($minPrice !== '' && is_numeric($minPrice)) {
            $where[] = "p.price >= ?";
            $params[] = (float)$minPrice;
        }
        if ($maxPrice !== '' && is_numeric($maxPrice)) {
            $where[] = "p.price <= ?";
            $params[] = (float)$maxPrice;
        }

        // Kiểm tra min_price <= max_price
        if ($minPrice !== '' && $maxPrice !== '' && (float)$minPrice > (float)$maxPrice) {
            sendResponse(400, 'error', 'min_price phải nhỏ hơn hoặc bằng max_price');
            return;
        }

        $whereClause = implode(" AND ", $where);

        // ✅ Sắp xếp
        $orderBy = "p.id DESC";  // Mặc định: mới nhất
        $allowedSorts = [
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'name_asc'   => 'p.name ASC',
            'name_desc'  => 'p.name DESC',
            'newest'     => 'p.created_at DESC',
            'oldest'     => 'p.created_at ASC',
        ];
        
        if ($sort !== '' && isset($allowedSorts[$sort])) {
            $orderBy = $allowedSorts[$sort];
        }

        // ✅ Query danh sách
        $sql = "SELECT p.*, c.name as category_name 
                FROM product p
                LEFT JOIN category c ON p.category_id = c.id
                WHERE $whereClause
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ✅ Đếm tổng số
        $countSql = "SELECT COUNT(*) as total FROM product p WHERE $whereClause";
        $stmt2 = $db->prepare($countSql);
        $stmt2->execute($params);
        $total = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];

        sendResponse(200, 'success', 'Lấy danh sách sản phẩm thành công', [
            'count' => count($products),
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sort
            ],
            'data' => $products
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Lấy chi tiết sản phẩm
 */
function getProduct($db, $id) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name 
            FROM product p
            LEFT JOIN category c ON p.category_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            sendResponse(404, 'error', 'Không tìm thấy sản phẩm');
            return;
        }

        sendResponse(200, 'success', 'Lấy thông tin sản phẩm thành công', [
            'data' => $product
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * ✅ Tạo sản phẩm mới - CHỈ ADMIN - VALIDATION ĐẦY ĐỦ
 */
function createProduct($db) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        // ✅ Validation: Tên không rỗng
        if (empty($data['name'])) {
            sendResponse(400, 'error', 'Tên sản phẩm không được rỗng');
            return;
        }
        $name = trim($data['name']);
        if (strlen($name) < 3) {
            sendResponse(400, 'error', 'Tên sản phẩm phải có ít nhất 3 ký tự');
            return;
        }
        if (strlen($name) > 255) {
            sendResponse(400, 'error', 'Tên sản phẩm không được vượt quá 255 ký tự');
            return;
        }

        // ✅ Validation: Giá phải là số > 0
        if (!isset($data['price']) || $data['price'] === '') {
            sendResponse(400, 'error', 'Thiếu giá sản phẩm');
            return;
        }
        if (!is_numeric($data['price'])) {
            sendResponse(400, 'error', 'Giá phải là số');
            return;
        }
        if ((float)$data['price'] <= 0) {
            sendResponse(400, 'error', 'Giá phải lớn hơn 0');
            return;
        }

        // ✅ Validation: Danh mục phải hợp lệ
        if (!empty($data['category_id'])) {
            $stmt = $db->prepare("SELECT id FROM category WHERE id = ?");
            $stmt->execute([$data['category_id']]);
            if ($stmt->rowCount() === 0) {
                sendResponse(400, 'error', 'Danh mục không tồn tại');
                return;
            }
        }

        // ✅ Validation: Hình ảnh đúng định dạng (nếu có)
        if (!empty($data['image'])) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($data['image'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                sendResponse(400, 'error', 'Hình ảnh phải có định dạng: ' . implode(', ', $allowedExtensions));
                return;
            }
        }

        $stmt = $db->prepare("
            INSERT INTO product (name, description, price, category_id, image, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $name,
            $data['description'] ?? '',
            (float)$data['price'],
            !empty($data['category_id']) ? (int)$data['category_id'] : null,
            $data['image'] ?? ''
        ]);

        $productId = $db->lastInsertId();

        sendResponse(201, 'success', 'Tạo sản phẩm thành công', [
            'product_id' => $productId
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * ✅ Cập nhật sản phẩm - CHỈ ADMIN - VALIDATION ĐẦY ĐỦ
 */
function updateProduct($db, $id) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $db->prepare("SELECT * FROM product WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            sendResponse(404, 'error', 'Không tìm thấy sản phẩm');
            return;
        }

        $fields = [];
        $params = [];

        // ✅ Validation tên
        if (isset($data['name'])) {
            $name = trim($data['name']);
            if (empty($name)) {
                sendResponse(400, 'error', 'Tên sản phẩm không được rỗng');
                return;
            }
            if (strlen($name) < 3) {
                sendResponse(400, 'error', 'Tên sản phẩm phải có ít nhất 3 ký tự');
                return;
            }
            if (strlen($name) > 255) {
                sendResponse(400, 'error', 'Tên sản phẩm không được vượt quá 255 ký tự');
                return;
            }
            $fields[] = "name = ?";
            $params[] = $name;
        }

        // ✅ Validation giá
        if (isset($data['price'])) {
            if (!is_numeric($data['price'])) {
                sendResponse(400, 'error', 'Giá phải là số');
                return;
            }
            if ((float)$data['price'] <= 0) {
                sendResponse(400, 'error', 'Giá phải lớn hơn 0');
                return;
            }
            $fields[] = "price = ?";
            $params[] = (float)$data['price'];
        }

        // ✅ Validation danh mục
        if (isset($data['category_id'])) {
            if (!empty($data['category_id'])) {
                $stmt = $db->prepare("SELECT id FROM category WHERE id = ?");
                $stmt->execute([$data['category_id']]);
                if ($stmt->rowCount() === 0) {
                    sendResponse(400, 'error', 'Danh mục không tồn tại');
                    return;
                }
                $fields[] = "category_id = ?";
                $params[] = (int)$data['category_id'];
            } else {
                $fields[] = "category_id = NULL";
            }
        }

        // ✅ Validation hình ảnh
        if (isset($data['image']) && !empty($data['image'])) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($data['image'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                sendResponse(400, 'error', 'Hình ảnh phải có định dạng: ' . implode(', ', $allowedExtensions));
                return;
            }
            $fields[] = "image = ?";
            $params[] = $data['image'];
        }

        // Các trường khác
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $params[] = $data['description'];
        }

        if (empty($fields)) {
            sendResponse(400, 'error', 'Không có dữ liệu để cập nhật');
            return;
        }

        $fields[] = "updated_at = NOW()";
        $params[] = $id;

        $sql = "UPDATE product SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        sendResponse(200, 'success', 'Cập nhật sản phẩm thành công');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Xóa sản phẩm - CHỈ ADMIN
 */
function deleteProduct($db, $id) {
    try {
        $stmt = $db->prepare("SELECT * FROM product WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            sendResponse(404, 'error', 'Không tìm thấy sản phẩm');
            return;
        }

        $stmt = $db->prepare("DELETE FROM product WHERE id = ?");
        $stmt->execute([$id]);

        sendResponse(200, 'success', 'Xóa sản phẩm thành công');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * Send JSON response
 */
function sendResponse($statusCode, $status, $message, $data = []) {
    http_response_code($statusCode);
    $response = array_merge(['status' => $status, 'message' => $message], $data);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}
?>