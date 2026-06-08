<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once 'app/config/google_config.php';
require_once('app/config/database.php');
require_once('app/models/AccountModel.php');

class AccountController {
    private $accountModel;
    private $googleClient; 
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
        $this->googleClient = new GoogleClient(); 
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $fullname = trim($_POST['fullname']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);

            // Validation
            $error = "";
            if (empty($username) || empty($password) || empty($fullname) || empty($email) || empty($phone)) {
                $error = "Vui lòng điền đầy đủ các thông tin bắt buộc.";
            } elseif ($password !== $confirm_password) {
                $error = "Mật khẩu xác nhận không khớp!";
            } elseif (strlen($password) < 6) {
                $error = "Mật khẩu phải có ít nhất 6 ký tự.";
            } else {
                // Kiểm tra username hoặc email đã tồn tại chưa
                $stmt = $this->db->prepare("SELECT id FROM account WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->rowCount() > 0) {
                    $error = "Tên đăng nhập hoặc Email đã được sử dụng.";
                } else {
                    // Hash mật khẩu
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // INSERT đầy đủ các cột
                    $sql = "INSERT INTO account (username, password, fullname, email, phone, address, role, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, 'user', 1, NOW())";
                    
                    $stmt = $this->db->prepare($sql);
                    $result = $stmt->execute([
                        $username, 
                        $hashed_password, 
                        $fullname, 
                        $email, 
                        $phone, 
                        $address
                    ]);

                    if ($result) {
                        $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
                        header('Location: /account/login');
                        exit();
                    } else {
                        $error = "Lỗi hệ thống, vui lòng thử lại sau.";
                    }
                }
            }
        }
        include 'app/views/account/register.php';
    }

    public function login() {
        include_once 'app/views/account/login.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $fullName = $_POST['fullname'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmpassword'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $errors = [];
            
            if (empty($username)) $errors['username'] = "Vui lòng nhập username!";
            if (empty($fullName)) $errors['fullname'] = "Vui lòng nhập fullname!";
            if (empty($password)) $errors['password'] = "Vui lòng nhập password!";
            if ($password != $confirmPassword) $errors['confirmPass'] = "Mật khẩu và xác nhận chưa khớp!";

            if (!in_array($role, ['admin', 'user'])) $role = 'user';
            
            if ($this->accountModel->getAccountByUsername($username)) {
                $errors['account'] = "Tài khoản này đã được đăng ký!";
            }
            
            if (count($errors) > 0) {
                include_once 'app/views/account/register.php';
            } else {
                $result = $this->accountModel->save($username, $fullName, $password, $role);
                if ($result) {
                    header('Location: /account/login');
                    exit;
                }
            }
        }
    }

    public function logout() {
        // ✅ ĐÃ SỬA: Xóa session_start() vì index.php đã gọi rồi
        unset($_SESSION['username']);
        unset($_SESSION['role']);
        // Optional: hủy toàn bộ session nếu muốn
        // session_destroy(); 
        header('Location: /product');
        exit;
    }

    public function checkLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $account = $this->accountModel->getAccountByUsername($username);
            
            if ($account && password_verify($password, $account->password)) {
                if ($account->status != 1) {
                    $error = "🔒 Tài khoản của bạn đã bị khoá. Vui lòng liên hệ admin!";
                    include_once 'app/views/account/login.php';
                    exit;
                }
    
                // ✅ QUAN TRỌNG: Clear session cũ & tạo session mới để tránh ghi đè ID
                $_SESSION = []; 
                session_regenerate_id(true); 
    
                // ✅ Gán đúng ID của tài khoản hiện tại
                $_SESSION['user_id']   = $account->id;
                $_SESSION['username']  = $account->username;
                $_SESSION['role']      = $account->role;
                $_SESSION['email']     = $account->email;
                $_SESSION['avatar']    = $account->avatar ?? '';
                
                header('Location: /account/profile'); // Redirect về profile thay vì /product
                exit;
            } else {
                $error = $account ? "Mật khẩu không đúng!" : "Không tìm thấy tài khoản!";
                include_once 'app/views/account/login.php';
                exit;
            }
        }
    }

    // ✅ Method Google Login (Giữ nguyên)
    public function googleLogin() {
        $authUrl = $this->googleClient->getAuthUrl();
        header('Location: ' . $authUrl);
        exit();
    }

    public function googleCallback() {
        if (isset($_GET['code'])) {
            try {
                $token = $this->googleClient->verifyCode($_GET['code']);
                if (!$token) throw new Exception("Lỗi xác thực Google");
    
                $userInfo = $this->googleClient->getUserInfo();
                $email = $userInfo->getEmail();
                $name = $userInfo->getName();
                $googleId = $userInfo->getId();
                $avatar = $userInfo->getPicture();
    
                // ✅ BƯỚC 1: Ưu tiên tìm theo google_id (Tránh lỗi Duplicate Key)
                $stmt = $this->db->prepare("SELECT * FROM account WHERE google_id = ?");
                $stmt->execute([$googleId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($user) {
                    // Đã tồn tại theo google_id -> Cập nhật thông tin mới & Đăng nhập
                    $upd = $this->db->prepare("UPDATE account SET fullname = ?, avatar = ?, email = ? WHERE google_id = ?");
                    $upd->execute([$name, $avatar, $email, $googleId]);
                } else {
                    // ✅ BƯỚC 2: Không có google_id -> Tìm theo email
                    $stmt = $this->db->prepare("SELECT * FROM account WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
                    if ($user) {
                        // Tồn tại email nhưng chưa绑定 google_id -> Gán google_id vào
                        $upd = $this->db->prepare("UPDATE account SET google_id = ?, fullname = ?, avatar = ? WHERE id = ?");
                        $upd->execute([$googleId, $name, $avatar, $user['id']]);
                    } else {
                        // ✅ BƯỚC 3: Hoàn toàn mới -> Tạo tài khoản
                        $username = 'google_' . substr(md5($email), 0, 8);
                        $stmt = $this->db->prepare("INSERT INTO account (username, password, fullname, email, google_id, avatar, role, status, created_at)
                                                   VALUES (?, ?, ?, ?, ?, ?, 'user', 1, NOW())");
                        $stmt->execute([
                            $username, 
                            password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                            $name, 
                            $email, 
                            $googleId,
                            $avatar
                        ]);
                        $user = [
                            'id' => $this->db->lastInsertId(), 
                            'role' => 'user', 
                            'username' => $username
                        ];
                    }
                }
    
                // ✅ TẠO SESSION AN TOÀN & ĐÚNG CHUẨN
                $_SESSION = [];
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'] ?? 'google_user'; // ✅ Dùng username thật từ DB
                $_SESSION['email']     = $email;
                $_SESSION['role']      = $user['role'];
                $_SESSION['avatar']    = $avatar;
                $_SESSION['success']   = "Đăng nhập bằng Google thành công!";
    
                header('Location: /account/profile');
                exit();
    
            } catch (PDOException $e) {
                // ✅ BẮT LỖI DB (Duplicate Key, v.v.) ĐỂ KHÔNG BỊ FATAL ERROR
                error_log("Google Login DB Error: " . $e->getMessage());
                $_SESSION['error'] = "Lỗi cơ sở dữ liệu. Vui lòng thử lại hoặc liên hệ admin.";
                header('Location: /account/login');
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: /account/login');
                exit();
            }
        } else {
            $_SESSION['error'] = "Không nhận được mã từ Google";
            header('Location: /account/login');
            exit();
        }
    }

    public function profile() {
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /account/login');
            exit();
        }
        
        // ✅ Dùng $this->accountModel thay vì tạo mới
        $account = $this->accountModel->getAccountByUsername($_SESSION['username']);
        
        if (!$account) {
            $_SESSION['error'] = 'Không tìm thấy tài khoản';
            header('Location: /');
            exit();
        }
        
        include 'app/views/account/profile.php';
    }
    
    /**
     * Cập nhật thông tin profile
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /account/profile');
            exit();
        }
        
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /account/login');
            exit();
        }
        
        $userId = $_SESSION['user_id'];
        $fullname = trim($_POST['fullname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        // ✅ DEBUG: In ra dữ liệu nhận được
        error_log("=== UPDATE PROFILE DEBUG ===");
        error_log("User ID: " . $userId);
        error_log("Fullname: " . $fullname);
        error_log("Phone: " . $phone);
        error_log("Address: " . $address);
        error_log("Email: " . $email);
        
        $errors = [];
        if (empty($fullname)) {
            $errors[] = 'Họ tên không được để trống';
        }
        
        // Xử lý upload ảnh đại diện (giữ nguyên code cũ)
        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            // ... code upload avatar giữ nguyên ...
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            
            if (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
                $errors[] = 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)';
            } elseif ($_FILES['avatar']['size'] > $maxSize) {
                $errors[] = 'Kích thước ảnh không vượt quá 5MB';
            } else {
                $uploadDir = 'uploads/avatars/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                    $avatarPath = $uploadPath;
                    
                    $accountModel = new AccountModel($this->db);
                    $oldAccount = $accountModel->getById($userId);
                    if ($oldAccount && !empty($oldAccount->avatar) && file_exists($oldAccount->avatar)) {
                        unlink($oldAccount->avatar);
                    }
                } else {
                    $errors[] = 'Lỗi khi tải ảnh lên';
                }
            }
        }
        
        if (empty($errors)) {
            try {
                $accountModel = new AccountModel($this->db);
                
                $data = [
                    'fullname' => $fullname,
                    'phone' => $phone,
                    'address' => $address,
                    'email' => $email
                ];
                
                if ($avatarPath) {
                    $data['avatar'] = $avatarPath;
                }
                
                // ✅ DEBUG: In ra data trước khi update
                error_log("Data to update: " . print_r($data, true));
                error_log("User ID: " . $userId);
                
                $result = $accountModel->update($userId, $data);
                
                // ✅ DEBUG: Kết quả update
                error_log("Update result: " . ($result ? 'SUCCESS' : 'FAILED'));
                
                if ($result) {
                    $_SESSION['success'] = '✅ Cập nhật thông tin thành công!';
                    
                    if ($email) {
                        $_SESSION['email'] = $email;
                    }
                } else {
                    $_SESSION['error'] = '❌ Cập nhật thất bại - Database update returned false';
                }
            } catch (Exception $e) {
                $_SESSION['error'] = '❌ Lỗi: ' . $e->getMessage();
                error_log("Exception: " . $e->getMessage());
            }
        } else {
            $_SESSION['error'] = implode('<br>', $errors);
            error_log("Validation errors: " . implode(', ', $errors));
        }
        
        header('Location: /account/profile');
        exit();
    }
    
    /**
     * Đổi mật khẩu
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /account/profile');
            exit();
        }
        
        if (!SessionHelper::isLoggedIn()) {
            header('Location: /account/login');
            exit();
        }
        
        $userId = $_SESSION['user_id'];
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        // Lấy thông tin user hiện tại
        $accountModel = new AccountModel($this->db);
        $account = $accountModel->getById($userId);
        
        if (!$account || !password_verify($oldPassword, $account->password)) {
            $errors[] = 'Mật khẩu cũ không đúng';
        }
        
        if (strlen($newPassword) < 6) {
            $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }
        
        if (empty($errors)) {
            try {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                if ($accountModel->update($userId, ['password' => $hashedPassword])) {
                    $_SESSION['success'] = '✅ Đổi mật khẩu thành công!';
                } else {
                    $_SESSION['error'] = '❌ Đổi mật khẩu thất bại';
                }
            } catch (Exception $e) {
                $_SESSION['error'] = '❌ Lỗi: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = implode('<br>', $errors);
        }
        
        header('Location: /account/profile');
        exit();
    }

    public function forgotPassword() {
        include 'app/views/account/forgot_password.php';
    }
    
    /**
     * Xử lý yêu cầu reset mật khẩu
     */
   public function handleForgotPassword() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /account/forgot-password');
        exit();
    }
    
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $_SESSION['error'] = 'Vui lòng nhập email!';
        header('Location: /account/forgot-password');
        exit();
    }
    
    // Kiểm tra email có tồn tại
    $stmt = $this->db->prepare("SELECT id, username FROM account WHERE email = ? AND status = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['success'] = 'Nếu email tồn tại, bạn sẽ nhận được hướng dẫn reset mật khẩu!';
        header('Location: /account/forgot-password');
        exit();
    }
    
    // ✅ TẠO TOKEN
    $token = bin2hex(random_bytes(32));
    
    // ✅ XÓA TOKEN CŨ TRƯỚC (vì bảng không có UNIQUE KEY)
    $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->execute([$email]);
    
    // ✅ DÙNG NOW() + INTERVAL CỦA MYSQL (đảm bảo cùng timezone khi check)
    $stmt = $this->db->prepare("
        INSERT INTO password_resets (email, token, expires_at) 
        VALUES (?, ?, NOW() + INTERVAL 1 HOUR)
    ");
    $stmt->execute([$email, $token]);
    
    // ✅ TẠO LINK ĐỘNG (không hardcode localhost)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $resetLink = $protocol . '://' . $host . '/account/reset-password?token=' . $token;
    
    // Debug: Xem token vừa tạo
    $stmt = $this->db->query("SELECT created_at, expires_at FROM password_resets WHERE token = '$token'");
    $debug = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $_SESSION['reset_token'] = $token;
    $_SESSION['reset_email'] = $email;
    $_SESSION['success'] = "✅ Mã reset đã được tạo! (Demo mode)<br><br>
                           <strong>Token:</strong> <code>$token</code><br><br>
                           <strong>Link reset:</strong> <a href='$resetLink' target='_blank' style='color:blue;font-weight:bold'>👉 Click vào đây để reset mật khẩu</a><br><br>
                           <small>📅 Created: {$debug['created_at']} | ⏰ Expires: {$debug['expires_at']}</small>";
    
    header('Location: /account/forgot-password');
    exit();
}

/**
 * Hiển thị trang reset mật khẩu (GET request)
 */
public function resetPassword() {
    // Lấy token từ URL
    $token = $_GET['token'] ?? '';
    
    // Debug log
    error_log("resetPassword() called - Token: " . substr($token, 0, 20) . "...");
    
    // Truyền token vào view
    include 'app/views/account/reset_password.php';
}
    
        /**
     * Hiển thị trang reset mật khẩu (LUÔN hiện form, không chặn)
     */
   /**
 * Xử lý khi người dùng bấm nút "Cập nhật mật khẩu"
 */
public function handleResetPassword() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /account/forgot-password');
        exit();
    }

    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // 1. Validate cơ bản
    if (empty($token)) {
        $_SESSION['error'] = 'Token không hợp lệ!';
        header('Location: /account/forgot-password');
        exit();
    }
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Mật khẩu phải có ít nhất 6 ký tự!';
        header('Location: /account/reset-password?token=' . urlencode($token));
        exit();
    }
    
    if ($password !== $confirm) {
        $_SESSION['error'] = 'Mật khẩu xác nhận không khớp!';
        header('Location: /account/reset-password?token=' . urlencode($token));
        exit();
    }

    // 2. ✅ DEBUG: Lấy đầy đủ thông tin token
    $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['error'] = '❌ Token không tồn tại trong database!';
        header('Location: /account/forgot-password');
        exit();
    }

    // 3. ✅ Check từng điều kiện riêng (tránh lỗi timezone)
    if ($row['used'] == 1) {
        $_SESSION['error'] = '❌ Token đã được sử dụng! Vui lòng yêu cầu reset lại.';
        header('Location: /account/forgot-password');
        exit();
    }
    
    // ✅ Dùng strtotime() để so sánh - tránh lỗi timezone giữa PHP và MySQL
    $now = time();
    $expiresTime = strtotime($row['expires_at']);
    
    if ($expiresTime < $now) {
        $_SESSION['error'] = '❌ Token đã hết hạn!<br>
                             <small>Expires: ' . $row['expires_at'] . '<br>
                             Now: ' . date('Y-m-d H:i:s') . '</small>';
        header('Location: /account/forgot-password');
        exit();
    }

    // 4. Cập nhật mật khẩu & đánh dấu token đã dùng
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $email = $row['email'];
    
    $this->db->prepare("UPDATE account SET password = ? WHERE email = ?")->execute([$hash, $email]);
    $this->db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")->execute([$token]);

    $_SESSION['success'] = '✅ Đổi mật khẩu thành công! Vui lòng đăng nhập lại.';
    header('Location: /account/login');
    exit();
}
    
    
}
?>