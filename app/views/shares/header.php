<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bán hàng</title>
    <!-- Bootstrap 4 CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (icon) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: bold; }
        
        /* Style cho Banner/Slider */
        .banner-section {
            margin-bottom: 30px;
        }
        
        .carousel-item {
            height: 400px;
        }
        
        .carousel-item img {
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }
        
        .carousel-caption {
            bottom: 30%;
        }
        
        .carousel-caption h3 {
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .carousel-caption p {
            font-size: 1.3rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(0,0,0,0.5);
            border-radius: 50%;
            padding: 20px;
        }
        
        .carousel-indicators li {
            background-color: #667eea;
        }
        
        /* Banner nhỏ hơn (nếu muốn) */
        .banner-small {
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .banner-small h2 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .banner-small p {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <a class="navbar-brand" href="/">
        <i class="fas fa-shopping-cart"></i> Shop Điện Tử Công Nghệ
    </a>
    
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            
            <!-- Menu Sản phẩm -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="productDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/Product/list">
                        <i class="fas fa-list"></i> Danh sách sản phẩm
                    </a>
                    <a class="dropdown-item" href="/Product/add">
                        <i class="fas fa-plus"></i> Thêm sản phẩm mới
                    </a>
                </div>
            </li>

            <!-- Menu Danh mục (Category) -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/Category/list">
                        <i class="fas fa-list"></i> Danh sách danh mục
                    </a>
                    <a class="dropdown-item" href="/Category/add">
                        <i class="fas fa-plus"></i> Thêm danh mục mới
                    </a>
                </div>
            </li>

            <!-- Menu Thương hiệu (Brand) -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="brandDropdown" role="button" data-toggle="dropdown">
                    <i class="fas fa-copyright"></i> Thương hiệu
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/Brand/list">
                        <i class="fas fa-list"></i> Danh sách thương hiệu
                    </a>
                    <a class="dropdown-item" href="/Brand/add">
                        <i class="fas fa-plus"></i> Thêm thương hiệu mới
                    </a>
                </div>
            </li>

            <!-- Menu Trang chủ -->
            <li class="nav-item">
                <a class="nav-link" href="/">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
            </li>
        </ul>

        <!-- User info -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <span class="nav-text text-muted small pt-2">Admin</span>
            </li>
        </ul>
    </div>
</nav>

<!-- PHẦN BANNER/SLIDER -->
<div class="banner-section">
    <div id="mainCarousel" class="carousel slide" data-ride="carousel" data-interval="3000">
        <!-- Indicators -->
        <ol class="carousel-indicators">
            <li data-target="#mainCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#mainCarousel" data-slide-to="1"></li>
            <li data-target="#mainCarousel" data-slide-to="2"></li>
        </ol>

        <!-- Carousel Items -->
        <div class="carousel-inner">
            <!-- Slide 1 -->
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200" class="d-block w-100" alt="Slide 1">
                <div class="carousel-caption d-none d-md-block">
                    <h3><i class="fas fa-mobile-alt"></i> Smartphone Cao Cấp</h3>
                    <p>Khám phá những dòng điện thoại mới nhất 2026</p>
                    <a href="/" class="btn btn-primary btn-lg">Mua Ngay</a>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=1200" class="d-block w-100" alt="Slide 2">
                <div class="carousel-caption d-none d-md-block">
                    <h3><i class="fas fa-laptop"></i> Laptop & PC</h3>
                    <p>Cấu hình mạnh mẽ - Giá cực tốt</p>
                    <a href="/" class="btn btn-success btn-lg">Xem Ngay</a>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=1200" class="d-block w-100" alt="Slide 3">
                <div class="carousel-caption d-none d-md-block">
                    <h3><i class="fas fa-headphones"></i> Phụ Kiện Chất Lượng</h3>
                    <p>Tai nghe, loa, và nhiều phụ kiện khác</p>
                    <a href="/" class="btn btn-info btn-lg">Khám Phá</a>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <a class="carousel-control-prev" href="#mainCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#mainCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>

<!-- HOẶC DÙNG BANNER ĐƠN GIẢN (Bỏ comment nếu muốn dùng banner này thay vì slider) -->
<!--
<div class="container">
    <div class="banner-small">
        <div>
            <h2><i class="fas fa-shopping-bag"></i> Shop HUTECH</h2>
            <p>Khám phá sản phẩm chất lượng với giá tốt nhất</p>
        </div>
    </div>
</div>
-->

<div class="container">
    <!-- Nội dung trang sẽ được include vào đây -->