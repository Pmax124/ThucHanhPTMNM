<?php
// ✅ Start session
session_start();

// ✅ Lấy thông tin user từ session
$currentUser = $_SESSION['username'] ?? 'Guest';
$currentRole = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? null;

// ✅ Thời gian truy cập
$accessTime = date('H:i:s d/m/Y');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Test Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-top: 50px;
        }
        .dashboard-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .api-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .api-icon.product { color: #667eea; }
        .api-icon.category { color: #f093fb; }
        .api-icon.account { color: #ffc107; }
        .api-icon.test { color: #4facfe; }
        .btn-custom {
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: 600;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        .preview-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        .preview-item:hover {
            background: #f8f9fa;
        }
        .preview-item:last-child {
            border-bottom: none;
        }
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(102, 126, 234, 0.3);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 5px 15px;
            border-radius: 20px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <!-- ✅ MỚI: Navbar với thông tin user -->
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-code"></i> API Dashboard
            </a>
            <div class="d-flex align-items-center">
                <?php if ($userId): ?>
                    <span class="user-info text-white">
                        <i class="fas fa-user-circle"></i> 
                        <strong><?= htmlspecialchars($currentUser) ?></strong>
                        <?php if ($currentRole === 'admin'): ?>
                            <span class="badge bg-danger ms-1">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-secondary ms-1">User</span>
                        <?php endif; ?>
                    </span>
                    <a href="/account/logout" class="btn btn-outline-light btn-sm ms-2">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                <?php else: ?>
                    <a href="/account/login" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 80px;">
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-code"></i> API Test Dashboard</h1>
            <p class="lead">Giao diện test RESTful API với PHP + jQuery</p>
            <!-- ✅ MỚI: Hiển thị thời gian truy cập -->
            <small class="text-white-50">
                <i class="fas fa-clock"></i> Truy cập: <?= $accessTime ?>
                <?php if ($userId): ?>
                    | <i class="fas fa-user"></i> Bởi: <strong><?= htmlspecialchars($currentUser) ?></strong>
                <?php endif; ?>
            </small>
        </div>

        <!-- THỐNG KÊ TỔNG QUAN -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="dashboard-card text-center">
                    <i class="fas fa-box api-icon product"></i>
                    <div class="stat-number" id="product-count">
                        <span class="loading-spinner"></span>
                    </div>
                    <div class="stat-label">Sản phẩm</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card text-center">
                    <i class="fas fa-tags api-icon category"></i>
                    <div class="stat-number" id="category-count">
                        <span class="loading-spinner"></span>
                    </div>
                    <div class="stat-label">Danh mục</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card text-center">
                    <i class="fas fa-users-cog api-icon account"></i>
                    <div class="stat-number" id="account-count" style="color: #ffc107;">
                        <span class="loading-spinner"></span>
                    </div>
                    <div class="stat-label">Tài khoản</div>
                </div>
            </div>
        </div>

        <!-- Navigation Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3 class="text-center mb-3">
                        <i class="fas fa-box text-primary"></i> Quản lý Sản phẩm
                    </h3>
                    <p class="text-muted text-center">Thêm, sửa, xóa và xem danh sách sản phẩm qua API</p>
                    <div id="product-preview" class="mb-3">
                        <div class="text-center py-3">
                            <span class="loading-spinner"></span>
                            <span class="ms-2">Đang tải...</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <!-- ✅ SỬA: products.html → products.php -->
                        <a href="products.php" class="btn btn-primary btn-custom">
                            <i class="fas fa-arrow-right"></i> Truy cập
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3 class="text-center mb-3">
                        <i class="fas fa-tags text-info"></i> Quản lý Danh mục
                    </h3>
                    <p class="text-muted text-center">Thêm, sửa, xóa và xem danh sách danh mục qua API</p>
                    <div id="category-preview" class="mb-3">
                        <div class="text-center py-3">
                            <span class="loading-spinner"></span>
                            <span class="ms-2">Đang tải...</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <!-- ✅ SỬA: categories.html → categories.php -->
                        <a href="categories.php" class="btn btn-primary btn-custom">
                            <i class="fas fa-arrow-right"></i> Truy cập
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="dashboard-card">
                    <h3 class="text-center mb-3">
                        <i class="fas fa-users-cog text-warning"></i> Quản lý Tài khoản
                    </h3>
                    <p class="text-muted text-center">Thêm, sửa, xóa và quản lý tài khoản người dùng</p>
                    <div id="account-preview" class="mb-3">
                        <div class="text-center py-3">
                            <span class="loading-spinner"></span>
                            <span class="ms-2">Đang tải...</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <!-- ✅ SỬA: accounts.html → accounts.php -->
                        <a href="accounts.php" class="btn btn-warning btn-custom">
                            <i class="fas fa-arrow-right"></i> Truy cập
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Endpoints Info -->
        <div class="dashboard-card mt-4">
            <h4><i class="fas fa-info-circle"></i> API Endpoints</h4>
            <div class="row mt-3">
                <div class="col-md-4">
                    <h6 class="text-primary">Product API</h6>
                    <ul class="list-unstyled small">
                        <li><code>GET /api/products</code></li>
                        <li><code>GET /api/products/{id}</code></li>
                        <li><code>POST /api/products</code></li>
                        <li><code>PUT /api/products/{id}</code></li>
                        <li><code>DELETE /api/products/{id}</code></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-info">Category API</h6>
                    <ul class="list-unstyled small">
                        <li><code>GET /api/categories</code></li>
                        <li><code>GET /api/categories/{id}</code></li>
                        <li><code>POST /api/categories</code></li>
                        <li><code>PUT /api/categories/{id}</code></li>
                        <li><code>DELETE /api/categories/{id}</code></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-warning">Account API</h6>
                    <ul class="list-unstyled small">
                        <li><code>GET /api/accounts</code></li>
                        <li><code>GET /api/accounts/{id}</code></li>
                        <li><code>POST /api/accounts</code></li>
                        <li><code>PUT /api/accounts/{id}</code></li>
                        <li><code>DELETE /api/accounts/{id}</code></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- API Test Console -->
        <div class="dashboard-card mt-4">
            <h4><i class="fas fa-terminal"></i> API Test Console</h4>
            <p class="text-muted">Test API trực tiếp từ dashboard</p>
            
            <div class="row g-3">
                <div class="col-md-3">
                    <select id="api-method" class="form-select">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" id="api-url" class="form-control" 
                           placeholder="/api/products hoặc /api/categories" 
                           value="/api/products">
                </div>
                <div class="col-md-3">
                    <button id="btn-test-api" class="btn btn-success w-100">
                        <i class="fas fa-play"></i> Test
                    </button>
                </div>
            </div>
            
            <div class="mt-3">
                <label class="form-label fw-semibold">Request Body (JSON):</label>
                <textarea id="api-body" class="form-control" rows="3" 
                          placeholder='{"name": "Test", "description": "Mô tả"}'></textarea>
            </div>
            
            <div class="mt-3">
                <label class="form-label fw-semibold">Response:</label>
                <pre id="api-response" class="bg-dark text-light p-3 rounded" 
                     style="max-height: 300px; overflow-y: auto;">Chưa có dữ liệu...</pre>
            </div>
        </div>

        <!-- ✅ MỚI: Footer với thông tin PHP -->
        <div class="text-center text-white-50 mt-4 mb-4">
            <small>
                <i class="fab fa-php"></i> Rendered by PHP | 
                <i class="fab fa-js"></i> Powered by jQuery | 
                <i class="fas fa-server"></i> Server Time: <?= date('Y-m-d H:i:s') ?>
            </small>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Load thống kê và preview
            loadStatistics();
            loadProductPreview();
            loadCategoryPreview();
            loadAccountPreview();
            
            // Test API console
            $('#btn-test-api').click(function() {
                testApi();
            });
            
            // Enter key trong URL input
            $('#api-url').keypress(function(e) {
                if (e.which === 13) {
                    testApi();
                }
            });
        });

        // Load thống kê tổng quan
        function loadStatistics() {
            $.ajax({
                url: '/api/products',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#product-count').text(response.count);
                    } else {
                        $('#product-count').text('0').css('color', 'red');
                    }
                },
                error: function() {
                    $('#product-count').text('❌').css('color', 'red');
                }
            });
            
            $.ajax({
                url: '/api/categories',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#category-count').text(response.count);
                    } else {
                        $('#category-count').text('0').css('color', 'red');
                    }
                },
                error: function() {
                    $('#category-count').text('❌').css('color', 'red');
                }
            });
            
            $.ajax({
                url: '/api/accounts?action=statistics',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        let total = 0;
                        $.each(response.data, function(i, stat) {
                            total += parseInt(stat.count);
                        });
                        $('#account-count').text(total);
                    } else {
                        $('#account-count').text('0').css('color', 'red');
                    }
                },
                error: function(xhr) {
                    console.error('Account API error:', xhr.responseText);
                    $('#account-count').text('❌').css('color', 'red');
                }
            });
        }

        // Load preview 3 sản phẩm đầu tiên
        function loadProductPreview() {
            $.ajax({
                url: '/api/products',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        let html = '<div class="list-group">';
                        $.each(response.data.slice(0, 3), function(index, product) {
                            html += `
                                <div class="preview-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${product.name}</strong>
                                        <br>
                                        <small class="text-muted">${product.category_name}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-danger fw-bold">
                                            ${parseInt(product.price).toLocaleString('vi-VN')}₫
                                        </span>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        if (response.data.length > 3) {
                            html += `<div class="text-center mt-2">
                                <small class="text-muted">... và ${response.data.length - 3} sản phẩm khác</small>
                            </div>`;
                        }
                        $('#product-preview').html(html);
                    } else {
                        $('#product-preview').html('<div class="text-center text-muted py-3">Chưa có sản phẩm</div>');
                    }
                },
                error: function() {
                    $('#product-preview').html('<div class="text-center text-danger py-3">❌ Lỗi khi tải dữ liệu</div>');
                }
            });
        }

        // Load preview 3 danh mục đầu tiên
        function loadCategoryPreview() {
            $.ajax({
                url: '/api/categories',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        let html = '<div class="list-group">';
                        $.each(response.data.slice(0, 3), function(index, cat) {
                            html += `
                                <div class="preview-item">
                                    <strong>${cat.name}</strong>
                                    <br>
                                    <small class="text-muted">${cat.description || 'Không có mô tả'}</small>
                                </div>
                            `;
                        });
                        html += '</div>';
                        if (response.data.length > 3) {
                            html += `<div class="text-center mt-2">
                                <small class="text-muted">... và ${response.data.length - 3} danh mục khác</small>
                            </div>`;
                        }
                        $('#category-preview').html(html);
                    } else {
                        $('#category-preview').html('<div class="text-center text-muted py-3">Chưa có danh mục</div>');
                    }
                },
                error: function() {
                    $('#category-preview').html('<div class="text-center text-danger py-3">❌ Lỗi khi tải dữ liệu</div>');
                }
            });
        }

        // Load account preview
        function loadAccountPreview() {
            $.ajax({
                url: '/api/accounts?action=statistics',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data.length > 0) {
                        let html = '<div class="list-group">';
                        $.each(response.data.slice(0, 4), function(index, stat) {
                            const roleLabel = stat.role === 'admin' ? '👑 Admin' : '👤 User';
                            const statusLabel = stat.status == 1 ? '✅ Hoạt động' : '🔒 Bị khóa';
                            html += `
                                <div class="preview-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${roleLabel}</strong>
                                        <br>
                                        <small class="text-muted">${statusLabel}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-${stat.role === 'admin' ? 'danger' : 'primary'} fs-6">
                                            ${stat.count}
                                        </span>
                                    </div>
                                </div>
                            `;
                        });
                        html += '</div>';
                        $('#account-preview').html(html);
                    } else {
                        $('#account-preview').html('<div class="text-center text-muted py-3">Chưa có tài khoản</div>');
                    }
                },
                error: function(xhr) {
                    console.error('Account preview error:', xhr.responseText);
                    $('#account-preview').html('<div class="text-center text-danger py-3">❌ Lỗi API Account</div>');
                }
            });
        }

        // Test API trực tiếp
        function testApi() {
            const method = $('#api-method').val();
            const url = $('#api-url').val();
            const body = $('#api-body').val();
            
            $('#api-response').html('<span class="loading-spinner"></span> Đang gọi API...');
            
            const ajaxOptions = {
                url: url,
                method: method,
                dataType: 'json',
                success: function(response, textStatus, xhr) {
                    const result = {
                        status_code: xhr.status,
                        status_text: textStatus,
                        response: response
                    };
                    $('#api-response').html(JSON.stringify(result, null, 2));
                },
                error: function(xhr, status, error) {
                    const result = {
                        status_code: xhr.status,
                        status_text: status,
                        error: error,
                        response: xhr.responseText || 'No response'
                    };
                    $('#api-response').html(JSON.stringify(result, null, 2));
                }
            };
            
            if ((method === 'POST' || method === 'PUT') && body) {
                ajaxOptions.contentType = 'application/json';
                ajaxOptions.data = body;
            }
            
            $.ajax(ajaxOptions);
        }
    </script>
</body>
</html>