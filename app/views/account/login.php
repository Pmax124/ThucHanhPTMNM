<?php include 'app/views/shares/header.php'; ?>

<style>
    .gradient-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        transform: translateY(0);
        transition: transform 0.3s ease;
    }
    
    .login-card:hover {
        transform: translateY(-5px);
    }
    
    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 2px solid #e1e1e1;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .input-group-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 0 0 10px;
        border: 2px solid #667eea;
    }
    
    .btn-login {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 12px 40px;
        border-radius: 25px;
        font-weight: 600;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-google {
        background: linear-gradient(135deg, #db4437 0%, #c53929 100%);
        border: none;
        color: white;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-google:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(219, 68, 55, 0.4);
        color: white;
    }
    
    .divider-text {
        position: relative;
        text-align: center;
        margin: 20px 0;
    }
    
    .divider-text::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #ddd, transparent);
    }
    
    .divider-text span {
        background: white;
        padding: 0 15px;
        position: relative;
        color: #666;
        font-size: 0.9rem;
    }
    
    .social-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 5px;
        transition: all 0.3s ease;
        background: #f0f0f0;
        color: #333;
    }
    
    .social-icon:hover {
        background: #667eea;
        color: white;
        transform: translateY(-3px);
    }
    
    .brand-logo {
        font-size: 3rem;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

<section class="vh-100 gradient-custom">
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card login-card p-5">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="brand-logo">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <h2 class="fw-bold mb-2" style="color: #667eea;">Chào mừng trở lại!</h2>
                            <p class="text-muted mb-4">Đăng nhập để tiếp tục mua sắm</p>
                        </div>

                        <form action="/account/checklogin" method="post">
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" 
                                           name="username" 
                                           class="form-control form-control-lg" 
                                           placeholder="Tên đăng nhập"
                                           required />
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           name="password" 
                                           class="form-control form-control-lg" 
                                           placeholder="Mật khẩu"
                                           required />
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe">
                                    <label class="form-check-label text-muted" for="rememberMe">
                                        Ghi nhớ đăng nhập
                                    </label>
                                </div>
                                <a href="/account/forgot-password" class="text-primary text-decoration-none">
                                    Quên mật khẩu?
                                </a>
                            </div>

                            <div class="text-center mb-4">
                                <button class="btn btn-login btn-lg px-5" type="submit">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                </button>
                            </div>

                            <div class="divider-text">
                                <span>Hoặc đăng nhập với</span>
                            </div>

                            <div class="text-center mb-4">
                                <a href="/account/google-login" class="btn btn-google btn-lg w-100">
                                    <i class="fab fa-google me-2"></i>Đăng nhập bằng Google
                                </a>
                            </div>

                            <div class="text-center">
                                <div class="mb-3">
                                    <a href="#!" class="social-icon">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="#!" class="social-icon">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#!" class="social-icon">
                                        <i class="fab fa-github"></i>
                                    </a>
                                </div>
                                <p class="mb-0 text-muted">
                                    Chưa có tài khoản? 
                                    <a href="/account/register" class="text-decoration-none fw-bold" style="color: #667eea;">
                                        Đăng ký ngay
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'app/views/shares/footer.php'; ?>