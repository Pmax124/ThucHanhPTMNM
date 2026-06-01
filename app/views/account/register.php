<?php include 'app/views/shares/header.php'; ?>

<style>
    .register-container {
        max-width: 900px;
        margin: 50px auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 10px;
        overflow: hidden;
    }
    .register-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        text-align: center;
    }
    .form-control {
        border-radius: 8px;
        padding: 10px 15px;
    }
    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
        border-radius: 8px 0 0 8px;
    }
    .form-control:focus {
        box-shadow: none;
        border-color: #667eea;
    }
    .input-group:focus-within .input-group-text {
        border-color: #667eea;
    }
    .btn-register {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 12px;
        font-weight: bold;
        letter-spacing: 1px;
        border-radius: 8px;
        transition: 0.3s;
    }
    .btn-register:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
    }
</style>

<div class="container mb-5">
    <div class="card register-container">
        <div class="register-header">
            <h2 class="mb-0"><i class="fas fa-user-plus me-2"></i> Đăng Ký Tài Khoản</h2>
            <p class="mb-0 opacity-75">Tạo tài khoản để mua sắm và nhận ưu đãi</p>
        </div>
        
        <div class="card-body p-4">
            <!-- Hiển thị lỗi nếu có -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Hiển thị thành công -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="/account/register" method="POST">
                <div class="row g-3">
                    
                    <!-- Cột trái: Thông tin đăng nhập -->
                    <div class="col-md-6">
                        <h5 class="text-primary mb-3"><i class="fas fa-lock me-2"></i>Thông tin đăng nhập</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="VD: nguyenvana" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Mật khẩu (tối thiểu 6 ký tự)" required minlength="6">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required minlength="6">
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải: Thông tin cá nhân -->
                    <div class="col-md-6">
                        <h5 class="text-primary mb-3"><i class="fas fa-address-card me-2"></i>Thông tin cá nhân</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" name="fullname" class="form-control" placeholder="VD: Nguyễn Văn A" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="phone" class="form-control" placeholder="0901234567" required pattern="[0-9]{10,11}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ nhận hàng</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" name="address" class="form-control" placeholder="Số nhà, đường, phường...">
                            </div>
                        </div>
                    </div>

                    <!-- Nút đăng ký -->
                    <div class="col-12 mt-4 text-center">
                        <button type="submit" class="btn btn-primary btn-register w-50">
                            <i class="fas fa-user-plus me-2"></i> ĐĂNG KÝ NGAY
                        </button>
                        <div class="mt-3">
                            <span class="text-muted">Đã có tài khoản? <a href="/account/login" class="text-decoration-none fw-bold">Đăng nhập ngay</a></span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>