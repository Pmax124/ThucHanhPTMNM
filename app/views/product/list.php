<?php 
include 'app/views/shares/header.php';

// Đảm bảo SessionHelper đã được load
if (!class_exists('SessionHelper')) {
    require_once __DIR__ . '/../helpers/SessionHelper.php';
}
?>

<h1>Danh sách sản phẩm</h1>

<!-- 🔐 CHỈ ADMIN MỚI THẤY NÚT "THÊM SẢN PHẨM" -->
<?php if (SessionHelper::isAdmin()): ?>
<a href="/Product/add" class="btn btn-success mb-2">
    <i class="fas fa-plus"></i> Thêm sản phẩm mới
</a>
<?php endif; ?>

<ul class="list-group">
<?php foreach ($products as $product): ?>
    <li class="list-group-item">
        <h2>
            <a href="/Product/show/<?php echo $product->id; ?>">
                <?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>
            </a>
        </h2>

        <?php if ($product->image): ?>
            <img src="/<?php echo $product->image; ?>" alt="Product Image" style="max-width: 100px;">
        <?php endif; ?>

        <p><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></p>

        <p>Giá: <?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?> VND</p>

        <p>Danh mục: <?php echo htmlspecialchars($product->category_name, ENT_QUOTES, 'UTF-8'); ?></p>

        <!-- Action Buttons -->
        <div class="mt-2 d-flex gap-2 flex-wrap">
            
            <!-- 🔐 Nút Sửa: CHỈ ADMIN -->
            <?php if (SessionHelper::isAdmin()): ?>
            <a href="/Product/edit/<?php echo $product->id; ?>" class="btn btn-warning btn-sm">
                <i class="fas fa-edit"></i> Sửa
            </a>
            <?php endif; ?>

            <!-- 🔐 Nút Xóa: CHỈ ADMIN -->
            <?php if (SessionHelper::isAdmin()): ?>
            <a href="/Product/delete/<?php echo $product->id; ?>" 
               class="btn btn-danger btn-sm" 
               onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                <i class="fas fa-trash"></i> Xóa
            </a>
            <?php endif; ?>

            <!-- 🛒 Nút Thêm vào giỏ: CHỈ USER ĐÃ LOGIN -->
            <?php if (SessionHelper::isLoggedIn()): ?>
            <a href="/Product/addToCart/<?php echo $product->id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
            </a>
            <?php endif; ?>
            
        </div>
    </li>
<?php endforeach; ?>
</ul>

<?php include 'app/views/shares/footer.php'; ?>