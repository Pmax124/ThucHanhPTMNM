<?php
require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/models/CategoryModel.php');
require_once 'app/models/ReviewModel.php';

class ProductController {
    private $productModel;
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
    }

    public function index() {
        $products = $this->productModel->getProducts();
        include 'app/views/product/list.php';
    }

    public function show($id) {
        $product = $this->productModel->getProductById($id);
        if ($product) {
            // Lấy danh sách ảnh phụ từ bảng product_images
            $stmt = $this->db->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY id ASC");
            $stmt->execute([$id]);
            $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Nếu không có ảnh phụ, dùng ảnh chính của sản phẩm
            if (empty($images) && !empty($product->image)) {
                $images = [$product->image];
            }
            
            include 'app/views/product/show.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    public function add() {
        $categories = (new CategoryModel($this->db))->getCategories();
        include_once 'app/views/product/add.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? '';
            $category_id = $_POST['category_id'] ?? null;

            // Ảnh đại diện (tạm thời để trống, sẽ cập nhật sau nếu có ảnh)
            $main_image = "";

            // 1. Lưu sản phẩm trước để lấy ID
            $result = $this->productModel->addProduct($name, $description, $price, $category_id, $main_image);

            if (is_array($result)) {
                $errors = $result;
                $categories = (new CategoryModel($this->db))->getCategories();
                include 'app/views/product/add.php';
            } else {
                $productId = $this->db->lastInsertId(); // Lấy ID sản phẩm vừa tạo

                // 2. Xử lý upload nhiều ảnh (từ input name="images[]")
                if (isset($_FILES['images']['name'][0]) && $_FILES['images']['name'][0] != '') {
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $file_name = $_FILES['images']['name'][$key];
                        $file_error = $_FILES['images']['error'][$key];
                        
                        if ($file_error == 0 && !empty($file_name)) {
                            $file_tmp = $_FILES['images']['tmp_name'][$key];
                            $target_file = $this->uploadImageSingle($file_tmp, $file_name);
                            
                            if ($target_file) {
                                // Lưu vào bảng product_images
                                $stmt = $this->db->prepare("INSERT INTO product_images (product_id, image_path) VALUES (:pid, :img)");
                                $stmt->execute([':pid' => $productId, ':img' => $target_file]);
                                
                                // Ảnh đầu tiên làm ảnh đại diện cho sản phẩm
                                if ($key == 0) {
                                    $stmtUpdate = $this->db->prepare("UPDATE product SET image = :img WHERE id = :id");
                                    $stmtUpdate->execute([':img' => $target_file, ':id' => $productId]);
                                }
                            }
                        }
                    }
                }
                
                header('Location: /Product/list');
                exit();
            }
        }
    }

    public function edit($id) {
        $product = $this->productModel->getProductById($id);
        // Lấy danh sách ảnh hiện có của sản phẩm
        $stmt = $this->db->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
        $stmt->execute([$id]);
        $existing_images = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $categories = (new CategoryModel($this->db))->getCategories();
        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];

            // Xử lý ảnh đại diện (giữ nguyên logic cũ)
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                $image = $_POST['existing_image'] ?? '';
            }

            $edit = $this->productModel->updateProduct($id, $name, $description, $price, $category_id, $image);
            
            // Xử lý upload thêm ảnh mới (nếu có input images[])
            if (isset($_FILES['images']['name'][0]) && $_FILES['images']['name'][0] != '') {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    $file_name = $_FILES['images']['name'][$key];
                    $file_error = $_FILES['images']['error'][$key];
                    
                    if ($file_error == 0 && !empty($file_name)) {
                        $file_tmp = $_FILES['images']['tmp_name'][$key];
                        $target_file = $this->uploadImageSingle($file_tmp, $file_name);
                        
                        if ($target_file) {
                            $stmt = $this->db->prepare("INSERT INTO product_images (product_id, image_path) VALUES (:pid, :img)");
                            $stmt->execute([':pid' => $id, ':img' => $target_file]);
                        }
                    }
                }
            }
            
            if ($edit) {
                header('Location: /Product/list');
            } else {
                echo "Đã xảy ra lỗi khi lưu sản phẩm.";
            }
        }
    }

    public function delete($id) {
        // Xóa các ảnh phụ trong thư mục uploads trước khi xóa DB
        $stmt = $this->db->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($images as $img_path) {
            if (file_exists($img_path)) {
                unlink($img_path);
            }
        }
        
        if ($this->productModel->deleteProduct($id)) {
            header('Location: /Product/list');
        } else {
            echo "Đã xảy ra lỗi khi xóa sản phẩm.";
        }
    }

    // Hàm upload ảnh đơn lẻ (dùng cho multiple upload)
    private function uploadImageSingle($tmp_name, $file_name) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Tạo tên file duy nhất để tránh trùng
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_filename = time() . '_' . uniqid() . '.' . $extension;
        $target_file = $target_dir . $new_filename;
        
        $check = getimagesize($tmp_name);
        if ($check === false) {
            return false;
        }
        
        $fileSize = filesize($tmp_name);
        if ($fileSize > 10 * 1024 * 1024) {
            return false;
        }
        
        if (!in_array($extension, ["jpg", "jpeg", "png", "gif", "webp"])) {
            return false;
        }
        
        if (move_uploaded_file($tmp_name, $target_file)) {
            return $target_file;
        }
        return false;
    }

    // Hàm upload ảnh cũ (giữ nguyên cho các trường hợp dùng single upload)
    private function uploadImage($file) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            throw new Exception("File không phải là hình ảnh.");
        }
        if ($file["size"] > 10 * 1024 * 1024) {
            throw new Exception("Hình ảnh có kích thước quá lớn.");
        }
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            throw new Exception("Chỉ cho phép các định dạng JPG, JPEG, PNG và GIF.");
        }
        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception("Có lỗi xảy ra khi tải lên hình ảnh.");
        }
        return $target_file;
    }

    // ========== GIỎ HÀNG & ĐẶT HÀNG ==========

    public function addToCart($id) {
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            echo "Không tìm thấy sản phẩm.";
            return;
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->image
            ];
        }
        header('Location: /Product/cart');
        exit();
    }

    public function cart() {
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        include 'app/views/product/cart.php';
    }

    public function removeFromCart($id) {
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
        header('Location: /Product/cart');
        exit();
    }

    public function updateCart() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
            foreach ($_POST['quantities'] as $product_id => $quantity) {
                $quantity = (int)$quantity;
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                } elseif (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                }
            }
        }
        header('Location: /Product/cart');
        exit();
    }

    public function checkout() {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            header('Location: /Product/cart');
            exit();
        }
        include 'app/views/product/checkout.php';
    }

    public function processCheckout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Product/cart');
            exit();
        }
    
        // 1. Lấy dữ liệu từ form
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $payment_method = $_POST['payment_method'] ?? 'cod';
        $voucher_code = trim($_POST['voucher_code'] ?? '');
        $original_total = $_POST['original_total'] ?? 0;
    
        // 2. Validation cơ bản
        if (empty($name) || empty($phone) || empty($address)) {
            $error = "Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ nhận hàng.";
            $cart = $_SESSION['cart'] ?? [];
            include 'app/views/product/checkout.php';
            return;
        }
    
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            header('Location: /Product/cart');
            exit();
        }
    
        // 3. Tính tổng tiền giỏ hàng
        $cart = $_SESSION['cart'];
        $total_amount = 0;
        foreach ($cart as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }
    
        // 4. Xử lý voucher nếu có
        $discount_amount = 0;
        $validatedVoucher = null;
        
        if (!empty($voucher_code) && $total_amount > 0) {
            require_once 'app/models/VoucherModel.php';
            $voucherModel = new VoucherModel($this->db);
            $validation = $voucherModel->validateVoucher($voucher_code, $total_amount);
            
            if ($validation['valid']) {
                $discount_amount = $validation['discount'];
                $validatedVoucher = $validation['voucher'];
            }
        }
    
        // Tính tổng cuối cùng sau khi trừ discount
        $final_amount = $total_amount - $discount_amount;
    
        // 5. Bắt đầu giao dịch database
        $this->db->beginTransaction();
        try {
            // Lưu thông tin đơn hàng vào bảng orders
            $query = "INSERT INTO orders (
                        name, phone, email, city, address, note, 
                        payment_method, total_amount, discount_amount, 
                        voucher_code, status, created_at
                    ) VALUES (
                        :name, :phone, :email, :city, :address, :note,
                        :payment_method, :total, :discount, :voucher, 'pending', NOW()
                    )";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':email' => $email ?: null,
                ':city' => $city ?: null,
                ':address' => $address,
                ':note' => $note ?: null,
                ':payment_method' => $payment_method,
                ':total' => $total_amount,
                ':discount' => $discount_amount,
                ':voucher' => $voucher_code ?: null
            ]);
    
            $order_id = $this->db->lastInsertId();
    
            // Lưu chi tiết đơn hàng vào bảng order_details
            $detailQuery = "INSERT INTO order_details (order_id, product_id, quantity, price) 
                            VALUES (:order_id, :product_id, :quantity, :price)";
            $stmtDetail = $this->db->prepare($detailQuery);
    
            foreach ($cart as $product_id => $item) {
                $stmtDetail->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $product_id,
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price']
                ]);
            }
    
            // Nếu voucher hợp lệ, tăng số lần sử dụng
            if ($validatedVoucher) {
                $stmtUsage = $this->db->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = :id");
                $stmtUsage->execute([':id' => $validatedVoucher->id]);
            }
    
            // Xóa giỏ hàng sau khi đặt thành công
            unset($_SESSION['cart']);
    
            // Commit giao dịch
            $this->db->commit();
    
            // Chuyển hướng đến trang xác nhận
            header('Location: /Product/orderConfirmation');
            exit();
    
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->db->rollBack();
            $error = "Lỗi hệ thống: " . $e->getMessage();
            $cart = $_SESSION['cart'] ?? [];
            include 'app/views/product/checkout.php';
        }
    }

    public function orderConfirmation() {
        include 'app/views/product/orderConfirmation.php';
    }

    public function list() {
        $products = $this->productModel->getProducts();
        require_once 'app/views/product/list.php';
    }

    public function review($product_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $customer_name = $_POST['customer_name'] ?? '';
            $customer_email = $_POST['customer_email'] ?? '';
            $rating = $_POST['rating'] ?? 0;
            $comment = $_POST['comment'] ?? '';
            
            if (empty($customer_name) || empty($rating) || $rating < 1 || $rating > 5) {
                $error = "Vui lòng điền đầy đủ thông tin và chọn số sao đánh giá";
            } else {
                $db = (new Database())->getConnection();
                $reviewModel = new ReviewModel($db);
                
                if ($reviewModel->addReview($product_id, $customer_name, $customer_email, $rating, $comment)) {
                    header("Location: /Product/show/" . $product_id . "?review_success=1");
                    exit();
                } else {
                    $error = "Có lỗi xảy ra, vui lòng thử lại";
                }
            }
        }
        header("Location: /Product/show/" . $product_id);
        exit();
    }

    public function addToCartAjax($id) {
        header('Content-Type: application/json');
        
        $product = $this->productModel->getProductById($id);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
            return;
        }
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->image
            ];
        }
        
        $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng',
            'cartCount' => $cartCount
        ]);
    }

}
?>