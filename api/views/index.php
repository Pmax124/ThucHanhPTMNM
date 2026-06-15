<?php
session_start();
$currentUser = $_SESSION['username'] ?? 'Guest';
$currentRole = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? null;
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
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding-top: 50px; }
        .dashboard-card { background: white; border-radius: 15px; padding: 30px; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); transition: transform 0.3s; }
        .dashboard-card:hover { transform: translateY(-5px); }
        .api-icon { font-size: 3rem; margin-bottom: 20px; }
        .api-icon.product { color: #667eea; }
        .api-icon.category { color: #f093fb; }
        .api-icon.account { color: #ffc107; }
        .api-icon.order { color: #28a745; }
        .btn-custom { border-radius: 25px; padding: 10px 30px; font-weight: 600; }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #667eea; }
        .stat-label { color: #6c757d; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        .preview-item { padding: 10px; border-bottom: 1px solid #eee; transition: background 0.2s; }
        .preview-item:hover { background: #f8f9fa; }
        .preview-item:last-child { border-bottom: none; }
        .loading-spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(102, 126, 234, 0.3); border-radius: 50%; border-top-color: #667eea; animation: spin 1s ease-in-out infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .user-info { background: rgba(255,255,255,0.1); padding: 5px 15px; border-radius: 20px; margin-left: 10px; cursor: pointer; transition: background 0.3s; }
        .user-info:hover { background: rgba(255,255,255,0.25); }
        .api-endpoint { transition: all 0.3s; padding: 2px 8px; border-radius: 4px; display: inline-block; text-decoration: none; }
        .api-endpoint:hover { background-color: rgba(102, 126, 234, 0.1); transform: translateX(5px); }
        .api-endpoint code { cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-code"></i> API Dashboard</a>
            <div class="d-flex align-items-center">
                <!-- ✅ THÊM: onclick="openProfileModal()" -->
                <span class="user-info text-white" onclick="openProfileModal()" title="Click để cập nhật thông tin">
                    <i class="fas fa-user-circle"></i> 
                    <strong id="current-username">Guest</strong>
                    <span id="current-role" class="badge bg-secondary ms-1">User</span>
                    <i class="fas fa-edit ms-1 small"></i>
                </span>
                <button onclick="logout()" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </button>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 80px;">
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-code"></i> API Test Dashboard</h1>
            <p class="lead">Giao diện test RESTful API với PHP + jQuery + JWT</p>
            <small class="text-white-50"><i class="fas fa-clock"></i> Truy cập: <?= $accessTime ?></small>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <i class="fas fa-box api-icon product"></i>
                    <div class="stat-number" id="product-count"><span class="loading-spinner"></span></div>
                    <div class="stat-label">Sản phẩm</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <i class="fas fa-tags api-icon category"></i>
                    <div class="stat-number" id="category-count"><span class="loading-spinner"></span></div>
                    <div class="stat-label">Danh mục</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <i class="fas fa-users-cog api-icon account"></i>
                    <div class="stat-number" id="account-count" style="color: #ffc107;"><span class="loading-spinner"></span></div>
                    <div class="stat-label">Tài khoản</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card text-center">
                    <i class="fas fa-shopping-cart api-icon order"></i>
                    <div class="stat-number" id="order-count" style="color: #28a745;"><span class="loading-spinner"></span></div>
                    <div class="stat-label">Đơn hàng</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h3 class="text-center mb-3"><i class="fas fa-box text-primary"></i> Sản phẩm</h3>
                    <div id="product-preview" class="mb-3"><div class="text-center py-3"><span class="loading-spinner"></span><span class="ms-2">Đang tải...</span></div></div>
                    <div class="text-center"><a href="products.php" class="btn btn-primary btn-custom"><i class="fas fa-arrow-right"></i> Truy cập</a></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h3 class="text-center mb-3"><i class="fas fa-tags text-info"></i> Danh mục</h3>
                    <div id="category-preview" class="mb-3"><div class="text-center py-3"><span class="loading-spinner"></span><span class="ms-2">Đang tải...</span></div></div>
                    <div class="text-center"><a href="categories.php" class="btn btn-info btn-custom"><i class="fas fa-arrow-right"></i> Truy cập</a></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h3 class="text-center mb-3"><i class="fas fa-users-cog text-warning"></i> Tài khoản</h3>
                    <div id="account-preview" class="mb-3"><div class="text-center py-3"><span class="loading-spinner"></span><span class="ms-2">Đang tải...</span></div></div>
                    <div class="text-center"><a href="accounts.php" class="btn btn-warning btn-custom"><i class="fas fa-arrow-right"></i> Truy cập</a></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card">
                    <h3 class="text-center mb-3"><i class="fas fa-shopping-cart text-success"></i> Đơn hàng</h3>
                    <div id="order-preview" class="mb-3"><div class="text-center py-3"><span class="loading-spinner"></span><span class="ms-2">Đang tải...</span></div></div>
                    <div class="text-center"><a href="orders.php" class="btn btn-success btn-custom"><i class="fas fa-arrow-right"></i> Truy cập</a></div>
                </div>
            </div>
        </div>

        <!-- API Documentation -->
        <div class="dashboard-card mt-4">
            <h4><i class="fas fa-book"></i> Hướng dẫn sử dụng API</h4>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-warning"><i class="fas fa-shopping-cart"></i> Cart API</h6>
                    <ul class="small">
                        <li><strong>Xem giỏ:</strong> GET /api/cart</li>
                        <li><strong>Thêm sản phẩm:</strong> POST /api/cart?action=add (cần product_id, quantity)</li>
                        <li><strong>Cập nhật SL:</strong> PUT /api/cart/{id} (cần quantity)</li>
                        <li><strong>Xóa sản phẩm:</strong> DELETE /api/cart/{id}</li>
                        <li><strong>Xóa giỏ:</strong> POST /api/cart?action=clear</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success"><i class="fas fa-credit-card"></i> Payment API</h6>
                    <ul class="small">
                        <li><strong>Tạo thanh toán:</strong> POST /api/payment?action=create (cần order_id, payment_method)</li>
                        <li><strong>Xác nhận COD:</strong> POST /api/payment?action=confirm (chỉ Admin)</li>
                        <li><strong>Xem thanh toán:</strong> GET /api/payment/{id}</li>
                        <li><strong>Phương thức:</strong> cod, transfer, momo, zalopay</li>
                    </ul>
                </div>
                <div class="col-md-12 mt-3">
                    <h6 class="text-info"><i class="fas fa-shopping-bag"></i> Order Checkout</h6>
                    <ul class="small">
                        <li><strong>Đặt từ giỏ:</strong> POST /api/orders?action=checkout</li>
                        <li><strong>Yêu cầu:</strong> Giỏ hàng phải có sản phẩm, đủ thông tin giao hàng</li>
                        <li><strong>Kết quả:</strong> Tạo đơn hàng + Xóa giỏ hàng + Trừ stock</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="dashboard-card mt-4">
            <h4><i class="fas fa-info-circle"></i> API Endpoints (Click để test)</h4>
            <div class="row mt-3">
              <!-- Product API -->
                <div class="col-md-3">
                    <h6 class="text-primary">Product API</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="products"><code>GET /api/products</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="products-search"><code>GET /api/products?search=iphone</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="products-category"><code>GET /api/products?category_id=1</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="products-price"><code>GET /api/products?min_price=1000000&max_price=50000000</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="products-sort"><code>GET /api/products?sort=price_asc</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="products-all"><code>GET /api/products?search=iphone&category_id=1&sort=price_asc&min_price=10000000&max_price=50000000</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="products-id"><code>GET /api/products/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="POST" data-api="products"><code>POST /api/products</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="PUT" data-api="products"><code>PUT /api/products/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="DELETE" data-api="products"><code>DELETE /api/products/{id}</code></a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-info">Category API</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="categories"><code>GET /api/categories</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="categories"><code>GET /api/categories/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="POST" data-api="categories"><code>POST /api/categories</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="PUT" data-api="categories"><code>PUT /api/categories/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="DELETE" data-api="categories"><code>DELETE /api/categories/{id}</code></a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-warning">Account API</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="accounts"><code>GET /api/accounts</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="accounts"><code>GET /api/accounts/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="POST" data-api="accounts"><code>POST /api/accounts</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="PUT" data-api="accounts"><code>PUT /api/accounts/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="DELETE" data-api="accounts"><code>DELETE /api/accounts/{id}</code></a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-success">Order API</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="orders"><code>GET /api/orders</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="orders"><code>GET /api/orders/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="POST" data-api="orders"><code>POST /api/orders</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="PUT" data-api="orders"><code>PUT /api/orders/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="DELETE" data-api="orders"><code>DELETE /api/orders/{id}</code></a></li>
                    </ul>
                </div>
                <!-- Cart API -->
                <div class="col-md-3">
                    <h6 class="text-warning">Cart API</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="cart"><code>GET /api/cart</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="cart-count"><code>GET /api/cart?action=count</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="cart-total"><code>GET /api/cart?action=total</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="POST" data-api="cart-add"><code>POST /api/cart?action=add</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="PUT" data-api="cart-update"><code>PUT /api/cart/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="DELETE" data-api="cart-delete"><code>DELETE /api/cart/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="POST" data-api="cart-clear"><code>POST /api/cart?action=clear</code></a></li>
                    </ul>
                </div>

                <!-- Payment API -->
                <div class="col-md-3">
                    <h6 class="text-success">Payment API</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="payment"><code>GET /api/payment</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="GET" data-api="payment-id"><code>GET /api/payment/{id}</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="POST" data-api="payment-create"><code>POST /api/payment?action=create</code></a></li>
                        <li><a href="#" class="api-endpoint" data-method="POST" data-api="payment-confirm"><code>POST /api/payment?action=confirm</code></a></li>
                    </ul>
                </div>
                
            </div>
        </div>

        <div class="dashboard-card mt-4" id="api-test-console">
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
                    <input type="text" id="api-url" class="form-control" placeholder="/api/products" value="/api/products">
                </div>
                <div class="col-md-3">
                    <button id="btn-test-api" class="btn btn-success w-100"><i class="fas fa-play"></i> Test</button>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label fw-semibold">Request Body (JSON):</label>
                <textarea id="api-body" class="form-control" rows="3" placeholder='{"name": "Test"}'></textarea>
            </div>
            <div class="mt-3">
                <label class="form-label fw-semibold">Response:</label>
                <pre id="api-response" class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">Chưa có dữ liệu...</pre>
            </div>
        </div>

        <div class="text-center text-white-50 mt-4 mb-4">
            <small>
                <i class="fab fa-php"></i> Rendered by PHP | 
                <i class="fab fa-js"></i> Powered by jQuery + JWT
            </small>
        </div>
    </div>

    <!-- Quick Test Buttons -->
    <div class="dashboard-card mt-4">
        <h4><i class="fas fa-bolt"></i> Quick Test - Giỏ hàng & Thanh toán</h4>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-shopping-cart"></i> Giỏ hàng</h6>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-sm btn-outline-warning w-100 mb-2" onclick="quickTest('cart')">
                            <i class="fas fa-eye"></i> Xem giỏ hàng
                        </button>
                        <button class="btn btn-sm btn-outline-warning w-100 mb-2" onclick="quickTest('cart-add')">
                            <i class="fas fa-plus"></i> Thêm sản phẩm
                        </button>
                        <button class="btn btn-sm btn-outline-warning w-100" onclick="quickTest('cart-clear')">
                            <i class="fas fa-trash"></i> Xóa giỏ
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-credit-card"></i> Thanh toán</h6>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-sm btn-outline-success w-100 mb-2" onclick="quickTest('payment')">
                            <i class="fas fa-list"></i> Danh sách thanh toán
                        </button>
                        <button class="btn btn-sm btn-outline-success w-100 mb-2" onclick="quickTest('payment-create')">
                            <i class="fas fa-plus"></i> Tạo thanh toán
                        </button>
                        <button class="btn btn-sm btn-outline-success w-100" onclick="quickTest('payment-confirm')">
                            <i class="fas fa-check"></i> Xác nhận COD
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-shopping-bag"></i> Đặt hàng</h6>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-sm btn-outline-info w-100 mb-2" onclick="quickTest('orders')">
                            <i class="fas fa-list"></i> Danh sách đơn
                        </button>
                        <button class="btn btn-sm btn-outline-info w-100 mb-2" onclick="quickTest('order-checkout')">
                            <i class="fas fa-cart-arrow-down"></i> Checkout từ giỏ
                        </button>
                        <button class="btn btn-sm btn-outline-info w-100" onclick="quickTest('order-stats')">
                            <i class="fas fa-chart-bar"></i> Thống kê đơn
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Test - Product Search & Filter -->
<div class="dashboard-card mt-4">
    <h4><i class="fas fa-search"></i> Quick Test - Tìm kiếm & Lọc sản phẩm</h4>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-search"></i> Tìm kiếm</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-sm btn-outline-primary w-100 mb-2" onclick="quickTestProduct('search-iphone')">
                        <i class="fas fa-search"></i> Tìm "iphone"
                    </button>
                    <button class="btn btn-sm btn-outline-primary w-100 mb-2" onclick="quickTestProduct('search-samsung')">
                        <i class="fas fa-search"></i> Tìm "samsung"
                    </button>
                    <button class="btn btn-sm btn-outline-primary w-100" onclick="quickTestProduct('search-dongho')">
                        <i class="fas fa-search"></i> Tìm "đồng hồ"
                    </button>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-filter"></i> Lọc</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-sm btn-outline-info w-100 mb-2" onclick="quickTestProduct('filter-category')">
                        <i class="fas fa-tags"></i> Theo danh mục (ID=1)
                    </button>
                    <button class="btn btn-sm btn-outline-info w-100 mb-2" onclick="quickTestProduct('filter-price')">
                        <i class="fas fa-dollar-sign"></i> Giá 10-50 triệu
                    </button>
                    <button class="btn btn-sm btn-outline-info w-100" onclick="quickTestProduct('filter-combine')">
                        <i class="fas fa-filter"></i> Kết hợp tất cả
                    </button>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-sort"></i> Sắp xếp</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-sm btn-outline-success w-100 mb-2" onclick="quickTestProduct('sort-price-asc')">
                        <i class="fas fa-arrow-up"></i> Giá tăng dần
                    </button>
                    <button class="btn btn-sm btn-outline-success w-100 mb-2" onclick="quickTestProduct('sort-price-desc')">
                        <i class="fas fa-arrow-down"></i> Giá giảm dần
                    </button>
                    <button class="btn btn-sm btn-outline-success w-100" onclick="quickTestProduct('sort-newest')">
                        <i class="fas fa-clock"></i> Mới nhất
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>

    <!-- ✅ MỚI: MODAL CẬP NHẬT THÔNG TIN CÁ NHÂN -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-edit"></i> Cập nhật thông tin cá nhân</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="profile-alert"></div>
                    <form id="profile-form">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user"></i> Username</label>
                            <input type="text" class="form-control" id="profile-username" readonly disabled>
                            <small class="text-muted">Username không thể thay đổi</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user-circle"></i> Họ tên</label>
                            <input type="text" class="form-control" id="profile-fullname" placeholder="Nguyễn Văn A">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-envelope"></i> Email *</label>
                            <input type="email" class="form-control" id="profile-email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-phone"></i> Số điện thoại</label>
                            <input type="text" class="form-control" id="profile-phone" placeholder="0901234567">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-map-marker-alt"></i> Địa chỉ</label>
                            <textarea class="form-control" id="profile-address" rows="2" placeholder="123 Nguyễn Văn A, Q.1, TP.HCM"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btn-save-profile">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ MỚI: MODAL ĐỔI MẬT KHẨU -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-key"></i> Đổi mật khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="password-alert"></div>
                    <form id="password-form">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-lock"></i> Mật khẩu cũ *</label>
                            <input type="password" class="form-control" id="old-password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-key"></i> Mật khẩu mới *</label>
                            <input type="password" class="form-control" id="new-password" required placeholder="Ít nhất 6 ký tự">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-lock"></i> Xác nhận mật khẩu mới *</label>
                            <input type="password" class="form-control" id="confirm-password" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-save"></i> Đổi mật khẩu
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" onclick="openPasswordModal()">
                        <i class="fas fa-key"></i> Đổi mật khẩu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ===== BIẾN GLOBAL =====
let errorShown = false;
let profileModal, passwordModal;

// ===== TỰ ĐỘNG GẮN TOKEN VÀO MỌI AJAX REQUEST =====
$(document).ajaxSend(function(event, xhr, settings) {
    const token = localStorage.getItem('token');
    if (token) {
        xhr.setRequestHeader('Authorization', 'Bearer ' + token);
    }
});

// ===== XỬ LÝ LỖI 401, 403 =====
$(document).ajaxError(function(event, xhr, settings) {
    if (xhr.status === 401 && !errorShown) {
        errorShown = true;
        console.error('❌ 401 Unauthorized:', settings.url);
        if (confirm('⚠️ Phiên đăng nhập đã hết hạn. Đăng nhập lại?')) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.replace('login.php');
        }
        setTimeout(() => { errorShown = false; }, 2000);
    } else if (xhr.status === 403 && !errorShown) {
        errorShown = true;
        const msg = xhr.responseJSON?.message || 'Bạn không có quyền';
        alert('🚫 ' + msg);
        setTimeout(() => { errorShown = false; }, 2000);
    }
});

// ===== LOGOUT =====
function logout() {
    if (confirm('Bạn có chắc muốn đăng xuất?')) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.replace('login.php');
    }
}

// ===== MỞ MODAL PROFILE =====
function openProfileModal() {
    if (!profileModal) {
        profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
    }
    
    $.ajax({
        url: '/api/auth?action=me',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                const user = res.data;
                $('#profile-username').val(user.username || '');
                $('#profile-fullname').val(user.fullname || '');
                $('#profile-email').val(user.email || '');
                $('#profile-phone').val(user.phone || '');
                $('#profile-address').val(user.address || '');
                $('#profile-alert').html('');
                profileModal.show();
            } else {
                alert('❌ Không thể tải thông tin: ' + res.message);
            }
        },
        error: function(xhr) {
            alert('❌ Lỗi: ' + (xhr.responseJSON?.message || 'Không thể kết nối server'));
        }
    });
}

// ===== MỞ MODAL ĐỔI MẬT KHẨU =====
function openPasswordModal() {
    if (!passwordModal) {
        passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
    }
    $('#password-form')[0].reset();
    $('#password-alert').html('');
    passwordModal.show();
}

// ===== API TEMPLATES (ĐÃ SỬA - KHÔNG LẶP LẠI) =====
const apiTemplates = {
    // Product API
    products: {
        GET: { url: '/api/products', body: {} },
        POST: { url: '/api/products', body: {"name": "iPhone 15 Pro", "description": "Điện thoại cao cấp", "price": 25000000, "category_id": 1, "image": "uploads/iphone.jpg"} },
        PUT: { url: '/api/products/{id}', body: {"name": "iPhone 15 Pro (Updated)", "price": 24000000} },
        DELETE: { url: '/api/products/{id}', body: {} }
    },
    'products-search': {
        GET: { url: '/api/products?search=iphone', body: {} }
    },
    'products-category': {
        GET: { url: '/api/products?category_id=1', body: {} }
    },
    'products-price': {
        GET: { url: '/api/products?min_price=10000000&max_price=50000000', body: {} }
    },
    'products-sort': {
        GET: { url: '/api/products?sort=price_asc', body: {} }
    },
    'products-all': {
        GET: { url: '/api/products?search=iphone&category_id=1&sort=price_asc&min_price=10000000&max_price=50000000&page=1', body: {} }
    },
    'products-id': {
        GET: { url: '/api/products/{id}', body: {} }
    },
    
    // Category API
    categories: {
        GET: { url: '/api/categories', body: {} },
        POST: { url: '/api/categories', body: {"name": "Điện thoại", "description": "Danh mục điện thoại"} },
        PUT: { url: '/api/categories/{id}', body: {"name": "Điện thoại cao cấp"} },
        DELETE: { url: '/api/categories/{id}', body: {} }
    },
    
    // Account API
    accounts: {
        GET: { url: '/api/accounts', body: {} },
        POST: { url: '/api/accounts', body: {"username": "newuser", "password": "password123", "email": "user@example.com", "fullname": "Nguyễn Văn A", "phone": "0901234567", "role": "user", "status": 1} },
        PUT: { url: '/api/accounts/{id}', body: {"fullname": "Nguyễn Văn A (Updated)", "status": 1} },
        DELETE: { url: '/api/accounts/{id}', body: {} }
    },
    
    // Order API
    orders: {
        GET: { url: '/api/orders', body: {} },
        POST: { url: '/api/orders', body: {"customer_name": "Nguyễn Văn A", "customer_phone": "0901234567", "customer_address": "123 ABC", "payment_method": "cod", "items": [{"product_id": 1, "quantity": 1, "price": 26000000}]} },
        PUT: { url: '/api/orders/{id}', body: {"status": "confirmed", "note": "Đã xác nhận"} },
        DELETE: { url: '/api/orders/{id}', body: {} }
    },
    'order-checkout': {
        POST: { url: '/api/orders?action=checkout', body: { payment_method: 'cod', notes: 'Giao nhanh' } }
    },
    'order-stats': {
        GET: { url: '/api/orders?action=statistics', body: {} }
    },
    
    // Cart API
    cart: {
        GET: { url: '/api/cart', body: {} }
    },
    'cart-count': {
        GET: { url: '/api/cart?action=count', body: {} }
    },
    'cart-total': {
        GET: { url: '/api/cart?action=total', body: {} }
    },
    'cart-add': {
        POST: { url: '/api/cart?action=add', body: { product_id: 1, quantity: 2 } }
    },
    'cart-update': {
        PUT: { url: '/api/cart/{id}', body: { quantity: 5 } }
    },
    'cart-delete': {
        DELETE: { url: '/api/cart/{id}', body: {} }
    },
    'cart-clear': {
        POST: { url: '/api/cart?action=clear', body: {} }
    },
    
    // Payment API
    payment: {
        GET: { url: '/api/payment', body: {} }
    },
    'payment-id': {
        GET: { url: '/api/payment/{id}', body: {} }
    },
    'payment-create': {
        POST: { url: '/api/payment?action=create', body: { order_id: 1, payment_method: 'cod' } }
    },
    'payment-confirm': {
        POST: { url: '/api/payment?action=confirm', body: { payment_id: 1 } }
    }
};

// ===== MAIN FUNCTION =====
$(document).ready(function() {
    console.log('📄 Index.php loaded');
    
    // Khởi tạo modal
    profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
    passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
    
    const token = localStorage.getItem('token');
    let user = {};
    
    try {
        user = JSON.parse(localStorage.getItem('user') || '{}');
    } catch(e) {
        user = {};
    }
    
    if (!token || !user.username) {
        window.location.replace('login.php');
        return;
    }
    
    $('#current-username').text(user.username);
    $('#current-role').text(user.role === 'admin' ? 'Admin' : 'User')
                     .removeClass('bg-secondary bg-danger')
                     .addClass(user.role === 'admin' ? 'bg-danger' : 'bg-secondary');
    
    loadStatistics();
    loadProductPreview();
    loadCategoryPreview();
    loadAccountPreview();
    loadOrderPreview();
    
    // ===== CLICK ENDPOINT → FILL FORM =====
    $(document).on('click', '.api-endpoint', function(e) {
        e.preventDefault();
        const method = $(this).data('method');
        const apiType = $(this).data('api');
        
        console.log('Clicked:', apiType, method);
        
        $('#api-method').val(method);
        
        const template = apiTemplates[apiType] && apiTemplates[apiType][method];
        if (template) {
            $('#api-url').val(template.url);
            $('#api-body').val(JSON.stringify(template.body, null, 2));
        } else {
            console.warn('⚠️ Template not found for:', apiType, method);
            $('#api-url').val('/api/' + apiType.replace(/-/g, '/'));
            $('#api-body').val('{}');
        }
        
        $('html, body').animate({ scrollTop: $('#api-test-console').offset().top - 100 }, 300);
    });
    
    // ===== CHANGE METHOD =====
    $('#api-method').change(function() {
        const method = $(this).val();
        const urlInput = $('#api-url');
        const bodyInput = $('#api-body');
        let urlValue = urlInput.val();
        let apiType = '';
        
        if (urlValue.includes('/products')) apiType = 'products';
        else if (urlValue.includes('/categories')) apiType = 'categories';
        else if (urlValue.includes('/accounts')) apiType = 'accounts';
        else if (urlValue.includes('/orders')) apiType = 'orders';
        else if (urlValue.includes('/cart')) apiType = 'cart';
        else if (urlValue.includes('/payment')) apiType = 'payment';
        
        if (method === 'PUT' || method === 'DELETE') {
            if (!urlValue.includes('{id}')) {
                urlValue = /\/\d+$/.test(urlValue) ? urlValue.replace(/\/\d+$/, '/{id}') : urlValue + '/{id}';
            }
        } else {
            urlValue = urlValue.replace(/\/\{id\}/, '').replace(/\/\d+$/, '');
        }
        
        urlInput.val(urlValue);
        bodyInput.val(apiTemplates[apiType] && apiTemplates[apiType][method] ? JSON.stringify(apiTemplates[apiType][method].body, null, 2) : '{}');
    });
    
    $('#btn-test-api').click(function() { testApi(); });
    $('#api-url').keypress(function(e) { if (e.which === 13) testApi(); });
    
    // ===== FORM PROFILE =====
    $('#profile-form').submit(function(e) {
        e.preventDefault();
        
        const data = {
            fullname: $('#profile-fullname').val(),
            email: $('#profile-email').val(),
            phone: $('#profile-phone').val(),
            address: $('#profile-address').val()
        };
        
        if (!data.email) {
            $('#profile-alert').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Email là bắt buộc</div>');
            return;
        }
        
        $('#btn-save-profile').prop('disabled', true).html('<span class="loading-spinner"></span> Đang lưu...');
        
        $.ajax({
            url: '/api/auth?action=profile',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            dataType: 'json',
            success: function(res) {
                $('#btn-save-profile').prop('disabled', false).html('<i class="fas fa-save"></i> Lưu thay đổi');
                
                if (res.status === 'success') {
                    $('#profile-alert').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Cập nhật thông tin thành công!</div>');
                    
                    const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
                    currentUser.email = data.email;
                    currentUser.fullname = data.fullname;
                    currentUser.phone = data.phone;
                    currentUser.address = data.address;
                    localStorage.setItem('user', JSON.stringify(currentUser));
                    
                    setTimeout(() => { profileModal.hide(); }, 1500);
                } else {
                    $('#profile-alert').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + res.message + '</div>');
                }
            },
            error: function(xhr) {
                $('#btn-save-profile').prop('disabled', false).html('<i class="fas fa-save"></i> Lưu thay đổi');
                const msg = xhr.responseJSON?.message || 'Lỗi server';
                $('#profile-alert').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + msg + '</div>');
            }
        });
    });
    
    // ===== FORM PASSWORD =====
    $('#password-form').submit(function(e) {
        e.preventDefault();
        
        const oldPassword = $('#old-password').val();
        const newPassword = $('#new-password').val();
        const confirmPassword = $('#confirm-password').val();
        
        if (newPassword.length < 6) {
            $('#password-alert').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Mật khẩu mới phải có ít nhất 6 ký tự</div>');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            $('#password-alert').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Mật khẩu xác nhận không khớp</div>');
            return;
        }
        
        $.ajax({
            url: '/api/auth?action=change-password',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({
                old_password: oldPassword,
                new_password: newPassword
            }),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#password-alert').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Đổi mật khẩu thành công!</div>');
                    setTimeout(() => {
                        passwordModal.hide();
                        $('#password-form')[0].reset();
                    }, 1500);
                } else {
                    $('#password-alert').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + res.message + '</div>');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Lỗi server';
                $('#password-alert').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' + msg + '</div>');
            }
        });
    });
});

