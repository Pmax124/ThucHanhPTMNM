<?php
class ProductModel
{
private $conn;
private $table_name = "product";
public function __construct($db)
{
$this->conn = $db;
}
public function getProducts()
{
$query = "SELECT p.id, p.name, p.description, p.price, p.image, c.name as
category_name
FROM " . $this->table_name . " p
LEFT JOIN category c ON p.category_id = c.id";


$stmt = $this->conn->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_OBJ);
return $result;
}
public function getProductById($id)
{
$query = "SELECT p.*, c.name as category_name
FROM " . $this->table_name . " p
LEFT JOIN category c ON p.category_id = c.id
WHERE p.id = :id";
$stmt = $this->conn->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_OBJ);
return $result;
}
public function addProduct($name, $description, $price, $category_id, $image)
{
$errors = [];
if (empty($name)) {
$errors['name'] = 'Tên sản phẩm không được để trống';
}
if (empty($description)) {
$errors['description'] = 'Mô tả không được để trống';
}
if (!is_numeric($price) || $price < 0) {
$errors['price'] = 'Giá sản phẩm không hợp lệ';
}
if (count($errors) > 0) {
return $errors;
}
$query = "INSERT INTO " . $this->table_name . " (name, description, price,
category_id, image) VALUES (:name, :description, :price, :category_id, :image)";
$stmt = $this->conn->prepare($query);
$name = htmlspecialchars(strip_tags($name));
$description = htmlspecialchars(strip_tags($description));
$price = htmlspecialchars(strip_tags($price));
$category_id = htmlspecialchars(strip_tags($category_id));
$image = htmlspecialchars(strip_tags($image));
$stmt->bindParam(':name', $name);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':price', $price);
$stmt->bindParam(':category_id', $category_id);
$stmt->bindParam(':image', $image);
if ($stmt->execute()) {
return true;
}

return false;
}
public function updateProduct(
$id,
$name,
$description,
$price,
$category_id,
$image
) {
$query = "UPDATE " . $this->table_name . " SET name=:name,
description=:description, price=:price, category_id=:category_id, image=:image WHERE
id=:id";
$stmt = $this->conn->prepare($query);
$name = htmlspecialchars(strip_tags($name));
$description = htmlspecialchars(strip_tags($description));
$price = htmlspecialchars(strip_tags($price));
$category_id = htmlspecialchars(strip_tags($category_id));
$image = htmlspecialchars(strip_tags($image));
$stmt->bindParam(':id', $id);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':description', $description);
$stmt->bindParam(':price', $price);
$stmt->bindParam(':category_id', $category_id);
$stmt->bindParam(':image', $image);
if ($stmt->execute()) {
return true;
}
return false;
}
public function deleteProduct($id)
{
$query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
$stmt = $this->conn->prepare($query);
$stmt->bindParam(':id', $id);
if ($stmt->execute()) {
return true;
}
return false;
}

// Lọc sản phẩm theo tên và danh mục (dùng cho trang chủ)
public function getProductsFiltered($keyword = '', $category_id = 0, $price_min = 0, $price_max = 0)
{
    $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name 
              FROM product p 
              LEFT JOIN category c ON p.category_id = c.id 
              WHERE 1=1";
    
    $params = [];
    
    // Lọc theo tên sản phẩm hoặc mô tả
    if (!empty($keyword)) {
        $query .= " AND (p.name LIKE :keyword OR p.description LIKE :keyword)";
        $params[':keyword'] = "%{$keyword}%";
    }
    
    // Lọc theo danh mục
    if ($category_id > 0) {
        $query .= " AND p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    // Lọc theo giá (THÊM PHẦN NÀY)
    if ($price_min > 0) {
        $query .= " AND p.price >= :price_min";
        $params[':price_min'] = $price_min;
    }
    
    if ($price_max > 0) {
        $query .= " AND p.price <= :price_max";
        $params[':price_max'] = $price_max;
    }
    
    $query .= " ORDER BY p.id DESC";
    
    $stmt = $this->conn->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}


}