<?php
session_start();
unset($_SESSION['error']);
unset($_SESSION['success']);
echo "✅ Đã xóa session! <a href='/account/forgot-password'>Quay lại trang quên mật khẩu</a>";
?>