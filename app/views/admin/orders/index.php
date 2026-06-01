<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Card styling */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            border-radius: 12px 12px 0 0 !important;
            padding: 1rem 1.25rem;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        /* Header section */
        .page-title {
            color: #2c3e50;
            font-weight: 700;
        }
        
        /* Alert styling */
        .alert {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* Statistics cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        
        .stat-card.pending { border-left-color: #ffc107; background: #fffbeb; }
        .stat-card.processing { border-left-color: #17a2b8; background: #e8f6f8; }
        .stat-card.confirmed { border-left-color: #6f42c1; background: #f3edfa; }
        .stat-card.shipping { border-left-color: #007bff; background: #e7f1ff; }
        .stat-card.completed { border-left-color: #28a745; background: #e8f5e9; }
        .stat-card.cancelled { border-left-color: #dc3545; background: #fcebee; }
        
        .stat-card h6 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .stat-card h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }
        
        .stat-card small {
            color: #28a745;
            font-weight: 600;
        }
        
        /* Filter form */
        .filter-card .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.6rem 1rem;
        }
        
        .filter-card .btn-primary {
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
        }
        
        /* Table styling - KHÔNG KÉO NGANG */
        .table-card .table {
            margin-bottom: 0;
            font-size: 0.85rem;
        }
        
        .table-card thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.3px;
            color: #495057;
            padding: 0.85rem 0.5rem;
            white-space: nowrap;
        }
        
        .table-card tbody td {
            padding: 0.85rem 0.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .table-card tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Cột ID */
        .table-card td:nth-child(1) {
            width: 70px;
            font-weight: 600;
            color: #667eea;
        }
        
        /* Cột Khách hàng - gộp Name + Phone */
        .table-card td:nth-child(2) {
            width: 140px;
        }
        
        .customer-name {
            font-weight: 600;
            color: #2c3e50;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
        }
        
        .customer-phone {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        /* Cột Email */
        .table-card td:nth-child(4) {
            width: 130px;
        }
        
        .email-cell {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 120px;
            display: inline-block;
            color: #495057;
        }
        
        /* Cột Địa chỉ */
        .table-card td:nth-child(5) {
            width: 140px;
        }
        
        .address-cell {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
            display: inline-block;
            color: #6c757d;
        }
        
        /* Cột Tổng tiền */
        .table-card td:nth-child(6) {
            width: 110px;
            font-weight: 600;
            color: #dc3545;
            white-space: nowrap;
        }
        
        /* Cột Trạng thái */
        .table-card td:nth-child(7) {
            width: 110px;
        }
        
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
            white-space: nowrap;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-confirmed { background: #e2d5f1; color: #4a2c7a; }
        .status-shipping { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        /* Cột Ngày tạo */
        .table-card td:nth-child(8) {
            width: 120px;
            font-size: 0.8rem;
            color: #6c757d;
            white-space: nowrap;
        }
        
        .date-time {
            display: block;
        }
        
        .date-time small {
            font-size: 0.7rem;
            color: #adb5bd;
        }
        
        /* Cột Thao tác */
        .table-card td:nth-child(9) {
            width: 130px;
        }
        
        .action-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin: 0 2px;
            font-size: 0.75rem;
            transition: transform 0.15s;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
        
        .action-btn i {
            font-size: 0.75rem;
        }
        
        /* Modal styling */
        .modal-content {
            border: none;
            border-radius: 12px;
        }
        
        .modal-header {
            border-radius: 12px 12px 0 0;
            padding: 1rem 1.5rem;
        }
        
        .modal-body {
            padding: 1.25rem 1.5rem;
        }
        
        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .modal .form-select {
            border-radius: 8px;
            padding: 0.6rem 1rem;
        }
        
        /* Pagination */
        .pagination .page-link {
            border-radius: 6px !important;
            margin: 0 2px;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            color: #495057;
            border: 1px solid #dee2e6;
        }
        
        .pagination .page-item.active .page-link {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }
        
        .pagination .page-link:hover {
            background: #f8f9fa;
            color: #2c3e50;
        }
        
        /* Responsive - ẩn bớt cột trên mobile */
        @media (max-width: 1400px) {
            .table-card td:nth-child(4),  /* Email */
            .table-card th:nth-child(4) {
                display: none;
            }
        }
        
        @media (max-width: 1200px) {
            .table-card td:nth-child(5),  /* Address */
            .table-card th:nth-child(5) {
                display: none;
            }
            .action-btn {
                width: 28px;
                height: 28px;
            }
        }
        
        @media (max-width: 992px) {
            .stat-card h3 {
                font-size: 1.5rem;
            }
            .table-card {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'app/views/shares/header.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-3 mb-4 border-bottom">
                    <h1 class="h3 page-title">
                        <i class="fas fa-shopping-cart me-2"></i>Quản lý đơn hàng
                    </h1>
                </div>

                <!-- Alert messages - GIỮ NGUYÊN LOGIC -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics - GIỮ NGUYÊN LOGIC -->
                <div class="row mb-4">
                    <?php foreach ($statistics as $stat): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card <?= $stat['status'] ?>">
                            <div class="card-body">
                                <h6 class="text-muted text-uppercase"><?= strtoupper($stat['status']) ?></h6>
                                <h3><?= $stat['count'] ?></h3>
                                <small><?= number_format($stat['total_revenue'] ?? 0) ?> VNĐ</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filter - GIỮ NGUYÊN LOGIC -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Lọc theo trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="pending" <?= ($status_filter == 'pending') ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option value="processing" <?= ($status_filter == 'processing') ? 'selected' : '' ?>>Đang xử lý</option>
                                    <option value="confirmed" <?= ($status_filter == 'confirmed') ? 'selected' : '' ?>>Đã xác nhận</option>
                                    <option value="shipping" <?= ($status_filter == 'shipping') ? 'selected' : '' ?>>Đang giao hàng</option>
                                    <option value="completed" <?= ($status_filter == 'completed') ? 'selected' : '' ?>>Hoàn thành</option>
                                    <option value="cancelled" <?= ($status_filter == 'cancelled') ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-1"></i> Lọc
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders Table - GIỮ NGUYÊN LOGIC PHP -->
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-list me-2"></i>Danh sách đơn hàng
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Khách hàng</th>
                                        <th>SĐT</th>
                                        <th>Email</th>
                                        <th>Địa chỉ</th>
                                        <th>Tổng tiền</th>
                            
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- GIỮ NGUYÊN: while loop và logic hiển thị -->
                                    <?php if ($orders->rowCount() > 0): ?>
                                        <?php while ($order = $orders->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><strong>#<?= $order['id'] ?></strong></td>
                                            
                                            <!-- Gộp Name + Phone vào 1 cột cho gọn -->
                                            <td>
                                                <span class="customer-name"><?= htmlspecialchars($order['name']) ?></span>
                                            </td>
                                            
                                            <!-- Cột SĐT -->
                                            <td>
                                                <span class="customer-phone"><?= htmlspecialchars($order['phone']) ?></span>
                                            </td>
                                            
                                            <!-- Email với ellipsis -->
                                            <td>
                                                <span class="email-cell" title="<?= htmlspecialchars($order['email'] ?? 'N/A') ?>">
                                                    <?= htmlspecialchars($order['email'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            
                                            <!-- Address với ellipsis -->
                                            <td>
                                                <span class="address-cell" title="<?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['city']) ?>">
                                                    <?= htmlspecialchars($order['city']) ?>
                                                </span>
                                            </td>
                                            
                                            <td>
                                                <?php 
                                                $discountAmount = $order['discount_amount'] ?? 0;
                                                $finalTotal = $order['total_amount'] - $discountAmount;
                                                ?>
                                                
                                                <?php if ($discountAmount > 0): ?>
                                                    <!-- Có giảm giá: hiển thị gạch ngang + giá sau giảm -->
                                                    <div class="text-muted text-decoration-line-through small">
                                                        <?= number_format($order['total_amount']) ?> đ
                                                    </div>
                                                    <div class="text-danger font-weight-bold">
                                                        <?= number_format($finalTotal) ?> đ
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Không có giảm giá: chỉ hiển thị giá gốc -->
                                                    <strong class="text-danger"><?= number_format($order['total_amount']) ?> đ</strong>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <!-- Status badge -->
                                            <td>
                                                <span class="status-badge status-<?= $order['status'] ?>">
                                                    <?php
                                                    $status_labels = [
                                                        'pending' => 'Chờ xử lý',
                                                        'processing' => 'Đang xử lý',
                                                        'confirmed' => 'Đã xác nhận',
                                                        'shipping' => 'Đang giao',
                                                        'completed' => 'Hoàn thành',
                                                        'cancelled' => 'Đã hủy'
                                                    ];
                                                    echo $status_labels[$order['status']] ?? $order['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            
                                            <!-- Date với format gọn -->
                                            <td>
                                                <span class="date-time">
                                                    <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                                                    <small><?= date('H:i', strtotime($order['created_at'])) ?></small>
                                                </span>
                                            </td>
                                            
                                            <!-- Action buttons -->
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/AdminOrder/show/<?= $order['id'] ?>" class="btn btn-sm btn-info action-btn" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-primary action-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#updateStatusModal<?= $order['id'] ?>" 
                                                            title="Cập nhật trạng thái">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger action-btn" 
                                                            onclick="confirmDelete(<?= $order['id'] ?>)" 
                                                            title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>

                                                <!-- Modal - GIỮ NGUYÊN LOGIC -->
                                                <div class="modal fade" id="updateStatusModal<?= $order['id'] ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                     <form method="POST" action="/AdminOrder/updateStatus">
                                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-primary text-white">
                                                                    <h5 class="modal-title">Cập nhật trạng thái đơn #<?= $order['id'] ?></h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                                                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                                                        <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                                                        <option value="shipping" <?= $order['status'] == 'shipping' ? 'selected' : '' ?>>Đang giao hàng</option>
                                                                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                                                    </select>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                                    <button type="submit" class="btn btn-primary">Lưu</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-5">
                                                <i class="fas fa-inbox fa-3x mb-3 text-secondary"></i>
                                                <p class="mb-0">Không có đơn hàng nào</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination - GIỮ NGUYÊN LOGIC -->
                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer bg-white border-top-0 pb-3">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $status_filter ? '&status='.$status_filter : '' ?>">Trước</a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $status_filter ? '&status='.$status_filter : '' ?>"><?= $i ?></a>
                                        </li>
                                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $status_filter ? '&status='.$status_filter : '' ?>">Sau</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // GIỮ NGUYÊN JS confirmDelete
        function confirmDelete(id) {
            if (confirm('Bạn có chắc chắn muốn xóa đơn hàng #' + id + '?\nHành động này không thể hoàn tác!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/AdminOrder/delete';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_id';
                input.value = id;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>