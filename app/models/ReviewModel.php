<?php
class ReviewModel
{
    private $conn;
    private $table_name = "reviews";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Lấy tất cả review của sản phẩm
    public function getReviewsByProduct($product_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE product_id = :product_id 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Tính điểm đánh giá trung bình
    public function getAverageRating($product_id)
    {
        $query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                  FROM " . $this->table_name . " 
                  WHERE product_id = :product_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    // Thêm review mới
    public function addReview($product_id, $customer_name, $customer_email, $rating, $comment)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (product_id, customer_name, customer_email, rating, comment) 
                  VALUES (:product_id, :customer_name, :customer_email, :rating, :comment)";
        
        $stmt = $this->conn->prepare($query);
        
        $customer_name = htmlspecialchars(strip_tags($customer_name));
        $customer_email = htmlspecialchars(strip_tags($customer_email));
        $rating = htmlspecialchars(strip_tags($rating));
        $comment = htmlspecialchars(strip_tags($comment));
        
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':customer_name', $customer_name);
        $stmt->bindParam(':customer_email', $customer_email);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        
        return $stmt->execute();
    }
}
?>