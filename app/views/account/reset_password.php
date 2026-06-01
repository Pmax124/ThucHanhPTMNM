<?php include 'app/views/shares/header.php'; ?>

<div class="container" style="max-width: 500px; margin-top: 60px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <h3 class="text-center mb-4 text-primary"><i class="fas fa-key me-2"></i>Đặt lại mật khẩu</h3>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form method="POST" action="/account/handle-reset-password">
                <!-- Token ẩn -->
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mật khẩu mới</label>
                    <input type="password" name="password" class="form-control rounded-3" placeholder="Tối thiểu 6 ký tự" required minlength="6">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Xác nhận mật khẩu</label>
                    <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Nhập lại mật khẩu" required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-semibold">
                    <i class="fas fa-save me-2"></i>Cập nhật mật khẩu
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="/account/login" class="text-decoration-none text-muted">← Quay lại đăng nhập</a>
            </div>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>