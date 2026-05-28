<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder nhap du lieu tu file Excel:
 * "Tổng hợp văn bản - Lê Chân TTCT.xlsx"
 *
 * Gom 5 sheet:
 * 1. Tổng quan văn bản
 * 2. QĐ 360 - Tổ chức TTCT cấp xã
 * 3. QĐ 1147 - Quy chế bồi dưỡng
 * 4. QĐ 700 - Đề án bồi dưỡng CBCC
 * 5. QĐ 04 - TTCT phường Lê Chân
 */
class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            // ============================================================
            // CATEGORY: tong-quan
            // ============================================================
            [
                'title'           => 'Tổng quan các văn bản về Trung tâm Chính trị phường - xã',
                'category'        => 'tong-quan',
                'source_document' => 'Tổng hợp',
                'sort_order'      => 1,
                'keywords'        => 'trung tâm chính trị, TTCT, văn bản, quy định, phường lê chân, tổng quan',
                'content'         => <<<CONTENT
Hệ thống văn bản pháp lý điều chỉnh hoạt động Trung tâm Chính trị (TTCT) phường Lê Chân gồm:

1. **QĐ 04-QĐ/ĐU** (01/7/2025) - Ban Chấp hành Đảng bộ phường Lê Chân: Quyết định thành lập TTCT phường Lê Chân. Hiệu lực từ 01/7/2025.

2. **QĐ 360-QĐ/TW** (29/8/2025) - Ban Bí thư Trung ương: Quy định chức năng, nhiệm vụ, tổ chức bộ máy trung tâm chính trị xã, phường, đặc khu. Hiệu lực từ ngày ký.

3. **QĐ 1147-QĐ/BTGDVTW** (01/12/2025) - Trưởng Ban Tuyên giáo và Dân vận TW: Ban hành Quy chế bồi dưỡng, giảng dạy của TTCT xã, phường, đặc khu.

4. **QĐ 700/QĐ-TTg** (20/4/2026) - Thủ tướng Chính phủ: Phê duyệt Đề án tăng cường bồi dưỡng CBCC cấp xã giai đoạn 2026-2031. Mục tiêu: 100% CBCC cấp xã được bồi dưỡng.

5. **QĐ 100-QĐ/TW** (28/02/2023) - Ban Bí thư TW: Quy định về trách nhiệm, quyền hạn và việc bổ nhiệm lãnh đạo nhà xuất bản (thay thế QĐ 282-QĐ/TW năm 2010).
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],

            // ============================================================
            // CATEGORY: to-chuc-bo-may (QĐ 360)
            // ============================================================
            [
                'title'           => 'Vị trí và chức năng TTCT cấp xã (QĐ 360, Điều 1)',
                'category'        => 'to-chuc-bo-may',
                'source_document' => 'QĐ 360-QĐ/TW ngày 29/8/2025',
                'sort_order'      => 10,
                'keywords'        => 'vị trí, chức năng, trung tâm chính trị, đảng ủy, sự nghiệp, bồi dưỡng, lý luận chính trị',
                'content'         => <<<CONTENT
**Vị trí:** TTCT cấp xã là đơn vị sự nghiệp trực thuộc đảng ủy xã, đặt dưới sự lãnh đạo, chỉ đạo của đảng ủy cấp xã nơi có trụ sở; trực tiếp và thường xuyên là Ban Thường vụ Đảng ủy.

**Chức năng:** Tổ chức bồi dưỡng lý luận chính trị cho đảng viên, đoàn viên, hội viên; cập nhật kiến thức, kỹ năng, nghiệp vụ công tác cho cán bộ, công chức, viên chức các cơ quan trong hệ thống chính trị cấp xã, phường, đặc khu được phân công.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Nhiệm vụ của TTCT cấp xã (QĐ 360, Điều 2)',
                'category'        => 'to-chuc-bo-may',
                'source_document' => 'QĐ 360-QĐ/TW ngày 29/8/2025',
                'sort_order'      => 11,
                'keywords'        => 'nhiệm vụ, mác lênin, tư tưởng hồ chí minh, đảng viên mới, cảm tình đảng, bí thư chi bộ, tập huấn, thời sự, lịch sử đảng',
                'content'         => <<<CONTENT
TTCT cấp xã có 06 nhiệm vụ chính:

1. Tổ chức bồi dưỡng chủ nghĩa Mác-Lênin, tư tưởng HCM, quan điểm, chủ trương, đường lối của Đảng, pháp luật Nhà nước; bồi dưỡng lý luận chính trị cho **đảng viên mới**, **nhận thức về Đảng** (cảm tình Đảng); **nghiệp vụ công tác đảng** cho cấp ủy viên cơ sở và bí thư chi bộ.

2. Tổ chức tập huấn kỹ năng lãnh đạo, quản lý, điều hành; cập nhật kiến thức về công tác xây dựng Đảng, quản lý nhà nước, Mặt trận, các tổ chức chính trị - xã hội.

3. Phổ biến, giáo dục **lịch sử Đảng** và lịch sử đảng bộ địa phương.

4. Tổ chức thông tin về **tình hình thời sự**, chính sách của Trung ương, tỉnh, thành phố, xã và bồi dưỡng nghiệp vụ cho đội ngũ báo cáo viên, tuyên truyền viên.

5. Tham gia nghiên cứu lý luận, tổng kết thực tiễn ở cơ sở.

6. Thực hiện một số nhiệm vụ khác theo sự chỉ đạo, phân công của cấp ủy cấp tỉnh và đảng ủy cấp xã.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Tổ chức bộ máy, biên chế và cơ sở vật chất TTCT (QĐ 360, Điều 3-4)',
                'category'        => 'to-chuc-bo-may',
                'source_document' => 'QĐ 360-QĐ/TW ngày 29/8/2025',
                'sort_order'      => 12,
                'keywords'        => 'giám đốc, phó giám đốc, biên chế, giảng viên, kiêm nhiệm, con dấu, tài khoản, trụ sở, giấy chứng nhận',
                'content'         => <<<CONTENT
**Nhân sự:** Gồm Giám đốc, 01 Phó Giám đốc, giảng viên và viên chức. (Các TTCT hiện có trên 1 phó giám đốc thì giữ nguyên số lượng, sau 5 năm thực hiện đúng quy định).

**Biên chế:** Do Ban Thường vụ Đảng ủy cấp xã nơi có trụ sở TTCT xem xét, quyết định trên cơ sở tổng biên chế được Ban Thường vụ cấp ủy cấp tỉnh giao.

**Giảng viên kiêm nhiệm:** TTCT cấp xã được thực hiện chế độ giảng viên kiêm nhiệm để phục vụ công tác giảng dạy.

**Cơ sở vật chất:** TTCT cấp xã có trụ sở làm việc, cơ sở vật chất, **con dấu riêng**, **tài khoản riêng** và được cấp kinh phí hoạt động theo quy định.

**Giấy chứng nhận:** Sử dụng mẫu giấy chứng nhận theo hướng dẫn của Trung ương. Giám đốc TTCT chịu trách nhiệm (hoặc ủy quyền) ký và cấp giấy chứng nhận.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],

            // ============================================================
            // CATEGORY: quy-che-boi-duong (QĐ 1147)
            // ============================================================
            [
                'title'           => 'Đối tượng bồi dưỡng tại TTCT (QĐ 1147, Điều 3)',
                'category'        => 'quy-che-boi-duong',
                'source_document' => 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
                'sort_order'      => 20,
                'keywords'        => 'đối tượng, học viên, cán bộ, đảng viên, công chức, viên chức, báo cáo viên, tuyên truyền viên, đoàn viên, hội viên, thôn, tổ dân phố',
                'content'         => <<<CONTENT
Đối tượng được bồi dưỡng tại TTCT bao gồm:
- Cán bộ, **đảng viên**, công chức, viên chức
- Báo cáo viên, tuyên truyền viên
- Đoàn viên, hội viên
- Người hoạt động không chuyên trách ở thôn, tổ dân phố

Thuộc các cơ quan, đơn vị, tổ chức trong **hệ thống chính trị ở xã, phường, đặc khu**.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Chương trình bồi dưỡng tại TTCT (QĐ 1147, Điều 4)',
                'category'        => 'quy-che-boi-duong',
                'source_document' => 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
                'sort_order'      => 21,
                'keywords'        => 'chương trình, lớp học, kết nạp đảng, đảng viên mới, bí thư chi bộ, tuyên giáo, dân vận, mặt trận, chuyên đề, kỹ năng lãnh đạo, thời sự',
                'content'         => <<<CONTENT
TTCT tổ chức 05 nhóm chương trình bồi dưỡng:

1. **Bồi dưỡng nhận thức về Đảng** (lớp đối tượng kết nạp Đảng - cảm tình Đảng).

2. **Bồi dưỡng đảng viên mới**.

3. **Bồi dưỡng chuyên môn, nghiệp vụ:**
   - Nghiệp vụ công tác đảng cho bí thư chi bộ và cấp ủy viên
   - Nghiệp vụ tuyên giáo và dân vận
   - Lý luận chính trị và nghiệp vụ cho cán bộ Mặt trận và đoàn thể

4. **Bồi dưỡng chuyên đề:** Chủ nghĩa yêu nước, lịch sử Đảng, tư tưởng HCM, đạo đức cách mạng, dân tộc - tôn giáo, lý luận đổi mới...

5. **Tập huấn kỹ năng** lãnh đạo, quản lý; cập nhật kiến thức xây dựng Đảng, quản lý nhà nước; lịch sử Đảng địa phương; thông tin thời sự, chủ trương, chính sách.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Tổ chức lớp học (QĐ 1147, Điều 5)',
                'category'        => 'quy-che-boi-duong',
                'source_document' => 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
                'sort_order'      => 22,
                'keywords'        => 'tổ chức lớp, sĩ số, học viên, chủ nhiệm, giáo vụ, ban cán sự, lớp trưởng, lớp phó, 25 học viên',
                'content'         => <<<CONTENT
Quy định tổ chức lớp học tại TTCT:
- Mỗi lớp tối thiểu **25 học viên**.
- Phân công **01 giảng viên làm chủ nhiệm** lớp.
- Phân công **01 giảng viên/viên chức làm giáo vụ**.
- Thành lập **ban cán sự lớp** gồm: 01 lớp trưởng và 01-02 lớp phó.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Đánh giá kết quả học tập - Bài thu hoạch (QĐ 1147, Điều 6)',
                'category'        => 'quy-che-boi-duong',
                'source_document' => 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
                'sort_order'      => 23,
                'keywords'        => 'bài thu hoạch, điểm, xếp loại, xuất sắc, giỏi, khá, trung bình, không đạt, 80%, dự học, hoàn thành, đánh giá kết quả',
                'content'         => <<<CONTENT
Quy định đánh giá kết quả học tập tại TTCT:

**Hình thức:** Làm **01 bài thu hoạch cuối khóa** (thang điểm 10).

**Điều kiện dự thi:** Học viên phải dự tối thiểu **80% số tiết** mới đủ điều kiện viết thu hoạch.

**Kết quả:**
- Từ **5,0 điểm trở lên:** Hoàn thành khóa học
- Dưới **5,0 điểm:** Không đạt, được làm lại **01 lần**

**Xếp loại:**
- **5,0 - dưới 7,0:** Trung bình
- **7,0 - dưới 8,0:** Khá
- **8,0 - dưới 9,0:** Giỏi
- **9,0 - 10,0:** Xuất sắc
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Cấp giấy chứng nhận hoàn thành khóa học (QĐ 1147, Điều 9)',
                'category'        => 'quy-che-boi-duong',
                'source_document' => 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
                'sort_order'      => 24,
                'keywords'        => 'giấy chứng nhận, cấp giấy, 15 ngày, sổ gốc, lưu trữ, vĩnh viễn, đóng dấu giáp lai, một lần',
                'content'         => <<<CONTENT
Quy định cấp giấy chứng nhận hoàn thành khóa học:

- **Thời hạn cấp:** 15 ngày kể từ ngày ký quyết định công nhận hoàn thành khóa học.
- **Số lần cấp:** Chỉ cấp **một lần** (trừ trường hợp lỗi in ấn).
- **Lưu trữ:** Phải lập sổ gốc cấp giấy chứng nhận, đánh số trang, đóng dấu giáp lai, **lưu trữ vĩnh viễn**.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Định mức giờ chuẩn giảng viên (QĐ 1147, Điều 13)',
                'category'        => 'quy-che-boi-duong',
                'source_document' => 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
                'sort_order'      => 25,
                'keywords'        => 'định mức, giờ chuẩn, giảng viên, 270 giờ, giám đốc, phó giám đốc, chấm bài, ra đề, kiêm nhiệm',
                'content'         => <<<CONTENT
Định mức giờ chuẩn giảng dạy hằng năm:

- **Giảng viên chuyên trách:** 270 giờ/năm học (tương đương 810 giờ hành chính), trong đó ít nhất **50% là giờ dạy trực tiếp**.
- **Giám đốc:** Dạy theo tỷ lệ **10% định mức** (≈27 giờ/năm).
- **Phó Giám đốc:** Dạy theo tỷ lệ **20% định mức** (≈54 giờ/năm).

**Quy đổi giờ chuẩn:**
- Chấm **06 bài thu hoạch = 01 giờ chuẩn**.
- Ra đề thu hoạch (gồm đề, đáp án, thang điểm): **1,5 giờ chuẩn/01 đề**.

**Giảng viên kiêm nhiệm:** Ưu tiên người có kinh nghiệm thực tế và khả năng truyền đạt. Chọn từ: cấp ủy viên cấp xã; trưởng/phó các phòng ban ngành, Mặt trận, đoàn thể cấp xã; giảng viên trường chính trị tỉnh, thành phố; công chức các sở, ban, ngành cấp tỉnh.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Kiểm tra, giám sát hoạt động TTCT (QĐ 1147, Điều 15)',
                'category'        => 'quy-che-boi-duong',
                'source_document' => 'QĐ 1147-QĐ/BTGDVTW ngày 01/12/2025',
                'sort_order'      => 26,
                'keywords'        => 'kiểm tra, giám sát, ban tuyên giáo, định kỳ, đột xuất, tỉnh ủy, thành ủy',
                'content'         => <<<CONTENT
Ban Tuyên giáo và Dân vận tỉnh ủy, thành ủy thực hiện **kiểm tra định kỳ hoặc đột xuất** về công tác bồi dưỡng tại Trung tâm Chính trị.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],

            // ============================================================
            // CATEGORY: de-an-boi-duong (QĐ 700)
            // ============================================================
            [
                'title'           => 'Mục tiêu Đề án bồi dưỡng CBCC cấp xã 2026-2031 (QĐ 700)',
                'category'        => 'de-an-boi-duong',
                'source_document' => 'QĐ 700/QĐ-TTg ngày 20/4/2026',
                'sort_order'      => 30,
                'keywords'        => 'đề án, mục tiêu, 100%, bồi dưỡng, CBCC, cấp xã, 2026, 2031, chính quyền 2 cấp, công chức chuyên môn',
                'content'         => <<<CONTENT
**Đối tượng & phạm vi:** Cán bộ, công chức đang công tác trong cơ quan của Đảng, HĐND, UBND ở xã, phường, đặc khu trên toàn quốc.

**Thời gian:** Từ năm 2026 đến hết năm 2031.

**Mục tiêu giai đoạn 2026-2028:**
- **100%** CBCC cấp xã chưa đáp ứng yêu cầu chuyên môn được cử đi bồi dưỡng kiến thức chuyên môn chuyên ngành (khóa 3 tháng).
- **50%** công chức chuyên môn được bồi dưỡng kiến thức chung về quản lý nhà nước đáp ứng yêu cầu vận hành chính quyền 2 cấp.
- **100%** CBCC được bồi dưỡng, tập huấn chuyên môn, nghiệp vụ theo ngành, lĩnh vực.

**Mục tiêu giai đoạn 2029-2031:**
- Tiếp tục bồi dưỡng kiến thức chuyên môn, kỹ năng lãnh đạo, quản lý.
- **100%** CBCC cấp xã được bổ sung kiến thức, kỹ năng, nghiệp vụ mới đến hết năm 2031.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Nội dung và phương thức bồi dưỡng (QĐ 700)',
                'category'        => 'de-an-boi-duong',
                'source_document' => 'QĐ 700/QĐ-TTg ngày 20/4/2026',
                'sort_order'      => 31,
                'keywords'        => 'nội dung bồi dưỡng, 3 tháng, quản lý nhà nước, kỹ năng lãnh đạo, tập huấn, đấu thầu, đất đai, chuyển đổi số, trực tuyến, cụm liên xã',
                'content'         => <<<CONTENT
**Nội dung bồi dưỡng chính (QĐ 700):**
1. Kiến thức chuyên môn chuyên ngành (khóa **3 tháng**)
2. Kiến thức chung về quản lý nhà nước đáp ứng chính quyền 2 cấp (**2 tuần**)
3. Kỹ năng lãnh đạo, quản lý, điều hành (theo Chương trình HVCTQG HCM)
4. Tập huấn chuyên môn, nghiệp vụ, kỹ năng (**2-5 ngày/khóa**)
5. **Ưu tiên:** lập quy hoạch, quản lý dự án, tài chính, đấu thầu, đất đai, an sinh xã hội, kinh tế số, chuyển đổi số, khiếu nại tố cáo...

**Phương thức:**
- Bồi dưỡng tại chỗ hoặc theo cụm liên xã
- Trực tiếp, **trực tuyến** hoặc kết hợp
- Theo chuyên đề, nhóm lĩnh vực chuyên môn
- Hỗ trợ tại chỗ giữa CBCC giàu kinh nghiệm với CBCC trẻ
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title'           => 'Đánh giá chất lượng sau bồi dưỡng (QĐ 700)',
                'category'        => 'de-an-boi-duong',
                'source_document' => 'QĐ 700/QĐ-TTg ngày 20/4/2026',
                'sort_order'      => 32,
                'keywords'        => 'đánh giá chất lượng, phần mềm, báo cáo kết quả, 3 tháng, xếp loại, quy hoạch, bổ nhiệm, tinh giản biên chế',
                'content'         => <<<CONTENT
Quy định đánh giá chất lượng sau bồi dưỡng (QĐ 700):

- **100%** CBCC được cử đi bồi dưỡng phải báo cáo kết quả qua **Phần mềm đánh giá và quản lý chất lượng bồi dưỡng** (Bộ Nội vụ xây dựng).
- Đánh giá CBCC sau bồi dưỡng **ít nhất 3 tháng** gắn với kết quả thực thi công vụ.
- Kết quả đánh giá dùng trong: **xếp loại hàng năm**, quy hoạch, bổ nhiệm, luân chuyển hoặc **tinh giản biên chế**.
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],

            // ============================================================
            // CATEGORY: ttct-le-chan (QĐ 04)
            // ============================================================
            [
                'title'           => 'Thành lập TTCT phường Lê Chân (QĐ 04-QĐ/ĐU)',
                'category'        => 'ttct-le-chan',
                'source_document' => 'QĐ 04-QĐ/ĐU ngày 01/7/2025',
                'sort_order'      => 40,
                'keywords'        => 'thành lập, phường lê chân, hải phòng, đảng ủy, con dấu, trụ sở, hiệu lực, đặng đông anh, bí thư',
                'content'         => <<<CONTENT
**Quyết định thành lập Trung tâm Chính trị phường Lê Chân** do Ban Chấp hành Đảng bộ phường Lê Chân (thành phố Hải Phòng) ban hành ngày 01/7/2025.

**Nội dung chính:**
- **Điều 1:** TTCT phường Lê Chân là **đơn vị sự nghiệp** của Đảng ủy phường Lê Chân. Được sử dụng **con dấu riêng**, được bố trí trụ sở và trang bị cơ sở vật chất.
- **Điều 2:** Giao Ban Thường vụ Đảng ủy phường ban hành quyết định **bổ nhiệm Giám đốc, Phó Giám đốc** TTCT theo quy định về phân cấp quản lý cán bộ.
- **Điều 3:** TTCT phường có trách nhiệm **tham mưu Ban Thường vụ Đảng ủy** phường xây dựng, ban hành **Quy chế làm việc** của TTCT phường.
- **Điều 4:** TTCT phường và các cơ quan liên quan chịu trách nhiệm thi hành.

**Hiệu lực:** Từ ngày **01/7/2025**

**Ký ban hành:** **Đặng Đông Anh** - Bí thư Đảng bộ phường Lê Chân

**Căn cứ pháp lý:**
- Nghị quyết 60-NQ/TW (12/4/2025)
- Nghị quyết 1669/NQ-UBTVQH15
- QĐ 298-QĐ/TW (09/6/2025)
- QĐ 1893-QĐ/TU, QĐ 1898-QĐ/TU (20/6/2025 của Đảng bộ TP Hải Phòng)
- Kết luận 01-KL/ĐU (01/7/2025)
CONTENT,
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
        ];

        // Xoa du lieu cu neu co
        $this->db->table('knowledge_base')->emptyTable();

        // Nhap du lieu moi
        $this->db->table('knowledge_base')->insertBatch($data);

        echo "KnowledgeBaseSeeder: Da nhap " . count($data) . " muc kien thuc.\n";
    }
}
