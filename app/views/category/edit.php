<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Chỉnh sửa danh mục</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="/Category/update/<?php echo $category->id; ?>" method="POST">
        <div class="form-group">
            <label for="name">Tên danh mục <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?php echo htmlspecialchars($category->name); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Mô tả</label>
            <textarea class="form-control" id="description" name="description" 
                      rows="3"><?php echo htmlspecialchars($category->description); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Cập nhật
        </button>
        <a href="/Category/list" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </form>
</div>

<?php include 'app/views/shares/footer.php'; ?>