// ===== LOAD STATISTICS =====
function loadStatistics() {
    $.ajax({ url: '/api/products', method: 'GET', dataType: 'json', 
        success: function(res) { if (res.status === 'success') $('#product-count').text(res.count); }, 
        error: function() { $('#product-count').text('❌').css('color', 'red'); } 
    });
    $.ajax({ url: '/api/categories', method: 'GET', dataType: 'json', 
        success: function(res) { if (res.status === 'success') $('#category-count').text(res.count); }, 
        error: function() { $('#category-count').text('❌').css('color', 'red'); } 
    });
    $.ajax({ url: '/api/accounts?action=statistics', method: 'GET', dataType: 'json', 
        success: function(res) { 
            if (res.status === 'success') { 
                let total = 0; 
                $.each(res.data, function(i, stat) { total += parseInt(stat.count); }); 
                $('#account-count').text(total); 
            } 
        }, 
        error: function() { $('#account-count').text('❌').css('color', 'red'); } 
    });
    $.ajax({ url: '/api/orders?action=statistics', method: 'GET', dataType: 'json', 
        success: function(res) { 
            if (res.status === 'success') { 
                let total = 0; 
                $.each(res.data, function(i, stat) { total += parseInt(stat.count); }); 
                $('#order-count').text(total); 
            } 
        }, 
        error: function() { $('#order-count').text('❌').css('color', 'red'); } 
    });
}

