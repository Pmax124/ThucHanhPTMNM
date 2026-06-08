<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/config/database.php';

echo "<h2>🔍 Test Reset Password Flow</h2>";

$db = (new Database())->getConnection();

// 1. Kiểm tra tokens hiện tại
echo "<h3>1. Tokens hiện tại:</h3>";
$stmt = $db->query("SELECT * FROM password_resets ORDER BY created_at DESC LIMIT 5");
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tokens)) {
    echo "<p style='color:red'>❌ Không có tokens nào!</p>";
} else {
    echo "<table border='1' style='border-collapse:collapse;width:100%;'>";
    echo "<tr><th>ID</th><th>Email</th><th>Token</th><th>Expires At</th><th>Used</th><th>Status</th></tr>";
    
    foreach ($tokens as $token) {
        $now = date('Y-m-d H:i:s');
        $isExpired = $token['expires_at'] < $now;
        $status = $isExpired ? "❌ HẾT HẠN" : "✅ CÒN HẠN";
        $color = $isExpired ? "red" : "green";
        
        echo "<tr>";
        echo "<td>{$token['id']}</td>";
        echo "<td>{$token['email']}</td>";
        echo "<td>" . substr($token['token'], 0, 20) . "...</td>";
        echo "<td>{$token['expires_at']}</td>";
        echo "<td>{$token['used']}</td>";
        echo "<td style='color:$color;font-weight:bold'>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 2. Tạo token mới để test
echo "<hr><h3>2. Tạo token mới:</h3>";

$testEmail = 'Van@gmail.com'; // Thay email của bạn

// Kiểm tra email có tồn tại
$stmt = $db->prepare("SELECT id, username FROM account WHERE email = ?");
$stmt->execute([$testEmail]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p style='color:red'>❌ Email '$testEmail' không tồn tại!</p>";
    echo "<p>Các email trong database:</p>";
    $emails = $db->query("SELECT email FROM account")->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($emails as $email) {
        echo "<li>$email</li>";
    }
    echo "</ul>";
} else {
    // Tạo token mới
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Xóa tokens cũ
    $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->execute([$testEmail]);
    
    // Lưu token mới
    $stmt = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$testEmail, $token, $expires_at]);
    
    $resetLink = "http://localhost:8080/account/reset-password?token=" . $token;
    
    echo "<div style='background:#d4edda;padding:20px;border-radius:5px;margin:20px 0;'>";
    echo "<h3 style='color:green'>✅ Token mới đã tạo!</h3>";
    echo "<p><strong>Email:</strong> $testEmail</p>";
    echo "<p><strong>Token:</strong> $token</p>";
    echo "<p><strong>Expires:</strong> $expires_at</p>";
    echo "<p><strong>Link reset:</strong> <a href='$resetLink' target='_blank' style='color:blue;text-decoration:underline'>$resetLink</a></p>";
    echo "</div>";
    
    echo "<p><a href='$resetLink' class='btn btn-primary'>👉 Click để test reset password</a></p>";
}
?>

<style>
.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.btn:hover {
    background: #0056b3;
}
</style>