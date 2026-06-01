<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    // Xử lý định dạng ngày từ datetime-local
    $start_date = !empty($_POST['start_date']) ? str_replace('T', ' ', $_POST['start_date']) . ':00' : null;
    $end_date = !empty($_POST['end_date']) ? str_replace('T', ' ', $_POST['end_date']) . ':00' : null;

    $data = [
        'code' => strtoupper(trim($_POST['code'] ?? '')),
        'description' => trim($_POST['description'] ?? ''),
        'discount_type' => $_POST['discount_type'] ?? 'percent',
        'discount_value' => floatval($_POST['discount_value'] ?? 0),
        'min_order_value' => floatval($_POST['min_order_value'] ?? 0),
        'max_discount' => !empty($_POST['max_discount']) ? floatval($_POST['max_discount']) : null,
        'usage_limit' => !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null,
        'used_count' => 0,  // ✅ MỚI: Khởi tạo = 0
        'usage_per_user' => !empty($_POST['usage_per_user']) ? intval($_POST['usage_per_user']) : 1, // ✅ MỚI
        'start_date' => $start_date,
        'end_date' => $end_date,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    // Validation chi tiết
    $errors = [];
    if (empty($data['code'])) {
        $errors[] = "Mã voucher không được để trống";
    }
    if (empty($data['end_date'])) {
        $errors[] = "Ngày kết thúc là bắt buộc";
    }
    if ($data['discount_value'] <= 0) {
        $errors[] = "Giá trị giảm phải lớn hơn 0";
    }
    if ($data['discount_type'] === 'percent' && ($data['discount_value'] < 1 || $data['discount_value'] > 100)) {
        $errors[] = "Giảm giá phần trăm phải từ 1-100";
    }

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

    // Debug: In ra dữ liệu để kiểm tra
    // echo "<pre>"; print_r($data); echo "</pre>"; exit;

    try {
        if ($this->voucherModel->create($data)) {
            $_SESSION['success'] = "✅ Tạo voucher '{$data['code']}' thành công!";
            header('Location: /Voucher/list');
            exit();
        } else {
            $error = "❌ Lỗi khi lưu voucher vào database.";
            include 'app/views/Voucher/add.php';
        }
    } catch (Exception $e) {
        // Log lỗi chi tiết
        error_log("Voucher Error: " . $e->getMessage());
        $error = "❌ Lỗi: " . $e->getMessage();
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
            // ✅ Quan trọng: Set header JSON
            header('Content-Type: application/json');
            
            // ✅ Log debug (xóa sau khi test xong)
            error_log("=== VOUCHER APPLY DEBUG ===");
            error_log("POST: " . print_r($_POST, true));
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['valid' => false, 'message' => 'Method không hợp lệ']);
                return;
            }
            
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $orderTotal = floatval($_POST['order_total'] ?? 0);
            
            error_log("Code: $code, Order Total: $orderTotal");
            
            if (empty($code)) {
                echo json_encode(['valid' => false, 'message' => 'Vui lòng nhập mã giảm giá']);
                return;
            }
            
            try {
                $result = $this->voucherModel->validateVoucher($code, $orderTotal);
                
                error_log("Model result: " . print_r($result, true));
                
                // ✅ Đảm bảo format trả về khớp frontend
                if (isset($result['valid']) && $result['valid'] === true) {
                    echo json_encode([
                        'valid' => true,
                        'discount' => $result['discount'] ?? 0,
                        'message' => $result['message'] ?? 'Áp dụng thành công!',
                        'code' => $code
                    ]);
                } else {
                    echo json_encode([
                        'valid' => false,
                        'message' => $result['message'] ?? 'Mã voucher không hợp lệ'
                    ]);
                }
            } catch (Exception $e) {
                error_log("Voucher Apply Exception: " . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'valid' => false,
                    'message' => 'Lỗi server: ' . $e->getMessage()
                ]);
            }
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