// ===== LOAD PREVIEWS =====
function loadProductPreview() {
    $.ajax({ url: '/api/products', method: 'GET', dataType: 'json', 
        success: function(res) { 
            if (res.status === 'success' && res.data.length > 0) { 
                let html = '<div class="list-group">'; 
                $.each(res.data.slice(0, 3), function(i, p) { 
                    html += `<div class="preview-item"><strong>${p.name}</strong><br><small class="text-muted">${parseInt(p.price).toLocaleString('vi-VN')}₫</small></div>`; 
                }); 
                html += '</div>'; 
                $('#product-preview').html(html); 
            } else { 
                $('#product-preview').html('<div class="text-center text-muted py-3">Chưa có sản phẩm</div>'); 
            } 
        } 
    });
}

function loadCategoryPreview() {
    $.ajax({ url: '/api/categories', method: 'GET', dataType: 'json', 
        success: function(res) { 
            if (res.status === 'success' && res.data.length > 0) { 
                let html = '<div class="list-group">'; 
                $.each(res.data.slice(0, 3), function(i, c) { 
                    html += `<div class="preview-item"><strong>${c.name}</strong></div>`; 
                }); 
                html += '</div>'; 
                $('#category-preview').html(html); 
            } else { 
                $('#category-preview').html('<div class="text-center text-muted py-3">Chưa có danh mục</div>'); 
            } 
        } 
    });
}

