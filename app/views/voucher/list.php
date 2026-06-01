<?php include 'app/views/shares/header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-gift"></i> Quản lý Voucher</h2>
        <a href="/Product/addVoucher" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo Voucher Mới
        </a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã</th>
                            <th>Mô tả</th>
                            <th>Giảm giá</th>
                            <th>Đơn tối thiểu</th>
                            <th>Đã dùng / Giới hạn</th>
                            <th>Thời hạn</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($vouchers)): ?>
                            <?php foreach ($vouchers as $v): ?>
                            <tr>
                                <td>
                                    <code class="font-weight-bold text-primary"><?php echo htmlspecialchars($v->code); ?></code>
                                    <button class="btn btn-sm btn-link p-0 ml-1" 
                                            onclick="navigator.clipboard.writeText('<?php echo $v->code; ?>'); alert('Đã copy!')">
                                        <i class="fas fa-copy text-muted"></i>
                                    </button>
                                </td>
                                <td><?php echo htmlspecialchars($v->description ?? '-'); ?></td>
                                <td>
                                    <?php if ($v->discount_type == 'percent'): ?>
                                        <span class="badge badge-warning">
                                            -<?php echo $v->discount_value; ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">
                                            -<?php echo number_format($v->discount_value, 0, ',', '.'); ?>đ
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($v->max_discount): ?>
                                        <br><small class="text-muted">Tối đa: <?php echo number_format($v->max_discount, 0, ',', '.'); ?>đ</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($v->min_order_value ?? 0, 0, ',', '.'); ?>đ</td>
                                <td>
                                    <?php echo $v->used_count ?? 0; ?>
                                    <?php if ($v->usage_limit): ?>
                                        / <?php echo $v->usage_limit; ?>
                                        <div class="progress mt-1" style="height: 4px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo min(100, ($v->used_count / $v->usage_limit) * 100); ?>%">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($v->end_date): ?>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y', strtotime($v->end_date)); ?>
                                        </small>
                                        <?php if (strtotime($v->end_date) < time()): ?>
                                            <br><span class="badge badge-danger">Hết hạn</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Vô hạn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($v->is_active): ?>
                                        <span class="badge badge-success">✅ Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">⏸ Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/Product/editVoucher/<?php echo $v->id; ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/Product/deleteVoucher/<?php echo $v->id; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Xóa voucher này?')" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                    Chưa có voucher nào. <a href="/Product/addVoucher">Tạo mới ngay</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>