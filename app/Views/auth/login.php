<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Zalo OA Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            width: 400px;
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
        }
        .brand-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #0068ff, #00c4ff);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 16px;
        }
    </style>
</head>
<body>
<div class="login-card card">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <div class="brand-icon">
                <i class="bi bi-robot text-white"></i>
            </div>
            <h4 class="fw-bold">Phường Lê Chân</h4>
            <p class="text-muted small">Claude AI Powered — Đăng nhập Admin</p>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-sm" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-sm" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('auth/login') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="username">Tên đăng nhập</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-person text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0"
                           id="username" name="username"
                           placeholder="admin"
                           value="<?= esc(old('username', '')) ?>"
                           required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" for="password">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password" class="form-control border-start-0 ps-0"
                           id="password" name="password"
                           placeholder="••••••••"
                           required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
            </button>
        </form>

        <hr class="my-4">
        <div class="text-center text-muted" style="font-size:.75rem">
            <i class="bi bi-shield-check me-1"></i>
            Webhook: <code><?= base_url('webhook') ?></code>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
