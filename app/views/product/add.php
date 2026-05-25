<?php include 'app/views/shares/header.php'; ?>

<h1>Thêm sản phẩm mới</h1>
<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
<ul>
<?php foreach ($errors as $error): ?>
<li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<form method="POST" action="/Product/save" enctype="multipart/form-data" onsubmit="return validateForm();">
    <div class="form-group">
        <label for="name">Tên sản phẩm:</label>
        <input type="text" id="name" name="name" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" class="form-control" required></textarea>
    </div>
    
    <div class="form-group">
        <label for="price">Giá:</label>
        <input type="number" id="price" name="price" class="form-control" step="0.01" required>
    </div>
    
    <div class="form-group">
        <label for="category_id">Danh mục:</label>
        <select id="category_id" name="category_id" class="form-control" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category->id; ?>">
                    <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- PHẦN SỬA ĐỔI: THÊM NHIỀU ẢNH -->
    <div class="form-group">
        <label for="images">Hình ảnh (Chọn nhiều ảnh):</label>
        <!-- Đổi name thành images[] và thêm multiple -->
        <input type="file" id="images" name="images[]" class="form-control" multiple accept="image/*">
        <small class="text-muted">Giữ phím Ctrl để chọn nhiều ảnh cùng lúc.</small>
        
        <!-- Khu vực xem trước ảnh -->
        <div id="image-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
    </div>

    <button type="submit" class="btn btn-primary">Thêm sản phẩm</button>
</form>
<a href="/Product/list" class="btn btn-secondary mt-2">Quay lại danh sách sản phẩm</a>

<script>
    // Script xem trước ảnh khi chọn file
    document.getElementById('images').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = ''; // Xóa ảnh cũ
        const files = e.target.files;
        
        for(let i=0; i<files.length; i++){
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.createElement('img');
                img.src = event.target.result;
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '5px';
                img.style.border = '1px solid #ddd';
                img.style.marginRight = '5px';
                preview.appendChild(img);
            }
            reader.readAsDataURL(files[i]);
        }
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>