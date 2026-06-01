<?php
require_once 'app/models/OrderModel.php';

class AdminOrderController {
    private $db;
    private $orderModel;

    public function __construct() {
        // Kết nối database
        $host = 'localhost';
        $dbname = 'my_store';
        $username = 'root';
        $password = '';
        
        try {
            $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->orderModel = new OrderModel($this->db);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    // Hiển thị danh sách đơn hàng
    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
        
        if ($status_filter) {
            $orders = $this->orderModel->getByStatus($status_filter, $limit, $offset);
            $total = $this->orderModel->countByStatus($status_filter);
        } else {
            $orders = $this->orderModel->getAll($limit, $offset);
            $total = $this->orderModel->countAll();
        }
        
        $total_pages = ceil($total / $limit);
        $statistics = $this->orderModel->getStatistics();
        
        require_once 'app/views/admin/orders/index.php';
    }

     // ✅ Xem chi tiết đơn hàng - SỬA redirect URL
    public function show($id) {
        $order = $this->orderModel->getById($id);
        
        if (!$order) {
            $_SESSION['error'] = 'Đơn hàng không tồn tại';
            header('Location: /AdminOrder/index');  // ✅ Redirect đúng format
            exit;
        }
        
        // Lấy chi tiết sản phẩm (nếu có bảng order_details)
        $orderDetails = [];
        try {
            $sql = "SELECT od.*, p.name as product_name, p.price, p.image 
                    FROM order_details od 
                    LEFT JOIN products p ON od.product_id = p.id 
                    WHERE od.order_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Không có bảng order_details thì bỏ qua
        }
        
        require_once 'app/views/admin/orders/show.php';
    }

            // ✅ Method đúng - KHÔNG có tham số $id
        public function updateStatus() {  // ✅ KHÔNG có tham số $id
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['order_id'] ?? null;  // ✅ Lấy từ POST
                $status = $_POST['status'] ?? '';
                
                if (!$id) {
                    $_SESSION['error'] = 'Thiếu ID đơn hàng';
                    header('Location: /AdminOrder/index');
                    exit;
                }
                
                if ($this->orderModel->updateStatus($id, $status)) {
                    $_SESSION['success'] = 'Cập nhật trạng thái thành công';
                } else {
                    $_SESSION['error'] = 'Cập nhật thất bại';
                }
                
                header('Location: /AdminOrder/index');  // ✅ Redirect đúng
                exit;
            }
        }

    // ✅ Xóa đơn hàng - SỬA LẠI: nhận POST + redirect đúng
    public function delete() {  // ✅ KHÔNG có tham số $id
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['order_id'] ?? null;  // ✅ Lấy từ POST
            
            if (!$id) {
                $_SESSION['error'] = 'Thiếu ID đơn hàng';
                header('Location: /AdminOrder/index');
                exit;
            }
            
            // Xóa chi tiết đơn hàng trước (nếu có bảng order_details)
            try {
                $sql = "DELETE FROM order_details WHERE order_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$id]);
            } catch (Exception $e) {
                // Bỏ qua nếu không có bảng order_details
            }
            
            // Xóa đơn hàng chính
            if ($this->orderModel->delete($id)) {
                $_SESSION['success'] = 'Xóa đơn hàng thành công';
            } else {
                $_SESSION['error'] = 'Xóa thất bại';
            }
            
            header('Location: /AdminOrder/index');  // ✅ Redirect đúng format
            exit;
        }
    }
}
?>