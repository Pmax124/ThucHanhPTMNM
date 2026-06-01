<?php include 'app/views/shares/header.php'; ?>

<style>
    .profile-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 20px;
    }
    
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        margin-bottom: 15px;
    }
    
    .profile-name {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .profile-role {
        background: rgba(255,255,255,0.2);
        padding: 5px 15px;
        border-radius: 20px;
        display: inline-block;
    }
    
    .profile-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        padding: 30px;
        margin-bottom: 30px;
    }
    
    .profile-card h3 {
        color: #667eea;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .form-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
    }
    
    .form-control {
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        padding: 12px 15px;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .btn-update {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        color: white;
        transition: transform 0.3s;
    }
    
    .btn-update:hover {
        transform: translateY(-2px);
        color: white;
    }
    
    .avatar-upload {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .avatar-upload label {
        display: inline-block;
        padding: 10px 20px;
        background: #667eea;
        color: white;
        border-radius: 25px;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    .avatar-upload label:hover {
        background: #5568d3;
    }
    
    .avatar-upload input[type="file"] {
        display: none;
    }
    
    .avatar-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
        border: 3px solid #ddd;
    }
</style>

<div class="profile-container">
    <!-- Header -->
    <div class="profile-header">
        <img src="<?= !empty($account->avatar) ? '/' . htmlspecialchars($account->avatar) : 'https://via.placeholder.com/150?text=' . strtoupper(substr($account->username, 0, 1)) ?>" 
             alt="Avatar" 
             class="profile-avatar"
             id="headerAvatar">
        <div class="profile-name"><?= htmlspecialchars($account->fullname ?? $account->username) ?></div>
        <span class="profile-role"><?= strtoupper($account->role) ?></span>
    </div>

    <!-- Thông báo -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form cập nhật thông tin -->
    <div class="profile-card">
        <h3><i class="fas fa-user-edit me-2"></i>Thông tin tài khoản</h3>
        
        <form method="POST" action="/account/updateProfile" enctype="multipart/form-data">
            <!-- Upload Avatar -->
            <div class="avatar-upload text-center mb-4">
                <img src="<?= !empty($account->avatar) ? '/' . htmlspecialchars($account->avatar) : 'https://via.placeholder.com/120?text=' . strtoupper(substr($account->username, 0, 1)) ?>" 
                     alt="Avatar Preview" 
                     class="avatar-preview"
                     id="avatarPreview">
                <br>
                <label for="avatarInput">
                    <i class="fas fa-camera me-2"></i>Thay đổi ảnh đại diện
                </label>
                <input type="file" id="avatarInput" name="avatar" accept="image/*" onchange="previewAvatar(this)">
                <small class="d-block mt-2 text-muted">JPG, PNG, GIF (Tối đa 5MB)</small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên đăng nhập</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($account->username) ?>" disabled>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <small class="text-muted">(Optional)</small></label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($account->email ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($account->fullname ?? '') ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số điện thoại <small class="text-muted">(Optional)</small></label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($account->phone ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Địa chỉ <small class="text-muted">(Optional)</small></label>
                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($account->address ?? '') ?></textarea>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-update">
                    <i class="fas fa-save me-2"></i>Cập nhật thông tin
                </button>
            </div>
        </form>
    </div>

    <!-- Form đổi mật khẩu -->
    <div class="profile-card">
        <h3><i class="fas fa-lock me-2"></i>Đổi mật khẩu</h3>
        
        <form method="POST" action="/account/changePassword">
            <div class="mb-3">
                <label class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                <input type="password" name="old_password" class="form-control" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                    <input type="password" name="new_password" class="form-control" minlength="6" required>
                    <small class="text-muted">Ít nhất 6 ký tự</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-update">
                    <i class="fas fa-key me-2"></i>Đổi mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Preview avatar trước khi upload
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
            document.getElementById('headerAvatar').src = e.target.result;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'app/views/shares/footer.php'; ?>