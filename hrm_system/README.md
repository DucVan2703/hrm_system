# Hệ thống Quản lý Nhân sự & Tiền lương - Đại Học Thành Đông

Hệ thống Quản lý Nhân sự và Tiền lương (HRM & Payroll System) chuyên nghiệp dành cho Cán bộ Giảng viên Đại Học Thành Đông. Ứng dụng được xây dựng trên cấu trúc framework **PHP MVC tự thiết kế (Custom PHP MVC)** gọn nhẹ, an toàn, dễ bảo trì và mở rộng.

---

## 🌟 Tính năng chính

### 1. Phân hệ Nhân viên (Hồ sơ tự phục vụ)
- **Hồ sơ cá nhân**: Cán bộ nhân viên tự kiểm tra thông tin cá nhân, tự cập nhật số điện thoại, email, địa chỉ liên lạc.
- **Tải lên tài liệu**: Cho phép tải ảnh thẻ chân dung làm avatar đại diện và chữ ký điện tử trực tuyến (tích hợp cơ chế tự phục hồi, lưu tệp dự phòng khi hosting bị chặn quyền ghi thư mục con).
- **Gửi đơn nghỉ phép trực tuyến**: Tự chọn khoảng ngày nghỉ (hệ thống tự tính toán tổng số ngày), nhập lý do và gửi trực tiếp tới bộ phận Nhân sự để chờ duyệt. Theo dõi trạng thái duyệt đơn theo thời gian thực dưới dạng badge màu trực quan.

### 2. Phân hệ Quản trị & Nhân sự (Admin / HR)
- **Quản lý danh sách nhân viên**: Thêm mới, sửa, xóa thông tin cán bộ giảng viên (CCCD, giới tính, ngày sinh, phòng ban, chức vụ, mức lương cơ bản, ngày vào làm...).
- **Quản lý Hợp đồng lao động**: Tạo mới hợp đồng, tự động quản lý thời hạn hiệu lực, trạng thái ký kết.
- **Xét duyệt Đơn nghỉ phép**: Trang duyệt đơn tập trung dành cho Admin/HR. Duyệt/Từ chối đơn xin nghỉ và đính kèm phản hồi/ghi chú duyệt.

### 3. Phân hệ Chấm công & Kế toán (Admin / Kế toán)
- **Bảng chấm công**: Chấm công chi tiết hàng tháng cho từng nhân viên (số ngày làm việc thực tế, ngày nghỉ phép, ngày vắng mặt không phép, số giờ tăng ca).
- **Bảng tính lương tự động**: Tự động tính toán lương thực lĩnh dựa trên công thức quy chuẩn:
  $$\text{Lương thực lĩnh} = \text{Lương ngày công} + \text{Phụ cấp} + \text{Tăng ca} - \text{Bảo hiểm (BHXH, BHYT, BHTN)} - \text{Thuế TNCN}$$
- **Quản lý phiếu lương**: Tạo, in và phê duyệt bảng lương tháng, cho phép xuất chi tiết thu nhập gửi tới từng nhân viên.

### 4. Trợ lý ảo AI (Chatbot)
- Trợ lý giải đáp thông tin tự động (`chatbot.php` / `troly.php`) tích hợp ngay trên giao diện.
- Giúp tra cứu nhanh danh bạ cán bộ, quy định nghỉ phép, công thức tính lương, chế độ bảo hiểm và giờ làm việc hành chính.

---

## 🏗️ Kiến trúc mã nguồn (Custom MVC)

Dự án áp dụng mô hình thiết kế Model-View-Controller thuần túy:

- **Bộ định tuyến (Router)**: Tệp [core/Router.php](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/core/Router.php) phân tích đường dẫn URL động (Dynamic URL Path) và gọi Controller / Action tương ứng.
- **Controllers**: Nằm ở thư mục [app/controllers/](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/app/controllers/) xử lý logic nghiệp vụ và dữ liệu đầu vào.
- **Models**: Nằm ở thư mục [app/models/](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/app/models/) tương tác trực tiếp với cơ sở dữ liệu (sử dụng thư viện PDO chuẩn hóa để chống SQL Injection).
- **Views**: Thư mục [app/views/](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/app/views/) chứa giao diện HTML/CSS, kế thừa khung layout chung (sidebar, header).

---

## 🛠️ Công nghệ sử dụng
1. **Back-end**: PHP (Phiên bản khuyến nghị: 7.4 - 8.2), kết nối PDO MySQL.
2. **Front-end**: HTML5, CSS3 nguyên bản (Vanilla CSS), JavaScript thuần (Vanilla JS).
3. **Icons & Fonts**: FontAwesome v6.5, Google Fonts (Outfit & Inter).

---

## ⚙️ Hướng dẫn cài đặt dự án

### Bước 1: Thiết lập Cơ sở dữ liệu (Database)
1. Tạo một cơ sở dữ liệu trống trên MySQL (ví dụ: `quanlyluong`).
2. Nhập (Import) tệp cấu trúc dữ liệu mẫu [database.sql](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/database.sql) vào cơ sở dữ liệu vừa tạo.

### Bước 2: Cấu hình Kết nối Cơ sở dữ liệu
Dự án có 2 tệp tin cấu hình kết nối cần được cập nhật đồng bộ các thông tin kết nối (Host, Username, Password, Database Name):
1. **Dành cho luồng chạy MVC chính**: Cấu hình trong tệp [config/db_config.php](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/config/db_config.php)
2. **Dành cho các công cụ chatbot, trợ lý độc lập**: Cấu hình trong tệp [config/database.php](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/config/database.php)

