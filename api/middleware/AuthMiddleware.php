<?php
/**
 * Middleware xác thực JWT và phân quyền
 * Compatible với firebase/php-jwt v7.x
 */

// ✅ ĐƯỜNG DẪN ĐÚNG (2 cấp)
require_once __DIR__ . '/../config/jwt_config.php';      // api/config/jwt_config.php
require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AuthMiddleware {
    
    /**
     * Lấy thông tin user từ token
     */
    public static function getUser() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
            return null;
        }
        
        $token = substr($authHeader, 7);
        
        try {
            $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));
            return [
                'user_id' => $decoded->user_id,
                'username' => $decoded->username,
                'role' => $decoded->role,
                'email' => $decoded->email ?? ''
            ];
        } catch (ExpiredException $e) {
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Bắt buộc phải đăng nhập
     */
    public static function requireAuth() {
        $user = self::getUser();
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Unauthorized - Vui lòng đăng nhập'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        return $user;
    }
    
    /**
     * Bắt buộc phải là Admin
     */
    public static function requireAdmin() {
        $user = self::requireAuth();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Forbidden - Bạn không có quyền Admin'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        return $user;
    }
    
    /**
     * Bắt buộc phải là User hoặc Admin
     */
    public static function requireUser() {
        $user = self::requireAuth();
        if (!in_array($user['role'], ['admin', 'user'])) {
            http_response_code(403);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Forbidden - Vai trò không hợp lệ'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        return $user;
    }
}
?>