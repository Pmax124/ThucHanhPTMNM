<?php include 'app/views/shares/header.php'; ?>

<style>
    /* CSS cho giao diện Checkout chuyên nghiệp */
    .checkout-header {
        border-bottom: 2px solid #eee;
        padding-bottom: 15px;
        margin-bottom: 25px;
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 15px;
        color: #495057;
        border-left: 4px solid #007bff;
        padding-left: 10px;
    }

    .card-custom {
        border: none;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .payment-option {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .payment-option:hover {
        background-color: #f8f9fa;
        border-color: #007bff;
    }

    .payment-option.active {
        background-color: #e7f1ff;
        border-color: #007bff;
    }

    .qr-box {
        display: none;
        text-align: center;
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 10px;
    }
    
    .qr-box img {
        max-width: 200px;
        border: 5px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .bank-info {
        text-align: left;
        margin-top: 15px;
        background: #fff;
        padding: 10px;
        border-radius: 5px;
    }

    .order-summary {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        position: sticky;
        top: 20px;
    }

    .product-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .product-item img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 10px;
    }

    .total-price {
        font-size: 1.5rem;
        font-weight: bold;
        color: #dc3545;
        text-align: right;
    }

    /* === CSS MỚI CHO VOUCHER === */
    .voucher-section {
        background: #fff;
        border: 2px dashed #007bff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .voucher-input-group .form-control {
        border-right: none;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    
    .voucher-input-group .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    
    .voucher-result {
        margin-top: 10px;
        font-size: 0.9rem;
    }
    
    .discount-row {
        color: #28a745;
        font-weight: 600;
    }
    
    .original-price {
        text-decoration: line-through;
        color: #999;
        font-size: 0.9rem;
        margin-right: 10px;
    }
</style>

<div class="container mt-4 mb-5">
    <div class="checkout-header">
        <h2><i class="fas fa-credit-card"></i> Thanh Toán</h2>
    </div>

    <?php
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    ?>

    <?php if (empty($cart)): ?>
        <div class="alert alert-warning text-center p-5">
            <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
            <h4>Giỏ hàng của bạn đang trống!</h4>
            <p>Hãy thêm sản phẩm yêu thích vào giỏ hàng để tiến hành thanh toán.</p>
            <a href="/Product" class="btn btn-primary mt-2">
                <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
            </a>
        </div>

    <?php else: ?>
    <form action="/Product/processCheckout" method="POST" id="checkoutForm">
        <div class="row">
            
            <!-- CỘT TRÁI: THÔNG TIN KHÁCH HÀNG & THANH TOÁN -->
            <div class="col-lg-8">
                
                <!-- 1. Thông tin nhận hàng -->
                <div class="card card-custom p-4">
                    <h5 class="section-title">1. Thông tin nhận hàng</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Nhập họ tên..." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="0901234567" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@example.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Tỉnh / Thành phố</label>
                            <select name="city" class="form-control">
                                <option value="">-- Chọn --</option>
                                <option value="Hà Nội">Hà Nội</option>
                                <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
                                <option value="Đà Nẵng">Đà Nẵng</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Địa chỉ cụ thể <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Số nhà, tên đường, phường/xã..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Ghi chú đơn hàng</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Ví dụ: Giao giờ hành chính, hàng dễ vỡ..."></textarea>
                    </div>
                </div>

                <!-- === MỚI: PHẦN NHẬP VOUCHER === -->
                <div class="card card-custom p-4">
                    <h5 class="section-title">3. Mã Giảm Giá</h5>
                    <div class="voucher-section">
                        <div class="input-group voucher-input-group">
                            <input type="text" id="voucherCode" name="voucher_code" class="form-control" 
                                   placeholder="Nhập mã voucher (VD: SALE10)" maxlength="50" autocomplete="off">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" onclick="applyVoucher()">
                                    <i class="fas fa-gift"></i> Áp dụng
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Nhập mã voucher để nhận ưu đãi giảm giá
                        </small>
                        <div id="voucherResult" class="voucher-result"></div>
                    </div>
                </div>
                <!-- === HẾT PHẦN VOUCHER === -->

                <!-- 2. Phương thức thanh toán -->
                <div class="card card-custom p-4">
                    <h5 class="section-title">2. Phương thức thanh toán</h5>
                    
                    <div class="payment-option active" onclick="selectPayment('cod')">
                        <label style="cursor: pointer; display: flex; align-items: center;">
                            <input type="radio" name="payment_method" value="cod" checked style="margin-right: 10px;">
                            <div>
                                <strong>Thanh toán khi nhận hàng (COD)</strong><br>
                                <small class="text-muted">Thanh toán tiền mặt trực tiếp khi nhận hàng.</small>
                            </div>
                        </label>
                    </div>

                    <div class="payment-option" onclick="selectPayment('qr')">
                        <label style="cursor: pointer; display: flex; align-items: center;">
                            <input type="radio" name="payment_method" value="qr" style="margin-right: 10px;">
                            <div>
                                <strong>Chuyển khoản qua QR Code</strong><br>
                                <small class="text-muted">Quét mã QR để thanh toán ngay.</small>
                            </div>
                        </label>
                    </div>

                    <div id="qr-section" class="qr-box">
                        <h5 class="text-primary">Quét mã để thanh toán</h5>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=ChuyenKhoanNganHang" alt="QR Code">
                        
                        <div class="bank-info">
                            <p><strong>Ngân hàng:</strong> Vietcombank</p>
                            <p><strong>Số tài khoản:</strong> 1234567890</p>
                            <p><strong>Chủ tài khoản:</strong> Nguyễn Hoàng Phúc</p>
                            <p><strong>Nội dung CK:</strong> [Tên bạn] - [SĐT]</p>
                            <p class="text-danger"><small>* Vui lòng chuyển đúng số tiền và chụp ảnh biên lai.</small></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: TÓM TẮT ĐƠN HÀNG -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h5 class="section-title">Đơn hàng của bạn</h5>
                    
                    <?php foreach ($cart as $id => $item): ?>
                        <div class="product-item">
                            <?php if(!empty($item['image'])): ?>
                                <img src="/<?php echo $item['image']; ?>" alt="img">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/60" alt="img">
                            <?php endif; ?>
                            <div>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="text-muted small">
                                    <?php echo number_format($item['price'], 0, ',', '.'); ?> đ x <?php echo $item['quantity']; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Tạm tính:</span>
                        <span id="subtotalDisplay"><?php echo number_format($total, 0, ',', '.'); ?> đ</span>
                    </div>
                    
                    <!-- === MỚI: Dòng giảm giá (ẩn mặc định) === -->
                    <div class="d-flex justify-content-between discount-row mb-2" id="discountRow" style="display: none;">
                        <span>Giảm giá (<span id="appliedCode"></span>):</span>
                        <span>-<span id="discountAmount">0</span> đ</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>Phí vận chuyển:</span>
                        <span class="text-success">Miễn phí</span>
                    </div>
                    <hr>
                    
                    <!-- === MỚI: Hiển thị tổng tiền có/không discount === -->
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size: 1.1rem; font-weight: bold;">Tổng cộng:</span>
                        <div class="text-right">
                            <span class="original-price" id="originalTotalDisplay" style="display: none;">
                                <?php echo number_format($total, 0, ',', '.'); ?> đ
                            </span>
                            <span class="total-price" id="finalTotal"><?php echo number_format($total, 0, ',', '.'); ?> đ</span>
                        </div>
                    </div>

                    <!-- Hidden inputs để gửi voucher khi submit -->
                    <input type="hidden" name="discount_amount" id="discountAmountInput" value="0">
                    <input type="hidden" name="original_total" id="originalTotalInput" value="<?php echo $total; ?>">

                    <button type="submit" class="btn btn-success btn-lg btn-block mt-4" style="border-radius: 50px;">
                        <i class="fas fa-check-circle"></i> Đặt Hàng Ngay
                    </button>
                    
                    <a href="/Product/cart" class="btn btn-outline-secondary btn-block mt-2">
                        Quay lại giỏ hàng
                    </a>
                </div>
            </div>

        </div>
    </form>
    <?php endif; ?>
</div>

<script>
    // Hàm xử lý chuyển đổi phương thức thanh toán
    function selectPayment(method) {
        document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('active'));
        event.currentTarget.classList.add('active');
        document.querySelector(`input[value="${method}"]`).checked = true;
        const qrSection = document.getElementById('qr-section');
        if (method === 'qr') {
            qrSection.style.display = 'block';
        } else {
            qrSection.style.display = 'none';
        }
    }

    // === MỚI: Hàm áp dụng voucher qua AJAX ===
    let originalTotal = <?php echo $total; ?>;
    let appliedDiscount = 0;

    function applyVoucher() {
        const code = document.getElementById('voucherCode').value.trim().toUpperCase();
        const resultDiv = document.getElementById('voucherResult');
        
        if (!code) {
            resultDiv.innerHTML = '<div class="alert alert-warning py-2 mb-0">Vui lòng nhập mã voucher</div>';
            resultDiv.style.display = 'block';
            return;
        }
        
        // Hiển thị loading
        resultDiv.innerHTML = '<small class="text-primary"><i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...</small>';
        resultDiv.style.display = 'block';
        
        // Gửi AJAX
        fetch('/Product/applyVoucher', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `code=${encodeURIComponent(code)}&order_total=${originalTotal}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                // Thành công
                appliedDiscount = data.discount;
                const finalTotal = originalTotal - appliedDiscount;
                
                resultDiv.innerHTML = `<div class="alert alert-success py-2 mb-0"><i class="fas fa-check-circle"></i> ${data.message}</div>`;
                
                // Hiển thị dòng giảm giá
                document.getElementById('discountRow').style.display = 'flex';
                document.getElementById('appliedCode').textContent = code;
                document.getElementById('discountAmount').textContent = 
                    appliedDiscount.toLocaleString('vi-VN');
                
                // Cập nhật tổng tiền
                document.getElementById('originalTotalDisplay').style.display = 'inline';
                document.getElementById('finalTotal').textContent = 
                    finalTotal.toLocaleString('vi-VN') + ' đ';
                
                // Lưu vào hidden input
                document.getElementById('discountAmountInput').value = appliedDiscount;
                
            } else {
                // Lỗi
                resultDiv.innerHTML = `<div class="alert alert-danger py-2 mb-0"><i class="fas fa-exclamation-circle"></i> ${data.message}</div>`;
                
                // Reset giao diện
                document.getElementById('discountRow').style.display = 'none';
                document.getElementById('originalTotalDisplay').style.display = 'none';
                document.getElementById('finalTotal').textContent = 
                    originalTotal.toLocaleString('vi-VN') + ' đ';
                document.getElementById('discountAmountInput').value = '0';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="fas fa-exclamation-circle"></i> Lỗi kết nối server</div>';
        });
    }

    // Cho phép nhấn Enter để áp dụng voucher
    document.getElementById('voucherCode').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyVoucher();
        }
    });
</script>

<?php include 'app/views/shares/footer.php'; ?>