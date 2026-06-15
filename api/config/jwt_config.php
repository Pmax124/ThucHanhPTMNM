<?php
/**
 * Cấu hình JWT
 */
define('JWT_SECRET', 'webbanhang_secret_key_2026_very_secure_key'); // Đổi key thật dài và bí mật
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRE', 3600 * 24); // 24 giờ
?>