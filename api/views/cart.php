<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng của tôi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .cart-item { transition: all 0.3s; }
        .cart-item:hover { background: #f1f3f5; }
        .cart-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .qty-input { width: 70px; text-align: center; }
        .summary-box { position: sticky; top: 80px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-arrow-left"></i> Quay lại</a>
            <span class="navbar-text text-white"><i class="fas fa-shopping-cart"></i> Giỏ hàng của tôi</span>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark" id="cart-badge">0 sản phẩm</span>
                <button onclick="logout()" class="btn btn-outline-light btn-sm ms-2">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="loading" class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>

        <div id="cart-content" style="display:none;">
            <div class="row">
                <!-- Danh sách sản phẩm -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Sản phẩm trong giỏ</h5>
                            <button id="btn-clear-cart" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i> Xóa tất cả
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div id="cart-list"></div>
                        </div>
                    </div>
                </div>

                <!-- Tổng kết -->
                <div class="col-md-4">
                    <div class="card summary-box">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-receipt"></i> Tổng đơn hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tạm tính:</span>
                                <strong id="subtotal">0₫</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Phí vận chuyển:</span>
                                <strong id="shipping-fee">30,000₫</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fs-5">Tổng cộng:</span>
                                <strong class="text-danger fs-4" id="total-amount">0₫</strong>
                            </div>
                            <button id="btn-checkout" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-credit-card"></i> Tiến hành đặt hàng
                            </button>
                            <a href="products.php" class="btn btn-outline-primary w-100 mt-2">
                                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="empty-cart" class="text-center py-5" style="display:none;">
            <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
            <h3 class="text-muted">Giỏ hàng trống</h3>
            <p class="text-muted">Hãy thêm sản phẩm vào giỏ hàng</p>
            <a href="products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag"></i> Mua sắm ngay
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_CART = '/api/cart';
        const SHIPPING_FEE = 30000;

        // Gửi token
        $(document).ajaxSend(function(event, xhr) {
            const token = localStorage.getItem('token');
            if (token) xhr.setRequestHeader('Authorization', 'Bearer ' + token);
        });

        $(document).ajaxError(function(event, xhr) {
            if (xhr.status === 401) {
                alert('Phiên đăng nhập hết hạn!');
                localStorage.clear();
                window.location.replace('login.php');
            }
        });

        function logout() {
            if (confirm('Đăng xuất?')) {
                localStorage.clear();
                window.location.replace('login.php');
            }
        }

        $(document).ready(function() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.replace('login.php');
                return;
            }
            loadCart();
        });

        function loadCart() {
            $.ajax({
                url: API_CART,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    $('#loading').hide();
                    if (res.status === 'success') {
                        if (res.data.length === 0) {
                            $('#empty-cart').show();
                            $('#cart-content').hide();
                        } else {
                            renderCart(res.data);
                            $('#cart-content').show();
                            $('#empty-cart').hide();
                            updateTotal(res.data);
                            $('#cart-badge').text(res.total_items + ' sản phẩm');
                        }
                    }
                },
                error: function(xhr) {
                    $('#loading').hide();
                    alert('Lỗi tải giỏ hàng: ' + xhr.status);
                }
            });
        }

        function renderCart(items) {
            let html = '';
            $.each(items, function(i, item) {
                const img = item.image ? `/${item.image.replace(/^\//, '')}` : 'https://via.placeholder.com/80';
                html += `
                    <div class="cart-item p-3 border-bottom d-flex align-items-center">
                        <img src="${img}" alt="${item.product_name}" onerror="this.src='https://via.placeholder.com/80'">
                        <div class="ms-3 flex-grow-1">
                            <h6 class="mb-1">${item.product_name}</h6>
                            <small class="text-muted">${item.category_name || 'N/A'}</small>
                            <div class="text-danger fw-bold mt-1">${parseInt(item.price).toLocaleString('vi-VN')}₫</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary btn-decrease" data-id="${item.id}" data-qty="${item.quantity}">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm qty-input" value="${item.quantity}" min="1" data-id="${item.id}">
                            <button class="btn btn-sm btn-outline-secondary btn-increase" data-id="${item.id}" data-qty="${item.quantity}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div class="ms-3 text-end" style="min-width: 120px;">
                            <div class="fw-bold text-danger">${parseInt(item.subtotal).toLocaleString('vi-VN')}₫</div>
                            <button class="btn btn-sm btn-outline-danger mt-1 btn-remove" data-id="${item.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            $('#cart-list').html(html);
        }

        function updateTotal(items) {
            let subtotal = 0;
            $.each(items, function(i, item) {
                subtotal += parseFloat(item.subtotal);
            });
            const total = subtotal + SHIPPING_FEE;
            $('#subtotal').text(subtotal.toLocaleString('vi-VN') + '₫');
            $('#shipping-fee').text(SHIPPING_FEE.toLocaleString('vi-VN') + '₫');
            $('#total-amount').text(total.toLocaleString('vi-VN') + '₫');
        }

        // Cập nhật số lượng
        $(document).on('change', '.qty-input', function() {
            const id = $(this).data('id');
            const qty = parseInt($(this).val());
            if (qty < 1) {
                alert('Số lượng phải lớn hơn 0');
                loadCart();
                return;
            }
            updateQuantity(id, qty);
        });

        $(document).on('click', '.btn-increase', function() {
            const id = $(this).data('id');
            const qty = parseInt($(this).data('qty')) + 1;
            updateQuantity(id, qty);
        });

        $(document).on('click', '.btn-decrease', function() {
            const id = $(this).data('id');
            const qty = parseInt($(this).data('qty')) - 1;
            if (qty < 1) {
                if (confirm('Xóa sản phẩm này khỏi giỏ?')) {
                    removeItem(id);
                }
                return;
            }
            updateQuantity(id, qty);
        });

        function updateQuantity(id, qty) {
            $.ajax({
                url: `${API_CART}/${id}`,
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({ quantity: qty }),
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') loadCart();
                    else alert('❌ ' + res.message);
                },
                error: function(xhr) {
                    alert('❌ ' + (xhr.responseJSON?.message || 'Lỗi'));
                }
            });
        }

        // Xóa sản phẩm
        $(document).on('click', '.btn-remove', function() {
            if (confirm('Xóa sản phẩm này?')) {
                removeItem($(this).data('id'));
            }
        });

        function removeItem(id) {
            $.ajax({
                url: `${API_CART}/${id}`,
                method: 'DELETE',
                dataType: 'json',
                success: function(res) {
                    loadCart();
                },
                error: function(xhr) {
                    alert('❌ ' + (xhr.responseJSON?.message || 'Lỗi'));
                }
            });
        }

        // Xóa tất cả
        $('#btn-clear-cart').click(function() {
            if (confirm('Xóa TOÀN BỘ giỏ hàng?')) {
                $.ajax({
                    url: `${API_CART}?action=clear`,
                    method: 'POST',
                    dataType: 'json',
                    success: function(res) {
                        loadCart();
                    }
                });
            }
        });

        // Đặt hàng
        $('#btn-checkout').click(function() {
            if (confirm('Xác nhận đặt hàng?')) {
                $.ajax({
                    url: '/api/orders?action=checkout',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        payment_method: 'cod',
                        notes: 'Đặt từ giỏ hàng'
                    }),
                    dataType: 'json',
                    success: function(res) {
                        alert('✅ ' + res.message + '\nMã đơn: ' + res.order_code + '\nTổng: ' + parseInt(res.total_amount).toLocaleString('vi-VN') + '₫');
                        window.location.href = 'orders.php';
                    },
                    error: function(xhr) {
                        alert('❌ ' + (xhr.responseJSON?.message || 'Lỗi'));
                    }
                });
            }
        });
    </script>
</body>
</html>