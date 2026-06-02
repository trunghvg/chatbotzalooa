# Hướng dẫn cài đặt trên CyberPanel (OpenLiteSpeed)

Chạy chatbot độc lập trên server riêng — không phụ thuộc Railway.

---

## Yêu cầu

- CyberPanel (OpenLiteSpeed + MySQL/MariaDB)
- PHP **8.1+** (khuyên 8.3) với extension: `mysqli, mbstring, curl, zip, gd, intl, xml`
- Composer
- (Tùy chọn) `poppler-utils` để import file PDF: `apt install poppler-utils`

---

## BƯỚC 0 — Xuất dữ liệu từ Railway (nếu muốn giữ dữ liệu cũ)

1. Đăng nhập admin panel Railway: `https://chatbotzalooa-production.up.railway.app/admin`
2. Sidebar → **Xuất Database (.sql)**
3. Lưu file `.sql` về máy tính

> Nếu cài mới hoàn toàn, bỏ qua bước này.

---

## BƯỚC 1 — Tạo Website trong CyberPanel

1. Đăng nhập CyberPanel → **Websites → Create Website**
2. Nhập domain (vd: `chatbot.phuonglechan.vn`), chọn PHP 8.3
3. Bật SSL: **SSL → Manage SSL → Issue SSL** (Let's Encrypt)

---

## BƯỚC 2 — Tải code lên

SSH vào server:

```bash
cd /home/chatbot.phuonglechan.vn/public_html

# Clone code từ nhánh đang chạy
git clone -b claude/peaceful-pascal-8aEXs https://github.com/trunghvg/chatbotzalooa.git .

# Hoặc nếu muốn merge vào main trước rồi clone main:
# git clone https://github.com/trunghvg/chatbotzalooa.git .

# Cài dependencies PHP
composer install --no-dev --optimize-autoloader
```

**Quan trọng:** Trỏ Document Root vào thư mục `public/`
- CyberPanel → **Websites → Manage → vHost Conf**
- Sửa `docRoot` thành: `.../public_html/public`

---

## BƯỚC 3 — Tạo Database & Import

### Cài mới (database trống):

```bash
mysql -u root -p < database/setup.sql
```

Hoặc mở **phpMyAdmin** trong CyberPanel → import file `database/setup.sql`.

### Di chuyển từ Railway (giữ dữ liệu cũ):

```bash
# Tạo database rỗng trong CyberPanel trước
# CyberPanel → Databases → Create Database

# Import file .sql đã xuất từ Railway
mysql -u DB_USER -p DB_NAME < chatbot_db_YYYYMMDD_HHmmss.sql
```

---

## BƯỚC 4 — Cấu hình .env

```bash
cp .env.example .env
nano .env
```

Điền đầy đủ các thông số:

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://chatbot.phuonglechan.vn/'

# Database
database.default.hostname = localhost
database.default.database = ten_db
database.default.username = user_db
database.default.password = mat_khau_db
database.default.DBDriver = MySQLi
database.default.port = 3306

# Zalo OA (lấy tại https://developers.zalo.me/)
ZALO_APP_ID = 
ZALO_APP_SECRET = 
ZALO_OA_ID = 

# Claude AI (lấy tại https://console.anthropic.com/)
CLAUDE_API_KEY = sk-ant-api03-...
CLAUDE_MODEL = claude-sonnet-4-6
CLAUDE_MAX_TOKENS = 2048

# Admin panel
ADMIN_USERNAME = admin
ADMIN_PASSWORD = mat_khau_manh_o_day

# Cron auto-refresh Zalo token (đặt chuỗi ngẫu nhiên)
CRON_SECRET = abc123xyz789random
```

> **Lưu ý:** Nếu di chuyển từ Railway, các setting nhạy cảm như Zalo token, Claude key đã nằm trong DB (bảng `settings`). Không cần nhập lại vào `.env`.

---

## BƯỚC 5 — Phân quyền thư mục

```bash
cd /home/chatbot.phuonglechan.vn/public_html

chmod -R 755 .
chmod -R 775 writable/
chown -R lsadm:lsadm writable/   # user của OpenLiteSpeed

# Tạo thư mục upload tài liệu
mkdir -p writable/uploads/docs
chmod -R 775 writable/uploads/
```

---

## BƯỚC 6 — Bật URL Rewrite

CyberPanel → **Websites → Manage → Rewrite Rules** → đảm bảo có:

```
rewrite {
    enable              1
    autoLoadHtaccess    1
}
```

File `public/.htaccess` đã có sẵn, không cần chỉnh.

---

## BƯỚC 7 — Kết nối Zalo OA

1. Truy cập `https://chatbot.phuonglechan.vn/` → đăng nhập admin
2. Sidebar → **Kết nối Zalo OA** (hoặc vào `/zalo/authorize`)
3. Đăng nhập tài khoản Zalo quản lý OA → cấp quyền
4. Trong **Zalo Developer Console** ([developers.zalo.me](https://developers.zalo.me)):
   - **Webhook URL**: `https://chatbot.phuonglechan.vn/webhook`
   - **Callback URL**: `https://chatbot.phuonglechan.vn/zalo/callback`

---

## BƯỚC 8 — Cài Cron Job (tự động refresh Zalo token)

Zalo access token hết hạn sau 1 giờ. Cài cron để tự động gia hạn:

CyberPanel → **Cron Jobs → Create Cron Job**:

```
*/30 * * * *    curl -s "https://chatbot.phuonglechan.vn/cron/refresh-token?secret=abc123xyz789random" > /dev/null
```

> Thay `abc123xyz789random` bằng `CRON_SECRET` đã đặt trong `.env`

---

## BƯỚC 9 — Kiểm tra

```bash
# Test webhook
curl https://chatbot.phuonglechan.vn/webhook
# → {"status":"ok","message":"Zalo OA Webhook is running",...}

# Test admin panel
# Truy cập https://chatbot.phuonglechan.vn/ → đăng nhập
```

Nhắn tin cho OA → bot phải trả lời.

---

## Ghi chú kỹ thuật

| Vấn đề | Chi tiết |
|--------|----------|
| Webhook async | Code tự dùng `litespeed_finish_request()` trên LiteSpeed — trả 200 cho Zalo ngay, gọi Claude AI trong nền, tránh timeout |
| Docker/nginx | Chỉ dùng cho Railway, bỏ qua khi chạy CyberPanel |
| PDF import | Cần `apt install poppler-utils` để đọc file PDF |
| Backup | Sao lưu DB + thư mục `writable/uploads/` định kỳ |
| Token Zalo | Hệ thống tự refresh khi gặp lỗi -216; cron job là lớp bảo vệ thêm |
