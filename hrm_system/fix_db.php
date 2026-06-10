<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = getDB();
    
    // 1. Create table cau_hinh
    $sql = "CREATE TABLE IF NOT EXISTS cau_hinh (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ma_cau_hinh VARCHAR(50) NOT NULL UNIQUE,
        gia_tri TEXT,
        mo_ta VARCHAR(255),
        ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql);
    echo "Đã tạo bảng 'cau_hinh' thành công.<br>";
    
    // Insert default data into cau_hinh
    $check = $db->query("SELECT COUNT(*) FROM cau_hinh")->fetchColumn();
    if ($check == 0) {
        $sqlInsert = "INSERT INTO cau_hinh (ma_cau_hinh, gia_tri, mo_ta) VALUES
        ('COMPANY_NAME', 'Đại Học Thành Đông', 'Tên trường'),
        ('COMPANY_ADDRESS', 'Số 3 Vũ Hựu, Thanh Bình, TP Hải Dương', 'Địa chỉ trường'),
        ('COMPANY_PHONE', '0220.389.0606', 'Số điện thoại liên hệ'),
        ('COMPANY_EMAIL', 'tbu@thanhdong.edu.vn', 'Email liên hệ'),
        ('WORKING_DAYS', '26', 'Số ngày làm việc tiêu chuẩn trong tháng'),
        ('CURRENCY', 'VNĐ', 'Đơn vị tiền tệ');";
        
        $db->exec($sqlInsert);
        echo "Đã thêm dữ liệu mẫu vào bảng 'cau_hinh'.<br>";
    } else {
        echo "Bảng 'cau_hinh' đã có dữ liệu.<br>";
    }
    
    // 2. Create table chatbot_faq (Nâng cấp Giám đốc điều khiển)
    // Tự động kiểm tra nếu bảng cũ thiếu cột 'keywords' thì xóa đi tạo lại
    $hasKeywords = false;
    try {
        $db->query("SELECT keywords FROM chatbot_faq LIMIT 1");
        $hasKeywords = true;
    } catch (Exception $e) {
        $hasKeywords = false;
    }

    if (!$hasKeywords) {
        $db->exec("DROP TABLE IF EXISTS chatbot_faq;");
        echo "Đã xóa bảng 'chatbot_faq' cũ để đồng bộ cấu trúc cột mới.<br>";
    }

    $sqlChatbot = "CREATE TABLE IF NOT EXISTS chatbot_faq (
        id INT AUTO_INCREMENT PRIMARY KEY,
        keywords VARCHAR(255) NOT NULL,
        reply TEXT NOT NULL,
        suggestions VARCHAR(255),
        nguoi_tao VARCHAR(100) DEFAULT 'Giám đốc',
        ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sqlChatbot);
    echo "Đã tạo bảng 'chatbot_faq' thành công.<br>";

    // Insert default chatbot rules
    $checkChatbot = $db->query("SELECT COUNT(*) FROM chatbot_faq")->fetchColumn();
    if ($checkChatbot == 0) {
        $sqlInsertChatbot = "INSERT INTO chatbot_faq (keywords, reply, suggestions, nguoi_tao) VALUES
        ('giới thiệu, trợ giúp, hello, chào, chao, tro giup, huong dan, tro ly', '👋 **Chào bạn! Tôi là Trợ lý ảo TDU**.\n\nTôi được Giám đốc cấu hình trực tiếp từ hệ thống quản trị để hỗ trợ cán bộ nhân viên tra cứu nhanh thông tin nhân sự, bảng công, lương thưởng và quy định nội bộ của trường.', '👤 Hồ sơ cá nhân,💰 Lương tháng này,📞 Liên hệ BGĐ/Kế toán,📞 Tra danh bạ', 'Giám đốc'),
        ('giờ làm việc, thời gian làm việc, ca làm, gio lam viec, thoi gian lam viec, ca lam, lich lam viec', '⏰ **QUY ĐỊNH GIỜ LÀM VIỆC HÀNH CHÍNH**\n\n— **Buổi Sáng:** Từ **08:00** đến **11:30**\n— **Buổi Chiều:** Từ **13:30** đến **17:00**\n— **Lịch làm việc:** Từ Thứ Hai đến hết Thứ Sáu và sáng Thứ Bảy.', '⏱️ Chấm công,📞 Tra danh bạ,🏖️ Quy định nghỉ phép', 'Giám đốc'),
        ('quy định nghỉ phép, cách xin nghỉ, đơn phép, nghỉ phép, xin nghỉ, nghi phep, xin nghi, don phep, nghi, phep', '🏖️ **HƯỚNG DẪN THỦ TỤC XIN NGHỈ PHÉP**\n\n1. Vào mục **Xin nghỉ phép** trên thanh Menu trái.\n2. Chọn **Tạo đơn mới**, điền ngày nghỉ và lý do cụ thể.\n3. Đơn sẽ tự động chuyển tới Trưởng phòng ban và Phòng Nhân sự duyệt trực tuyến.', '🏖️ Đơn nghỉ phép,⏱️ Chấm công,⏰ Giờ làm việc', 'Giám đốc'),
        ('cách tính lương, công thức lương, tinh luong, cong thuc luong, cach tinh luong, tinh luong, tinhluong', '🧮 **CÔNG THỨC VÀ QUY CHẾ TÍNH LƯƠNG**\n\n`Lương thực lĩnh = Lương theo công + Phụ cấp + Thưởng - Khấu trừ - Thuế TNCN`\n\nTrong đó, lương theo công được tính dựa trên số ngày đi làm thực tế chia cho 26 ngày công chuẩn.', '💰 Lương tháng này,📅 Lịch sử lương,⏱️ Chấm công', 'Giám đốc'),
        ('bảo hiểm, bhxh, bhyt, bao hiem, bh', '🛡️ **QUY ĐỊNH VỀ CHẾ ĐỘ BẢO HIỂM**\n\n— **Cán bộ đóng:** BHXH (8%), BHYT (1.5%), BHTN (1%) (Khấu trừ 10.5% vào lương).\n— **Nhà trường đóng:** BHXH (17.5%), BHYT (3%), BHTN (1%) (Tổng cộng 21.5% đối ứng hỗ trợ).', '🧮 Cách tính lương,💰 Lương tháng này,👤 Hồ sơ cá nhân', 'Giám đốc');";
        
        $db->exec($sqlInsertChatbot);
        echo "Đã thêm dữ liệu mẫu vào bảng 'chatbot_faq'.<br>";
    } else {
        echo "Bảng 'chatbot_faq' đã có dữ liệu.<br>";
    }
    
    echo "<br><strong>Fix hoàn tất! Bạn có thể xóa file này và quay lại sử dụng hệ thống.</strong>";
    
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?>
