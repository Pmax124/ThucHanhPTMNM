<?php
/**
 * RESTful API cho Authentication & Account Management
 * Endpoint: /api/auth
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/jwt_config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        switch ($action) {
            case 'login':
                login($db);
                break;
            case 'register':
                register($db);
                break;
            case 'forgot-password':
                forgotPassword($db);
                break;
            case 'reset-password':
                resetPassword($db);
                break;
            default:
                sendResponse(400, 'error', 'Action không hợp lệ');
        }
        break;
        
    case 'GET':
        switch ($action) {
            case 'me':
                getMe($db);
                break;
            default:
                sendResponse(400, 'error', 'Action không hợp lệ');
        }
        break;
        
    case 'PUT':
        switch ($action) {
            case 'profile':
                updateProfile($db);
                break;
            case 'change-password':
                changePassword($db);
                break;
            default:
                sendResponse(400, 'error', 'Action không hợp lệ');
        }
        break;
        
    default:
        sendResponse(405, 'error', 'Method not allowed');
}

/**
 * ✅ ĐĂNG NHẬP
 */
function login($db) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['username']) || empty($data['password'])) {
            sendResponse(400, 'error', 'Thiếu username hoặc password');
            return;
        }

        $stmt = $db->prepare("SELECT * FROM account WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            sendResponse(401, 'error', 'Sai tên đăng nhập hoặc mật khẩu');
            return;
        }

        // Kiểm tra tài khoản bị khóa
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $lockedUntil = date('H:i:s d/m/Y', strtotime($user['locked_until']));
            sendResponse(403, 'error', "Tài khoản bị khóa đến $lockedUntil. Vui lòng thử lại sau.");
            return;
        }

        // Kiểm tra mật khẩu
        if (!password_verify($data['password'], $user['password'])) {
            // Tăng số lần đăng nhập sai
            $attempts = ($user['login_attempts'] ?? 0) + 1;
            $lockedUntil = null;
            
            if ($attempts >= 5) {
                // Khóa 15 phút
                $lockedUntil = date('Y-m-d H:i:s', time() + 900);
                $stmt = $db->prepare("UPDATE account SET login_attempts = ?, locked_until = ? WHERE id = ?");
                $stmt->execute([$attempts, $lockedUntil, $user['id']]);
                sendResponse(403, 'error', 'Đăng nhập sai quá 5 lần. Tài khoản bị khóa 15 phút.');
                return;
            } else {
                $stmt = $db->prepare("UPDATE account SET login_attempts = ? WHERE id = ?");
                $stmt->execute([$attempts, $user['id']]);
                sendResponse(401, 'error', "Sai mật khẩu. Còn " . (5 - $attempts) . " lần thử.");
                return;
            }
        }

        // Kiểm tra trạng thái tài khoản
        if ($user['status'] == 0) {
            sendResponse(403, 'error', 'Tài khoản đã bị khóa. Liên hệ Admin.');
            return;
        }

        // Reset số lần đăng nhập sai
        $stmt = $db->prepare("UPDATE account SET login_attempts = 0, locked_until = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Tạo JWT Token
        $payload = [
            'iss' => 'webbanhang',
            'iat' => time(),
            'exp' => time() + JWT_EXPIRE,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        $token = JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);

        sendResponse(200, 'success', 'Đăng nhập thành công', [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'fullname' => $user['fullname'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'role' => $user['role']
            ]
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * ✅ ĐĂNG KÝ
 */
function register($db) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validation
        if (empty($data['username'])) {
            sendResponse(400, 'error', 'Username là bắt buộc');
            return;
        }
        if (strlen($data['username']) < 3) {
            sendResponse(400, 'error', 'Username phải có ít nhất 3 ký tự');
            return;
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            sendResponse(400, 'error', 'Username chỉ chứa chữ cái, số và dấu gạch dưới');
            return;
        }
        if (empty($data['password'])) {
            sendResponse(400, 'error', 'Password là bắt buộc');
            return;
        }
        if (strlen($data['password']) < 6) {
            sendResponse(400, 'error', 'Password phải có ít nhất 6 ký tự');
            return;
        }
        if (empty($data['email'])) {
            sendResponse(400, 'error', 'Email là bắt buộc');
            return;
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            sendResponse(400, 'error', 'Email không hợp lệ');
            return;
        }

        // Kiểm tra username/email đã tồn tại
        $stmt = $db->prepare("SELECT id FROM account WHERE username = ? OR email = ?");
        $stmt->execute([$data['username'], $data['email']]);
        if ($stmt->rowCount() > 0) {
            sendResponse(400, 'error', 'Username hoặc email đã tồn tại');
            return;
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO account (username, password, email, fullname, phone, address, role, status, login_attempts, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'user', 1, 0, NOW(), NOW())
        ");
        
        $stmt->execute([
            $data['username'],
            $hashedPassword,
            $data['email'],
            $data['fullname'] ?? '',
            $data['phone'] ?? '',
            $data['address'] ?? ''
        ]);

        $userId = $db->lastInsertId();

        sendResponse(201, 'success', 'Đăng ký thành công', [
            'user_id' => $userId,
            'username' => $data['username']
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * ✅ XEM THÔNG TIN USER ĐANG ĐĂNG NHẬP
 */
function getMe($db) {
    try {
        $user = AuthMiddleware::requireAuth();

        $stmt = $db->prepare("
            SELECT id, username, email, fullname, phone, address, role, status, created_at, updated_at 
            FROM account WHERE id = ?
        ");
        $stmt->execute([$user['user_id']]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userInfo) {
            sendResponse(404, 'error', 'Không tìm thấy thông tin user');
            return;
        }

        sendResponse(200, 'success', 'Lấy thông tin thành công', [
            'data' => $userInfo
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * ✅ CẬP NHẬT HỒ SƠ CÁ NHÂN
 */
function updateProfile($db) {
    try {
        $user = AuthMiddleware::requireAuth();
        $data = json_decode(file_get_contents("php://input"), true);

        $fields = [];
        $params = [];

        $allowedFields = ['fullname', 'email', 'phone', 'address'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                // Validation email
                if ($field === 'email' && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    sendResponse(400, 'error', 'Email không hợp lệ');
                    return;
                }
                
                // Kiểm tra email đã tồn tại (nếu đổi email)
                if ($field === 'email') {
                    $stmt = $db->prepare("SELECT id FROM account WHERE email = ? AND id != ?");
                    $stmt->execute([$data[$field], $user['user_id']]);
                    if ($stmt->rowCount() > 0) {
                        sendResponse(400, 'error', 'Email đã được sử dụng');
                        return;
                    }
                }
                
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            sendResponse(400, 'error', 'Không có dữ liệu để cập nhật');
            return;
        }

        $fields[] = "updated_at = NOW()";
        $params[] = $user['user_id'];

        $sql = "UPDATE account SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        sendResponse(200, 'success', 'Cập nhật hồ sơ thành công');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * ✅ ĐỔI MẬT KHẨU
 */
function changePassword($db) {
    try {
        $user = AuthMiddleware::requireAuth();
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['old_password']) || empty($data['new_password'])) {
            sendResponse(400, 'error', 'Thiếu old_password hoặc new_password');
            return;
        }

        if (strlen($data['new_password']) < 6) {
            sendResponse(400, 'error', 'Mật khẩu mới phải có ít nhất 6 ký tự');
            return;
        }

        // Lấy thông tin user
        $stmt = $db->prepare("SELECT password FROM account WHERE id = ?");
        $stmt->execute([$user['user_id']]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra mật khẩu cũ
        if (!password_verify($data['old_password'], $userInfo['password'])) {
            sendResponse(400, 'error', 'Mật khẩu cũ không đúng');
            return;
        }

        // Hash mật khẩu mới
        $newHashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare("UPDATE account SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newHashedPassword, $user['user_id']]);

        sendResponse(200, 'success', 'Đổi mật khẩu thành công');
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * ✅ QUÊN MẬT KHẨU (MÔ PHỎNG)
 */
function forgotPassword($db) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['email'])) {
            sendResponse(400, 'error', 'Thiếu email');
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            sendResponse(400, 'error', 'Email không hợp lệ');
            return;
        }

        // Kiểm tra email có tồn tại
        $stmt = $db->prepare("SELECT id, username FROM account WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Vẫn trả về success để không lộ thông tin
            sendResponse(200, 'success', 'Nếu email tồn tại, chúng tôi đã gửi link reset mật khẩu');
            return;
        }

        // Tạo reset token
        $resetToken = bin2hex(random_bytes(32));
        $resetTokenExpires = date('Y-m-d H:i:s', time() + 3600); // 1 giờ

        $stmt = $db->prepare("UPDATE account SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        $stmt->execute([$resetToken, $resetTokenExpires, $user['id']]);

        // Mô phỏng gửi email (trong thực tế sẽ dùng PHPMailer)
        $resetLink = "http://localhost:8080/api/views/reset-password.php?token=$resetToken";
        
        // Log ra console để debug (trong thực tế sẽ gửi email)
        error_log("Reset link for {$user['email']}: $resetLink");

        sendResponse(200, 'success', 'Nếu email tồn tại, chúng tôi đã gửi link reset mật khẩu', [
            'debug_reset_link' => $resetLink,  // Chỉ dùng để test, bỏ trong production
            'debug_token' => $resetToken
        ]);
    } catch (Exception $e) {
        sendResponse(500, 'error', 'Lỗi server: ' . $e->getMessage());
    }
}

/**
 * ✅ RESET MẬT KHẨU
 */
function resetPassword($db) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['token']) || empty($data['new_password'])) {
            sendResponse(400, 'error', 'Thiếu token hoặc new_password');
            return;
        }

        if (strlen($data['new_password']) < 6) {
            sendResponse(400, 'error', 'Mật khẩu mới phải có ít nhất 6 ký tự');
            return;
        }

        // Kiểm tra token
        $stmt = $db->prepare("SELECT id FROM account WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$data['token']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            sendResponse(400, 'error', 'Token không hợp lệ hoặc đã hết hạn');
            return;
        }

        // Hash mật khẩu mới
        $newHashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);

        // Cập nhật mật khẩu và xóa token
        $stmt = $db->prepare("
            UPDATE account 
            SET password = ?, reset_token = NULL, reset_token_expires = NULL, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$newHashedPassword, $user['id']]);

        sendResponse(200, 'success', 'Đặt lại mật khẩu thành công');
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