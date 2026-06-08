<?php
// ✅ Start session
session_start();

// ✅ Optional: Kiểm tra đăng nhập (bỏ comment nếu muốn bảo mật)
// if (!isset($_SESSION['user_id'])) {
//     header('Location: /account/login');
//     exit;
// }

// ✅ Lấy thông tin user từ session (nếu có)
$username = $_SESSION['username'] ?? 'Guest';
$role = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .product-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .product-img {
            height: 200px;
            object-fit: cover;
        }
        .price {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .btn-action {
            width: 35px;
            height: 35px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
                <i class="fas fa-box"></i> Quản lý sản phẩm (jQuery)
            </span>
            <!-- ✅ MỚI: Hiển thị thông tin user (nếu đã đăng nhập) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="navbar-text text-white">
                    <i class="fas fa-user"></i> Xin chào, <strong><?= htmlspecialchars($username) ?></strong>
                    <?php if ($role === 'admin'): ?>
                        <span class="badge bg-danger">Admin</span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-box"></i> Danh sách sản phẩm</h2>
                <!-- ✅ MỚI: Hiển thị thời gian truy cập bằng PHP -->
                <small class="text-muted">
                    Truy cập lúc: <?= date('H:i:s d/m/Y') ?>
                </small>
            </div>
            <div class="col text-end">
                <button class="btn btn-success" id="btn-add-product">
                    <i class="fas fa-plus"></i> Thêm sản phẩm
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Product List - Load bằng jQuery AJAX -->
        <div id="product-list" class="row g-4"></div>
    </div>

    <!-- Modal Thêm/Sửa -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="product-form">
                        <input type="hidden" id="product_id">
                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm *</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả *</label>
                            <textarea class="form-control" id="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Giá *</label>
                            <input type="number" class="form-control" id="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Danh mục *</label>
                            <select class="form-select" id="category_id" required>
                                <option value="">-- Chọn danh mục --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh</label>
                            <input type="text" class="form-control" id="image" placeholder="uploads/image.jpg">
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
        const API_BASE = '/api/products';
        let productModal;

        $(document).ready(function() {
            productModal = new bootstrap.Modal(document.getElementById('productModal'));
            loadProducts();
            loadCategories();
        });

        // Load danh sách sản phẩm bằng jQuery AJAX
        function loadProducts() {
            $.ajax({
                url: API_BASE,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#loading').hide();
                    
                    if (response.status === 'success') {
                        let html = '';
                        $.each(response.data, function(index, product) {
                            html += `
                                <div class="col-md-4">
                                    <div class="card product-card h-100">
                                        <img src="/${product.image || 'uploads/no-image.jpg'}" 
                                             class="card-img-top product-img" 
                                             alt="${product.name}"
                                             onerror="this.src='/uploads/no-image.jpg'">
                                        <div class="card-body">
                                            <h5 class="card-title">${product.name}</h5>
                                            <p class="card-text text-muted small">${product.description}</p>
                                            <p class="card-text">
                                                <span class="badge bg-info">${product.category_name}</span>
                                            </p>
                                            <p class="price">${parseInt(product.price).toLocaleString('vi-VN')}₫</p>
                                            <div class="btn-group">
                                                <button class="btn btn-warning btn-action btn-edit" data-id="${product.id}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-action btn-delete" data-id="${product.id}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        $('#product-list').html(html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    $('#loading').html('<div class="alert alert-danger">Lỗi khi tải dữ liệu!</div>');
                }
            });
        }

        // Load danh mục bằng jQuery AJAX
        function loadCategories() {
            $.ajax({
                url: '/api/categories',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        let select = $('#category_id');
                        $.each(response.data, function(index, cat) {
                            select.append(`<option value="${cat.id}">${cat.name}</option>`);
                        });
                    }
                }
            });
        }

        // Click nút thêm sản phẩm
        $('#btn-add-product').click(function() {
            $('#modalTitle').text('Thêm sản phẩm');
            $('#product-form')[0].reset();
            $('#product_id').val('');
            productModal.show();
        });

        // Click nút sửa sản phẩm (event delegation)
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            
            $.ajax({
                url: `${API_BASE}/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const product = response.data;
                        $('#modalTitle').text('Sửa sản phẩm');
                        $('#product_id').val(product.id);
                        $('#name').val(product.name);
                        $('#description').val(product.description);
                        $('#price').val(product.price);
                        $('#category_id').val(product.category_id);
                        $('#image').val(product.image);
                        productModal.show();
                    }
                }
            });
        });

        // Click nút xóa sản phẩm (event delegation)
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                $.ajax({
                    url: `${API_BASE}/${id}`,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        alert(response.message);
                        loadProducts();
                    },
                    error: function(xhr, status, error) {
                        alert('Lỗi khi xóa sản phẩm!');
                    }
                });
            }
        });

        // Submit form thêm/sửa sản phẩm
        $('#product-form').submit(function(e) {
            e.preventDefault();
            
            const id = $('#product_id').val();
            const data = {
                name: $('#name').val(),
                description: $('#description').val(),
                price: parseFloat($('#price').val()),
                category_id: parseInt($('#category_id').val()),
                image: $('#image').val()
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
                    productModal.hide();
                    loadProducts();
                },
                error: function(xhr, status, error) {
                    alert('Lỗi: ' + (xhr.responseJSON?.message || error));
                }
            });
        });
    </script>
</body>
</html>