<?php
/**
 * API Đăng nhập / Đăng ký / Refresh token / Quản lý tài khoản
 * Endpoint: /api/auth
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
// ✅ THÊM: GET, PUT để hỗ trợ /me, /profile, /change-password
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config/jwt_config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';  // ✅ THÊM

use Firebase\JWT\JWT;

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        if ($action === 'login') {
            login($db);
        } elseif ($action === 'register') {
            register($db);
        } elseif ($action === 'refresh') {
            refresh($db);
        } elseif ($action === 'forgot-password') {
            forgotPassword($db);
        } elseif ($action === 'reset-password') {
            resetPassword($db);
        } else {
            sendResponse(400, 'error', 'Action POST không hợp lệ');
        }
        break;
        
    // ✅ THÊM: Xử lý GET
    case 'GET':
        if ($action === 'me') {
            getMe($db);
        } else {
            sendResponse(400, 'error', 'Action GET không hợp lệ');
        }
        break;
        
    // ✅ THÊM: Xử lý PUT
    case 'PUT':
        if ($action === 'profile') {
            updateProfile($db);
        } elseif ($action === 'change-password') {
            changePassword($db);
        } else {
            sendResponse(400, 'error', 'Action PUT không hợp lệ');
        }
        break;
        
    default:
        sendResponse(405, 'error', 'Method not allowed');
}

/**
 * ✅ ĐĂNG NHẬP (GIỮ NGUYÊN)
 */
function login($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        sendResponse(400, 'error', 'Vui lòng nhập username và password');
        return;
    }
    
    $stmt = $db->prepare("SELECT * FROM account WHERE (username = ? OR email = ?) AND status = 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password'])) {
        sendResponse(401, 'error', 'Sai tên đăng nhập hoặc mật khẩu');
        return;
    }
    
    if ($user['status'] != 1) {
        sendResponse(403, 'error', 'Tài khoản đã bị khóa');
        return;
    }
    
    // Tạo JWT token
    $payload = [
        'iat' => time(),
        'exp' => time() + JWT_EXPIRE,
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'email' => $user['email'] ?? ''
    ];
    
    $token = JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
    
    sendResponse(200, 'success', 'Đăng nhập thành công', [
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'email' => $user['email'] ?? '',
            'fullname' => $user['fullname'] ?? '',
            'phone' => $user['phone'] ?? '',
            'address' => $user['address'] ?? ''
        ]
    ]);
}

/**
 * ✅ ĐĂNG KÝ (GIỮ NGUYÊN)
 */
function register($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';
    $fullname = $data['fullname'] ?? '';
    $phone = $data['phone'] ?? '';
    
    if (empty($username) || empty($password) || empty($email)) {
        sendResponse(400, 'error', 'Thiếu thông tin bắt buộc');
        return;
    }
    
    // Validation username
    if (strlen($username) < 3) {
        sendResponse(400, 'error', 'Username phải có ít nhất 3 ký tự');
        return;
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        sendResponse(400, 'error', 'Username chỉ chứa chữ cái, số và dấu gạch dưới');
        return;
    }
    
    // Validation password
    if (strlen($password) < 6) {
        sendResponse(400, 'error', 'Password phải có ít nhất 6 ký tự');
        return;
    }
    
    // Validation email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(400, 'error', 'Email không hợp lệ');
        return;
    }
    
    // Kiểm tra trùng
    $stmt = $db->prepare("SELECT id FROM account WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        sendResponse(400, 'error', 'Username hoặc email đã tồn tại');
        return;
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT INTO account (username, password, email, fullname, phone, role, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, 'user', 1, NOW())");
    $stmt->execute([$username, $hashedPassword, $email, $fullname, $phone]);
    
    sendResponse(201, 'success', 'Đăng ký thành công', [
        'user_id' => $db->lastInsertId(),
        'username' => $username
    ]);
}

/**
 * ✅ REFRESH TOKEN (GIỮ NGUYÊN)
 */
