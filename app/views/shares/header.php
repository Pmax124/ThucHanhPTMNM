<?php
// Đảm bảo SessionHelper đã được load
if (!class_exists('SessionHelper')) {
    require_once __DIR__ . '/../helpers/SessionHelper.php';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bán hàng</title>
    <!-- Bootstrap 4 CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* ✅ CHỐNG CUỘN NGANG TOÀN TRANG */
        html, body {
            overflow-x: hidden;
            max-width: 100vw;
        }

        /* ✅ NAVBAR COMPACT */
        .navbar {
            padding: 0.3rem 1rem !important;
            min-height: 50px !important;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.1rem !important;
            padding: 0 !important;
        }

        .navbar-nav .nav-link {
            padding: 0.4rem 0.6rem !important;
            font-size: 0.85rem !important;
        }

        .dropdown-menu {
            padding: 0.3rem 0 !important;
            font-size: 0.85rem !important;
        }

        .dropdown-item {
            padding: 0.3rem 1rem !important;
        }

        /* ✅ BADGE NHỎ GỌN */
        .badge {
            font-size: 0.65rem !important;
            padding: 0.25em 0.5em !important;
        }

        .cart-count {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 5px;
            font-size: 0.65rem;
            font-weight: bold;
            min-width: 16px;
            text-align: center;
            line-height: 1;
        }

        /* ✅ ROLE BADGE NHỎ */
        .role-badge {
            font-size: 0.65rem;
            padding: 1px 6px;
            border-radius: 8px;
            margin-left: 4px;
            font-weight: 600;
            display: inline-block;
        }
        .role-badge.admin { background: #dc3545; color: white; }
        .role-badge.user { background: #6c757d; color: white; }

        /* ✅ CAROUSEL COMPACT */
        .banner-section { margin-bottom: 20px; }
        .carousel-item { height: 300px; }
        .carousel-item img {
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }
        .carousel-caption {
            bottom: 25%;
            padding: 0 1rem;
        }
        .carousel-caption h3 {
            font-size: 1.8rem !important;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .carousel-caption p {
            font-size: 1rem !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        .carousel-caption .btn {
            padding: 0.3rem 0.8rem !important;
            font-size: 0.9rem !important;
        }

        /* ✅ RESPONSIVE: Ẩn bớt menu trên mobile */
        @media (max-width: 992px) {
            .navbar-nav .dropdown-toggle::after { display: none; }
            .navbar-collapse {
                max-height: 80vh;
                overflow-y: auto;
            }
            .carousel-item { height: 250px; }
            .carousel-caption h3 { font-size: 1.4rem !important; }
            .carousel-caption p { font-size: 0.9rem !important; }
        }

        @media (max-width: 768px) {
            .navbar-brand { font-size: 1rem !important; }
            .navbar-nav .nav-link { font-size: 0.9rem !important; }
            .role-badge { display: none; } /* Ẩn badge role trên mobile cho gọn */
            .carousel-item { height: 200px; }
            .carousel-caption { display: none !important; } /* Ẩn caption trên mobile nhỏ */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <a class="navbar-brand" href="/">
        <i class="fas fa-shopping-cart"></i> Shop Điện Tử
    </a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            
            <!-- 🔐 Menu Sản phẩm -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="productDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/Product/list">
                        <i class="fas fa-list"></i> Danh sách
                    </a>
                    <?php if (SessionHelper::isAdmin()): ?>
                    <a class="dropdown-item" href="/Product/add">
                        <i class="fas fa-plus"></i> Thêm mới
                    </a>
                    <?php endif; ?>
                </div>
            </li>

            <!-- 🔐 Menu Danh mục: CHỈ ADMIN -->
            <?php if (SessionHelper::isAdmin()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/Category/list">
                        <i class="fas fa-list"></i> Danh sách
                    </a>
                    <a class="dropdown-item" href="/Category/add">
                        <i class="fas fa-plus"></i> Thêm mới
                    </a>
                </div>
            </li>
            <?php endif; ?>

            <!-- 🔐 Menu Thương hiệu: CHỈ ADMIN -->
            <?php if (SessionHelper::isAdmin()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="brandDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-copyright"></i> Thương hiệu
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/Brand/list">
                        <i class="fas fa-list"></i> Danh sách
                    </a>
                    <a class="dropdown-item" href="/Brand/add">
                        <i class="fas fa-plus"></i> Thêm mới
                    </a>
                </div>
            </li>
            <?php endif; ?>

            <!-- 🔐 Menu Voucher: CHỈ ADMIN -->
            <?php if (SessionHelper::isAdmin()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="voucherDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-gift"></i> Voucher
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/Voucher/list">
                        <i class="fas fa-list"></i> Danh sách
                    </a>
                    <a class="dropdown-item" href="/Voucher/add">
                        <i class="fas fa-plus"></i> Tạo mới
                    </a>
                </div>
            </li>
            <?php endif; ?>

            <!-- 🔐 Menu Đơn hàng: CHỈ ADMIN -->
            <?php if (SessionHelper::isAdmin()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="orderDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-shopping-bag"></i> Đơn hàng
                    <?php 
                    function countPendingOrdersInHeader() {
                        try {
                            $host = 'localhost';
                            $dbname = 'my_store';
                            $user = 'root';
                            $pass = '';
                            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
                            $stmt->execute();
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            return $row['total'] ?? 0;
                        } catch (Exception $e) {
                            return 0;
                        }
                    }
                    $pending_count = countPendingOrdersInHeader();
                    if ($pending_count > 0): 
                    ?>
                        <span class="badge badge-danger ml-1"><?= $pending_count ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/AdminOrder/index">
                        <i class="fas fa-list"></i> Tất cả
                    </a>
                    <a class="dropdown-item" href="/AdminOrder/index?status=pending">
                        <i class="fas fa-clock"></i> Chờ xử lý
                        <?php if ($pending_count > 0): ?>
                            <span class="badge badge-danger float-right"><?= $pending_count ?></span>
                        <?php endif; ?>
                    </a>
                    <a class="dropdown-item" href="/AdminOrder/index?status=processing">
                        <i class="fas fa-truck"></i> Đang giao
                    </a>
                    <a class="dropdown-item" href="/AdminOrder/index?status=completed">
                        <i class="fas fa-check-circle"></i> Hoàn thành
                    </a>
                </div>
            </li>
            <?php endif; ?>

            <!-- 🔐 Menu Tài khoản: CHỈ ADMIN -->
            <?php if (SessionHelper::isAdmin()): ?>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-users-cog"></i> Tài khoản
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/User/list">
                        <i class="fas fa-list"></i> Danh sách
                    </a>
                    <a class="dropdown-item" href="/User/add">
                        <i class="fas fa-user-plus"></i> Thêm mới
                    </a>
                </div>
            </li>
            <?php endif; ?>

            <!-- Menu Trang chủ -->
            <li class="nav-item">
                <a class="nav-link" href="/">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
            </li>
        </ul>

        <!-- User info & Cart -->
        <ul class="navbar-nav align-items-center">
            <!-- Giỏ hàng -->
            <?php if (SessionHelper::isLoggedIn()): ?>
            <li class="nav-item mr-2">
                <a class="nav-link cart-icon position-relative" href="/Product/cart">
                    <i class="fas fa-shopping-cart"></i>
                    <?php 
                    $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                    if ($cart_count > 0): 
                    ?>
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <li class="nav-item">
                <span class="nav-link text-muted">|</span>
            </li>
            <?php endif; ?>
            
            <!-- User info -->
            <?php if (SessionHelper::isLoggedIn()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-user"></i> 
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                        <span class="role-badge <?php echo SessionHelper::getRole(); ?>">
                            <?php echo strtoupper(SessionHelper::getRole()); ?>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <?php if (SessionHelper::isAdmin()): ?>
                            <h6 class="dropdown-header text-primary">⚙️ Quản trị</h6>
                            <a class="dropdown-item" href="/Product/add">
                                <i class="fas fa-plus-circle"></i> Thêm sản phẩm
                            </a>
                            <a class="dropdown-item" href="/Category/list">
                                <i class="fas fa-tags"></i> Danh mục
                            </a>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        
                        <a class="dropdown-item" href="/account/profile">
                            <i class="fas fa-user-circle"></i> Hồ sơ
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="/account/logout">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </div>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="/account/login">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- BANNER/SLIDER COMPACT -->
<div class="banner-section">
    <div id="mainCarousel" class="carousel slide" data-ride="carousel" data-interval="3000">
        <ol class="carousel-indicators">
            <li data-target="#mainCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#mainCarousel" data-slide-to="1"></li>
            <li data-target="#mainCarousel" data-slide-to="2"></li>
        </ol>

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200" class="d-block w-100" alt="Slide 1">
                <div class="carousel-caption d-none d-md-block">
                    <h3><i class="fas fa-mobile-alt"></i> Smartphone Cao Cấp</h3>
                    <p>Khám phá dòng điện thoại mới nhất 2026</p>
                    <a href="/" class="btn btn-primary btn-sm">Mua Ngay</a>
                </div>
            </div>

            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=1200" class="d-block w-100" alt="Slide 2">
                <div class="carousel-caption d-none d-md-block">
                    <h3><i class="fas fa-laptop"></i> Laptop & PC</h3>
                    <p>Cấu hình mạnh - Giá cực tốt</p>
                    <a href="/" class="btn btn-success btn-sm">Xem Ngay</a>
                </div>
            </div>

            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=1200" class="d-block w-100" alt="Slide 3">
                <div class="carousel-caption d-none d-md-block">
                    <h3><i class="fas fa-headphones"></i> Phụ Kiện Chất Lượng</h3>
                    <p>Tai nghe, loa, và nhiều phụ kiện khác</p>
                    <a href="/" class="btn btn-info btn-sm">Khám Phá</a>
                </div>
            </div>
        </div>

        <a class="carousel-control-prev" href="#mainCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </a>
        <a class="carousel-control-next" href="#mainCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </a>
    </div>
</div>

<div class="container">
    <!-- Nội dung trang sẽ được include vào đây -->