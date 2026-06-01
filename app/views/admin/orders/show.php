<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn #<?= $order['id'] ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
   <?php include 'app/views/shares/header.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
           
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-shopping-bag me-2"></i>Chi tiết đơn hàng #<?= $order['id'] ?></h2>
                    <a href="/admin/orders" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>

                <div class="row">
                    <!-- Thông tin khách hàng -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin khách hàng</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Họ tên:</strong> <?= htmlspecialchars($order['name']) ?></p>
                                <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($order['email'] ?? 'Chưa cung cấp') ?></p>
                                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['city']) ?></p>
                                <?php if ($order['note']): ?>
                                <p><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin đơn hàng -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Thông tin đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Mã đơn:</strong> #<?= $order['id'] ?></p>
                                <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></p>
                                <p><strong>Phương thức thanh toán:</strong> 
                                    <span class="badge bg-info"><?= strtoupper($order['payment_method']) ?></span>
                                </p>
                                <p><strong>Trạng thái:</strong> 
                                    <span class="badge bg-<?= $order['status'] == 'completed' ? 'success' : ($order['status'] == 'cancelled' ? 'danger' : 'warning') ?>">
                                        <?= strtoupper($order['status']) ?>
                                    </span>
                                </p>
                                <?php if ($order['voucher_code']): ?>
                                <p><strong>Mã giảm giá:</strong> <?= htmlspecialchars($order['voucher_code']) ?></p>
                                <p><strong>Giảm giá:</strong> <?= number_format($order['discount_amount']) ?> VNĐ</p>
                                <?php endif; ?>
                                   <!-- === SỬA PHẦN HIỂN THỊ GIÁ === -->
                                <?php 
                                $discountAmount = $order['discount_amount'] ?? 0;
                                $finalTotal = $order['total_amount'] - $discountAmount;
                                ?>
                                
                                <?php if ($discountAmount > 0): ?>
                                    <!-- Có giảm giá: hiển thị gạch ngang -->
                                    <p><strong>Tổng tiền:</strong> 
                                        <span class="text-muted text-decoration-line-through">
                                            <?= number_format($order['total_amount']) ?> VNĐ
                                        </span>
                                    </p>
                                    <p><strong>Giảm giá:</strong> 
                                        <span class="text-success">
                                            -<?= number_format($discountAmount) ?> VNĐ
                                        </span>
                                    </p>
                                <?php else: ?>
                                    <!-- Không có giảm giá: chỉ hiển thị tổng tiền bình thường -->
                                    <p><strong>Tổng tiền:</strong> 
                                        <span class="text-dark">
                                            <?= number_format($order['total_amount']) ?> VNĐ
                                        </span>
                                    </p>
                                <?php endif; ?>
                                
                                <hr>
                                <h4 class="text-success">
                                    <strong>Số tiền thực nhận: <?= number_format($finalTotal) ?> VNĐ</strong>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cập nhật trạng thái -->
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Cập nhật trạng thái</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/admin/orders/<?= $order['id'] ?>/update-status" class="row g-3">
                            <div class="col-md-4">
                                <select name="status" class="form-select" required>
                                    <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                    <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                    <option value="shipping" <?= $order['status'] == 'shipping' ? 'selected' : '' ?>>Đang giao hàng</option>
                                    <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
                                    <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Lưu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Chi tiết sản phẩm (nếu có bảng order_details) -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Chi tiết sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Chi tiết sản phẩm sẽ hiển thị ở đây (cần kết nối với bảng order_details)</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>