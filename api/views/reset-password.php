<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-header i {
            font-size: 3rem;
            color: #ffc107;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-reset {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            border: none;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
        }
        .alert-custom {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-header">
            <i class="fas fa-key"></i>
            <h2>Đặt lại mật khẩu</h2>
            <p class="text-muted">Nhập mật khẩu mới cho tài khoản của bạn</p>
        </div>
        
        <div id="alert-box"></div>
        
        <form id="reset-form">
            <input type="hidden" id="reset-token">
            
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-ticket-alt"></i> Token</label>
                <input type="text" class="form-control" id="token-display" readonly>
                <small class="text-muted">Token được gửi đến email của bạn</small>
            </div>
            
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-key"></i> Mật khẩu mới *</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="new-password" required placeholder="Ít nhất 6 ký tự">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new-password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-lock"></i> Xác nhận mật khẩu *</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm-password" required placeholder="Nhập lại mật khẩu">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm-password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-reset">
                <i class="fas fa-save"></i> Đặt lại mật khẩu
            </button>
            
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Lấy token từ URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        
        if (!token) {
            showAlert('❌ Thiếu token reset password. Vui lòng yêu cầu reset password mới.', 'danger');
            $('#reset-form').hide();
        } else {
            $('#reset-token').val(token);
            $('#token-display').val(token);
        }
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        function showAlert(message, type = 'danger') {
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
            $('#alert-box').html(`
                <div class="alert alert-${type} alert-custom">
                    <i class="fas fa-${icon}"></i> ${message}
                </div>
            `);
        }
        
        $('#reset-form').submit(function(e) {
            e.preventDefault();
            $('#alert-box').html('');
            
            const token = $('#reset-token').val();
            const newPassword = $('#new-password').val();
            const confirmPassword = $('#confirm-password').val();
            
            // Validation
            if (newPassword.length < 6) {
                showAlert('❌ Mật khẩu phải có ít nhất 6 ký tự');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showAlert('❌ Mật khẩu xác nhận không khớp');
                return;
            }
            
            $.ajax({
                url: '/api/auth?action=reset-password',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    token: token,
                    new_password: newPassword
                }),
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        showAlert('✅ ' + res.message + '<br><br><a href="login.php" class="btn btn-primary mt-2"><i class="fas fa-sign-in-alt"></i> Đăng nhập ngay</a>', 'success');
                        $('#reset-form').hide();
                    } else {
                        showAlert('❌ ' + res.message);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Lỗi server: ' + xhr.status;
                    showAlert('❌ ' + msg);
                }
            });
        });
    </script>
</body>
</html>