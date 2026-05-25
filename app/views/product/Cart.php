<?php include 'app/views/shares/header.php'; ?>

<h1>Giỏ hàng</h1>

<?php if (empty($cart)): ?>
    <div class="alert alert-info mt-3">
        Giỏ hàng của bạn đang trống. 
        <a href="/Product" class="alert-link">Tiếp tục mua sắm</a>
    </div>
<?php else: ?>
    <ul class="list-group mt-3">
    <?php 
    $total = 0;
    foreach ($cart as $id => $item): 
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;
    ?>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <!-- Hiển thị ảnh sản phẩm -->
            <?php if (!empty($item['image'])): ?>
                <img src="/<?php echo htmlspecialchars($item['image']); ?>" 
                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                     style="max-width: 100px; margin-right: 15px; object-fit: cover;">
            <?php else: ?>
                <img src="/uploads/no-image.jpg" alt="No Image" style="max-width: 100px; margin-right: 15px;">
            <?php endif; ?>

            <div>
                <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                <p class="mb-1 text-muted">Giá: <?php echo number_format($item['price'], 0, ',', '.'); ?> đ</p>
                
                <!-- Form cập nhật số lượng -->
                <form action="/Product/updateCart" method="POST" class="d-inline">
                    <input type="number" name="quantities[<?php echo $id; ?>]" 
                           value="<?php echo $item['quantity']; ?>" 
                           min="1" style="width: 60px;" class="form-control form-control-sm d-inline">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Cập nhật</button>
                </form>
            </div>
        </div>

        <div class="text-end">
            <p class="mb-2">Thành tiền: <strong class="text-danger"><?php echo number_format($subtotal, 0, ',', '.'); ?> đ</strong></p>
            <!-- Nút xóa khỏi giỏ -->
            <a href="/Product/removeFromCart/<?php echo $id; ?>" 
               class="btn btn-sm btn-danger" 
               onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?');">
                <i class="fas fa-trash"></i> Xóa
            </a>
        </div>
    </li>
    <?php endforeach; ?>
    </ul>

    <!-- Tổng tiền & Nút điều hướng -->
    <div class="mt-4 text-end">
        <h3>Tổng cộng: <span class="text-primary"><?php echo number_format($total, 0, ',', '.'); ?> đ</span></h3>
        <a href="/" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
        </a>
        <a href="/Product/checkout" class="btn btn-success">
            <i class="fas fa-credit-card"></i> Thanh Toán
        </a>
    </div>
<?php endif; ?>

<?php include 'app/views/shares/footer.php'; ?>