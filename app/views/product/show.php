<?php include 'app/views/shares/header.php'; ?>

<style>
    /* Gallery Images */
    .gallery-wrapper {
        margin-bottom: 20px;
    }
    
    .main-image-box {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 10px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 350px;
    }
    
    .main-image-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: opacity 0.2s;
    }
    
    .thumbnails-wrapper {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 5px 0;
    }
    
    .thumb-box {
        flex: 0 0 70px;
        height: 70px;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        overflow: hidden;
        cursor: pointer;
        opacity: 0.7;
        transition: all 0.2s;
    }
    
    .thumb-box:hover,
    .thumb-box.active {
        border-color: #007bff;
        opacity: 1;
    }
    
    .thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Giữ nguyên CSS sao đánh giá của bạn */
    .star-rating { direction: rtl; font-size: 1.5rem; }
    .star-rating input { display: none; }
    .star-rating label { color: #ddd; cursor: pointer; margin: 0 2px; }
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label { color: #ffc107; }
</style>

<div class="container mt-4">
<div class="card shadow-lg">
<div class="card-header bg-primary text-white text-center">
<h2 class="mb-0">Chi tiết sản phẩm</h2>
</div>
<div class="card-body">
<?php if ($product): ?>
<div class="row">
    <!-- CỘT HÌNH ẢNH (ĐÃ SỬA ĐỂ HIỂN THỊ NHIỀU ẢNH) -->
    <div class="col-md-6">
        <div class="gallery-wrapper">
            <!-- Ảnh chính lớn -->
            <div class="main-image-box">
                <?php 
                // Ảnh mặc định là ảnh đầu tiên trong mảng $images
                $defaultImg = !empty($images[0]) ? $images[0] : ($product->image ?? 'images/no-image.png');
                ?>
                <img id="mainProductImg" src="/<?php echo htmlspecialchars($defaultImg, ENT_QUOTES, 'UTF-8'); ?>" 
                     class="img-fluid rounded" alt="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            
            <!-- Danh sách ảnh nhỏ (chỉ hiển thị nếu có nhiều hơn 1 ảnh) -->
            <?php if (count($images) > 1): ?>
            <div class="thumbnails-wrapper">
                <?php foreach ($images as $index => $img): ?>
                    <div class="thumb-box <?php echo $index === 0 ? 'active' : ''; ?>" 
                         onclick="switchImage('<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>', this)">
                        <img src="/<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" alt="Thumb">
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- CỘT THÔNG TIN (GIỮ NGUYÊN) -->
    <div class="col-md-6">
        <h3 class="card-title text-dark font-weight-bold">
            <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
        </h3>
        <p class="card-text">
            <?php echo nl2br(htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8')); ?>
        </p>
        <p class="text-danger font-weight-bold h4">
            💰 <?php echo number_format($product->price, 0, ',', '.'); ?> VND
        </p>
        <p><strong>Danh mục:</strong>
            <span class="badge bg-info text-white">
                <?php echo !empty($product->category_name) ? htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8') : 'Chưa có danh mục'; ?>
            </span>
        </p>
        <div class="mt-4">
            <a href="/Product/addToCart/<?php echo $product->id; ?>" class="btn btn-success px-4">
                ➕ Thêm vào giỏ hàng
            </a>
            <a href="/Product/list" class="btn btn-secondary px-4 ml-2">Quay lại danh sách</a>
        </div>
    </div>
</div>

<?php else: ?>
<div class="alert alert-danger text-center">
<h4>Không tìm thấy sản phẩm!</h4>
</div>
<?php endif; ?>
</div>
</div>
</div>

<!-- PHẦN ĐÁNH GIÁ SẢN PHẨM (GIỮ NGUYÊN 100%) -->
<div class="container mt-5">
<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h4 class="mb-0"><i class="fas fa-star"></i> Đánh giá & Bình luận</h4>
    </div>
    <div class="card-body">
        
        <?php
        // Kết nối DB và load Model (nếu chưa có ở đầu file)
        if (!class_exists('ReviewModel')) {
            require_once 'app/models/ReviewModel.php';
        }
        if (!class_exists('Database')) {
            require_once 'app/config/database.php';
        }
        
        $db = (new Database())->getConnection();
        $reviewModel = new ReviewModel($db);
        
        // Lấy dữ liệu đánh giá
        $reviews = $reviewModel->getReviewsByProduct($product->id);
        $avgRating = $reviewModel->getAverageRating($product->id);
        ?>

        <!-- 1. Thống kê điểm -->
        <div class="text-center mb-4 pb-3 border-bottom">
            <h2 class="display-4 text-warning mb-2">
                <?php echo number_format($avgRating->avg_rating ?? 0, 1); ?> 
                <small class="text-muted h5">/ 5</small>
            </h2>
            <div class="mb-2">
                <?php for($i=1; $i<=5; $i++): ?>
                    <i class="fas fa-star<?php echo $i <= round($avgRating->avg_rating ?? 0) ? ' text-warning' : ' text-muted'; ?>"></i>
                <?php endfor; ?>
            </div>
            <span class="text-muted">(<?php echo $avgRating->total_reviews ?? 0; ?> đánh giá)</span>
        </div>

        <!-- 2. Form gửi đánh giá -->
        <div class="review-form bg-light p-4 rounded mb-5">
            <h5 class="mb-3">Viết đánh giá của bạn</h5>
            <form action="/Product/review/<?php echo $product->id; ?>" method="POST">
                <div class="form-group">
                    <label>Họ tên <span class="text-danger">*</span></label>
                    <input type="text" name="customer_name" class="form-control" required placeholder="Nhập tên của bạn">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="customer_email" class="form-control" placeholder="Email của bạn">
                </div>
                <div class="form-group">
                    <label>Đánh giá <span class="text-danger">*</span></label><br>
                    <div class="star-rating d-inline-block">
                        <input type="radio" name="rating" id="star5" value="5" required><label for="star5">☆</label>
                        <input type="radio" name="rating" id="star4" value="4"><label for="star4">☆</label>
                        <input type="radio" name="rating" id="star3" value="3"><label for="star3">☆</label>
                        <input type="radio" name="rating" id="star2" value="2"><label for="star2">☆</label>
                        <input type="radio" name="rating" id="star1" value="1"><label for="star1">☆</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Bình luận</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Nhận xét về sản phẩm..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Gửi đánh giá</button>
            </form>
        </div>

        <!-- 3. Danh sách bình luận -->
        <h5 class="mb-3">Bình luận gần đây</h5>
        <?php if(count($reviews) > 0): ?>
            <?php foreach($reviews as $rev): ?>
            <div class="media border-bottom py-3">
                <div class="media-body">
                    <div class="d-flex justify-content-between">
                        <h6 class="mt-0 text-primary">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($rev->customer_name); ?>
                        </h6>
                        <small class="text-muted">
                            <?php echo date('d/m/Y', strtotime($rev->created_at)); ?>
                        </small>
                    </div>
                    <div class="text-warning mb-1 small">
                        <?php for($k=1; $k<=5; $k++): ?>
                            <i class="fas fa-star<?php echo $k <= $rev->rating ? '' : ' text-muted'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="mb-0 text-secondary">
                        <?php echo nl2br(htmlspecialchars($rev->comment)); ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted fst-italic">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
        <?php endif; ?>

    </div>
</div>
</div>

<!-- JavaScript xử lý đổi ảnh -->
<script>
function switchImage(src, thumbElement) {
    // Đổi ảnh chính
    const mainImg = document.getElementById('mainProductImg');
    mainImg.style.opacity = '0.5';
    
    setTimeout(function() {
        mainImg.src = '/' + src;
        mainImg.style.opacity = '1';
    }, 150);
    
    // Đổi class active cho thumbnail
    document.querySelectorAll('.thumb-box').forEach(el => el.classList.remove('active'));
    thumbElement.classList.add('active');
}
</script>

<?php include 'app/views/shares/footer.php'; ?>