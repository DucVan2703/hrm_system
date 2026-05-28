CREATE TABLE IF NOT EXISTS cham_cong_chi_tiet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nhan_vien INT NOT NULL,
    ngay DATE NOT NULL,
    gio_vao TIME,
    gio_ra TIME,
    trang_thai ENUM('Đúng giờ', 'Đi muộn', 'Về sớm', 'Nghỉ') DEFAULT 'Đúng giờ',
    ghi_chu TEXT,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_nv_ngay (id_nhan_vien, ngay),
    FOREIGN KEY (id_nhan_vien) REFERENCES nhan_vien(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
