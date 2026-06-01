<?php include 'app/views/shares/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4"><i class="fas fa-users me-2"></i>Quản lý tài khoản</h2>
        <a href="/User/add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Thêm tài khoản</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['success']; unset($_SESSION['success']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['error']; unset($_SESSION['error']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- Tìm kiếm -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="🔍 Tìm theo tên hoặc email..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="/User/list" class="btn btn-outline-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td>#<?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['username']) ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                                <td><span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>"><?= strtoupper($u['role']) ?></span></td>
                                <td><?= $u['status'] ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-warning">Khóa</span>' ?></td>
                                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <a href="/User/edit/<?= $u['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <button onclick="if(confirm('Xóa?')) location.href='/User/delete/<?= $u['id'] ?>'" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    
                                </td>
                                  <!-- ✅ Cột Trạng thái: Hiển thị badge màu -->
                            <td>
                                <?php if ($u['status'] == 1): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Bị khoá</span>
                                <?php endif; ?>
                            </td>
                            
                            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                            
                            <!-- ✅ Cột Thao tác: Thêm nút Khoá/Mở khoá -->
                            <td>
                                <div class="btn-group">
                                    <!-- Nút Edit -->
                                    <a href="/User/edit/<?= $u['id'] ?>" class="btn btn-sm btn-info" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <!-- ✅ Nút Khoá/Mở khoá -->
                                    <?php if ($u['status'] == 1): ?>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="toggleUserStatus(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>', 0)" 
                                                title="Khoá tài khoản">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="toggleUserStatus(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>', 1)" 
                                                title="Mở khoá tài khoản">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Nút Delete -->
                                    <button type="button" onclick="confirmDelete(<?= $u['id'] ?>)" class="btn btn-sm btn-danger" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                </table>
            </div>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-white">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ✅ Hàm toggle status
    function toggleUserStatus(userId, username, newStatus) {
        const actionText = newStatus === 1 ? 'Mở khoá' : 'Khoá';
        const confirmMsg = `Bạn có chắc chắn muốn ${actionText} tài khoản "${username}"?\n\n• Khoá: Người dùng không thể đăng nhập\n• Mở khoá: Người dùng có thể đăng nhập bình thường`;
        
        if (confirm(confirmMsg)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/User/toggleStatus';
            
            const userIdInput = document.createElement('input');
            userIdInput.type = 'hidden';
            userIdInput.name = 'user_id';
            userIdInput.value = userId;
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = newStatus;
            
            form.appendChild(userIdInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // ✅ Hàm confirm delete
    function confirmDelete(userId) {
        if (confirm('Bạn có chắc chắn muốn xóa tài khoản này?\nHành động này không thể hoàn tác!')) {
            location.href = '/User/delete/' + userId;
        }
    }
</script>