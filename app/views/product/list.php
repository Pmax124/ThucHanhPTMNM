<?php include 'app/views/shares/header.php'; ?>
<h1>Danh sách sản phẩm</h1>
<a href="/Product/add" class="btn btn-success mb-2">Thêm sản phẩm mới</a>
<ul class="list-group">
<?php foreach ($products as $product): ?>
<li class="list-group-item">
<h2><a href="/Product/show/<?php echo $product->id; ?>"><?php

echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?></a></h2>

<?php if ($product->image): ?>
<img src="/<?php echo $product->image; ?>" alt="Product

Image" style="max-width: 100px;">
<?php endif; ?>

<p><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></p>

<p>Giá: <strong><?php echo number_format($product->price, 0, ',', '.'); ?> đ</strong></p>

<p>Danh mục: <?php echo htmlspecialchars($product->category_name,

ENT_QUOTES, 'UTF-8'); ?></p>

<a href="/Product/edit/<?php echo $product->id; ?>" class="btn

btn-warning">Sửa</a>

<a href="/Product/show/<?php echo $product->id; ?>" 
       class="btn btn-info btn-sm" 
       title="Xem chi tiết"
       style="padding: 8px 12px;">
        <i class="fas fa-eye"></i>
    </a>

<!-- Nút Xóa -->
<a href="/Product/delete/<?php echo $product->id; ?>" 
   class="btn btn-danger" 
   title="Xóa sản phẩm"
   onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.');">
   <i class="fas fa-trash"></i>
</a>
</li>
<?php endforeach; ?>
</ul>
<?php include 'app/views/shares/footer.php'; ?>