function loadAccountPreview() {
    $.ajax({ url: '/api/accounts?action=statistics', method: 'GET', dataType: 'json', 
        success: function(res) { 
            if (res.status === 'success' && res.data.length > 0) { 
                let html = '<div class="list-group">'; 
                $.each(res.data.slice(0, 4), function(i, stat) { 
                    const label = stat.role === 'admin' ? '👑 Admin' : '👤 User'; 
                    html += `<div class="preview-item d-flex justify-content-between"><strong>${label}</strong><span class="badge bg-primary">${stat.count}</span></div>`; 
                }); 
                html += '</div>'; 
                $('#account-preview').html(html); 
            } else { 
                $('#account-preview').html('<div class="text-center text-muted py-3">Chưa có tài khoản</div>'); 
            } 
        } 
    });
}

function loadOrderPreview() {
    $.ajax({ url: '/api/orders?action=statistics', method: 'GET', dataType: 'json', 
        success: function(res) { 
            if (res.status === 'success' && res.data.length > 0) { 
                let html = '<div class="list-group">'; 
                const statusLabels = { 
                    pending: '⏳ Chờ xử lý', 
                    confirmed: '✅ Đã xác nhận', 
                    processing: '🔄 Đang xử lý', 
                    shipping: '🚚 Đang giao', 
                    completed: '✔️ Hoàn thành', 
                    cancelled: '❌ Đã hủy' 
                }; 
                $.each(res.data.slice(0, 4), function(i, stat) { 
                    html += `<div class="preview-item d-flex justify-content-between"><strong>${statusLabels[stat.status] || stat.status}</strong><span class="badge bg-success">${stat.count}</span></div>`; 
                }); 
                html += '</div>'; 
                $('#order-preview').html(html); 
            } else { 
                $('#order-preview').html('<div class="text-center text-muted py-3">Chưa có đơn hàng</div>'); 
            } 
        } 
    });
}

