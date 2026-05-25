<?php
require_once 'app/config/database.php';
require_once 'app/models/VoucherModel.php';

class VoucherController {
    private $db;
    private $voucherModel;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->voucherModel = new VoucherModel($this->db);
    }

    /**
     * Hiển thị danh sách voucher (Admin)
     */
    public function list() {
        $vouchers = $this->voucherModel->getAllVouchers();
        include 'app/views/Voucher/list.php';
    }

    /**
     * Hiển thị form tạo voucher mới
     */
    public function add() {
        include 'app/views/Voucher/add.php';
    }

    /**
     * Xử lý lưu voucher mới
     */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Voucher/add');
            exit();
        }
    
        // === QUAN TRỌNG: Xử lý định dạng ngày từ datetime-local ===
        $start_date = !empty($_POST['start_date']) ? str_replace('T', ' ', $_POST['start_date']) . ':00' : null;
        $end_date = !empty($_POST['end_date']) ? str_replace('T', ' ', $_POST['end_date']) . ':00' : null;
        // ========================================================
    
        $data = [
            'code' => strtoupper(trim($_POST['code'] ?? '')),
            'description' => trim($_POST['description'] ?? ''),
            'discount_type' => $_POST['discount_type'] ?? 'percent',
            'discount_value' => $_POST['discount_value'] ?? 0,
            'min_order_value' => $_POST['min_order_value'] ?? 0,
            'max_discount' => $_POST['max_discount'] ?? null,
            'usage_limit' => $_POST['usage_limit'] ?? null,
            'start_date' => $start_date,  // ✅ Đã sửa
            'end_date' => $end_date,      // ✅ Đã sửa
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
    
        // Validation
        $errors = [];
        if (empty($data['code'])) $errors[] = "Mã voucher không được để trống";
        if (empty($data['end_date'])) $errors[] = "Ngày kết thúc là bắt buộc";
        if ($data['discount_value'] <= 0) $errors[] = "Giá trị giảm phải lớn hơn 0";
    
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
            include 'app/views/Voucher/add.php';
            return;
        }
    
        // Kiểm tra mã trùng
        if ($this->voucherModel->voucherExists($data['code'])) {
            $error = "Mã voucher '{$data['code']}' đã tồn tại!";
            include 'app/views/Voucher/add.php';
            return;
        }
    
        // Lưu vào database với try-catch để bắt lỗi
        try {
            if ($this->voucherModel->create($data)) {
                $_SESSION['success'] = "✅ Tạo voucher '{$data['code']}' thành công!";
                header('Location: /Voucher/list');
                exit();
            } else {
                $error = "❌ Lỗi khi lưu voucher (Kiểm tra database).";
                include 'app/views/Voucher/add.php';
            }
        } catch (Exception $e) {
            // Hiển thị lỗi chi tiết để debug
            $error = "❌ Exception: " . $e->getMessage();
            include 'app/views/Voucher/add.php';
        }
    }

    /**
     * Hiển thị form sửa voucher
     */
    public function edit($id) {
        $voucher = $this->voucherModel->getById($id);
        if (!$voucher) {
            echo "Không tìm thấy voucher.";
            return;
        }
        include 'app/views/Voucher/edit.php';
    }

    /**
     * Xử lý cập nhật voucher
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Voucher/list');
            exit();
        }
    
        $id = $_POST['id'] ?? 0;
        
        // === THÊM: Convert định dạng ngày ===
        $start_date = !empty($_POST['start_date']) 
            ? str_replace('T', ' ', $_POST['start_date']) . ':00' 
            : null;
        $end_date = !empty($_POST['end_date']) 
            ? str_replace('T', ' ', $_POST['end_date']) . ':00' 
            : null;
        // ===================================
        
        $data = [
            'code' => strtoupper(trim($_POST['code'] ?? '')),
            'description' => trim($_POST['description'] ?? ''),
            'discount_type' => $_POST['discount_type'] ?? 'percent',
            'discount_value' => $_POST['discount_value'] ?? 0,
            'min_order_value' => $_POST['min_order_value'] ?? 0,
            'max_discount' => $_POST['max_discount'] ?? null,
            'usage_limit' => $_POST['usage_limit'] ?? null,
            'start_date' => $start_date,  // ✅ Đã convert
            'end_date' => $end_date,      // ✅ Đã convert
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        // ... phần còn lại giữ nguyên ...
    }

    /**
     * Xóa voucher
     */
    public function delete($id) {
        if ($this->voucherModel->delete($id)) {
            $_SESSION['success'] = "✅ Đã xóa voucher!";
        } else {
            $_SESSION['error'] = "❌ Lỗi khi xóa voucher.";
        }
        header('Location: /Voucher/list');
        exit();
    }

    /**
     * AJAX: Kiểm tra & áp dụng voucher (dùng ở checkout)
     */
    public function apply() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method không hợp lệ']);
            return;
        }
        
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $orderTotal = $_POST['order_total'] ?? 0;
        
        $result = $this->voucherModel->validateVoucher($code, $orderTotal);
        echo json_encode($result);
    }

    /**
     * AJAX: Lấy danh sách voucher active cho trang chủ
     */
    public function getActive() {
        header('Content-Type: application/json');
        $vouchers = $this->voucherModel->getActiveVouchers();
        echo json_encode(['success' => true, 'data' => $vouchers]);
    }
}
?>