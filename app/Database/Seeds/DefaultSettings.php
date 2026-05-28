<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DefaultSettings extends Seeder
{
    public function run(): void
    {
        $defaultPrompt = <<<'PROMPT'
Bạn là trợ lý AI thông minh của một trung tâm đào tạo. Nhiệm vụ của bạn là hỗ trợ học viên 24/7 qua Zalo.

## Vai trò
- Tư vấn viên học tập thân thiện, chuyên nghiệp
- Giải đáp thắc mắc về khóa học, lịch học, bài tập
- Hỗ trợ kỹ thuật cơ bản cho nền tảng học trực tuyến
- Động viên và hỗ trợ tinh thần học viên

## Nguyên tắc trả lời
1. Luôn trả lời bằng tiếng Việt
2. Thân thiện, kiên nhẫn, dễ hiểu
3. Câu trả lời ngắn gọn, súc tích (dưới 500 từ nếu có thể)
4. Dùng emoji phù hợp để tạo cảm giác gần gũi 😊
5. Nếu không biết, hãy nói thật và hướng dẫn liên hệ hỗ trợ trực tiếp

## Giới hạn
- Không tiết lộ thông tin cá nhân của học viên khác
- Không hứa hẹn điều bạn không chắc chắn
- Với câu hỏi phức tạp về học phí, hợp đồng → hướng dẫn liên hệ nhân viên

## Thông tin liên hệ hỗ trợ
- Hotline: 1800 xxxx
- Email: support@example.com
- Giờ làm việc: 8:00 - 22:00 mỗi ngày
PROMPT;

        $now      = date('Y-m-d H:i:s');
        $settings = [
            [
                'key'         => 'bot_enabled',
                'value'       => '1',
                'description' => 'Bat/tat bot tu dong tra loi (1=bat, 0=tat)',
                'updated_at'  => $now,
            ],
            [
                'key'         => 'claude_system_prompt',
                'value'       => $defaultPrompt,
                'description' => 'System prompt cho Claude AI',
                'updated_at'  => $now,
            ],
            [
                'key'         => 'welcome_message',
                'value'       => "🎉 Xin chào! Cảm ơn bạn đã quan tâm đến chúng tôi!\n\nTôi là trợ lý AI, sẵn sàng hỗ trợ bạn 24/7 về:\n📚 Thông tin khóa học\n📅 Lịch học và bài tập\n❓ Giải đáp thắc mắc\n\nBạn cần hỗ trợ gì, hãy nhắn tin cho tôi nhé!",
                'description' => 'Tin nhan chao mung khi nguoi dung quan tam OA',
                'updated_at'  => $now,
            ],
            [
                'key'         => 'image_reply',
                'value'       => '📷 Cảm ơn bạn đã gửi hình ảnh! Hiện tại tôi chỉ có thể xử lý tin nhắn văn bản. Bạn hãy mô tả câu hỏi bằng chữ để tôi hỗ trợ nhé!',
                'description' => 'Tra loi khi nhan duoc hinh anh',
                'updated_at'  => $now,
            ],
            [
                'key'         => 'transfer_keywords',
                'value'       => 'nhân viên,tư vấn viên,gặp người thật,hỗ trợ trực tiếp',
                'description' => 'Tu khoa de chuyen sang nhan vien (phan cach bang dau phay)',
                'updated_at'  => $now,
            ],
            [
                'key'         => 'transfer_message',
                'value'       => "🙋 Tôi đã ghi nhận yêu cầu của bạn và sẽ kết nối bạn với nhân viên hỗ trợ ngay!\n\n⏰ Thời gian làm việc: 8:00 - 22:00\nNhân viên sẽ phản hồi bạn trong thời gian sớm nhất.",
                'description' => 'Tin nhan khi chuyen sang nhan vien',
                'updated_at'  => $now,
            ],
            [
                'key'         => 'contact_hotline',
                'value'       => '1800 xxxx',
                'description' => 'So hotline ho tro',
                'updated_at'  => $now,
            ],
            [
                'key'         => 'contact_email',
                'value'       => 'support@example.com',
                'description' => 'Email ho tro',
                'updated_at'  => $now,
            ],
            [
                'key'         => 'contact_hours',
                'value'       => '8:00 - 22:00 mỗi ngày',
                'description' => 'Gio lam viec',
                'updated_at'  => $now,
            ],
        ];

        $this->db->table('settings')->insertBatch($settings);
    }
}