function refresh($db) {
    $user = AuthMiddleware::requireAuth();
    
    // Lấy thông tin mới nhất từ DB
    $stmt = $db->prepare("SELECT * FROM account WHERE id = ?");
    $stmt->execute([$user['user_id']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userInfo) {
        sendResponse(404, 'error', 'Không tìm thấy user');
        return;
    }
    
    $payload = [
        'iat' => time(),
        'exp' => time() + JWT_EXPIRE,
        'user_id' => $userInfo['id'],
        'username' => $userInfo['username'],
        'role' => $userInfo['role'],
        'email' => $userInfo['email'] ?? ''
    ];
    
    $token = JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
    
    sendResponse(200, 'success', 'Refresh token thành công', [
        'token' => $token
    ]);
}

/**
 * ✅ MỚI: XEM THÔNG TIN USER ĐANG ĐĂNG NHẬP
 * GET /api/auth?action=me
 */
function getMe($db) {
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
}

/**
 * ✅ MỚI: CẬP NHẬT HỒ SƠ CÁ NHÂN
 * PUT /api/auth?action=profile
 */
function updateProfile($db) {
    $user = AuthMiddleware::requireAuth();
    $data = json_decode(file_get_contents("php://input"), true);
    
    $fields = [];
    $params = [];
    
    $allowedFields = ['fullname', 'email', 'phone', 'address'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            // Validation email
            if ($field === 'email') {
                if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    sendResponse(400, 'error', 'Email không hợp lệ');
                    return;
                }
                
                // Kiểm tra email đã tồn tại
                $stmt = $db->prepare("SELECT id FROM account WHERE email = ? AND id != ?");
                $stmt->execute([$data[$field], $user['user_id']]);
                if ($stmt->rowCount() > 0) {
                    sendResponse(400, 'error', 'Email đã được sử dụng bởi tài khoản khác');
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
}

/**
 * ✅ MỚI: ĐỔI MẬT KHẨU
 * PUT /api/auth?action=change-password
 */
function changePassword($db) {
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
    
    // Lấy mật khẩu hiện tại
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
}

/**
 * ✅ MỚI: QUÊN MẬT KHẨU (MÔ PHỎNG)
 * POST /api/auth?action=forgot-password
 */
function forgotPassword($db) {
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
    
    // ✅ Luôn trả về success để không lộ thông tin
    if (!$user) {
        sendResponse(200, 'success', 'Nếu email tồn tại, chúng tôi đã gửi link reset mật khẩu');
        return;
    }
    
    // Tạo reset token
    $resetToken = bin2hex(random_bytes(32));
    $resetTokenExpires = date('Y-m-d H:i:s', time() + 3600); // 1 giờ
    
    $stmt = $db->prepare("UPDATE account SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
    $stmt->execute([$resetToken, $resetTokenExpires, $user['id']]);
    
    // Mô phỏng gửi email
    $resetLink = "http://localhost:8080/api/views/reset-password.php?token=$resetToken";
    
    sendResponse(200, 'success', 'Nếu email tồn tại, chúng tôi đã gửi link reset mật khẩu', [
        'debug_reset_link' => $resetLink,
        'debug_token' => $resetToken
    ]);
}

/**
 * ✅ MỚI: RESET MẬT KHẨU
 * POST /api/auth?action=reset-password
 */
/**
 * ✅ MỚI: RESET MẬT KHẨU - CÓ DEBUG CHI TIẾT
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

        // ✅ Trim và decode token
        $token = trim(urldecode($data['token']));
        
        // Debug: Lấy tất cả token đang có trong DB
        $debugStmt = $db->query("
            SELECT 
                id, 
                username, 
                email,
                reset_token,
                LENGTH(reset_token) as db_token_length,
                reset_token_expires,
                NOW() as server_now,
                TIMESTAMPDIFF(SECOND, NOW(), reset_token_expires) as seconds_left
            FROM account 
            WHERE reset_token IS NOT NULL
            LIMIT 5
        ");
        $debugTokens = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug info
        $debugInfo = [
            'received_token' => $token,
            'received_token_length' => strlen($token),
            'tokens_in_db' => $debugTokens
        ];

        // ✅ Tìm user theo token
        $stmt = $db->prepare("
            SELECT id, reset_token, reset_token_expires 
            FROM account 
            WHERE reset_token = ?
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // Token không tìm thấy - có thể do encoding
            sendResponse(400, 'error', 'Token không tồn tại trong hệ thống', $debugInfo);
            return;
        }

        // ✅ Kiểm tra thời gian hết hạn
        $expiresTime = strtotime($user['reset_token_expires']);
        $currentTime = time();
        
        if ($expiresTime <= $currentTime) {
            sendResponse(400, 'error', 'Token đã hết hạn', [
                'token_expires' => $user['reset_token_expires'],
                'server_now' => date('Y-m-d H:i:s'),
                'expired_seconds_ago' => $currentTime - $expiresTime
            ]);
            return;
        }

        // ✅ Hash mật khẩu mới
        $newHashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);

        // ✅ Cập nhật mật khẩu và XÓA token
        $stmt = $db->prepare("
            UPDATE account 
            SET 
                password = ?,
                reset_token = NULL,
                reset_token_expires = NULL,
                updated_at = NOW()
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