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

-- Bang co so kien thuc (knowledge base) cho chatbot
CREATE TABLE IF NOT EXISTS `knowledge_base` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Du lieu mac dinh
-- ============================================================
INSERT IGNORE INTO `settings` (`key`, `value`, `description`, `updated_at`) VALUES
('bot_enabled', '1', 'Bat/tat bot tu dong (1=bat, 0=tat)', NOW()),
('claude_system_prompt', 'Bạn là trợ lý AI của **Trung tâm Chính trị phường Lê Chân** (thành phố Hải Phòng).\nNhiệm vụ: Hỗ trợ học viên 24/7 qua Zalo về các vấn đề học tập, quy chế, thủ tục tại TTCT.\n\n## Vai trò\n- Giải đáp thắc mắc về các lớp bồi dưỡng lý luận chính trị\n- Hướng dẫn quy trình đăng ký, học tập, thi thu hoạch, nhận giấy chứng nhận\n- Cung cấp thông tin chính xác dựa trên các văn bản pháp lý của TTCT\n- Hỗ trợ thân thiện, tận tình\n\n## Nguyên tắc trả lời\n1. **Luôn trả lời bằng tiếng Việt**, ngắn gọn, dễ hiểu\n2. Khi trả lời các vấn đề về quy chế, hãy **trích dẫn rõ số quyết định và điều khoản**\n3. Nếu không chắc chắn → nói thật và hướng dẫn liên hệ TTCT trực tiếp\n4. Thân thiện, dùng emoji phù hợp 😊\n5. Không bịa đặt thông tin; chỉ trả lời dựa trên kiến thức được cung cấp\n\n## Giới hạn\n- Không tiết lộ thông tin cá nhân học viên khác\n- Câu hỏi phức tạp về nhân sự, kỷ luật → hướng dẫn gặp trực tiếp Ban Giám đốc\n\n## Thông tin liên hệ TTCT phường Lê Chân\n- Địa chỉ: Phường Lê Chân, Quận Lê Chân, TP. Hải Phòng\n- Giờ làm việc: Giờ hành chính các ngày trong tuần', 'System prompt cho Claude AI', NOW()),
('welcome_message', '🎉 Xin chào! Cảm ơn bạn đã quan tâm đến chúng tôi!\n\nTôi là trợ lý AI, sẵn sàng hỗ trợ bạn 24/7 về:\n📚 Thông tin khóa học\n📅 Lịch học và bài tập\n❓ Giải đáp thắc mắc\n\nBạn cần hỗ trợ gì, hãy nhắn tin cho tôi nhé!', 'Tin nhan chao mung', NOW()),
('image_reply', '📷 Cảm ơn bạn đã gửi hình ảnh! Hiện tại tôi chỉ có thể xử lý tin nhắn văn bản. Bạn hãy mô tả câu hỏi bằng chữ để tôi hỗ trợ nhé!', 'Tra loi khi nhan hinh anh', NOW()),
('transfer_keywords', 'nhân viên,tư vấn viên,gặp người thật,hỗ trợ trực tiếp', 'Tu khoa chuyen sang nhan vien', NOW()),
('transfer_message', '🙋 Tôi đã ghi nhận yêu cầu của bạn và sẽ kết nối bạn với nhân viên hỗ trợ ngay!\n\n⏰ Thời gian làm việc: 8:00 - 22:00\nNhân viên sẽ phản hồi bạn trong thời gian sớm nhất.', 'Tin nhan khi chuyen nhan vien', NOW()),
('contact_hotline', '1800 xxxx', 'So hotline', NOW()),
('contact_email', 'support@example.com', 'Email ho tro', NOW()),
('contact_hours', '8:00 - 22:00 mỗi ngày', 'Gio lam viec', NOW());

-- ============================================================
-- Du lieu Knowledge Base - Van ban phap ly TTCT phuong Le Chan
-- (Chay file PHP seeder de co du lieu day du hon)
-- ============================================================
INSERT IGNORE INTO `knowledge_base` (`title`, `category`, `source_document`, `content`, `keywords`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('Vị trí và chức năng TTCT cấp xã', 'to-chuc-bo-may', 'QĐ 360-QĐ/TW ngày 29/8/2025',
 'TTCT cấp xã là đơn vị sự nghiệp trực thuộc đảng ủy xã. Chức năng: tổ chức bồi dưỡng lý luận chính trị cho đảng viên, đoàn viên, hội viên; cập nhật kiến thức, kỹ năng, nghiệp vụ cho CBCC các cơ quan trong hệ thống chính trị cấp xã, phường, đặc khu.',
 'trung tâm chính trị, vị trí, chức năng, bồi dưỡng, lý luận', 1, 10, NOW(), NOW()),

('Đánh giá kết quả học tập - Bài thu hoạch', 'quy-che-boi-duong', 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
 'Học viên làm 01 bài thu hoạch cuối khóa (thang điểm 10). Phải dự tối thiểu 80% số tiết mới được viết thu hoạch. Từ 5,0 điểm: hoàn thành; dưới 5,0: không đạt (được làm lại 1 lần). Xếp loại: 5-<7: Trung bình; 7-<8: Khá; 8-<9: Giỏi; 9-10: Xuất sắc.',
 'bài thu hoạch, điểm, xếp loại, xuất sắc, giỏi, khá, trung bình, 80%, hoàn thành, không đạt', 1, 23, NOW(), NOW()),

('Cấp giấy chứng nhận hoàn thành khóa học', 'quy-che-boi-duong', 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
 'Thời hạn cấp: 15 ngày kể từ ngày ký quyết định công nhận hoàn thành. Chỉ cấp một lần (trừ lỗi in ấn). Phải lập sổ gốc cấp giấy chứng nhận, đánh số trang, đóng dấu giáp lai, lưu trữ vĩnh viễn.',
 'giấy chứng nhận, 15 ngày, cấp giấy, sổ gốc, lưu trữ vĩnh viễn', 1, 24, NOW(), NOW()),

('Định mức giờ chuẩn giảng viên', 'quy-che-boi-duong', 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
 'Giảng viên chuyên trách: 270 giờ/năm (ít nhất 50% giờ dạy trực tiếp). Giám đốc: 10% định mức (~27 giờ). Phó GĐ: 20% (~54 giờ). Chấm 6 bài thu hoạch = 1 giờ chuẩn. Ra đề = 1,5 giờ chuẩn/đề.',
 'giờ chuẩn, định mức, giảng viên, giám đốc, phó giám đốc, chấm bài, ra đề', 1, 25, NOW(), NOW()),

('Thành lập TTCT phường Lê Chân', 'ttct-le-chan', 'QĐ 04-QĐ/ĐU ngày 01/7/2025',
 'TTCT phường Lê Chân là đơn vị sự nghiệp của Đảng ủy phường Lê Chân (TP. Hải Phòng). Có con dấu riêng, trụ sở và cơ sở vật chất. Hiệu lực từ 01/7/2025. Ký: Đặng Đông Anh - Bí thư.',
 'thành lập, phường lê chân, hải phòng, đảng ủy, con dấu, 01/7/2025', 1, 40, NOW(), NOW());

-- Chay lenh nay sau khi setup xong de import day du du lieu:
-- php spark db:seed KnowledgeBaseSeeder

SELECT 'Database setup completed!' AS status;
