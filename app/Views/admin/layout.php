<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin') ?> | Zalo OA Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --zalo-blue: #0068ff;
            --sidebar-bg: #1a1d23;
        }
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

        /* Sidebar */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: transform .3s;
        }
        #sidebar .brand {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        #sidebar .brand h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1rem;
        }
        #sidebar .brand small { color: rgba(255,255,255,.5); font-size: .7rem; }
        #sidebar .nav-link {
            color: rgba(255,255,255,.65);
            padding: 10px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all .2s;
            font-size: .875rem;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(0,104,255,.2);
            color: #4d9fff;
        }
        #sidebar .nav-section {
            color: rgba(255,255,255,.3);
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: 16px 20px 4px;
            font-weight: 600;
        }

        /* Main content */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Topbar */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,.1) !important;
        }
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }

        /* Chat bubbles */
        .msg-bubble {
            max-width: 75%;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: .875rem;
            line-height: 1.5;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .msg-user .msg-bubble {
            background: #e8f0fe;
            border-bottom-right-radius: 4px;
            margin-left: auto;
        }
        .msg-bot .msg-bubble {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-bottom-left-radius: 4px;
        }

        /* Bot status badge */
        .bot-status-on  { background: #dcfce7; color: #166534; }
        .bot-status-off { background: #fee2e2; color: #991b1b; }

        /* Alert flash */
        .alert-flash {
            position: fixed;
            top: 20px; right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn .3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<nav id="sidebar">
    <div class="brand">
        <div class="d-flex align-items-center gap-2">
            <div style="width:36px;height:36px;background:#0068ff;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-robot text-white"></i>
            </div>
            <div>
                <h5>ZaloOA Bot</h5>
                <small>Claude AI Powered</small>
            </div>
        </div>
    </div>

    <div class="py-2">
        <div class="nav-section">Tổng quan</div>
        <a href="<?= base_url('admin') ?>" class="nav-link <?= uri_string() === 'admin' || uri_string() === 'admin/dashboard' ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>

        <div class="nav-section">Hội thoại</div>
        <a href="<?= base_url('admin/conversations') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/conversations') ? 'active' : '' ?>">
            <i class="bi bi-chat-dots-fill"></i> Cuộc hội thoại
        </a>
        <a href="<?= base_url('admin/messages') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/messages') ? 'active' : '' ?>">
            <i class="bi bi-journal-text"></i> Nhật ký tin nhắn
        </a>

        <div class="nav-section">Cấu hình AI</div>
        <a href="<?= base_url('admin/knowledge') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/knowledge') ? 'active' : '' ?>">
            <i class="bi bi-database-fill"></i> Cơ sở kiến thức
        </a>
        <a href="<?= base_url('admin/prompt') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/prompt') ? 'active' : '' ?>">
            <i class="bi bi-cpu-fill"></i> System Prompt AI
        </a>
        <a href="<?= base_url('admin/settings') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/settings') ? 'active' : '' ?>">
            <i class="bi bi-gear-fill"></i> Cài đặt
        </a>
        <a href="<?= base_url('zalo/authorize') ?>" class="nav-link" target="_blank">
            <i class="bi bi-key-fill"></i> Kết nối Zalo OA
        </a>

        <div class="nav-section">Tài khoản</div>
        <a href="<?= base_url('auth/logout') ?>" class="nav-link">
            <i class="bi bi-box-arrow-left"></i> Đăng xuất
        </a>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div id="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="mb-0 fw-semibold"><?= esc($title ?? 'Admin') ?></h6>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge py-2 px-3 <?= session()->get('bot_status', '') === '0' ? 'bot-status-off' : 'bot-status-on' ?>">
                <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>
                Bot <?= session()->get('bot_status', '') === '0' ? 'Tắt' : 'Đang chạy' ?>
            </span>
            <small class="text-muted"><?= session()->get('admin_username') ?></small>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert-flash">
        <div class="alert alert-success alert-dismissible shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert-flash">
        <div class="alert alert-danger alert-dismissible shadow-sm" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page Content -->
    <div class="p-4">
        <?= $this->renderSection('content') ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto dismiss flash alerts
setTimeout(() => {
    document.querySelectorAll('.alert-flash .alert').forEach(el => {
        new bootstrap.Alert(el).close();
    });
}, 4000);
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
