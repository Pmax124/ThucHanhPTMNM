<?php
class VoucherModel {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Lấy tất cả voucher (cho admin)
    public function getAllVouchers() {
        $stmt = $this->db->query("SELECT * FROM vouchers ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    // Lấy voucher active (cho khách hàng)
    public function getActiveVouchers() {
        $query = "SELECT * FROM vouchers 
                  WHERE is_active = 1 
                  AND (start_date IS NULL OR start_date <= NOW()) 
                  AND (end_date IS NULL OR end_date >= NOW())
                  AND (usage_limit IS NULL OR used_count < usage_limit)
                  ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    // Lấy voucher theo ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM vouchers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    // Kiểm tra mã voucher có tồn tại không
    public function voucherExists($code, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM vouchers WHERE code = ?";
        if ($excludeId) {
            $query .= " AND id != ?";
        }
        $stmt = $this->db->prepare($query);
        if ($excludeId) {
            $stmt->execute([$code, $excludeId]);
        } else {
            $stmt->execute([$code]);
        }
        return $stmt->fetchColumn() > 0;
    }
    
    // ✅ Tạo voucher mới - ĐÃ SỬA: Thêm used_count = 0
    public function create($data) {
        try {
            $query = "INSERT INTO vouchers (
                code, description, discount_type, discount_value, 
                min_order_value, max_discount, usage_limit, used_count,
                start_date, end_date, is_active, created_at
            ) VALUES (
                :code, :desc, :type, :value, :min, :max, :limit, 0,
                :start, :end, :active, NOW()
            )";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':code' => $data['code'],
                ':desc' => $data['description'],
                ':type' => $data['discount_type'],
                ':value' => $data['discount_value'],
                ':min' => $data['min_order_value'],
                ':max' => $data['max_discount'],
                ':limit' => $data['usage_limit'],
                ':start' => $data['start_date'],
                ':end' => $data['end_date'],
                ':active' => $data['is_active']
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Voucher Insert Error: " . print_r($errorInfo, true));
                throw new Exception("Lỗi SQL: " . $errorInfo[2]);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Voucher Create PDO Error: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
    
    // ✅ Cập nhật voucher - ĐÃ SỬA: Thêm used_count
    public function update($id, $data) {
        $query = "UPDATE vouchers SET 
            code = :code, description = :desc, discount_type = :type, 
            discount_value = :value, min_order_value = :min, 
            max_discount = :max, usage_limit = :limit,
            used_count = COALESCE(used_count, 0),
            start_date = :start, end_date = :end, is_active = :active 
            WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':code' => $data['code'],
            ':desc' => $data['description'],
            ':type' => $data['discount_type'],
            ':value' => $data['discount_value'],
            ':min' => $data['min_order_value'],
            ':max' => $data['max_discount'],
            ':limit' => $data['usage_limit'],
            ':start' => $data['start_date'],
            ':end' => $data['end_date'],
            ':active' => $data['is_active']
        ]);
    }
    
    // Xóa voucher
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM vouchers WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Validate & tính discount
    public function validateVoucher($code, $orderTotal) {
        $query = "SELECT * FROM vouchers 
                  WHERE code = :code 
                  AND is_active = 1 
                  AND (start_date IS NULL OR start_date <= NOW()) 
                  AND (end_date IS NULL OR end_date >= NOW())
                  AND (usage_limit IS NULL OR used_count < usage_limit)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':code' => $code]);
        $voucher = $stmt->fetch(PDO::FETCH_OBJ);
        
        if (!$voucher) {
            return ['valid' => false, 'message' => 'Mã voucher không hợp lệ'];
        }
        
        if ($orderTotal < $voucher->min_order_value) {
            return ['valid' => false, 'message' => 'Đơn hàng cần đạt tối thiểu ' . number_format($voucher->min_order_value, 0, ',', '.') . 'đ'];
        }
        
        $discount = 0;
        if ($voucher->discount_type == 'percent') {
            $discount = $orderTotal * ($voucher->discount_value / 100);
            if ($voucher->max_discount && $discount > $voucher->max_discount) {
                $discount = $voucher->max_discount;
            }
        } else {
            $discount = $voucher->discount_value;
        }
        
        return [
            'valid' => true,
            'voucher' => $voucher,
            'discount' => round($discount),
            'message' => 'Áp dụng thành công!'
        ];
    }
    
    // Tăng số lần sử dụng
    public function incrementUsage($voucherId) {
        $stmt = $this->db->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?");
        return $stmt->execute([$voucherId]);
    }
}
?>