---

## 🚀 Hướng dẫn triển khai dự án lên Hosting thực tế (Ví dụ: Awardspace)

Để đưa trang web hoạt động trên Internet bằng dịch vụ Hosting miễn phí/trả phí của **Awardspace**, hãy thực hiện tuần tự theo hướng dẫn chi tiết dưới đây:

### 1. Tạo và Nhập cơ sở dữ liệu trên Awardspace
1. Đăng nhập vào trang quản trị (Control Panel) của **Awardspace**.
2. Tìm và chọn công cụ **Database Manager** (Quản lý Cơ sở dữ liệu).
3. Tạo một Database mới bằng cách nhập Tên Database và Mật khẩu kết nối.
4. Sau khi tạo xong, hệ thống sẽ cung cấp các thông số kết nối:
   - **Database Server / Host**: Thường có dạng `fdb1032.awardspace.net`.
   - **Database Name & User**: Thường có tiền tố số (ví dụ: `4762137_quanlyluong`).
5. Click vào nút **phpMyAdmin** bên cạnh Database vừa tạo.
6. Trong phpMyAdmin, click vào tab **Import** (Nhập), nhấn nút **Choose File** để chọn tệp [database.sql](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/database.sql) trên máy tính của bạn, sau đó cuộn xuống nhấn **Import** để tải dữ liệu lên.

### 2. Cấu hình thông số trên mã nguồn cục bộ
Trước khi tải code lên host, bạn cần chỉnh sửa thông số kết nối CSDL trùng với các thông số Awardspace vừa cấp ở bước trên.
- Mở tệp [config/db_config.php](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/config/db_config.php) và điền:
  ```php
  'host' => 'fdb1032.awardspace.net',
  'username' => '4762137_quanlyluong',
  'password' => 'Mật_Khẩu_Của_Bạn',
  'dbname' => '4762137_quanlyluong',
  ```
- Mở tệp [config/database.php](file:///c:/Users/NgoTruong/OneDrive/Desktop/ducvan.id.vn/config/database.php) và cập nhật các hằng số tương tự:
  ```php
  define('DB_HOST', 'fdb1032.awardspace.net');
  define('DB_USER', '4762137_quanlyluong');
  define('DB_PASS', 'Mật_Khẩu_Của_Bạn');
  define('DB_NAME', '4762137_quanlyluong');
  ```

### 3. Tải mã nguồn lên Hosting bằng FTP (FileZilla)
1. Tải và cài đặt phần mềm **FileZilla** trên máy tính của bạn.
2. Trên Awardspace Control Panel, tìm mục **FTP Manager** để lấy thông tin kết nối FTP:
   - **FTP Host / Host**: Ví dụ: `ftp.ducvan.id.vn` hoặc địa chỉ IP của host.
   - **FTP Username / User**: Tên đăng nhập FTP của bạn.
   - **FTP Password**: Mật khẩu tài khoản Awardspace.
   - **Port**: `21`.
3. Mở **FileZilla**, điền các thông tin trên vào thanh kết nối nhanh (Quickconnect) và nhấn **Quickconnect**.
4. Ở khung bên phải (Remote Site - Máy chủ), truy cập vào thư mục trùng với tên miền của bạn (ví dụ: `/ducvan.id.vn/`).
5. Ở khung bên trái (Local Site - Máy tính), tìm đến thư mục chứa mã nguồn dự án này.
6. Chọn tất cả các file và thư mục của dự án, nhấp chuột phải và chọn **Upload** để tải toàn bộ lên hosting.

### 4. Cấp quyền ghi (Chmod) cho thư mục tải lên tệp tin
Để nhân viên có thể tải lên ảnh đại diện và chữ ký trên server thực tế, thư mục uploads phải có quyền ghi:
1. Trên FileZilla (khung bên phải), tìm đến thư mục `public/assets/uploads/`.
2. Nhấp chuột phải vào thư mục `uploads/`, chọn **File permissions...** (Quyền của tệp).
3. Tại ô **Numeric value** (Giá trị số), nhập vào **`777`** hoặc **`755`**.
4. Tích chọn vào ô **Recurse into subdirectories** (Áp dụng cho tất cả thư mục con) -> chọn **Apply to all files and directories** -> Nhấn **OK**.

### 5. Chạy kiểm tra chẩn đoán
Sau khi tải lên hoàn tất, bạn hãy truy cập đường dẫn sau trên trình duyệt để chạy chẩn đoán tự động:
```
http://ducvan.id.vn/check_columns.php
```
Trang này sẽ tự động kiểm tra xem kết nối Database đã thông suốt chưa, các cột cần thiết đã tồn tại chưa và thư mục upload tệp tin đã cấp quyền ghi thành công chưa.

---

## 🔑 Tài khoản đăng nhập thử nghiệm (Demo)
Sau khi cài đặt thành công, bạn đăng nhập bằng các tài khoản mặc định dưới đây:
- **Tài khoản Quản trị (Admin / HR)**:
  - Tên đăng nhập: `admin`
  - Mật khẩu: `password`
- **Tài khoản Cán bộ Nhân viên**:
  - Tên đăng nhập: `nv_dung`
  - Mật khẩu: `password`
