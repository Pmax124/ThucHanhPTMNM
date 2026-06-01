<?php
class UserModel {
    private $db;
    private $table = 'account';

    public function __construct($db) {
        $this->db = $db;
    }

    // Lấy danh sách user có tìm kiếm & phân trang
    public function getAll($search = '', $limit = 20, $offset = 0) {
        $sql = "SELECT id, username, email, phone, address, role, status, created_at 
                FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (username LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $i => $val) {
            $stmt->bindValue($i + 1, $val, PDO::PARAM_STR);
        }
        $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Đếm tổng số user (có tìm kiếm)
    public function countAll($search = '') {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (username LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Lấy user theo ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo user mới
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (username, password, email, phone, address, role, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['email'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['role'] ?? 'user',
            $data['status'] ?? 1
        ]);
    }

    // Cập nhật user
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET username=?, email=?, phone=?, address=?, role=?, status=?";
        $params = [
            $data['username'], $data['email'], 
            $data['phone'] ?? null, $data['address'] ?? null, 
            $data['role'], $data['status']
        ];
        
        // Nếu có nhập password mới thì cập nhật
        if (!empty($data['password'])) {
            $sql .= ", password=?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id=?";
        $params[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Xóa user
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>