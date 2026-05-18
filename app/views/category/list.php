<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Danh sách danh mục</h1>
    
    <a href="/Category/add" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Thêm danh mục mới
    </a>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Thao tác thành công!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Có lỗi xảy ra!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($categories) > 0): ?>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo $category->id; ?></td>
                    <td><?php echo htmlspecialchars($category->name); ?></td>
                    <td><?php echo htmlspecialchars($category->description); ?></td>
                    <td>
                        <a href="/Category/edit/<?php echo $category->id; ?>" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="/Category/delete/<?php echo $category->id; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">Không có danh mục nào</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'app/views/shares/footer.php'; ?>