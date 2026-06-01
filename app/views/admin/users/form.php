<?php include 'app/views/shares/header.php'; 
$isEdit = isset($user) && !empty($user);
?>

<div class="container mt-4 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-user-<?= $isEdit ? 'edit' : 'plus' ?>"></i> <?= $isEdit ? 'Sửa tài khoản #' . $user['id'] : 'Tạo tài khoản mới' ?></h4>
            <a href="/User/list" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= $isEdit ? "/User/update/{$user['id']}" : '/User/save' ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mật khẩu <?= $isEdit ? '(để trống nếu không đổi)' : '<span class="text-danger">*</span>' ?></label>
                        <input type="password" name="password" class="form-control" <?= !$isEdit ? 'required' : '' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Vai trò</label>
                        <select name="role" class="form-select">
                            <option value="user" <?= ($user['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>Người dùng</option>
                            <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" <?= ($user['status'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="status">Tài khoản hoạt động</label>
                        </div>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Lưu</button>
                        <a href="/User/list" class="btn btn-secondary px-4">Hủy</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>