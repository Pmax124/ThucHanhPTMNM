<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục - API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .category-card {
            transition: all 0.3s;
            border-left: 4px solid #667eea;
        }
        .category-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
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
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <span class="navbar-text text-white">
                <i class="fas fa-tags"></i> Quản lý danh mục (JWT API)
            </span>
            
            <!-- ✅ MỚI: Hiển thị thông tin user bằng JavaScript -->
            <div class="d-flex align-items-center">
                <span class="user-info text-white">
                    <i class="fas fa-user-circle"></i> 
                    <strong id="current-username">Guest</strong>
                    <span id="current-role" class="badge bg-secondary ms-1">User</span>
                </span>
                <button onclick="logout()" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-info-circle"></i> 
                <strong>Trang quản lý danh mục</strong> - jQuery AJAX + JWT Token
            </div>
            <small class="text-muted">
                <i class="fas fa-clock"></i> <span id="access-time">Loading...</span>
            </small>
        </div>

        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-tags"></i> Danh sách danh mục</h2>
            </div>
            <div class="col text-end">
                <button class="btn btn-success" id="btn-add-category">
                    <i class="fas fa-plus"></i> Thêm danh mục
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Category List -->
        <div id="category-list" class="row g-3"></div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="category-form">
                        <input type="hidden" id="category_id">
                        <div class="mb-3">
                            <label class="form-label">Tên danh mục *</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" rows="3"></textarea>
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
        const API_BASE = '/api/categories';
        let categoryModal;
        let currentUser = null;

        // ✅ GỬI TOKEN TRONG MỌI AJAX REQUEST
        $(document).ajaxSend(function(event, xhr, settings) {
            const token = localStorage.getItem('token');
            if (token) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            }
        });

        // ✅ XỬ LÝ LỖI 401, 403
        $(document).ajaxError(function(event, xhr, settings) {
            if (xhr.status === 401) {
                alert('⚠️ Phiên đăng nhập hết hạn!');
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.replace('login.php');
            } else if (xhr.status === 403) {
                const msg = xhr.responseJSON?.message || 'Bạn không có quyền';
                alert('🚫 ' + msg);
            }
        });

        function logout() {
            if (confirm('Bạn có chắc muốn đăng xuất?')) {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.replace('login.php');
            }
        }

        $(document).ready(function() {
            // ✅ KIỂM TRA ĐĂNG NHẬP BẰNG JWT
            const token = localStorage.getItem('token');
            try {
                currentUser = JSON.parse(localStorage.getItem('user') || '{}');
            } catch(e) {
                currentUser = {};
            }
            
            if (!token || !currentUser.username) {
                alert('⚠️ Vui lòng đăng nhập!');
                window.location.replace('login.php');
                return;
            }
            
            // Cập nhật navbar
            $('#current-username').text(currentUser.username);
            $('#current-role').text(currentUser.role === 'admin' ? 'Admin' : 'User')
                             .removeClass('bg-secondary bg-danger')
                             .addClass(currentUser.role === 'admin' ? 'bg-danger' : 'bg-secondary');
            
            // Cập nhật thời gian
            $('#access-time').text(new Date().toLocaleString('vi-VN'));
            
            categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
            loadCategories();
        });

        // Load danh sách danh mục
        function loadCategories() {
            $.ajax({
                url: API_BASE,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#loading').hide();
                    
                    if (response.status === 'success') {
                        let html = '';
                        $.each(response.data, function(index, cat) {
                            html += `
                                <div class="col-md-6">
                                    <div class="card category-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="card-title mb-1">${cat.name}</h5>
                                                    <p class="card-text text-muted small mb-0">${cat.description || 'Không có mô tả'}</p>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-warning btn-edit" data-id="${cat.id}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger btn-delete" data-id="${cat.id}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        $('#category-list').html(html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    $('#loading').html('<div class="alert alert-danger">Lỗi khi tải dữ liệu: ' + xhr.status + '</div>');
                }
            });
        }

        $('#btn-add-category').click(function() {
            $('#modalTitle').text('Thêm danh mục');
            $('#category-form')[0].reset();
            $('#category_id').val('');
            categoryModal.show();
        });

        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            
            $.ajax({
                url: `${API_BASE}/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const cat = response.data;
                        $('#modalTitle').text('Sửa danh mục');
                        $('#category_id').val(cat.id);
                        $('#name').val(cat.name);
                        $('#description').val(cat.description || '');
                        categoryModal.show();
                    }
                }
            });
        });

        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            
            if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
                $.ajax({
                    url: `${API_BASE}/${id}`,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        alert(response.message);
                        loadCategories();
                    },
                    error: function(xhr, status, error) {
                        alert('Lỗi khi xóa: ' + (xhr.responseJSON?.message || error));
                    }
                });
            }
        });

        $('#category-form').submit(function(e) {
            e.preventDefault();
            
            const id = $('#category_id').val();
            const data = {
                name: $('#name').val(),
                description: $('#description').val()
            };

            const url = id ? `${API_BASE}/${id}` : API_BASE;
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    categoryModal.hide();
                    loadCategories();
                },
                error: function(xhr, status, error) {
                    alert('Lỗi: ' + (xhr.responseJSON?.message || error));
                }
            });
        });
    </script>
</body>
</html>