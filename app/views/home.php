<?php include 'app/views/shares/header.php'; ?>

<style>
    .hero-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 0;
        margin-bottom: 30px;
        border-radius: 10px;
    }
    
    .filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .product-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 10px;
        overflow: hidden;
        height: 100%;
    }

    .product-card img {
        height: auto;
        min-height: 200px;
        max-height: 300px;
        object-fit: contain;
        width: 100%;
        padding: 10px;
        background: #fff;
        border-bottom: 1px solid #eee;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    
    
    .product-card .card-body {
        padding: 15px;
    }
    
    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }
    
    .product-price {
        color: #e74c3c;
        font-size: 1.3rem;
        font-weight: bold;
        margin: 10px 0;
    }
    
    .category-badge {
        background: #667eea;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        display: inline-block;
        margin-bottom: 10px;
    }
    
    .search-box {
        position: relative;
    }
    
    .search-box input {
        padding-left: 40px;
        border-radius: 25px;
        border: 2px solid #ddd;
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }
    
    .filter-select {
        border-radius: 25px;
        border: 2px solid #ddd;
        padding: 10px 20px;
    }
</style>

<!-- Hero Banner -->
<div class="hero-banner text-center">
    <div class="container">
        <h1 class="display-4 mb-3">
            <i class="fas fa-shopping-bag"></i> Shop Đồ Công Nghệ Chất Lượng Cao
        </h1>
        <p class="lead">Khám phá sản phẩm chất lượng với giá tốt nhất</p>
    </div>
</div>

<div class="container">
    <!-- Bộ lọc sản phẩm -->
    <div class="filter-section">
        <form method="GET" action="/" class="row align-items-end">
            <div class="col-md-6 mb-3">
                <label class="form-label font-weight-bold">
                    <i class="fas fa-search"></i> Tìm kiếm sản phẩm
                </label>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" 
                           name="keyword" 
                           class="form-control" 
                           placeholder="Nhập tên sản phẩm..."
                           value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <label class="form-label font-weight-bold">
                    <i class="fas fa-tags"></i> Danh mục
                </label>
                <select name="category" class="form-control filter-select">
                    <option value="0">-- Tất cả danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>" 
                                <?php echo (isset($category_id) && $category_id == $cat->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label font-weight-bold">
                <i class="fas fa-money-bill-wave"></i> Giá từ
                </label>
                <input type="text" 
                    name="price_min" 
                    class="form-control" 
                    placeholder="0đ"
                    value="<?php echo isset($price_min) ? number_format($price_min, 0, ',', '.') : ''; ?>"
                    onkeyup="formatPrice(this)">
            </div>

            <div class="col-md-3 mb-3">
                <label class="form-label font-weight-bold">
                    <i class="fas fa-money-bill-wave"></i> Đến
                </label>
                <input type="text" 
                    name="price_max" 
                    class="form-control" 
                    placeholder="100.000.000đ"
                    value="<?php echo isset($price_max) ? number_format($price_max, 0, ',', '.') : ''; ?>"
                    onkeyup="formatPrice(this)">
            </div>
            
            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </div>
        </form>
        
        <?php if (!empty($keyword) || (isset($category_id) && $category_id > 0) || !empty($price_min) || !empty($price_max)): ?>
        <div class="mt-3">
            <a href="/" class="btn btn-sm btn-secondary">
                <i class="fas fa-redo"></i> Xem tất cả
            </a>
            <span class="ml-3 text-muted">
                Kết quả: <?php echo count($products); ?> sản phẩm
                <?php 
                $filters = [];
                if (!empty($price_min)) $filters[] = "từ " . number_format($price_min) . "đ";
                if (!empty($price_max)) $filters[] = "đến " . number_format($price_max) . "đ";
                if (!empty($filters)) echo "(" . implode(" ", $filters) . ")";
                ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Danh sách sản phẩm -->
    <h2 class="mb-4 font-weight-bold">
        <i class="fas fa-box-open"></i> Sản phẩm
    </h2>
    
    <?php if (count($products) > 0): ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card product-card">
                    <img src="<?php echo !empty($product->image) ? '/' . $product->image : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" 
                        class="card-img-top" 
                        alt="<?php echo htmlspecialchars($product->name); ?>"
                        onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                    <div class="card-body d-flex flex-column">
                        <span class="category-badge">
                            <?php echo htmlspecialchars($product->category_name ?? 'Chưa phân loại'); ?>
                        </span>
                        <h5 class="product-title">
                            <?php echo htmlspecialchars($product->name); ?>
                        </h5>
                        <p class="text-muted small flex-grow-1">
                            <?php echo htmlspecialchars(substr($product->description ?? '', 0, 80)); ?>...
                        </p>
                        <div class="product-price">
                            <?php echo number_format($product->price, 0, ',', '.'); ?> đ
                        </div>
                        <a href="/Product/show/<?php echo $product->id; ?>" 
                           class="btn btn-outline-primary btn-block">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-3x mb-3"></i>
            <h4>Không tìm thấy sản phẩm nào</h4>
            <p>Thử thay đổi từ khóa tìm kiếm hoặc danh mục</p>
            <a href="/" class="btn btn-primary">
                <i class="fas fa-redo"></i> Xem tất cả sản phẩm
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
// Format số tiền với dấu chấm ngăn cách hàng nghìn
function formatNumberWithDots(input) {
    // Loại bỏ tất cả dấu chấm và ký tự không phải số
    let value = input.value.replace(/\./g, '').replace(/\D/g, '');
    
    // Format với dấu chấm
    if (value) {
        input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
}

// Xử lý khi focus vào ô input
document.querySelectorAll('input[name="price_min"], input[name="price_max"]').forEach(function(input) {
    // Khi nhập
    input.addEventListener('input', function(e) {
        formatNumberWithDots(e.target);
    });
    
    // Khi submit form - loại bỏ dấu chấm
    input.addEventListener('blur', function(e) {
        // Giữ nguyên format khi blur
    });
});

// Xử lý trước khi submit form
document.querySelector('form[method="GET"]').addEventListener('submit', function(e) {
    const priceInputs = this.querySelectorAll('input[name="price_min"], input[name="price_max"]');
    priceInputs.forEach(function(input) {
        // Loại bỏ dấu chấm trước khi submit
        let cleanValue = input.value.replace(/\./g, '');
        input.value = cleanValue;
    });
});

// Format lại khi trang load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="price_min"], input[name="price_max"]').forEach(function(input) {
        if (input.value && !input.value.includes('.')) {
            let value = input.value.replace(/\D/g, '');
            if (value) {
                input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        }
    });
});
</script>

<?php include 'app/views/shares/footer.php'; ?>