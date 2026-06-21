<?php
/**
 * Login View
 * @var array $errors
 */
$pageTitle = 'Login';
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-center">
    <div class="card shadow-sm mt-4" style="max-width: 400px; width: 100%;">
        <div class="card-header text-center"><strong>🔐 System Login</strong></div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form action="<?= e(url('auth', 'doLogin')) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" class="form-control" id="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>