<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - API Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 450px;
        }
        .auth-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .auth-tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
            color: #666;
        }
        .auth-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .auth-tab:hover:not(.active) {
            background: #f0f0f0;
        }
        .auth-form { display: none; }
        .auth-form.active { display: block; }
        .password-wrapper { position: relative; }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
        }
        .forgot-link {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 15px;
        }
        .forgot-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .forgot-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="fas fa-lock fa-3x text-primary" id="main-icon"></i>
            <h2 class="mt-3" id="main-title">Đăng nhập hệ thống</h2>
            <p class="text-muted">API Test Dashboard</p>
        </div>
        
        <!-- TAB SWITCH -->
        <div class="auth-tabs">
            <div class="auth-tab active" onclick="switchTab('login')">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </div>
            <div class="auth-tab" onclick="switchTab('register')">
                <i class="fas fa-user-plus"></i> Đăng ký
            </div>
            <div class="auth-tab" onclick="switchTab('forgot')">
                <i class="fas fa-question-circle"></i> Quên MK
            </div>
        </div>
        
        <div id="alert-box"></div>
        
        <!-- ========== FORM ĐĂNG NHẬP ========== -->
        <form id="login-form" class="auth-form active">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user"></i> Username hoặc Email</label>
                <input type="text" class="form-control" id="username" required placeholder="admin hoặc admin@example.com">
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-key"></i> Mật khẩu</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                </div>
            </div>
            <div class="forgot-link">
                <a onclick="switchTab('forgot')">Quên mật khẩu?</a>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>
            <div class="text-center mt-3">
                <small class="text-muted">
                    Demo: <strong>admin</strong> / <strong>admin123</strong> hoặc <strong>user</strong> / <strong>user123</strong>
                </small>
            </div>
        </form>
        
        <!-- ========== FORM ĐĂNG KÝ ========== -->
        <form id="register-form" class="auth-form">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user"></i> Username *</label>
                <input type="text" class="form-control" id="reg-username" required placeholder="Ít nhất 3 ký tự, chỉ chữ và số">
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-envelope"></i> Email *</label>
                <input type="email" class="form-control" id="reg-email" required placeholder="email@example.com">
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user-circle"></i> Họ tên</label>
                <input type="text" class="form-control" id="reg-fullname" placeholder="Nguyễn Văn A">
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-phone"></i> Số điện thoại</label>
                <input type="text" class="form-control" id="reg-phone" placeholder="0901234567">
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-key"></i> Mật khẩu *</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="reg-password" required placeholder="Ít nhất 6 ký tự">
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('reg-password')"></i>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-lock"></i> Xác nhận mật khẩu *</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="reg-confirm-password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePassword('reg-confirm-password')"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-success w-100 py-2">
                <i class="fas fa-user-plus"></i> Đăng ký
            </button>
        </form>
        
        <!-- ========== FORM QUÊN MẬT KHẨU ========== -->
        <form id="forgot-form" class="auth-form">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nhập email để nhận link đặt lại mật khẩu.
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-envelope"></i> Email của bạn</label>
                <input type="email" class="form-control" id="forgot-email" required placeholder="email@example.com">
            </div>
            <button type="submit" class="btn btn-warning w-100 py-2">
                <i class="fas fa-paper-plane"></i> Gửi link reset
            </button>
            <div id="reset-link-box" class="mt-3" style="display:none;"></div>
        </form>
        
        <!-- ========== FORM RESET MẬT KHẨU ========== -->
        <form id="reset-form" class="auth-form">
            <div class="alert alert-info">
                <i class="fas fa-key"></i> Nhập token và mật khẩu mới.
            </div>
            <input type="hidden" id="reset-token">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-ticket-alt"></i> Token</label>
                <input type="text" class="form-control" id="reset-token-input" required readonly>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-key"></i> Mật khẩu mới *</label>
                <input type="password" class="form-control" id="reset-password" required placeholder="Ít nhất 6 ký tự">
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-lock"></i> Xác nhận mật khẩu *</label>
                <input type="password" class="form-control" id="reset-confirm-password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="fas fa-save"></i> Đặt lại mật khẩu
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // ===== CHUYỂN TAB =====
        function switchTab(tab) {
            $('.auth-tab').removeClass('active');
            $('.auth-form').removeClass('active');
            
            const tabMap = {
                'login': 0,
                'register': 1,
                'forgot': 2
            };
            
            $('.auth-tab').eq(tabMap[tab]).addClass('active');
            $(`#${tab}-form`).addClass('active');
            
            const titles = {
                'login': 'Đăng nhập hệ thống',
                'register': 'Đăng ký tài khoản',
                'forgot': 'Quên mật khẩu'
            };
            const icons = {
                'login': 'fa-lock',
                'register': 'fa-user-plus',
                'forgot': 'fa-question-circle'
            };
            
            $('#main-title').text(titles[tab]);
            $('#main-icon').removeClass().addClass(`fas fa-3x ${icons[tab]} text-primary`);
            
            $('#alert-box').html('');
        }
        
        // ===== TOGGLE PASSWORD =====
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        // ===== HIỂN THỊ ALERT =====
        function showAlert(message, type = 'danger') {
            const icon = type === 'success' ? 'check-circle' : (type === 'info' ? 'info-circle' : 'exclamation-circle');
            $('#alert-box').html(`<div class="alert alert-${type}"><i class="fas fa-${icon}"></i> ${message}</div>`);
        }
        
        $(document).ready(function() {
            // Kiểm tra đã đăng nhập chưa
            const token = localStorage.getItem('token');
            if (token) {
                console.log('✅ Đã có token, chuyển đến index...');
                window.location.replace('index.php');
                return;
            }
            
            // Kiểm tra có token reset từ URL không
            const urlParams = new URLSearchParams(window.location.search);
            const resetToken = urlParams.get('token');
            if (resetToken) {
                $('#reset-token-input').val(resetToken);
                switchTab('reset');
                // Thêm tab reset nếu chưa có
                if ($('.auth-tab').length < 4) {
                    $('.auth-tabs').append('<div class="auth-tab active" onclick="switchTab(\'reset\')"><i class="fas fa-key"></i> Reset</div>');
                    $('#reset-form').addClass('auth-form active');
                }
            }
            
            // ===== ĐĂNG NHẬP =====
            $('#login-form').submit(function(e) {
                e.preventDefault();
                $('#alert-box').html('');
                
                const data = {
                    username: $('#username').val(),
                    password: $('#password').val()
                };
                
                $.ajax({
                    url: '/api/auth?action=login',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            localStorage.setItem('token', res.token);
                            localStorage.setItem('user', JSON.stringify(res.user));
                            showAlert(res.message, 'success');
                            setTimeout(() => window.location.replace('index.php'), 1000);
                        } else {
                            showAlert(res.message);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Lỗi server: ' + xhr.status;
                        showAlert(msg);
                    }
                });
            });
            
            // ===== ĐĂNG KÝ =====
            $('#register-form').submit(function(e) {
                e.preventDefault();
                $('#alert-box').html('');
                
                const password = $('#reg-password').val();
                const confirmPassword = $('#reg-confirm-password').val();
                const username = $('#reg-username').val();
                
                // Validation
                if (username.length < 3) {
                    showAlert('Username phải có ít nhất 3 ký tự');
                    return;
                }
                if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    showAlert('Username chỉ chứa chữ cái, số và dấu gạch dưới');
                    return;
                }
                if (password.length < 6) {
                    showAlert('Mật khẩu phải có ít nhất 6 ký tự');
                    return;
                }
                if (password !== confirmPassword) {
                    showAlert('Mật khẩu xác nhận không khớp');
                    return;
                }
                
                const data = {
                    username: username,
                    password: password,
                    email: $('#reg-email').val(),
                    fullname: $('#reg-fullname').val(),
                    phone: $('#reg-phone').val()
                };
                
                $.ajax({
                    url: '/api/auth?action=register',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            showAlert('✅ Đăng ký thành công! Vui lòng đăng nhập.', 'success');
                            setTimeout(() => {
                                $('#username').val(username);
                                switchTab('login');
                            }, 1500);
                        } else {
                            showAlert(res.message);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Lỗi server';
                        showAlert(msg);
                    }
                });
            });
            
            // ===== QUÊN MẬT KHẨU =====
            $('#forgot-form').submit(function(e) {
                e.preventDefault();
                $('#alert-box').html('');
                
                const email = $('#forgot-email').val();
                
                $.ajax({
                    url: '/api/auth?action=forgot-password',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ email: email }),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            showAlert(res.message, 'success');
                            
                            // Hiển thị link reset (chỉ dùng khi test)
                            if (res.debug_reset_link) {
                                $('#reset-link-box').html(`
                                    <div class="alert alert-warning">
                                        <strong>🔗 Link reset (chỉ dùng để test):</strong><br>
                                        <a href="${res.debug_reset_link}" target="_blank" class="text-break">${res.debug_reset_link}</a>
                                    </div>
                                `).show();
                            }
                        } else {
                            showAlert(res.message);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Lỗi server';
                        showAlert(msg);
                    }
                });
            });
            
            // ===== RESET MẬT KHẨU =====
            $('#reset-form').submit(function(e) {
                e.preventDefault();
                $('#alert-box').html('');
                
                const password = $('#reset-password').val();
                const confirmPassword = $('#reset-confirm-password').val();
                
                if (password.length < 6) {
                    showAlert('Mật khẩu phải có ít nhất 6 ký tự');
                    return;
                }
                if (password !== confirmPassword) {
                    showAlert('Mật khẩu xác nhận không khớp');
                    return;
                }
                
                const data = {
                    token: $('#reset-token-input').val(),
                    new_password: password
                };
                
                $.ajax({
                    url: '/api/auth?action=reset-password',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            showAlert('✅ ' + res.message + ' Vui lòng đăng nhập.', 'success');
                            setTimeout(() => switchTab('login'), 2000);
                        } else {
                            showAlert(res.message);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Lỗi server';
                        showAlert(msg);
                    }
                });
            });
        });
    </script>
</body>
</html>