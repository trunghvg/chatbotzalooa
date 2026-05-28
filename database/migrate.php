<?php
/**
 * One-time database setup — run by railway-start.sh on every boot.
 * Uses IF NOT EXISTS so it is safe to run repeatedly.
 */

// Priority 1: individual Railway vars (truthy skips empty strings)
if (getenv('MYSQLHOST')) {
    $host = getenv('MYSQLHOST');
    $port = (int) (getenv('MYSQLPORT') ?: 3306);
    $user = getenv('MYSQLUSER') ?: 'root';
    $pass = (string) getenv('MYSQLPASSWORD');
    $db   = getenv('MYSQLDATABASE') ?: 'railway';
} elseif (getenv('DB_HOST')) {
    $host = getenv('DB_HOST');
    $port = (int) (getenv('DB_PORT') ?: 3306);
    $user = getenv('DB_USER') ?: 'root';
    $pass = (string) getenv('DB_PASS');
    $db   = getenv('DB_NAME') ?: 'railway';
} else {
    // Try public URL first (works outside Railway private network too)
    $url = getenv('MYSQL_PUBLIC_URL') ?: getenv('MYSQL_URL') ?: getenv('DATABASE_URL') ?: '';
    if (!$url) {
        echo "[migrate] No DB env vars found — skipping.\n";
        exit(0);
    }
    // Normalize scheme: _mysql:// or mysqli:// → mysql://
    $url  = preg_replace('#^[^/]*mysql[^/]*://#i', 'mysql://', $url);
    $p    = parse_url($url);
    $host = $p['host'] ?? '127.0.0.1';
    $port = (int) ($p['port'] ?? 3306);
    $user = isset($p['user']) ? urldecode($p['user']) : 'root';
    $pass = isset($p['pass']) ? urldecode($p['pass']) : '';
    $db   = ltrim($p['path'] ?? '', '/') ?: 'railway';
}

echo "[migrate] Connecting to $host:$port db=$db user=$user\n";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[migrate] Connected to $db@$host\n";
} catch (Exception $e) {
    echo "[migrate] DB connection failed: " . $e->getMessage() . "\n";
    echo "[migrate] App will start anyway — run migration manually if needed.\n";
    exit(0); // don't crash the container
}

$statements = [

'conversations' => "CREATE TABLE IF NOT EXISTS `conversations` (
    `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `zalo_user_id`    VARCHAR(50)      NOT NULL,
    `user_name`       VARCHAR(200)     DEFAULT 'Học viên',
    `user_avatar`     TEXT,
    `last_message`    VARCHAR(500)     DEFAULT '',
    `last_message_at` DATETIME,
    `message_count`   INT              DEFAULT 0,
    `status`          ENUM('active','pending_human','closed') DEFAULT 'active',
    `created_at`      DATETIME,
    `updated_at`      DATETIME,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_zalo_user_id` (`zalo_user_id`),
    KEY `idx_last_message_at` (`last_message_at`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'messages' => "CREATE TABLE IF NOT EXISTS `messages` (
    `id`                  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `conversation_id`     INT(11) UNSIGNED NOT NULL,
    `role`                ENUM('user','assistant') NOT NULL,
    `content`             TEXT NOT NULL,
    `zalo_msg_id`         VARCHAR(100),
    `tokens_used`         INT DEFAULT 0,
    `processing_time_ms`  INT DEFAULT 0,
    `created_at`          DATETIME,
    PRIMARY KEY (`id`),
    KEY `idx_conversation_id` (`conversation_id`),
    KEY `idx_role` (`role`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_messages_conversation`
        FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'settings' => "CREATE TABLE IF NOT EXISTS `settings` (
    `key`         VARCHAR(100) NOT NULL,
    `value`       LONGTEXT,
    `description` VARCHAR(255),
    `updated_at`  DATETIME,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

'knowledge_base' => "CREATE TABLE IF NOT EXISTS `knowledge_base` (
    `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(300)     NOT NULL,
    `category`        VARCHAR(100)     DEFAULT 'general',
    `source_document` VARCHAR(200),
    `content`         LONGTEXT         NOT NULL,
    `keywords`        TEXT,
    `is_active`       TINYINT(1)       DEFAULT 1,
    `sort_order`      INT              DEFAULT 0,
    `created_at`      DATETIME,
    `updated_at`      DATETIME,
    PRIMARY KEY (`id`),
    KEY `idx_category` (`category`),
    KEY `idx_is_active` (`is_active`),
    FULLTEXT KEY `ft_search` (`title`, `content`, `keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

];

foreach ($statements as $table => $sql) {
    $pdo->exec($sql);
    echo "[migrate] Table '$table' ready.\n";
}

// Insert default settings (only if settings table is empty)
$count = $pdo->query("SELECT COUNT(*) FROM `settings`")->fetchColumn();
if ($count == 0) {
    $defaults = [
        ['bot_enabled',        '1',                                        'Bật/tắt bot tự động'],
        ['welcome_message',    "🎉 Xin chào! Tôi là trợ lý AI, sẵn sàng hỗ trợ bạn 24/7!\nBạn cần hỗ trợ gì hãy nhắn tin nhé!", 'Tin nhắn chào mừng'],
        ['image_reply',        '📷 Cảm ơn bạn đã gửi hình ảnh! Vui lòng mô tả câu hỏi bằng chữ để tôi hỗ trợ nhé!', 'Trả lời khi nhận hình'],
        ['transfer_keywords',  'nhân viên,tư vấn viên,gặp người thật,hỗ trợ trực tiếp', 'Từ khóa chuyển nhân viên'],
        ['transfer_message',   '🙋 Tôi sẽ kết nối bạn với nhân viên hỗ trợ ngay! Vui lòng chờ trong giây lát.', 'Tin nhắn chuyển nhân viên'],
        ['claude_system_prompt', 'Bạn là trợ lý AI hỗ trợ học viên. Luôn trả lời bằng tiếng Việt, ngắn gọn và thân thiện.', 'System prompt Claude AI'],
        ['contact_hotline',    '1800 xxxx',         'Số hotline'],
        ['contact_email',      'support@example.com', 'Email hỗ trợ'],
        ['contact_hours',      '8:00 - 22:00 mỗi ngày', 'Giờ làm việc'],
    ];

    $stmt = $pdo->prepare(
        "INSERT IGNORE INTO `settings` (`key`, `value`, `description`, `updated_at`) VALUES (?, ?, ?, NOW())"
    );
    foreach ($defaults as [$key, $value, $desc]) {
        $stmt->execute([$key, $value, $desc]);
    }
    echo "[migrate] Default settings inserted.\n";
}

echo "[migrate] Done.\n";
