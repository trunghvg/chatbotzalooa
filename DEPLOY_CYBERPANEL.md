# Hướng dẫn cài đặt trên CyberPanel (OpenLiteSpeed)

Hướng dẫn cài thủ công chatbot lên server CyberPanel của riêng bạn, độc lập với Railway.

---

## 1. Yêu cầu

- CyberPanel đã cài (OpenLiteSpeed + MySQL/MariaDB)
- PHP **8.1+** (khuyên dùng 8.3) với các extension:
  `mysqli, mbstring, curl, zip, gd, intl, xml`
- (Tùy chọn) `poppler-utils` để import PDF: `apt install poppler-utils`
- Composer

---

## 2. Tạo Website trong CyberPanel

1. Đăng nhập CyberPanel → **Websites → Create Website**
2. Nhập domain (vd: `chatbot.yourdomain.com`), chọn PHP 8.3
3. Sau khi tạo, vào **Manage → File Manager**

---

## 3. Tải code lên

```bash
# SSH vào server
cd /home/chatbot.yourdomain.com/

# Clone code (hoặc upload zip qua File Manager rồi giải nén)
git clone https://github.com/trunghvg/chatbotzalooa.git tmp
mv tmp/* tmp/.* public_html/ 2>/dev/null
rm -rf tmp

cd public_html

# Cài dependencies
composer install --no-dev --optimize-autoloader
```

> **Quan trọng:** Document Root phải trỏ vào thư mục `public/`.
> Trong CyberPanel: **Websites → Manage → vHost Conf**, sửa
> `docRoot` thành `.../public_html/public`

---

## 4. Tạo Database

1. CyberPanel → **Databases → Create Database**
2. Ghi nhớ: tên DB, username, password
3. Import schema:

```bash
mysql -u DB_USER -p DB_NAME < database/setup.sql
```

Hoặc dùng **phpMyAdmin** trong CyberPanel để import file `database/setup.sql`.

---

## 5. Cấu hình .env

```bash
cp .env.example .env
nano .env
```

Điền các giá trị:

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://chatbot.yourdomain.com/'

# Database (dùng đúng format này cho CyberPanel)
database.default.hostname = localhost
database.default.database = ten_db_cua_ban
database.default.username = user_db_cua_ban
database.default.password = mat_khau_db
database.default.DBDriver = MySQLi
database.default.port = 3306

# Zalo OA
ZALO_APP_ID = ...
ZALO_APP_SECRET = ...
ZALO_OA_ID = ...

# Claude AI
CLAUDE_API_KEY = sk-ant-api03-...
CLAUDE_MODEL = claude-sonnet-4-6
CLAUDE_MAX_TOKENS = 2048

# Admin
ADMIN_USERNAME = admin
ADMIN_PASSWORD = mat_khau_manh
```

---

## 6. Phân quyền thư mục

```bash
cd /home/chatbot.yourdomain.com/public_html
chmod -R 755 .
chmod -R 775 writable/
chown -R lsadm:lsadm writable/   # user của OpenLiteSpeed

# Tạo thư mục upload nếu chưa có
mkdir -p writable/uploads/docs writable/logs writable/cache writable/session
chmod -R 775 writable/
```

---

## 7. Bật URL Rewrite

OpenLiteSpeed hỗ trợ `.htaccess` (đã có sẵn trong `public/.htaccess`).
Đảm bảo trong **vHost Conf** có:

```
rewrite  {
  enable                  1
  autoLoadHtaccess        1
}
```

---

## 8. Cài SSL

CyberPanel → **SSL → Manage SSL** → chọn website → **Issue SSL** (Let's Encrypt)

---

## 9. Kết nối Zalo OA

1. Truy cập `https://chatbot.yourdomain.com/` → đăng nhập admin
2. Vào **Kết nối Zalo OA** để lấy access token mới
3. Trong **Zalo Developer Console**:
   - Cập nhật **Webhook URL**: `https://chatbot.yourdomain.com/webhook`
   - Cập nhật **Callback URL** (OAuth): `https://chatbot.yourdomain.com/zalo/callback`
   - Xác thực domain (nếu cần): upload file xác thực vào `public/`

---

## 10. Kiểm tra

- Truy cập `https://chatbot.yourdomain.com/webhook` → trả về JSON `{"status":"ok",...}`
- Nhắn tin cho OA → bot trả lời
- Vào Dashboard xem token usage

---

## Ghi chú kỹ thuật

- **Webhook bất đồng bộ:** Code tự động dùng `litespeed_finish_request()` trên
  LiteSpeed (hoặc `fastcgi_finish_request()` trên PHP-FPM) để trả 200 cho Zalo
  ngay lập tức rồi mới gọi Claude AI trong nền — tránh timeout.
- **Không cần Docker / nginx.conf / railway-*.** Các file này chỉ dùng cho Railway,
  có thể bỏ qua khi chạy CyberPanel.
- **Backup:** sao lưu định kỳ DB + thư mục `writable/uploads/`.
- **Cron làm mới token Zalo (tùy chọn):** Zalo access token hết hạn sau ~1 giờ,
  refresh token sau 3 tháng. Có thể thêm cron gọi refresh, hoặc hệ thống tự
  refresh khi gặp lỗi -216.
