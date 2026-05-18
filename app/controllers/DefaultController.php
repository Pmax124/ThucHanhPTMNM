<?php
require_once 'app/config/database.php'; 
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';

class DefaultController
{
    public function index()
    {
        $db = (new Database())->getConnection();
        $productModel = new ProductModel($db);
        $categoryModel = new CategoryModel($db);
        
        // Lấy từ khóa tìm kiếm và category từ URL
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
        $price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : 0;
        $price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : 0;
    
    // Lấy danh sách sản phẩm có lọc
    $products = $productModel->getProductsFiltered($keyword, $category_id, $price_min, $price_max);
    
    // Lấy danh sách danh mục
    $categories = $categoryModel->getCategories();
    
    // Include view trang chủ
    include 'app/views/home.php';
    }
}