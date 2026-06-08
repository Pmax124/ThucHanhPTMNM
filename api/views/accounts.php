<?php
// ✅ Start session
session_start();

// ✅ Lấy thông tin user từ session
$currentUser = $_SESSION['username'] ?? 'Guest';
$currentRole = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? null;

// ✅ BẢO MẬT: Chỉ admin mới được truy cập trang quản lý tài khoản
// (Bỏ comment nếu muốn áp dụng)
// if (!$userId || $currentRole !== 'admin') {
//     $_SESSION['error'] = 'Bạn không có quyền truy cập trang này!';
//     header('Location: /account/login');
//     exit;
// }

// ✅ Thời gian truy cập
$accessTime = date('H:i:s d/m/Y');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản - API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .stat-card { border-left: 4px solid; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card.admin-active { border-left-color: #dc3545; }
        .stat-card.user-active { border-left-color: #28a745; }
        .stat-card.admin-inactive { border-left-color: #6c757d; }
        .stat-card.user-inactive { border-left-color: #ffc107; }
        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 5px 15px;
            border-radius: 20px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <!-- ✅ SỬA: Đổi từ index.html thành index.php -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <span class="navbar-text text-white">
                <i class="fas fa-users-cog"></i> Quản lý tài khoản (PHP + jQuery)
            </span>
            
            <!-- ✅ MỚI: Hiển thị thông tin user (PHP) -->
            <?php if ($userId): ?>
                <span class="user-info text-white">
                    <i class="fas fa-user-circle"></i> 
                    <strong><?= htmlspecialchars($currentUser) ?></strong>
                    <?php if ($currentRole === 'admin'): ?>
                        <span class="badge bg-danger ms-1">Admin</span>
                    <?php else: ?>
                        <span class="badge bg-secondary ms-1">User</span>
                    <?php endif; ?>
                </span>
                <a href="/account/logout" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            <?php else: ?>
                <a href="/account/login" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- ✅ MỚI: Hiển thị thông tin truy cập (PHP) -->
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-info-circle"></i> 
                <strong>Trang quản lý tài khoản</strong> - Sử dụng jQuery AJAX gọi API
            </div>
            <small class="text-muted">
                <i class="fas fa-clock"></i> Truy cập: <?= $accessTime ?>
                <?php if ($userId): ?>
                    | <i class="fas fa-user"></i> Bởi: <strong><?= htmlspecialchars($currentUser) ?></strong>
                <?php endif; ?>
            </small>
        </div>

        <!-- Thống kê -->
        <div id="statistics" class="row g-3 mb-4"></div>

        <!-- Bộ lọc -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" id="search-keyword" class="form-control" placeholder="🔍 Tìm kiếm...">
                    </div>
                    <div class="col-md-2">
                        <select id="filter-role" class="form-select">
                            <option value="">Tất cả vai trò</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filter-status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1">Hoạt động</option>
                            <option value="0">Bị khóa</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button id="btn-filter" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </div>
                    <div class="col-md-3 text-end">
                        <button id="btn-add" class="btn btn-success">
                            <i class="fas fa-plus"></i> Thêm tài khoản
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>

        <!-- Bảng tài khoản -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="account-list"></tbody>
                    </table>
                </div>
            </div>
            <div id="pagination" class="card-footer"></div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="accountModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="account-form">
                        <input type="hidden" id="account_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mật khẩu <span id="password-required">*</span></label>
                                <input type="password" class="form-control" id="password">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ tên</label>
                                <input type="text" class="form-control" id="fullname">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SĐT</label>
                                <input type="text" class="form-control" id="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vai trò</label>
                                <select class="form-select" id="role">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" id="status">
                                <option value="1">Hoạt động</option>
                                <option value="0">Bị khóa</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = '/api/accounts';
        let accountModal;
        let currentPage = 1;
        let currentFilters = { keyword: '', role: '', status: '' };

        const roleLabels = { admin: 'Admin', user: 'User' };
        const roleColors = { admin: 'danger', user: 'secondary' };

        $(document).ready(function() {
            accountModal = new bootstrap.Modal(document.getElementById('accountModal'));
            loadStatistics();
            loadAccounts();

            $('#btn-filter').click(function() {
                currentFilters = {
                    keyword: $('#search-keyword').val(),
                    role: $('#filter-role').val(),
                    status: $('#filter-status').val()
                };
                currentPage = 1;
                loadAccounts();
            });

            $('#btn-add').click(function() {
                $('#modalTitle').text('Thêm tài khoản');
                $('#account-form')[0].reset();
                $('#account_id').val('');
                $('#password').prop('required', true);
                $('#password-required').show();
                accountModal.show();
            });

            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                $.getJSON(`${API_BASE}/${id}`, function(res) {
                    if (res.status === 'success') {
                        const a = res.data;
                        $('#modalTitle').text('Sửa tài khoản #' + a.id);
                        $('#account_id').val(a.id);
                        $('#username').val(a.username);
                        $('#password').val('').prop('required', false);
                        $('#password-required').hide();
                        $('#fullname').val(a.fullname || '');
                        $('#email').val(a.email || '');
                        $('#phone').val(a.phone || '');
                        $('#role').val(a.role);
                        $('#address').val(a.address || '');
                        $('#status').val(a.status);
                        accountModal.show();
                    }
                });
            });

            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (confirm('Xóa tài khoản này? Hành động không thể hoàn tác!')) {
                    $.ajax({
                        url: `${API_BASE}/${id}`,
                        method: 'DELETE',
                        dataType: 'json',
                        success: function(res) {
                            alert(res.message);
                            loadAccounts();
                            loadStatistics();
                        }
                    });
                }
            });

            $('#account-form').submit(function(e) {
                e.preventDefault();
                const id = $('#account_id').val();
                const data = {
                    username: $('#username').val(),
                    email: $('#email').val(),
                    fullname: $('#fullname').val(),
                    phone: $('#phone').val(),
                    role: $('#role').val(),
                    address: $('#address').val(),
                    status: parseInt($('#status').val())
                };

                const password = $('#password').val();
                if (password || !id) {
                    data.password = password;
                }

                $.ajax({
                    url: id ? `${API_BASE}/${id}` : API_BASE,
                    method: id ? 'PUT' : 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    success: function(res) {
                        alert(res.message);
                        accountModal.hide();
                        loadAccounts();
                        loadStatistics();
                    },
                    error: function(xhr) {
                        alert('Lỗi: ' + (xhr.responseJSON?.message || 'Không xác định'));
                    }
                });
            });
        });

        function loadStatistics() {
            $.getJSON(`${API_BASE}?action=statistics`, function(res) {
                if (res.status === 'success') {
                    let stats = { admin_active: 0, user_active: 0, admin_inactive: 0, user_inactive: 0 };
                    $.each(res.data, function(i, stat) {
                        const key = `${stat.role}_${stat.status == 1 ? 'active' : 'inactive'}`;
                        stats[key] = parseInt(stat.count);
                    });

                    let html = `
                        <div class="col-md-3">
                            <div class="card stat-card admin-active">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase small">Admin (Active)</h6>
                                    <h4 class="mb-1 text-danger">${stats.admin_active}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card user-active">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase small">User (Active)</h6>
                                    <h4 class="mb-1 text-success">${stats.user_active}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card admin-inactive">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase small">Admin (Inactive)</h6>
                                    <h4 class="mb-1 text-secondary">${stats.admin_inactive}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card user-inactive">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase small">User (Inactive)</h6>
                                    <h4 class="mb-1 text-warning">${stats.user_inactive}</h4>
                                </div>
                            </div>
                        </div>
                    `;
                    $('#statistics').html(html);
                }
            });
        }

        function loadAccounts() {
            let url = `${API_BASE}?page=${currentPage}`;
            if (currentFilters.keyword) url += `&search=${encodeURIComponent(currentFilters.keyword)}`;
            if (currentFilters.role) url += `&role=${currentFilters.role}`;
            if (currentFilters.status !== '') url += `&status=${currentFilters.status}`;

            $.getJSON(url, function(res) {
                $('#loading').hide();
                if (res.status === 'success') {
                    let html = '';
                    if (res.data.length === 0) {
                        html = '<tr><td colspan="9" class="text-center text-muted py-4">Không có tài khoản</td></tr>';
                    } else {
                        $.each(res.data, function(i, a) {
                            html += `
                                <tr>
                                    <td><strong>#${a.id}</strong></td>
                                    <td>${a.username}</td>
                                    <td>${a.fullname || '-'}</td>
                                    <td>${a.email || '-'}</td>
                                    <td>${a.phone || '-'}</td>
                                    <td><span class="badge bg-${roleColors[a.role] || 'secondary'}">${roleLabels[a.role] || a.role}</span></td>
                                    <td><span class="badge bg-${a.status == 1 ? 'success' : 'warning'}">${a.status == 1 ? 'Hoạt động' : 'Bị khóa'}</span></td>
                                    <td>${new Date(a.created_at).toLocaleDateString('vi-VN')}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-edit" data-id="${a.id}"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger btn-delete" data-id="${a.id}"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    $('#account-list').html(html);
                    renderPagination(res.page, res.total_pages);
                }
            });
        }

        function renderPagination(current, total) {
            if (total <= 1) { $('#pagination').html(''); return; }
            let html = '<nav><ul class="pagination justify-content-center mb-0">';
            html += `<li class="page-item ${current <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current-1}">Trước</a></li>`;
            for (let i = 1; i <= total; i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            html += `<li class="page-item ${current >= total ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current+1}">Sau</a></li></ul></nav>`;
            $('#pagination').html(html);
            $('#pagination .page-link').click(function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page >= 1 && page <= total) {
                    currentPage = page;
                    loadAccounts();
                }
            });
        }
    </script>
</body>
</html>