<?php
session_start();
$accessTime = date('H:i:s d/m/Y');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .stat-card { border-left: 4px solid; transition: transform 0.2s; cursor: pointer; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card.pending { border-left-color: #ffc107; }
        .stat-card.confirmed { border-left-color: #17a2b8; }
        .stat-card.processing { border-left-color: #007bff; }
        .stat-card.shipping { border-left-color: #6f42c1; }
        .stat-card.completed { border-left-color: #28a745; }
        .stat-card.cancelled { border-left-color: #dc3545; }
        .badge-status { font-size: 0.8rem; padding: 4px 8px; }
        .order-row { cursor: pointer; transition: background 0.2s; }
        .order-row:hover { background-color: #f1f3f5; }
        .detail-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .detail-item:last-child { border-bottom: none; }
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
                <i class="fas fa-shopping-cart"></i> Quản lý đơn hàng (JWT API)
            </span>
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
                <strong>Quản lý đơn hàng</strong> - jQuery AJAX + JWT Token
            </div>
            <small class="text-muted">
                <i class="fas fa-clock"></i> <?= $accessTime ?>
            </small>
        </div>

        <!-- Thống kê -->
        <div id="statistics" class="row g-3 mb-4"></div>

        <!-- Bộ lọc -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" id="search-keyword" class="form-control" placeholder="🔍 Tìm mã đơn, khách hàng...">
                    </div>
                    <div class="col-md-2">
                        <select id="filter-status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending">Chờ xử lý</option>
                            <option value="confirmed">Đã xác nhận</option>
                            <option value="processing">Đang xử lý</option>
                            <option value="shipping">Đang giao</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filter-payment" class="form-select">
                            <option value="">Thanh toán</option>
                            <option value="pending">Chưa TT</option>
                            <option value="paid">Đã TT</option>
                            <option value="failed">Thất bại</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button id="btn-filter" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </div>
                    <div class="col-md-3 text-end">
                        <button id="btn-add" class="btn btn-success">
                            <i class="fas fa-plus"></i> Tạo đơn hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-5">
            <div class="spinner-border text-primary"></div>
        </div>

        <!-- Bảng đơn hàng -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>SĐT</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="order-list"></tbody>
                    </table>
                </div>
            </div>
            <div id="pagination" class="card-footer"></div>
        </div>
    </div>

    <!-- Modal chi tiết -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-receipt"></i> Chi tiết đơn hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detail-content">
                    <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal cập nhật -->
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Cập nhật đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="update-form">
                        <input type="hidden" id="update_order_id">
                        <div class="mb-3">
                            <label class="form-label">Trạng thái đơn hàng</label>
                            <select class="form-select" id="update_status" required>
                                <option value="pending">Chờ xử lý</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="processing">Đang xử lý</option>
                                <option value="shipping">Đang giao</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trạng thái thanh toán</label>
                            <select class="form-select" id="update_payment_status" required>
                                <option value="pending">Chưa thanh toán</option>
                                <option value="paid">Đã thanh toán</option>
                                <option value="failed">Thất bại</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="update_notes" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal tạo đơn -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tạo đơn hàng mới</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="create-form">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="fas fa-user"></i> Thông tin khách hàng</h6>
                                <div class="mb-3">
                                    <label class="form-label">Tên khách hàng *</label>
                                    <input type="text" class="form-control" id="create_customer_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" id="create_customer_email">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SĐT *</label>
                                    <input type="text" class="form-control" id="create_customer_phone" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ *</label>
                                    <textarea class="form-control" id="create_customer_address" rows="2" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phương thức thanh toán</label>
                                    <select class="form-select" id="create_payment_method">
                                        <option value="cod">COD</option>
                                        <option value="transfer">Chuyển khoản</option>
                                        <option value="momo">Ví MoMo</option>
                                        <option value="zalopay">ZaloPay</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="fas fa-box"></i> Sản phẩm</h6>
                                <div id="items-list">
                                    <div class="item-row mb-2 p-2 border rounded">
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control form-control-sm item-name" placeholder="Tên sản phẩm" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="number" class="form-control form-control-sm item-qty" placeholder="SL" value="1" min="1" required>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" class="form-control form-control-sm item-price" placeholder="Giá" required>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-sm btn-danger remove-item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="btn-add-item" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="fas fa-plus"></i> Thêm sản phẩm
                                </button>
                                
                                <hr>
                                <div class="mb-3">
                                    <label class="form-label">Giảm giá</label>
                                    <input type="number" class="form-control" id="create_discount" value="0" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phí vận chuyển</label>
                                    <input type="number" class="form-control" id="create_shipping" value="30000" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea class="form-control" id="create_notes" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mt-3">
                            <i class="fas fa-save"></i> Tạo đơn hàng
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE = '/api/orders';
        let detailModal, updateModal, createModal;
        let currentPage = 1;
        let currentFilters = { keyword: '', status: '', payment_status: '' };
        let currentUser = null;

        const statusLabels = {
            pending: 'Chờ xử lý', confirmed: 'Đã xác nhận', processing: 'Đang xử lý',
            shipping: 'Đang giao', completed: 'Hoàn thành', cancelled: 'Đã hủy'
        };
        const statusColors = {
            pending: 'warning', confirmed: 'info', processing: 'primary',
            shipping: 'secondary', completed: 'success', cancelled: 'danger'
        };
        const paymentLabels = {
            pending: 'Chưa TT', paid: 'Đã TT', failed: 'Thất bại'
        };
        const paymentColors = {
            pending: 'warning', paid: 'success', failed: 'danger'
        };

        // ✅ ✅ ✅ QUAN TRỌNG NHẤT: GỬI TOKEN TRONG MỌI AJAX REQUEST ✅ ✅ ✅
        $(document).ajaxSend(function(event, xhr, settings) {
            const token = localStorage.getItem('token');
            if (token) {
                xhr.setRequestHeader('Authorization', 'Bearer ' + token);
            }
        });

        // ✅ XỬ LÝ LỖI 401, 403
        let errorShown = false;
        $(document).ajaxError(function(event, xhr, settings) {
            if (xhr.status === 401 && !errorShown) {
                errorShown = true;
                alert('⚠️ Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại!');
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.replace('login.php');
                setTimeout(() => { errorShown = false; }, 2000);
            } else if (xhr.status === 403 && !errorShown) {
                errorShown = true;
                const msg = xhr.responseJSON?.message || 'Bạn không có quyền';
                alert('🚫 ' + msg);
                setTimeout(() => { errorShown = false; }, 2000);
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
            // ✅ KIỂM TRA ĐĂNG NHẬP BẰNG JWT (KHÔNG DÙNG PHP SESSION)
            const token = localStorage.getItem('token');
            try {
                currentUser = JSON.parse(localStorage.getItem('user') || '{}');
            } catch(e) {
                currentUser = {};
            }
            
            if (!token || !currentUser.username) {
                alert('⚠️ Vui lòng đăng nhập để xem đơn hàng!');
                window.location.replace('login.php');
                return;
            }
            
            console.log('✅ Logged in as:', currentUser.username, 'Role:', currentUser.role);
            
            // Cập nhật navbar
            $('#current-username').text(currentUser.username);
            $('#current-role').text(currentUser.role === 'admin' ? 'Admin' : 'User')
                             .removeClass('bg-secondary bg-danger')
                             .addClass(currentUser.role === 'admin' ? 'bg-danger' : 'bg-secondary');
            
            detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            updateModal = new bootstrap.Modal(document.getElementById('updateModal'));
            createModal = new bootstrap.Modal(document.getElementById('createModal'));
            
            loadStatistics();
            loadOrders();

            $('#btn-filter').click(function() {
                currentFilters = {
                    keyword: $('#search-keyword').val(),
                    status: $('#filter-status').val(),
                    payment_status: $('#filter-payment').val()
                };
                currentPage = 1;
                loadOrders();
            });

            $('#search-keyword').keypress(function(e) {
                if (e.which === 13) $('#btn-filter').click();
            });

            $(document).on('click', '.stat-card', function() {
                const status = $(this).data('status');
                $('#filter-status').val(status);
                currentFilters.status = status;
                currentPage = 1;
                loadOrders();
            });

            $('#btn-add').click(function() {
                $('#create-form')[0].reset();
                $('#items-list').html(`
                    <div class="item-row mb-2 p-2 border rounded">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control form-control-sm item-name" placeholder="Tên sản phẩm" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control form-control-sm item-qty" placeholder="SL" value="1" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control form-control-sm item-price" placeholder="Giá" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-danger remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `);
                createModal.show();
            });

            $('#btn-add-item').click(function() {
                $('#items-list').append(`
                    <div class="item-row mb-2 p-2 border rounded">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control form-control-sm item-name" placeholder="Tên sản phẩm" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control form-control-sm item-qty" placeholder="SL" value="1" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control form-control-sm item-price" placeholder="Giá" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-sm btn-danger remove-item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `);
            });

            $(document).on('click', '.remove-item', function() {
                if ($('.item-row').length > 1) {
                    $(this).closest('.item-row').remove();
                } else {
                    alert('Phải có ít nhất 1 sản phẩm!');
                }
            });

            $('#create-form').submit(function(e) {
                e.preventDefault();
                
                const items = [];
                $('.item-row').each(function() {
                    items.push({
                        product_name: $(this).find('.item-name').val(),
                        quantity: parseInt($(this).find('.item-qty').val()),
                        price: parseFloat($(this).find('.item-price').val())
                    });
                });

                const data = {
                    customer_id: currentUser.user_id || currentUser.id || 0,
                    customer_name: $('#create_customer_name').val(),
                    customer_email: $('#create_customer_email').val(),
                    customer_phone: $('#create_customer_phone').val(),
                    customer_address: $('#create_customer_address').val(),
                    payment_method: $('#create_payment_method').val(),
                    discount_amount: parseFloat($('#create_discount').val()) || 0,
                    shipping_fee: parseFloat($('#create_shipping').val()) || 0,
                    notes: $('#create_notes').val(),
                    items: items
                };

                $.ajax({
                    url: API_BASE,
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    success: function(res) {
                        alert('✅ ' + res.message + '\nMã đơn: ' + (res.order_code || ''));
                        createModal.hide();
                        loadOrders();
                        loadStatistics();
                    },
                    error: function(xhr) {
                        alert('❌ Lỗi: ' + (xhr.responseJSON?.message || 'Không xác định'));
                    }
                });
            });

            $('#update-form').submit(function(e) {
                e.preventDefault();
                const id = $('#update_order_id').val();
                const data = {
                    status: $('#update_status').val(),
                    payment_status: $('#update_payment_status').val(),
                    notes: $('#update_notes').val()
                };

                $.ajax({
                    url: `${API_BASE}/${id}`,
                    method: 'PUT',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    success: function(res) {
                        alert('✅ ' + res.message);
                        updateModal.hide();
                        loadOrders();
                        loadStatistics();
                    },
                    error: function(xhr) {
                        alert('❌ Lỗi: ' + (xhr.responseJSON?.message || 'Không xác định'));
                    }
                });
            });
        });

        // ✅ Load thống kê - Dùng $.ajax thay vì $.getJSON để gửi token
        function loadStatistics() {
            $.ajax({
                url: `${API_BASE}?action=statistics`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        let stats = {
                            pending: 0, confirmed: 0, processing: 0,
                            shipping: 0, completed: 0, cancelled: 0
                        };
                        $.each(res.data, function(i, stat) {
                            stats[stat.status] = parseInt(stat.count);
                        });

                        let html = '';
                        $.each(stats, function(status, count) {
                            html += `
                                <div class="col-md-2">
                                    <div class="card stat-card ${status}" data-status="${status}">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted text-uppercase small mb-1">${statusLabels[status]}</h6>
                                            <h3 class="mb-0 text-${statusColors[status]}">${count}</h3>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        $('#statistics').html(html);
                    }
                },
                error: function(xhr) {
                    console.error('❌ Statistics error:', xhr.status, xhr.responseText);
                    $('#statistics').html('<div class="col-12"><div class="alert alert-danger">❌ Không thể tải thống kê</div></div>');
                }
            });
        }

        // ✅ Load danh sách đơn hàng - Dùng $.ajax thay vì $.getJSON
        function loadOrders() {
            $('#loading').show();
            let url = `${API_BASE}?page=${currentPage}`;
            if (currentFilters.keyword) url += `&search=${encodeURIComponent(currentFilters.keyword)}`;
            if (currentFilters.status) url += `&status=${currentFilters.status}`;
            if (currentFilters.payment_status) url += `&payment_status=${currentFilters.payment_status}`;

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    $('#loading').hide();
                    if (res.status === 'success') {
                        let html = '';
                        if (res.data.length === 0) {
                            html = '<tr><td colspan="8" class="text-center text-muted py-4">Không có đơn hàng</td></tr>';
                        } else {
                            // Tìm đoạn này và sửa thành:
                                $.each(res.data, function(i, o) {
                                    // ✅ SỬA THEO DATABASE THỰC TẾ
                                    html += `
                                        <tr class="order-row" data-id="${o.id}">
                                            <td><strong class="text-primary">${o.order_code || 'ORD-' + o.id}</strong></td>
                                            <td>${o.customer_name || o.name || '-'}</td>
                                            <td>${o.customer_phone || o.phone || '-'}</td>
                                            <td class="text-danger fw-bold">${parseInt(o.total_amount).toLocaleString('vi-VN')}₫</td>
                                            <td><span class="badge bg-${paymentColors[o.payment_status || o.payment_method] || 'secondary'} badge-status">${paymentLabels[o.payment_status] || (o.payment_method || 'cod').toUpperCase()}</span></td>
                                            <td><span class="badge bg-${statusColors[o.status] || 'secondary'} badge-status">${statusLabels[o.status] || o.status}</span></td>
                                            <td>${new Date(o.created_at).toLocaleDateString('vi-VN')}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info btn-detail" data-id="${o.id}" title="Chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning btn-update" data-id="${o.id}" title="Cập nhật">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-delete" data-id="${o.id}" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                        }
                        $('#order-list').html(html);
                        renderPagination(res.page, res.total_pages);
                    }
                },
                error: function(xhr) {
                    $('#loading').hide();
                    console.error('❌ Orders error:', xhr.status, xhr.responseText);
                    $('#order-list').html(`<tr><td colspan="8" class="text-center text-danger py-4">❌ Lỗi tải dữ liệu: ${xhr.status}</td></tr>`);
                }
            });
        }

        // Xem chi tiết đơn hàng
        $(document).on('click', '.btn-detail, .order-row', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            
            $('#detail-content').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
            detailModal.show();

            $.ajax({
                url: `${API_BASE}/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        const o = res.data;
                        let itemsHtml = '';
                        let totalQty = 0;
                        if (o.details && o.details.length > 0) {
                            $.each(o.details, function(i, item) {
                                totalQty += parseInt(item.quantity);
                                itemsHtml += `
                                    <div class="detail-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>${item.product_name}</strong>
                                            <br>
                                            <small class="text-muted">SL: ${item.quantity} × ${parseInt(item.price).toLocaleString('vi-VN')}₫</small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-danger">${parseInt(item.subtotal).toLocaleString('vi-VN')}₫</strong>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            itemsHtml = '<p class="text-muted text-center">Không có chi tiết</p>';
                        }

                        const html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary"><i class="fas fa-receipt"></i> Thông tin đơn hàng</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Mã đơn:</strong></td><td>${o.order_code || 'ORD-' + o.id}</td></tr>
                                        <tr><td><strong>Ngày tạo:</strong></td><td>${new Date(o.created_at).toLocaleString('vi-VN')}</td></tr>
                                        <tr><td><strong>Thanh toán:</strong></td><td><span class="badge bg-${paymentColors[o.payment_status]}">${paymentLabels[o.payment_status]}</span></td></tr>
                                        <tr><td><strong>Trạng thái:</strong></td><td><span class="badge bg-${statusColors[o.status]}">${statusLabels[o.status]}</span></td></tr>
                                        <tr><td><strong>PTTT:</strong></td><td>${(o.payment_method || 'cod').toUpperCase()}</td></tr>
                                    </table>
                                    ${o.notes || o.note ? `<div class="alert alert-info"><i class="fas fa-sticky-note"></i> <strong>Ghi chú:</strong> ${o.notes || o.note}</div>` : ''}
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary"><i class="fas fa-user"></i> Khách hàng</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Tên:</strong></td><td>${o.customer_name || o.name || '-'}</td></tr>
                                        <tr><td><strong>Email:</strong></td><td>${o.customer_email || o.email || '-'}</td></tr>
                                        <tr><td><strong>SĐT:</strong></td><td>${o.customer_phone || o.phone || '-'}</td></tr>
                                        <tr><td><strong>Địa chỉ:</strong></td><td>${o.customer_address || o.address || '-'}</td></tr>
                                    </table>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-primary"><i class="fas fa-box"></i> Sản phẩm (${totalQty} sản phẩm)</h6>
                            ${itemsHtml}
                            <hr>
                            <div class="row">
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr><td>Tạm tính:</td><td class="text-end">${parseInt(o.total_amount - (o.discount_amount || 0) - (o.shipping_fee || 0)).toLocaleString('vi-VN')}₫</td></tr>
                                        <tr><td>Giảm giá:</td><td class="text-end text-success">-${parseInt(o.discount_amount || 0).toLocaleString('vi-VN')}₫</td></tr>
                                        <tr><td>Phí vận chuyển:</td><td class="text-end">${parseInt(o.shipping_fee || 0).toLocaleString('vi-VN')}₫</td></tr>
                                        <tr class="table-primary">
                                            <td><strong>TỔNG CỘNG:</strong></td>
                                            <td class="text-end"><strong class="text-danger fs-5">${parseInt(o.total_amount).toLocaleString('vi-VN')}₫</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        `;
                        $('#detail-content').html(html);
                    }
                },
                error: function(xhr) {
                    $('#detail-content').html(`<div class="text-danger text-center">❌ Lỗi: ${xhr.status}</div>`);
                }
            });
        });

        // Cập nhật đơn hàng
        $(document).on('click', '.btn-update', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            $('#update_order_id').val(id);
            
            $.ajax({
                url: `${API_BASE}/${id}`,
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        const o = res.data;
                        $('#update_status').val(o.status);
                        $('#update_payment_status').val(o.payment_status || 'pending');
                        $('#update_notes').val(o.notes || '');
                        updateModal.show();
                    }
                }
            });
        });

        // Xóa đơn hàng
        $(document).on('click', '.btn-delete', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            if (confirm('⚠️ Xóa đơn hàng này?\nHành động không thể hoàn tác!')) {
                $.ajax({
                    url: `${API_BASE}/${id}`,
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(res) {
                        alert('✅ ' + res.message);
                        loadOrders();
                        loadStatistics();
                    },
                    error: function(xhr) {
                        alert('❌ Lỗi: ' + (xhr.responseJSON?.message || 'Không xác định'));
                    }
                });
            }
        });

        // Pagination
        function renderPagination(current, total) {
            if (total <= 1) { $('#pagination').html(''); return; }
            let html = '<nav><ul class="pagination justify-content-center mb-0">';
            html += `<li class="page-item ${current <= 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current-1}">« Trước</a></li>`;
            for (let i = 1; i <= Math.min(total, 5); i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            if (total > 5) html += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
            html += `<li class="page-item ${current >= total ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${current+1}">Sau »</a></li></ul></nav>`;
            $('#pagination').html(html);
            
            $('#pagination .page-link').click(function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page >= 1 && page <= total) {
                    currentPage = page;
                    loadOrders();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }
    </script>
</body>
</html>