<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Tạo bảng cham_cong_chi_tiet nếu chưa có
    $sqlTable = "CREATE TABLE IF NOT EXISTS cham_cong_chi_tiet (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sqlTable);
    echo "Đã tạo bảng 'cham_cong_chi_tiet'.<br>";

    // 2. Chuẩn bị dữ liệu để random
    $ho = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Phan', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô'];
    $dem_nam = ['Văn', 'Hữu', 'Đức', 'Thành', 'Minh', 'Anh', 'Quốc', 'Tuấn', 'Hoàng'];
    $dem_nu = ['Thị', 'Phương', 'Ngọc', 'Diệu', 'Mai', 'Quỳnh', 'Thảo', 'Linh'];
    $ten_nam = ['An', 'Bình', 'Cường', 'Dũng', 'Em', 'Giang', 'Hải', 'Hùng', 'Kiên', 'Long', 'Minh', 'Nam', 'Phúc', 'Quân', 'Sơn', 'Tuấn', 'Việt', 'Tùng', 'Bách', 'Khoa'];
    $ten_nu = ['Anh', 'Bình', 'Chi', 'Diệp', 'Hoa', 'Hương', 'Lan', 'Linh', 'Mai', 'Ngọc', 'Phương', 'Quỳnh', 'Thảo', 'Trang', 'Vân', 'Vy', 'Hà', 'Yến', 'Trâm', 'Diệu'];

    $phong_ban_ids = [1, 2, 3, 4, 5, 6];
    $chuc_vu_ids = [1, 2, 3, 4, 5, 6, 7];
    
    // Hash password mặc định
    $password = password_hash('password', PASSWORD_DEFAULT);

    echo "Bắt đầu thêm 100 nhân viên...<br>";

    for ($i = 1; $i <= 100; $i++) {
        $gioi_tinh_val = (rand(0, 1) == 0) ? 'Nam' : 'Nữ';
        
        $h = $ho[array_rand($ho)];
        if ($gioi_tinh_val == 'Nam') {
            $d = $dem_nam[array_rand($dem_nam)];
            $t = $ten_nam[array_rand($ten_nam)];
        } else {
            $d = $dem_nu[array_rand($dem_nu)];
            $t = $ten_nu[array_rand($ten_nu)];
        }
        
        $ho_ten = "$h $d $t";
        $ma_nv = "NV" . str_pad($i + 10, 3, '0', STR_PAD_LEFT); // Bắt đầu từ NV011
        $email = strtolower(str_replace(' ', '', $t . '.' . $h . $i)) . "@company.vn";
        $sdt = "09" . rand(10000000, 99999999);
        $ngay_sinh = rand(1980, 2004) . "-" . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $id_pb = $phong_ban_ids[array_rand($phong_ban_ids)];
        $id_cv = $chuc_vu_ids[array_rand($chuc_vu_ids)];
        $luong = rand(8, 30) * 1000000;
        $ngay_vao = "2024-" . str_pad(rand(1, 5), 2, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);

        // Chèn nhân viên
        $stmt = $db->prepare("INSERT INTO nhan_vien (ma_nv, ho_ten, ngay_sinh, gioi_tinh, so_dien_thoai, email, id_phong_ban, id_chuc_vu, luong_co_ban, ngay_vao_lam) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ma_nv, $ho_ten, $ngay_sinh, $gioi_tinh_val, $sdt, $email, $id_pb, $id_cv, $luong, $ngay_vao]);
        
        $id_nv = $db->lastInsertId();

        // Chèn tài khoản
        $ten_dang_nhap = strtolower($ma_nv);
        $stmtAccount = $db->prepare("INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, id_nhan_vien, vai_tro) VALUES (?, ?, ?, 'nhanvien')");
        $stmtAccount->execute([$ten_dang_nhap, $password, $id_nv]);
        
        // 3. Thêm dữ liệu chấm công ngẫu nhiên cho 7 ngày qua
        for ($j = 0; $j < 7; $j++) {
            $ngay_cc = date('Y-m-d', strtotime("-$j days"));
            $is_weekend = (date('N', strtotime($ngay_cc)) >= 6);
            if ($is_weekend && rand(0, 5) > 0) continue; // Cuối tuần thường không đi làm

            $gio_vao = str_pad(rand(7, 8), 2, '0', STR_PAD_LEFT) . ":" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ":" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
            $gio_ra = str_pad(rand(17, 18), 2, '0', STR_PAD_LEFT) . ":" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ":" . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
            $trang_thai = ($gio_vao > "08:00:00") ? 'Đi muộn' : 'Đúng giờ';
            
            $stmtCC = $db->prepare("INSERT IGNORE INTO cham_cong_chi_tiet (id_nhan_vien, ngay, gio_vao, gio_ra, trang_thai) VALUES (?, ?, ?, ?, ?)");
            $stmtCC->execute([$id_nv, $ngay_cc, $gio_vao, $gio_ra, $trang_thai]);
        }

        if ($i % 20 == 0) echo "Đã thêm $i nhân viên và dữ liệu chấm công...<br>";
    }


    echo "<strong>Xong! Đã thêm 100 nhân viên và tài khoản. Mật khẩu mặc định là 'password'.</strong>";

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
