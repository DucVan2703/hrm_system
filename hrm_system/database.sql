-- ============================================
-- HỆ THỐNG QUẢN LÝ NHÂN SỰ VÀ TIỀN LƯƠNG
-- Database: hrm_system
-- ============================================

-- CREATE DATABASE IF NOT EXISTS hrm_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE hrm_system;

-- ============================================
-- BẢNG PHÒNG BAN
-- ============================================
CREATE TABLE IF NOT EXISTS phong_ban (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_pb VARCHAR(20) NOT NULL UNIQUE,
    ten_pb VARCHAR(100) NOT NULL,
    mo_ta TEXT,
    truong_phong INT DEFAULT NULL,
    trang_thai TINYINT(1) DEFAULT 1,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- BẢNG CHỨC VỤ
-- ============================================
CREATE TABLE IF NOT EXISTS chuc_vu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_cv VARCHAR(20) NOT NULL UNIQUE,
    ten_cv VARCHAR(100) NOT NULL,
    mo_ta TEXT,
    he_so_luong DECIMAL(5,2) DEFAULT 1.00,
    trang_thai TINYINT(1) DEFAULT 1,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- BẢNG NHÂN VIÊN
-- ============================================
CREATE TABLE IF NOT EXISTS nhan_vien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_nv VARCHAR(20) NOT NULL UNIQUE,
    ho_ten VARCHAR(100) NOT NULL,
    ngay_sinh DATE,
    gioi_tinh ENUM('Nam','Nữ','Khác') DEFAULT 'Nam',
    cccd VARCHAR(20),
    so_dien_thoai VARCHAR(15),
    email VARCHAR(100),
    dia_chi TEXT,
    id_phong_ban INT,
    id_chuc_vu INT,
    luong_co_ban DECIMAL(15,2) DEFAULT 0,
    ngay_vao_lam DATE,
    trang_thai ENUM('Đang làm','Nghỉ việc','Tạm nghỉ') DEFAULT 'Đang làm',
    hinh_anh VARCHAR(255) DEFAULT NULL,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_phong_ban) REFERENCES phong_ban(id) ON DELETE SET NULL,
    FOREIGN KEY (id_chuc_vu) REFERENCES chuc_vu(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- BẢNG TÀI KHOẢN
-- ============================================
CREATE TABLE IF NOT EXISTS tai_khoan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_dang_nhap VARCHAR(50) NOT NULL UNIQUE,
    mat_khau VARCHAR(255) NOT NULL,
    id_nhan_vien INT DEFAULT NULL,
    vai_tro ENUM('admin','hr','ketoan','nhanvien') DEFAULT 'nhanvien',
    trang_thai TINYINT(1) DEFAULT 1,
    lan_dang_nhap_cuoi TIMESTAMP NULL,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_nhan_vien) REFERENCES nhan_vien(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- BẢNG HỢP ĐỒNG LAO ĐỘNG
-- ============================================
CREATE TABLE IF NOT EXISTS hop_dong (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ma_hd VARCHAR(30) NOT NULL UNIQUE,
    id_nhan_vien INT NOT NULL,
    loai_hop_dong ENUM('Thử việc','Xác định thời hạn','Không xác định thời hạn') DEFAULT 'Xác định thời hạn',
    ngay_bat_dau DATE NOT NULL,
    ngay_ket_thuc DATE DEFAULT NULL,
    luong_hop_dong DECIMAL(15,2) DEFAULT 0,
    trang_thai ENUM('Đang hiệu lực','Hết hạn','Đã kết thúc','Gia hạn') DEFAULT 'Đang hiệu lực',
    ghi_chu TEXT,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_nhan_vien) REFERENCES nhan_vien(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- BẢNG CHẤM CÔNG
-- ============================================
CREATE TABLE IF NOT EXISTS cham_cong (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nhan_vien INT NOT NULL,
    thang INT NOT NULL,
    nam INT NOT NULL,
    so_ngay_lam DECIMAL(5,1) DEFAULT 0,
    so_ngay_nghi INT DEFAULT 0,
    so_ngay_phep INT DEFAULT 0,
    so_ngay_vang INT DEFAULT 0,
    so_gio_tang_ca DECIMAL(5,1) DEFAULT 0,
    ghi_chu TEXT,
    nguoi_cap_nhat INT DEFAULT NULL,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cham_cong (id_nhan_vien, thang, nam),
    FOREIGN KEY (id_nhan_vien) REFERENCES nhan_vien(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- BẢNG PHỤ CẤP
-- ============================================
CREATE TABLE IF NOT EXISTS phu_cap (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_phu_cap VARCHAR(100) NOT NULL,
    ma_pc VARCHAR(20) NOT NULL UNIQUE,
    so_tien DECIMAL(15,2) DEFAULT 0,
    mo_ta TEXT,
    trang_thai TINYINT(1) DEFAULT 1,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- BẢNG LƯƠNG
-- ============================================
CREATE TABLE IF NOT EXISTS bang_luong (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nhan_vien INT NOT NULL,
    thang INT NOT NULL,
    nam INT NOT NULL,
    luong_co_ban DECIMAL(15,2) DEFAULT 0,
    so_ngay_lam DECIMAL(5,1) DEFAULT 0,
    so_ngay_chuan INT DEFAULT 26,
    luong_theo_cong DECIMAL(15,2) DEFAULT 0,
    phu_cap_an_trua DECIMAL(15,2) DEFAULT 0,
    phu_cap_xang_xe DECIMAL(15,2) DEFAULT 0,
    phu_cap_khac DECIMAL(15,2) DEFAULT 0,
    thuong_kpi DECIMAL(15,2) DEFAULT 0,
    thuong_khac DECIMAL(15,2) DEFAULT 0,
    phat_di_muon DECIMAL(15,2) DEFAULT 0,
    khau_tru_khac DECIMAL(15,2) DEFAULT 0,
    bao_hiem_xa_hoi DECIMAL(15,2) DEFAULT 0,
    bao_hiem_y_te DECIMAL(15,2) DEFAULT 0,
    thue_tncn DECIMAL(15,2) DEFAULT 0,
    tong_thu_nhap DECIMAL(15,2) DEFAULT 0,
    tong_khau_tru DECIMAL(15,2) DEFAULT 0,
    thuc_linh DECIMAL(15,2) DEFAULT 0,
    trang_thai ENUM('Nháp','Đã duyệt','Đã thanh toán') DEFAULT 'Nháp',
    ghi_chu TEXT,
    nguoi_tao INT DEFAULT NULL,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_luong (id_nhan_vien, thang, nam),
    FOREIGN KEY (id_nhan_vien) REFERENCES nhan_vien(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- BẢNG ĐƠN NGHỈ PHÉP
-- ============================================
CREATE TABLE IF NOT EXISTS don_nghi_phep (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nhan_vien INT NOT NULL,
    ngay_bat_dau DATE NOT NULL,
    ngay_ket_thuc DATE NOT NULL,
    so_ngay INT DEFAULT 1,
    ly_do TEXT,
    loai_phep ENUM('Phép năm','Phép không lương','Phép ốm','Phép cưới','Phép tang') DEFAULT 'Phép năm',
    trang_thai ENUM('Chờ duyệt','Đã duyệt','Từ chối') DEFAULT 'Chờ duyệt',
    nguoi_duyet INT DEFAULT NULL,
    ghi_chu_duyet TEXT,
    ngay_gui TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_duyet TIMESTAMP NULL,
    FOREIGN KEY (id_nhan_vien) REFERENCES nhan_vien(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DỮ LIỆU MẪU
-- ============================================

-- Phòng ban
INSERT INTO phong_ban (ma_pb, ten_pb, mo_ta) VALUES
('PB001', 'Ban Giám Đốc', 'Ban lãnh đạo công ty'),
('PB002', 'Phòng Nhân Sự', 'Quản lý nhân sự và tuyển dụng'),
('PB003', 'Phòng Kế Toán', 'Quản lý tài chính và kế toán'),
('PB004', 'Phòng Kỹ Thuật', 'Phát triển và vận hành hệ thống'),
('PB005', 'Phòng Kinh Doanh', 'Bán hàng và phát triển thị trường'),
('PB006', 'Phòng Marketing', 'Quảng bá và truyền thông');

-- Chức vụ
INSERT INTO chuc_vu (ma_cv, ten_cv, mo_ta, he_so_luong) VALUES
('CV001', 'Giám Đốc', 'Giám đốc điều hành', 5.00),
('CV002', 'Phó Giám Đốc', 'Phó giám đốc', 4.00),
('CV003', 'Trưởng Phòng', 'Trưởng phòng ban', 3.00),
('CV004', 'Phó Phòng', 'Phó phòng ban', 2.50),
('CV005', 'Nhân Viên Cao Cấp', 'Nhân viên cao cấp', 2.00),
('CV006', 'Nhân Viên', 'Nhân viên chính thức', 1.50),
('CV007', 'Thực Tập Sinh', 'Thực tập sinh', 0.80);

-- Nhân viên mẫu
INSERT INTO nhan_vien (ma_nv, ho_ten, ngay_sinh, gioi_tinh, cccd, so_dien_thoai, email, dia_chi, id_phong_ban, id_chuc_vu, luong_co_ban, ngay_vao_lam, trang_thai) VALUES
('NV001', 'Nguyễn Văn An', '1985-03-15', 'Nam', '001085003456', '0912345678', 'an.nguyen@company.vn', 'Hà Nội', 1, 1, 25000000, '2020-01-01', 'Đang làm'),
('NV002', 'Trần Thị Bình', '1990-07-22', 'Nữ', '001090007890', '0923456789', 'binh.tran@company.vn', 'Hà Nội', 2, 3, 15000000, '2020-03-01', 'Đang làm'),
('NV003', 'Lê Văn Cường', '1992-11-08', 'Nam', '001092011234', '0934567890', 'cuong.le@company.vn', 'Hà Nội', 3, 3, 14000000, '2020-06-01', 'Đang làm'),
('NV004', 'Phạm Thị Dung', '1995-05-30', 'Nữ', '001095005678', '0945678901', 'dung.pham@company.vn', 'Hà Nội', 4, 6, 12000000, '2021-01-15', 'Đang làm'),
('NV005', 'Hoàng Văn Em', '1993-09-12', 'Nam', '001093009012', '0956789012', 'em.hoang@company.vn', 'Hà Nội', 5, 6, 11000000, '2021-03-01', 'Đang làm'),
('NV006', 'Vũ Thị Phương', '1997-01-25', 'Nữ', '001097001234', '0967890123', 'phuong.vu@company.vn', 'Hà Nội', 6, 6, 10000000, '2022-01-01', 'Đang làm'),
('NV007', 'Đặng Văn Giang', '1988-06-14', 'Nam', '001088006789', '0978901234', 'giang.dang@company.vn', 'Hà Nội', 4, 5, 13000000, '2019-05-01', 'Đang làm'),
('NV008', 'Bùi Thị Hoa', '1996-08-28', 'Nữ', '001096008901', '0989012345', 'hoa.bui@company.vn', 'Hà Nội', 2, 6, 9500000, '2022-06-01', 'Đang làm'),
('NV009', 'Ngô Văn Inh', '1991-12-03', 'Nam', '001091012345', '0990123456', 'inh.ngo@company.vn', 'Hà Nội', 5, 4, 13500000, '2020-09-01', 'Nghỉ việc'),
('NV010', 'Đinh Thị Kiều', '1994-04-17', 'Nữ', '001094004567', '0901234567', 'kieu.dinh@company.vn', 'Hà Nội', 3, 6, 10500000, '2021-07-01', 'Đang làm');

-- Tài khoản
INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, id_nhan_vien, vai_tro) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin'),
('hr_binh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'hr'),
('kt_cuong', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'ketoan'),
('nv_dung', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'nhanvien'),
('nv_em', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 'nhanvien'),
('nv_phuong', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 'nhanvien');
-- Mật khẩu mặc định: password

-- Hợp đồng
INSERT INTO hop_dong (ma_hd, id_nhan_vien, loai_hop_dong, ngay_bat_dau, ngay_ket_thuc, luong_hop_dong, trang_thai) VALUES
('HD001', 1, 'Không xác định thời hạn', '2020-01-01', NULL, 25000000, 'Đang hiệu lực'),
('HD002', 2, 'Xác định thời hạn', '2020-03-01', '2022-02-28', 15000000, 'Hết hạn'),
('HD003', 2, 'Không xác định thời hạn', '2022-03-01', NULL, 15000000, 'Đang hiệu lực'),
('HD004', 3, 'Xác định thời hạn', '2020-06-01', '2022-05-31', 14000000, 'Hết hạn'),
('HD005', 3, 'Không xác định thời hạn', '2022-06-01', NULL, 14000000, 'Đang hiệu lực'),
('HD006', 4, 'Xác định thời hạn', '2021-01-15', '2023-01-14', 12000000, 'Hết hạn'),
('HD007', 4, 'Không xác định thời hạn', '2023-01-15', NULL, 12000000, 'Đang hiệu lực'),
('HD008', 5, 'Xác định thời hạn', '2021-03-01', '2023-02-28', 11000000, 'Đang hiệu lực');

-- Chấm công tháng 4/2025
INSERT INTO cham_cong (id_nhan_vien, thang, nam, so_ngay_lam, so_ngay_nghi, so_ngay_phep, so_ngay_vang, so_gio_tang_ca) VALUES
(1, 4, 2025, 26, 0, 0, 0, 8),
(2, 4, 2025, 25, 1, 1, 0, 4),
(3, 4, 2025, 24, 2, 2, 0, 0),
(4, 4, 2025, 26, 0, 0, 0, 2),
(5, 4, 2025, 23, 3, 1, 2, 0),
(6, 4, 2025, 25, 1, 0, 1, 0),
(7, 4, 2025, 26, 0, 0, 0, 6),
(8, 4, 2025, 22, 4, 2, 2, 0);

-- Chấm công tháng 5/2025
INSERT INTO cham_cong (id_nhan_vien, thang, nam, so_ngay_lam, so_ngay_nghi, so_ngay_phep, so_ngay_vang, so_gio_tang_ca) VALUES
(1, 5, 2025, 26, 0, 0, 0, 10),
(2, 5, 2025, 26, 0, 0, 0, 5),
(3, 5, 2025, 25, 1, 1, 0, 0),
(4, 5, 2025, 24, 2, 2, 0, 3),
(5, 5, 2025, 26, 0, 0, 0, 0),
(6, 5, 2025, 23, 3, 1, 2, 0),
(7, 5, 2025, 26, 0, 0, 0, 8),
(8, 5, 2025, 25, 1, 1, 0, 0);

-- Phụ cấp
INSERT INTO phu_cap (ten_phu_cap, ma_pc, so_tien, mo_ta) VALUES
('Phụ cấp ăn trưa', 'PC001', 730000, 'Phụ cấp ăn trưa hàng tháng (26 ngày x 28.000đ)'),
('Phụ cấp xăng xe', 'PC002', 500000, 'Phụ cấp đi lại bằng phương tiện cá nhân'),
('Phụ cấp điện thoại', 'PC003', 300000, 'Phụ cấp sử dụng điện thoại công việc'),
('Phụ cấp nhà ở', 'PC004', 1000000, 'Phụ cấp nhà ở cho nhân viên ngoại tỉnh'),
('Phụ cấp độc hại', 'PC005', 400000, 'Phụ cấp làm việc trong môi trường độc hại');

-- Bảng lương tháng 4/2025
INSERT INTO bang_luong (id_nhan_vien, thang, nam, luong_co_ban, so_ngay_lam, so_ngay_chuan, luong_theo_cong, phu_cap_an_trua, phu_cap_xang_xe, phu_cap_khac, thuong_kpi, thuong_khac, phat_di_muon, bao_hiem_xa_hoi, bao_hiem_y_te, thue_tncn, tong_thu_nhap, tong_khau_tru, thuc_linh, trang_thai) VALUES
(1, 4, 2025, 25000000, 26, 26, 25000000, 730000, 500000, 0, 2000000, 0, 0, 2125000, 375000, 1500000, 28230000, 4000000, 24230000, 'Đã thanh toán'),
(2, 4, 2025, 15000000, 25, 26, 14423077, 730000, 500000, 0, 1000000, 0, 100000, 1272500, 225000, 0, 16653077, 1597500, 15055577, 'Đã thanh toán'),
(3, 4, 2025, 14000000, 24, 26, 12923077, 730000, 500000, 0, 800000, 0, 0, 1190000, 210000, 0, 14953077, 1400000, 13553077, 'Đã thanh toán'),
(4, 4, 2025, 12000000, 26, 26, 12000000, 730000, 500000, 0, 500000, 0, 50000, 1020000, 180000, 0, 13730000, 1200000, 12530000, 'Đã thanh toán'),
(5, 4, 2025, 11000000, 23, 26, 9730769, 730000, 500000, 0, 0, 0, 200000, 935000, 165000, 0, 10960769, 1100000, 9860769, 'Đã thanh toán'),
(6, 4, 2025, 10000000, 25, 26, 9615385, 730000, 500000, 0, 300000, 0, 100000, 850000, 150000, 0, 11145385, 1000000, 10145385, 'Đã thanh toán'),
(7, 4, 2025, 13000000, 26, 26, 13000000, 730000, 500000, 0, 1500000, 0, 0, 1105000, 195000, 0, 15730000, 1300000, 14430000, 'Đã thanh toán'),
(8, 4, 2025, 9500000, 22, 26, 8038462, 730000, 500000, 0, 0, 0, 150000, 807500, 142500, 0, 9268462, 950000, 8318462, 'Đã thanh toán');
