-- ============================================================
-- ZaloOA Bot - Claude AI
-- Script tao database va cac bang can thiet
-- Chay: mysql -u root -p < database/setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `chatbotzalooa`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `chatbotzalooa`;

-- Bang cuoc hoi thoai (moi nguoi dung Zalo = 1 conversation)
CREATE TABLE IF NOT EXISTS `conversations` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bang tin nhan (moi tin nhan = 1 row, role: user | assistant)
CREATE TABLE IF NOT EXISTS `messages` (
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
        FOREIGN KEY (`conversation_id`)
        REFERENCES `conversations` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bang cai dat he thong (key-value store)
CREATE TABLE IF NOT EXISTS `settings` (
    `key`         VARCHAR(100) NOT NULL,
    `value`       LONGTEXT,
    `description` VARCHAR(255),
    `updated_at`  DATETIME,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Du lieu mac dinh
-- ============================================================
INSERT IGNORE INTO `settings` (`key`, `value`, `description`, `updated_at`) VALUES
('bot_enabled', '1', 'Bat/tat bot tu dong (1=bat, 0=tat)', NOW()),
('claude_system_prompt', 'Bạn là trợ lý AI thông minh của một trung tâm đào tạo. Nhiệm vụ của bạn là hỗ trợ học viên 24/7 qua Zalo.\n\n## Vai trò\n- Tư vấn viên học tập thân thiện, chuyên nghiệp\n- Giải đáp thắc mắc về khóa học, lịch học, bài tập\n- Hỗ trợ kỹ thuật cơ bản cho nền tảng học trực tuyến\n- Động viên và hỗ trợ tinh thần học viên\n\n## Nguyên tắc trả lời\n1. Luôn trả lời bằng tiếng Việt\n2. Thân thiện, kiên nhẫn, dễ hiểu\n3. Câu trả lời ngắn gọn, súc tích (dưới 500 từ nếu có thể)\n4. Dùng emoji phù hợp để tạo cảm giác gần gũi 😊\n5. Nếu không biết, hãy nói thật và hướng dẫn liên hệ hỗ trợ trực tiếp\n\n## Giới hạn\n- Không tiết lộ thông tin cá nhân của học viên khác\n- Không hứa hẹn điều bạn không chắc chắn\n- Với câu hỏi phức tạp về học phí, hợp đồng → hướng dẫn liên hệ nhân viên\n\n## Thông tin liên hệ hỗ trợ\n- Hotline: 1800 xxxx\n- Email: support@example.com\n- Giờ làm việc: 8:00 - 22:00 mỗi ngày', 'System prompt cho Claude AI', NOW()),
('welcome_message', '🎉 Xin chào! Cảm ơn bạn đã quan tâm đến chúng tôi!\n\nTôi là trợ lý AI, sẵn sàng hỗ trợ bạn 24/7 về:\n📚 Thông tin khóa học\n📅 Lịch học và bài tập\n❓ Giải đáp thắc mắc\n\nBạn cần hỗ trợ gì, hãy nhắn tin cho tôi nhé!', 'Tin nhan chao mung', NOW()),
('image_reply', '📷 Cảm ơn bạn đã gửi hình ảnh! Hiện tại tôi chỉ có thể xử lý tin nhắn văn bản. Bạn hãy mô tả câu hỏi bằng chữ để tôi hỗ trợ nhé!', 'Tra loi khi nhan hinh anh', NOW()),
('transfer_keywords', 'nhân viên,tư vấn viên,gặp người thật,hỗ trợ trực tiếp', 'Tu khoa chuyen sang nhan vien', NOW()),
('transfer_message', '🙋 Tôi đã ghi nhận yêu cầu của bạn và sẽ kết nối bạn với nhân viên hỗ trợ ngay!\n\n⏰ Thời gian làm việc: 8:00 - 22:00\nNhân viên sẽ phản hồi bạn trong thời gian sớm nhất.', 'Tin nhan khi chuyen nhan vien', NOW()),
('contact_hotline', '1800 xxxx', 'So hotline', NOW()),
('contact_email', 'support@example.com', 'Email ho tro', NOW()),
('contact_hours', '8:00 - 22:00 mỗi ngày', 'Gio lam viec', NOW());

SELECT 'Database setup completed!' AS status;
