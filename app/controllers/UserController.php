<?php
require_once 'app/config/database.php';
require_once 'app/models/UserModel.php';

class UserController {
    private $db;
    private $userModel;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->userModel = new UserModel($this->db);
    }

    // Danh sách user
    public function list() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        
        $users = $this->userModel->getAll($search, $limit, $offset);
        $total = $this->userModel->countAll($search);
        $total_pages = ceil($total / $limit);
        
        include 'app/views/admin/users/list.php';
    }

    // Form thêm user
    public function add() {
        $user = null; // Chế độ thêm mới
        include 'app/views/admin/users/form.php';
    }

    // Xử lý thêm
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /User/list'); exit; }
        
        $data = [
            'username' => trim($_POST['username']),
            'password' => $_POST['password'],
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'role' => $_POST['role'] ?? 'user',
            'status' => isset($_POST['status']) ? 1 : 0
        ];

        $errors = [];
        if (empty($data['username'])) $errors[] = "Tên đăng nhập không được trống";
        if (empty($data['password'])) $errors[] = "Mật khẩu không được trống";
        if (empty($data['email'])) $errors[] = "Email không được trống";

        if (!empty($errors)) {
            $error = implode('<br>', $errors);
            include 'app/views/admin/users/form.php';
            return;
        }

        try {
            $this->userModel->create($data);
            $_SESSION['success'] = "✅ Tạo tài khoản thành công!";
            header('Location: /User/list');
        } catch (Exception $e) {
            $error = "❌ Lỗi: " . $e->getMessage();
            include 'app/views/admin/users/form.php';
        }
    }

    // Form sửa user
    public function edit($id) {
        $user = $this->userModel->getById($id);
        if (!$user) { header('Location: /User/list'); exit; }
        include 'app/views/admin/users/form.php';
    }

    // Xử lý sửa
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /User/list'); exit; }
        
        $data = [
            'username' => trim($_POST['username']),
            'password' => $_POST['password'], // Có thể để trống
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'role' => $_POST['role'] ?? 'user',
            'status' => isset($_POST['status']) ? 1 : 0
        ];

        try {
            $this->userModel->update($id, $data);
            $_SESSION['success'] = "✅ Cập nhật tài khoản thành công!";
            header('Location: /User/list');
        } catch (Exception $e) {
            $error = "❌ Lỗi: " . $e->getMessage();
            $user = $this->userModel->getById($id); // Giữ dữ liệu cũ để fill form
            include 'app/views/admin/users/form.php';
        }
    }

    // Xóa user
    public function delete($id) {
        if ($this->userModel->delete($id)) {
            $_SESSION['success'] = "✅ Đã xóa tài khoản!";
        } else {
            $_SESSION['error'] = "❌ Lỗi khi xóa!";
        }
        header('Location: /User/list');
    }

    public function toggleStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /User/list');
            exit();
        }
        
        $userId = $_POST['user_id'] ?? 0;
        $newStatus = $_POST['status'] ?? 0;
        
        if (!$userId) {
            $_SESSION['error'] = 'Thiếu ID người dùng';
            header('Location: /User/list');
            exit();
        }
        
        // Không cho khoá chính mình
        if (isset($_SESSION['user_id']) && $userId == $_SESSION['user_id']) {
            $_SESSION['error'] = '❌ Không thể khoá tài khoản đang đăng nhập!';
            header('Location: /User/list');
            exit();
        }
        
        try {
            $sql = "UPDATE account SET status = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$newStatus, $userId]);
            
            if ($result) {
                $actionText = $newStatus == 1 ? 'Mở khoá' : 'Khoá';
                $_SESSION['success'] = "✅ Đã {$actionText} tài khoản thành công!";
            } else {
                $_SESSION['error'] = '❌ Cập nhật thất bại';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = '❌ Lỗi: ' . $e->getMessage();
        }
        
        header('Location: /User/list');
        exit();
    }
}
?>