<?php
session_start();
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
        .btn-add-cart {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            border: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
            color: white;
        }
        .btn-add-cart:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
        }
        .admin-actions {
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="cart.php" class="btn btn-warning btn-sm ms-2 position-relative">
                <i class="fas fa-shopping-cart"></i> Giỏ hàng
                <span id="cart-count-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
            </a>
            <span class="navbar-text text-white">
                <i class="fas fa-box"></i> Quản lý sản phẩm (jQuery)
            </span>
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

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-box"></i> Danh sách sản phẩm</h2>
                <small class="text-muted">
                    Truy cập lúc: <?= date('H:i:s d/m/Y') ?>
                </small>
            </div>
            <div class="col text-end">
                <?php if ($role === 'admin'): ?>
                    <button class="btn btn-success" id="btn-add-product">
                        <i class="fas fa-plus"></i> Thêm sản phẩm
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Product List -->
        <div id="product-list" class="row g-4"></div>
    </div>

    <!-- Modal Thêm/Sửa (chỉ Admin) -->
    <?php if ($role === 'admin'): ?>
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
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = '/api/products';
        let productModal;

        // ✅ TỰ ĐỘNG GẮN TOKEN VÀO MỌI AJAX REQUEST
        $(document).ajaxSend(function(event, xhr, settings) {
            const token = localStorage.getItem('token');
            if (token) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            }
        });

        $(document).ready(function() {
            <?php if ($role === 'admin'): ?>
            productModal = new bootstrap.Modal(document.getElementById('productModal'));
            loadCategories();
            
            $('#btn-add-product').click(function() {
                $('#modalTitle').text('Thêm sản phẩm');
                $('#product-form')[0].reset();
                $('#product_id').val('');
                productModal.show();
            });
            <?php endif; ?>
            
            loadProducts();
            loadCartCount();
        });

        // ✅ HIỂN THỊ TOAST THÔNG BÁO
        function showToast(message, type = 'success') {
            const icon = type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' : 'times-circle');
            const bgClass = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning' : 'bg-danger');
            
            const toastHtml = `
                <div class="toast show" role="alert" style="min-width: 300px;">
                    <div class="toast-header ${bgClass} text-white">
                        <i class="fas fa-${icon} me-2"></i>
                        <strong class="me-auto">${type === 'success' ? 'Thành công' : (type === 'warning' ? 'Cảnh báo' : 'Lỗi')}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            const toast = $(toastHtml);
            $('#toast-container').append(toast);
            
            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                toast.fadeOut(300, function() { $(this).remove(); });
            }, 3000);
        }

        // ✅ LOAD DANH SÁCH SẢN PHẨM
        function loadProducts() {
            $.ajax({
                url: API_BASE,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#loading').hide();
                    
                    if (response.status === 'success') {
                        let html = '';
                        const isAdmin = '<?= $role ?>' === 'admin';
                        const isLoggedIn = !!localStorage.getItem('token');
                        
                        $.each(response.data, function(index, product) {
                            html += `
                                <div class="col-md-4">
                                    <div class="card product-card h-100">
                                        <img src="/${product.image || 'uploads/no-image.jpg'}" 
                                             class="card-img-top product-img" 
                                             alt="${product.name}"
                                             onerror="this.src='/uploads/no-image.jpg'">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">${product.name}</h5>
                                            <p class="card-text text-muted small flex-grow-1">${product.description || ''}</p>
                                            <p class="card-text">
                                                <span class="badge bg-info">${product.category_name || 'Chưa phân loại'}</span>
                                            </p>
                                            <p class="price">${parseInt(product.price).toLocaleString('vi-VN')}₫</p>
                                            
                                            <!-- ✅ NÚT THÊM VÀO GIỎ (TẤT CẢ USER) -->
                                            <button class="btn btn-add-cart w-100 mb-2 btn-add-to-cart" 
                                                    data-id="${product.id}" 
                                                    data-name="${product.name.replace(/"/g, '&quot;')}">
                                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                            </button>
                                            
                                            <!-- Nút Edit/Delete (CHỈ ADMIN) -->
                                            ${isAdmin ? `
                                            <div class="admin-actions">
                                                <div class="btn-group w-100">
                                                    <button class="btn btn-warning btn-action btn-edit" data-id="${product.id}" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-action btn-delete" data-id="${product.id}" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        if (response.data.length === 0) {
                            html = '<div class="col-12 text-center py-5"><i class="fas fa-box-open fa-3x text-muted mb-3"></i><p class="text-muted">Chưa có sản phẩm nào</p></div>';
                        }
                        
                        $('#product-list').html(html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    $('#loading').html('<div class="alert alert-danger">Lỗi khi tải dữ liệu!</div>');
                }
            });
        }

        // ✅ XỬ LÝ CLICK NÚT "THÊM VÀO GIỎ"
        $(document).on('click', '.btn-add-to-cart', function() {
            const btn = $(this);
            const productId = btn.data('id');
            const productName = btn.data('name');
            
            // Kiểm tra đã đăng nhập chưa
            const token = localStorage.getItem('token');
            if (!token) {
                showToast('⚠️ Vui lòng đăng nhập để thêm vào giỏ hàng!', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
                return;
            }
            
            // Disable button và hiển thị loading
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang thêm...');
            
            // Gọi API thêm vào giỏ
            $.ajax({
                url: '/api/cart?action=add',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    product_id: parseInt(productId),
                    quantity: 1
                }),
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).html(originalHtml);
                    
                    if (res.status === 'success') {
                        showToast('✅ Đã thêm "' + productName + '" vào giỏ hàng!', 'success');
                        loadCartCount(); // Cập nhật badge
                    } else {
                        showToast('❌ ' + res.message, 'danger');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(originalHtml);
                    const msg = xhr.responseJSON?.message || 'Lỗi khi thêm vào giỏ!';
                    
                    if (xhr.status === 401) {
                        showToast('⚠️ Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại!', 'warning');
                        setTimeout(() => {
                            localStorage.removeItem('token');
                            localStorage.removeItem('user');
                            window.location.href = 'login.php';
                        }, 1500);
                    } else {
                        showToast('❌ ' + msg, 'danger');
                    }
                }
            });
        });

        // ✅ LOAD SỐ LƯỢNG GIỎ HÀNG
        function loadCartCount() {
            const token = localStorage.getItem('token');
            if (!token) {
                $('#cart-count-badge').hide();
                return;
            }
            
            $.ajax({
                url: '/api/cart?action=count',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success' && res.count > 0) {
                        $('#cart-count-badge').text(res.count).show();
                    } else {
                        $('#cart-count-badge').hide();
                    }
                },
                error: function() {
                    $('#cart-count-badge').hide();
                }
            });
        }

        <?php if ($role === 'admin'): ?>
        // ✅ LOAD DANH MỤC
        function loadCategories() {
            $.ajax({
                url: '/api/categories',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        let select = $('#category_id');
                        select.find('option:not(:first)').remove();
                        $.each(response.data, function(index, cat) {
                            select.append(`<option value="${cat.id}">${cat.name}</option>`);
                        });
                    }
                }
            });
        }

        // ✅ CLICK NÚT SỬA
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

        // ✅ CLICK NÚT XÓA
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                $.ajax({
                    url: `${API_BASE}/${id}`,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(response) {
                        showToast(response.message, 'success');
                        loadProducts();
                    },
                    error: function(xhr) {
                        showToast('Lỗi: ' + (xhr.responseJSON?.message || 'Không thể xóa'), 'danger');
                    }
                });
            }
        });

        // ✅ SUBMIT FORM THÊM/SỬA
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
                    showToast(response.message, 'success');
                    productModal.hide();
                    loadProducts();
                },
                error: function(xhr) {
                    showToast('Lỗi: ' + (xhr.responseJSON?.message || 'Không thể lưu'), 'danger');
                }
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>