<?php
/**
 * RESTful API cho Account/User
 * Endpoint: /api/accounts
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
        AuthMiddleware::requireAdmin();  // Chỉ admin xem thống kê
        if ($action === 'statistics') {
            
            getStatistics($db);
        } elseif ($id) {
            getAccount($db, $id);
        } else {
            getAccounts($db);
        }
        break;
    case 'POST':
         AuthMiddleware::requireAdmin();  // Chỉ admin xem thống kê
        createAccount($db);
        break;
    case 'PUT':
         AuthMiddleware::requireAdmin();  // Chỉ admin xem thống kê
        if ($id) {
            updateAccount($db, $id);
        } else {
            sendResponse(400, 'error', 'Missing account ID');
        }
        break;
    case 'DELETE':
         AuthMiddleware::requireAdmin();  // Chỉ admin xem thống kê
        if ($id) {
            deleteAccount($db, $id);
        } else {
            sendResponse(400, 'error', 'Missing account ID');
        }
        break;
    default:
        sendResponse(405, 'error', 'Method not allowed');
}

function getAccounts($db) {
    try {
        $keyword = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $where = ["1=1"];
        $params = [];
        
        if ($keyword) {
            $where[] = "(username LIKE ? OR fullname LIKE ? OR email LIKE ?)";
            $kw = "%$keyword%";
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }
        if ($role) {
            $where[] = "role = ?";
            $params[] = $role;
        }
        if ($status !== '') {
            $where[] = "status = ?";
            $params[] = $status;
        }
        
        $whereClause = implode(" AND ", $where);
        
        // ✅ SỬA: Không dùng prepare cho LIMIT/OFFSET
        $sql = "SELECT * FROM account WHERE $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $accounts = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $sql2 = "SELECT COUNT(*) as total FROM account WHERE $whereClause";
        $stmt2 = $db->prepare($sql2);
        $stmt2->execute($params);
        $total = $stmt2->fetch(PDO::FETCH_OBJ)->total;

        sendResponse(200, 'success', 'Lấy danh sách tài khoản thành công', [
            'count' => count($accounts),
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $limit),
            'data' => $accounts
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

function getAccount($db, $id) {
    try {
        $stmt = $db->prepare("SELECT * FROM account WHERE id = ?");
        $stmt->execute([$id]);
        $account = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($account) {
            sendResponse(200, 'success', 'Lấy thông tin tài khoản thành công', ['data' => $account]);
        } else {
            sendResponse(404, 'error', 'Không tìm thấy tài khoản');
        }
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

function getStatistics($db) {
    try {
        $stmt = $db->query("SELECT role, status, COUNT(*) as count FROM account GROUP BY role, status");
        $stats = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        sendResponse(200, 'success', 'Lấy thống kê thành công', [
            'count' => count($stats),
            'data' => $stats
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

function createAccount($db) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            sendResponse(400, 'error', 'Username, password và email là bắt buộc');
            return;
        }

        $stmt = $db->prepare("SELECT id FROM account WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['email']]);
        if ($stmt->rowCount() > 0) {
            sendResponse(400, 'error', 'Username hoặc email đã tồn tại');
            return;
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO account (username, password, fullname, email, phone, address, role, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $data['username'],
            $hashedPassword,
            $data['fullname'] ?? '',
            $data['email'],
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['role'] ?? 'user',
            $data['status'] ?? 1
        ]);

        sendResponse(201, 'success', 'Tạo tài khoản thành công');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

function updateAccount($db, $id) {
    try {
        $stmt = $db->prepare("SELECT * FROM account WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch(PDO::FETCH_OBJ)) {
            sendResponse(404, 'error', 'Không tìm thấy tài khoản');
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        
        $fields = [];
        $params = [];
        
        if (isset($data['fullname'])) {
            $fields[] = "fullname = ?";
            $params[] = $data['fullname'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        if (isset($data['phone'])) {
            $fields[] = "phone = ?";
            $params[] = $data['phone'];
        }
        if (isset($data['address'])) {
            $fields[] = "address = ?";
            $params[] = $data['address'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            sendResponse(400, 'error', 'Không có dữ liệu để cập nhật');
            return;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE account SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        sendResponse(200, 'success', 'Cập nhật tài khoản thành công');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

function deleteAccount($db, $id) {
    try {
        $stmt = $db->prepare("SELECT * FROM account WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch(PDO::FETCH_OBJ)) {
            sendResponse(404, 'error', 'Không tìm thấy tài khoản');
            return;
        }

        $stmt = $db->prepare("DELETE FROM account WHERE id = ?");
        $stmt->execute([$id]);
        
        sendResponse(200, 'success', 'Xóa tài khoản thành công');
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