// ===== TEST API =====
function testApi() {
    const method = $('#api-method').val();
    let url = $('#api-url').val();
    const body = $('#api-body').val();
    
    if (url.includes('{id}')) { 
        const testId = prompt('Nhập ID để test:', '1'); 
        if (testId === null) return; 
        url = url.replace('{id}', testId); 
    }
    
    $('#api-response').html('<span class="loading-spinner"></span> Đang gọi API...');
    
    const ajaxOptions = { 
        url: url, 
        method: method, 
        dataType: 'json', 
        success: function(response, textStatus, xhr) { 
            $('#api-response').html(JSON.stringify({ status_code: xhr.status, response: response }, null, 2)); 
        }, 
        error: function(xhr, status, error) { 
            $('#api-response').html(JSON.stringify({ 
                status_code: xhr.status, 
                error: error, 
                response: xhr.responseText || 'No response' 
            }, null, 2)); 
        } 
    };
    
    if ((method === 'POST' || method === 'PUT') && body && body.trim() !== '{}') { 
        ajaxOptions.contentType = 'application/json'; 
        ajaxOptions.data = body; 
    }
    
    $.ajax(ajaxOptions);
}

// ===== QUICK TEST =====
function quickTest(apiType) {
    const templates = {
        'cart': { method: 'GET', url: '/api/cart', body: {} },
        'cart-count': { method: 'GET', url: '/api/cart?action=count', body: {} },
        'cart-total': { method: 'GET', url: '/api/cart?action=total', body: {} },
        'cart-add': { method: 'POST', url: '/api/cart?action=add', body: { product_id: 1, quantity: 2 } },
        'cart-clear': { method: 'POST', url: '/api/cart?action=clear', body: {} },
        'payment': { method: 'GET', url: '/api/payment', body: {} },
        'payment-create': { method: 'POST', url: '/api/payment?action=create', body: { order_id: 1, payment_method: 'cod' } },
        'payment-confirm': { method: 'POST', url: '/api/payment?action=confirm', body: { payment_id: 1 } },
        'orders': { method: 'GET', url: '/api/orders', body: {} },
        'order-checkout': { method: 'POST', url: '/api/orders?action=checkout', body: { payment_method: 'cod', notes: 'Giao nhanh' } },
        'order-stats': { method: 'GET', url: '/api/orders?action=statistics', body: {} }
    };
    
    const template = templates[apiType];
    if (template) {
        $('#api-method').val(template.method);
        $('#api-url').val(template.url);
        $('#api-body').val(JSON.stringify(template.body, null, 2));
        $('html, body').animate({ scrollTop: $('#api-test-console').offset().top - 100 }, 300);
        setTimeout(() => { testApi(); }, 500);
    }
}

