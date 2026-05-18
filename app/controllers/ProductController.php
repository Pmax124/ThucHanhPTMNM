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
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
$image = $this->uploadImage($_FILES['image']);
} else {
$image = "";
}

$result = $this->productModel->addProduct($name, $description, $price,

$category_id, $image);

if (is_array($result)) {
$errors = $result;
$categories = (new CategoryModel($this->db))->getCategories();
include 'app/views/product/add.php';
} else {
header('Location: /Product');
}
}
}
public function edit($id) {
$product = $this->productModel->getProductById($id);
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
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
$image = $this->uploadImage($_FILES['image']);
} else {
$image = $_POST['existing_image'];
}
$edit = $this->productModel->updateProduct($id, $name, $description,

$price, $category_id, $image);
if ($edit) {
header('Location: /Product');
} else {
echo "Đã xảy ra lỗi khi lưu sản phẩm.";
}
}
}


public function delete($id) {
if ($this->productModel->deleteProduct($id)) {
header('Location: /Product');
} else {
echo "Đã xảy ra lỗi khi xóa sản phẩm.";
}
}
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
}
public function list() {
$products = $this->productModel->getProducts();
require_once 'app/views/product/list.php';
}

// Thêm method này vào ProductController
public function review($product_id)
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $customer_name = $_POST['customer_name'] ?? '';
        $customer_email = $_POST['customer_email'] ?? '';
        $rating = $_POST['rating'] ?? 0;
        $comment = $_POST['comment'] ?? '';
        
        // Validation
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
    
    // Redirect về trang chi tiết
    header("Location: /Product/show/" . $product_id);
    exit();
}

}
?>