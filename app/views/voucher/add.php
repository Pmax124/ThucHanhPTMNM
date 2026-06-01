<?php include 'app/views/shares/header.php'; ?>
<!-- Hiển thị thông báo lỗi -->
<?php if (isset($error) && !empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>
    <?= $error ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Hiển thị thông báo thành công từ session -->
<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <?= $_SESSION['success'] ?>
    <?php unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="container mt-4 mb-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-gift"></i> Tạo Mã Giảm Giá Mới</h3>
            <a href="/Voucher/list" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
        
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form action="/Voucher/save" method="POST" id="voucherForm">
                
                <!-- Mã voucher -->
                <div class="form-group">
                    <label for="code">Mã Voucher <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                        </div>
                        <input type="text" 
                               id="code" 
                               name="code" 
                               class="form-control text-uppercase" 
                               placeholder="VD: SALE10, FREESHIP, NEWUSER" 
                               maxlength="50" 
                               required
                               pattern="[A-Za-z0-9_]+"
                               title="Chỉ dùng chữ, số và dấu gạch dưới">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                <i class="fas fa-magic"></i> Tạo ngẫu nhiên
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Mã duy nhất, không dấu, viết hoa. Ví dụ: <code>SALE20</code></small>
                </div>

                <!-- Mô tả -->
                <div class="form-group">
                    <label for="description">Mô tả / Ghi chú</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-control" 
                              rows="2" 
                              placeholder="Ví dụ: Giảm 10% cho đơn hàng đầu tiên"></textarea>
                </div>

                <!-- Loại giảm giá -->
                <div class="form-group">
                    <label>Loại Giảm Giá <span class="text-danger">*</span></label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label class="btn btn-outline-primary active">
                            <input type="radio" name="discount_type" value="percent" checked autocomplete="off">
                            <i class="fas fa-percentage"></i> Theo phần trăm (%)
                        </label>
                        <label class="btn btn-outline-primary">
                            <input type="radio" name="discount_type" value="fixed" autocomplete="off">
                            <i class="fas fa-dollar-sign"></i> Theo số tiền (VNĐ)
                        </label>
                    </div>
                </div>

                <!-- Giá trị giảm giá -->
                <div class="form-group">
                    <label for="discount_value" id="discountLabel">Giá trị giảm (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" 
                               id="discount_value" 
                               name="discount_value" 
                               class="form-control" 
                               min="1" 
                               max="100" 
                               value="10" 
                               required>
                        <div class="input-group-append">
                            <span class="input-group-text" id="discountUnit">%</span>
                        </div>
                    </div>
                    <small class="form-text text-muted" id="discountHelp">Nhập số từ 1-100 để giảm theo phần trăm</small>
                </div>

                <!-- Giảm tối đa (chỉ hiện với percent) -->
                <div class="form-group" id="maxDiscountGroup">
                    <label for="max_discount">Giảm tối đa (VNĐ)</label>
                    <input type="number" 
                           id="max_discount" 
                           name="max_discount" 
                           class="form-control" 
                           min="0" 
                           placeholder="Ví dụ: 50000"
                           step="1000">
                    <small class="form-text text-muted">Giới hạn số tiền giảm tối đa cho mỗi đơn (chỉ áp dụng khi giảm theo %)</small>
                </div>

                <!-- Điều kiện đơn hàng -->
                <div class="form-group">
                    <label for="min_order_value">Giá trị đơn hàng tối thiểu (VNĐ)</label>
                    <input type="number" 
                           id="min_order_value" 
                           name="min_order_value" 
                           class="form-control" 
                           min="0" 
                           value="0"
                           step="1000"
                           placeholder="0 = không giới hạn">
                    <small class="form-text text-muted">Khách hàng cần mua tối thiểu bao nhiêu để được áp dụng voucher</small>
                </div>

                <!-- Giới hạn sử dụng -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="usage_limit">Số lần sử dụng tối đa</label>
                            <input type="number" 
                                   id="usage_limit" 
                                   name="usage_per_user"
                                   class="form-control" 
                                   min="1" 
                                   placeholder="Để trống = không giới hạn">
                            <small class="form-text text-muted">Tổng số lần voucher có thể được dùng</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="per_user_limit">Số lần dùng / 1 khách</label>
                            <input type="number" 
                                   id="per_user_limit" 
                                   name="per_user_limit" 
                                   class="form-control" 
                                   min="1" 
                                   value="1"
                                   placeholder="1">
                            <small class="form-text text-muted">Mỗi tài khoản/email được dùng tối đa bao nhiêu lần</small>
                        </div>
                    </div>
                </div>

                <!-- Thời hạn -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Ngày bắt đầu</label>
                            <input type="datetime-local" 
                                   id="start_date" 
                                   name="start_date" 
                                   class="form-control">
                            <small class="form-text text-muted">Để trống = hiệu lực ngay</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                   id="end_date" 
                                   name="end_date" 
                                   class="form-control" 
                                   required>
                            <small class="form-text text-muted">Voucher sẽ hết hạn sau thời điểm này</small>
                        </div>
                    </div>
                </div>

                <!-- Trạng thái -->
                <div class="form-group">
                    <label>Trạng thái</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" 
                               class="custom-control-input" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               checked>
                        <label class="custom-control-label" for="is_active">
                            <strong>Kích hoạt ngay</strong> - Voucher có thể sử dụng được
                        </label>
                    </div>
                </div>

                <!-- Nút hành động -->
                <div class="border-top pt-4 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-plus-circle"></i> Tạo Voucher
                    </button>
                    <a href="/Voucher/list" class="btn btn-secondary btn-lg px-4 ml-2">
                        Hủy
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- JavaScript xử lý form -->
<script>
// Toggle giữa % và VNĐ
$('input[name="discount_type"]').change(function() {
    if ($(this).val() === 'percent') {
        $('#discountLabel').html('Giá trị giảm (%) <span class="text-danger">*</span>');
        $('#discountUnit').text('%');
        $('#discount_value').attr({min: 1, max: 100, placeholder: '10'});
        $('#discountHelp').text('Nhập số từ 1-100 để giảm theo phần trăm');
        $('#maxDiscountGroup').show();
    } else {
        $('#discountLabel').html('Giá trị giảm (VNĐ) <span class="text-danger">*</span>');
        $('#discountUnit').text('đ');
        $('#discount_value').attr({min: 1000, max: null, placeholder: '50000', step: 1000});
        $('#discountHelp').text('Nhập số tiền giảm trực tiếp (tối thiểu 1.000đ)');
        $('#maxDiscountGroup').hide();
        $('#max_discount').val('');
    }
});

// Tạo mã ngẫu nhiên
function generateCode() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    // Thêm prefix cho dễ nhận diện
    const prefixes = ['SALE', 'GIFT', 'PROMO', 'NEW'];
    const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
    document.getElementById('code').value = prefix + code;
}

// Validate ngày kết thúc >= ngày bắt đầu
document.getElementById('voucherForm').addEventListener('submit', function(e) {
    const start = new Date(document.getElementById('start_date').value);
    const end = new Date(document.getElementById('end_date').value);
    
    if (document.getElementById('start_date').value && start > end) {
        e.preventDefault();
        alert('⚠️ Ngày bắt đầu không được sau ngày kết thúc!');
        document.getElementById('end_date').focus();
        return false;
    }
    
    const discountValue = parseInt(document.getElementById('discount_value').value);
    const discountType = document.querySelector('input[name="discount_type"]:checked').value;
    
    if (discountType === 'percent' && (discountValue < 1 || discountValue > 100)) {
        e.preventDefault();
        alert('⚠️ Giá trị giảm theo % phải từ 1 đến 100!');
        document.getElementById('discount_value').focus();
        return false;
    }
});

// Format số tiền khi nhập (optional)
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    if (value) {
        input.value = parseInt(value).toLocaleString('vi-VN');
    }
}
</script>

<?php include 'app/views/shares/footer.php'; ?>