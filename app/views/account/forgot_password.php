<?php include 'app/views/shares/header.php'; ?>

<style>
    .forgot-container {
        max-width: 500px;
        margin: 50px auto;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .forgot-header {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .forgot-header i {
        font-size: 4rem;
        color: #667eea;
        margin-bottom: 15px;
    }
    
    .forgot-header h2 {
        color: #333;
        margin-bottom: 10px;
    }
    
    .forgot-header p {
        color: #666;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }
    
    .form-control {
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        padding: 12px 15px;
        width: 100%;
    }
    
    .form-control:focus {
        border-color: #667eea;
        outline: none;
    }
    
    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        width: 100%;
        cursor: pointer;
        transition: transform 0.3s;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
    }
    
    .back-link {
        text-align: center;
        margin-top: 20px;
    }
    
    .back-link a {
        color: #667eea;
        text-decoration: none;
    }
    
    .back-link a:hover {
        text-decoration: underline;
    }
    
    .token-display {
        background: #f8f9fa;
        border-left: 4px solid #28a745;
        padding: 15px;
        margin: 20px 0;
        border-radius: 5px;
    }
    
    .token-display code {
        background: #fff;
        padding: 5px 10px;
        border-radius: 3px;
        display: block;
        margin: 10px 0;
        word-break: break-all;
    }
</style>

<div class="forgot-container">
    <div class="forgot-header">
        <i class="fas fa-lock-open"></i>
        <h2>Quên mật khẩu?</h2>
        <p>Nhập email của bạn để nhận hướng dẫn đặt lại mật khẩu</p>
    </div>

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

    <form method="POST" action="/account/handle-forgot-password">
        <div class="form-group">
            <label for="email">
                <i class="fas fa-envelope me-2"></i>Email
            </label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control" 
                   placeholder="nhap@email.com" 
                   required 
                   autofocus>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-paper-plane me-2"></i>Gửi hướng dẫn
        </button>
    </form>

    <div class="back-link">
        <a href="/account/login">
            <i class="fas fa-arrow-left me-2"></i>Quay lại đăng nhập
        </a>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>