// ===== QUICK TEST PRODUCT =====
function quickTestProduct(testType) {
    const tests = {
        'search-iphone': { url: '/api/products?search=iphone', desc: 'Tìm kiếm "iphone"' },
        'search-samsung': { url: '/api/products?search=samsung', desc: 'Tìm kiếm "samsung"' },
        'search-dongho': { url: '/api/products?search=đồng hồ', desc: 'Tìm kiếm "đồng hồ"' },
        'filter-category': { url: '/api/products?category_id=1', desc: 'Lọc theo danh mục ID=1' },
        'filter-price': { url: '/api/products?min_price=10000000&max_price=50000000', desc: 'Lọc giá từ 10-50 triệu' },
        'filter-combine': { url: '/api/products?search=iphone&category_id=1&min_price=10000000&max_price=50000000&sort=price_asc&page=1', desc: 'Kết hợp tất cả bộ lọc' },
        'sort-price-asc': { url: '/api/products?sort=price_asc', desc: 'Sắp xếp giá tăng dần' },
        'sort-price-desc': { url: '/api/products?sort=price_desc', desc: 'Sắp xếp giá giảm dần' },
        'sort-newest': { url: '/api/products?sort=newest', desc: 'Sắp xếp theo ngày mới nhất' }
    };
    
    const test = tests[testType];
    if (test) {
        $('#api-method').val('GET');
        $('#api-url').val(test.url);
        $('#api-body').val('{}');
        
        $('html, body').animate({ scrollTop: $('#api-test-console').offset().top - 100 }, 300);
        alert('🔍 Test: ' + test.desc + '\n\nURL: ' + test.url);
        
        setTimeout(() => { testApi(); }, 1500);
    }
}
</script>
</body>
</html>