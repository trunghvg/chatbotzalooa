# ZaloOA Chatbot × Claude AI

Ứng dụng PHP CodeIgniter 4 tích hợp **Zalo Official Account** với **Claude AI (Anthropic)** để tự động trả lời tin nhắn học viên 24/7.

---

## Tính năng

| Tính năng | Mô tả |
|-----------|-------|
| 🤖 **Auto Reply** | Claude AI tự động trả lời mọi tin nhắn văn bản |
| 💬 **Lịch sử hội thoại** | Nhớ context 10 tin nhắn gần nhất mỗi người |
| 👋 **Chào mừng tự động** | Gửi tin nhắn khi học viên quan tâm OA |
| 🔄 **Auto Refresh Token** | Tự động làm mới Zalo Access Token khi hết hạn |
| 👤 **Chuyển sang NV** | Nhận diện từ khóa và chuyển sang nhân viên |
| 📊 **Admin Panel** | Dashboard quản lý hội thoại, tin nhắn, cài đặt |
| ✏️ **Custom System Prompt** | Tùy chỉnh tính cách AI qua giao diện web |
| 🔑 **Zalo OAuth** | Lấy token qua OAuth 2.0 + PKCE |

---

## Yêu cầu

- PHP >= 8.1
- MySQL >= 5.7 / MariaDB >= 10.3
- Composer
- HTTPS domain (Zalo yêu cầu HTTPS cho webhook)
- Tài khoản [Zalo OA Developer](https://developers.zalo.me/)
- API key [Anthropic Claude](https://console.anthropic.com/)

---

## Cài đặt

### 1. Clone & cài dependencies

```bash
git clone <repo-url> chatbotzalooa
cd chatbotzalooa
composer install
```

### 2. Cấu hình môi trường

```bash
cp .env.example .env
nano .env
```

Điền đầy đủ các giá trị:

```ini
# Zalo OA (https://developers.zalo.me/)
ZALO_APP_ID      = 123456789
ZALO_APP_SECRET  = your_app_secret
ZALO_OA_ID       = your_oa_id
ZALO_ACCESS_TOKEN  = <lấy từ bước OAuth bên dưới>
ZALO_REFRESH_TOKEN = <lấy từ bước OAuth bên dưới>

# Claude AI (https://console.anthropic.com/)
CLAUDE_API_KEY  = sk-ant-api03-...
CLAUDE_MODEL    = claude-opus-4-7
CLAUDE_MAX_TOKENS = 2048

# Database
database.default.hostname = localhost
database.default.database = chatbotzalooa
database.default.username = root
database.default.password = your_password

# Admin
ADMIN_USERNAME = admin
ADMIN_PASSWORD = Admin@YourSecurePassword
```

### 3. Tạo database

```bash
# Option A: Dùng script SQL
mysql -u root -p < database/setup.sql

# Option B: Dùng CodeIgniter migrate
php spark migrate
php spark db:seed DefaultSettings
```

### 4. Cấu hình web server

**Apache** (copy `public/.htaccess` đã có sẵn):
```apache
# DocumentRoot trỏ vào thư mục public/
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/chatbotzalooa/public
    <Directory /path/to/chatbotzalooa/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx**:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/chatbotzalooa/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Phân quyền thư mục

```bash
chmod -R 775 writable/
chown -R www-data:www-data writable/
```

---

## Kết nối Zalo OA

### Lấy Access Token (OAuth 2.0)

1. Truy cập **Admin Panel** → **Kết nối Zalo OA**
2. Đăng nhập bằng tài khoản Zalo chủ OA
3. Cấp quyền → hệ thống tự lưu token vào DB

### Cấu hình Webhook tại Zalo Developer Console

1. Vào [developers.zalo.me](https://developers.zalo.me) → chọn app → **Official Account**
2. Tab **Webhook** → URL: `https://yourdomain.com/webhook`
3. Chọn các sự kiện:
   - ✅ `user_send_text`
   - ✅ `user_send_image`
   - ✅ `follow`
   - ✅ `unfollow`
4. Lưu → Zalo gửi GET với `challenge` → hệ thống tự xác minh

---

## Admin Panel

Truy cập: `https://yourdomain.com/auth/login`

| Trang | URL | Chức năng |
|-------|-----|-----------|
| Dashboard | `/admin` | Thống kê, biểu đồ, thao tác nhanh |
| Hội thoại | `/admin/conversations` | Danh sách và chi tiết hội thoại |
| Tin nhắn | `/admin/messages` | Nhật ký toàn bộ tin nhắn |
| System Prompt | `/admin/prompt` | Chỉnh nhân cách Claude AI |
| Cài đặt | `/admin/settings` | Cấu hình bot và liên hệ |

---

## Kiến trúc

```
Học viên (Zalo)
    │
    ▼
Zalo OA Server
    │  POST /webhook
    ▼
Webhook Controller
    │  Verify signature
    │  Parse event
    ├─► handleTextMessage()
    │       │  findOrCreate conversation
    │       │  saveMessage (user)
    │       │  checkTransferKeywords
    │       │
    │       ▼
    │   ClaudeAI::chat()
    │       │  Load conversation history
    │       │  POST api.anthropic.com/v1/messages
    │       │
    │       ▼
    │   AI Response
    │       │  saveMessage (assistant)
    │       │
    │       ▼
    │   ZaloOA::sendTextMessage()
    │       │  POST openapi.zalo.me/v3.0/oa/message/cs
    │       ▼
    └─► Học viên nhận phản hồi ✅
```

---

## Biến môi trường

| Biến | Mô tả | Ví dụ |
|------|-------|-------|
| `ZALO_APP_ID` | ID ứng dụng Zalo | `1234567890` |
| `ZALO_APP_SECRET` | Secret key ứng dụng | `abc...xyz` |
| `ZALO_OA_ID` | ID Official Account | `123456789012` |
| `ZALO_ACCESS_TOKEN` | Token gửi tin nhắn | `...` |
| `ZALO_REFRESH_TOKEN` | Token làm mới | `...` |
| `CLAUDE_API_KEY` | Anthropic API key | `sk-ant-...` |
| `CLAUDE_MODEL` | Model Claude AI | `claude-opus-4-7` |
| `CLAUDE_MAX_TOKENS` | Tối đa tokens mỗi reply | `2048` |
| `ADMIN_USERNAME` | Tên đăng nhập admin | `admin` |
| `ADMIN_PASSWORD` | Mật khẩu admin | `Admin@123` |

---

## License

MIT © 2024
