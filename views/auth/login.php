<?php
$pageTitle = 'Đăng nhập';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-center">
    <div class="card shadow-sm mt-4" style="max-width:420px; width:100%;">
        <div class="card-header"><strong>🔐 Đăng nhập hệ thống</strong></div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= e(url('auth', 'doLogin')) ?>">
                <?= csrf_field() /* [NEW] CSRF token */ ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
            <p class="text-muted small mt-3 mb-0">
                Tài khoản lấy từ bảng <code>users</code>. Mật khẩu được đối chiếu bằng <code>password_verify()</code>
                với cột <code>password_hash</code>.
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
