<?php
class AccountModel {
    private $db;
    // private $conn; // ❌ Xóa biến thừa này để tránh nhầm lẫn với $db
    private $table_name = "account";

    public function __construct($db) {
        $this->db = $db; // ✅ Gán vào $db
    }

    public function getAccountByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        
        // ✅ SỬA: Dùng $this->db thay vì $this->conn
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function save($username, $fullName, $password, $role = 'user') {
        if ($this->getAccountByUsername($username)) {
            return false;
        }
        $query = "INSERT INTO " . $this->table_name . " SET username=:username, fullname=:fullname, password=:password, role=:role";
        
        // ✅ SỬA: Dùng $this->db thay vì $this->conn
        $stmt = $this->db->prepare($query);
        
        $username = htmlspecialchars(strip_tags($username));
        $fullName = htmlspecialchars(strip_tags($fullName));
        $password = password_hash($password, PASSWORD_BCRYPT);
        $role = htmlspecialchars(strip_tags($role));
        
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":fullname", $fullName);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":role", $role);
        return $stmt->execute();
    }

    /**
     * Lấy thông tin tài khoản theo ID
     */
    public function getById($id) {
        if (!$this->db) {
            throw new Exception("Database connection is missing in AccountModel");
        }

        $sql = "SELECT * FROM account WHERE id = ?";
        // ✅ SỬA: Dùng $this->db
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ); 
    }

    /**
     * ✅ SỬA: Chuyển hàm update VÀO TRONG class (trước dấu } đóng class)
     */
    public function update($userId, $data) {
        if (!$this->db) {
            error_log("Database connection is null in AccountModel::update()");
            return false;
        }
        
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            // ✅ SỬA: Chấp nhận cả giá trị rỗng (empty string)
            if ($value !== null && $value !== '') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        // ✅ DEBUG
        error_log("Fields to update: " . implode(', ', $fields));
        error_log("Values: " . print_r($values, true));
        error_log("User ID: " . $userId);
        
        if (empty($fields)) {
            error_log("No fields to update!");
            return false;
        }
        
        $values[] = $userId;
        
        $sql = "UPDATE account SET " . implode(', ', $fields) . " WHERE id = ?";
        
        // ✅ DEBUG: In ra SQL
        error_log("SQL Query: " . $sql);
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($values);
            
            if ($result) {
                error_log("Update successful! Rows affected: " . $stmt->rowCount());
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Update failed! Error: " . $errorInfo[2]);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in update(): " . $e->getMessage());
            return false;
        }
    }

} // ✅ Dấu đóng class PHẢI nằm ở cuối cùng, sau tất cả các hàm
?>