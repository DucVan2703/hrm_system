<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Hỗ trợ cả nhận JSON và POST Form Data để vượt tường lửa InfinityFree
$data = json_decode(file_get_contents('php://input'), true);
$raw_message = $data['message'] ?? $_POST['message'] ?? '';
$message = mb_strtolower(trim($raw_message));

$reply = "";
$suggestions = [];

// Khởi tạo phản hồi mặc định
$default_reply = "Xin lỗi, tôi chưa hiểu rõ câu hỏi của bạn. 😅\n\nBạn có thể thử hỏi hoặc nhấn chọn các gợi ý bên dưới để tôi hỗ trợ nhé:";

// Kiểm tra phiên đăng nhập
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;
$ho_ten = $_SESSION['ho_ten'] ?? 'bạn';
$vai_tro = $_SESSION['vai_tro'] ?? 'nhanvien';
$id_nv = $_SESSION['id_nhan_vien'] ?? null;

$db = getDB();

// -------------------------------------------------------------
// TRA CỨU CSDL CHATBOT_FAQ DO GIÁM ĐỐC CẤU HÌNH TRỰC TIẾP
// -------------------------------------------------------------
try {
    $stmt = $db->query("SELECT * FROM chatbot_faq");
    $db_faqs = $stmt->fetchAll();
    foreach ($db_faqs as $faq) {
        $keywords = array_map('trim', explode(',', $faq['keywords']));
        foreach ($keywords as $kw) {
            $kw_lower = mb_strtolower($kw);
            if (!empty($kw_lower) && strpos($message, $kw_lower) !== false) {
                $reply = $faq['reply'];
                $suggestions = !empty($faq['suggestions']) ? array_map('trim', explode(',', $faq['suggestions'])) : [];
                
                // Trả về JSON ngay lập tức nếu khớp luật do Giám Đốc cấu hình
                echo json_encode([
                    'reply' => $reply,
                    'suggestions' => $suggestions
                ]);
                exit();
            }
        }
    }
} catch (PDOException $e) {
    // Bỏ qua nếu bảng chưa được tạo hoặc chưa cập nhật CSDL
}

// -------------------------------------------------------------
// CHỨC NĂNG XỬ LÝ Ý ĐỊNH HỘI THOẠI (INTENT SELECTION)
// -------------------------------------------------------------

// 1. CHÀO HỎI & GIỚI THIỆU TRỢ GIÚP
if (empty($message) || preg_match('/^(chào|hi|hello|ơi|bắt đầu|start|help|trợ giúp|cần giúp|hỏi gì)/u', $message)) {
    $reply = "👋 **Chào $ho_ten!** Tôi là **Trợ lý ảo TDU** siêu cấp phiên bản mới.\n\nTôi có thể giúp bạn tra cứu hồ sơ, bảng lương, ngày công, hợp đồng, danh bạ đồng nghiệp và các quy chế nhân sự. Hãy thử nhấn chọn các gợi ý bên dưới nhé:";
    $suggestions = ["👤 Hồ sơ cá nhân", "💰 Lương tháng này", "📞 Liên hệ BGĐ/Kế toán", "📞 Tra danh bạ"];
}

// 2. THÔNG TIN CÁ NHÂN / HỒ SƠ
elseif (preg_match('/(thông tin|hồ sơ|tôi là ai|cá nhân|tài khoản|my profile)/u', $message) && !strpos($message, 'danh bạ') && !strpos($message, 'danh ba')) {
    if ($id_nv) {
        $stmt = $db->prepare("
            SELECT nv.*, pb.ten_pb, cv.ten_cv 
            FROM nhan_vien nv 
            LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id 
            LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id 
            WHERE nv.id = ?
        ");
        $stmt->execute([$id_nv]);
        $nv = $stmt->fetch();

        if ($nv) {
            $reply = "💳 **THÔNG TIN HỒ SƠ CÁN BỘ**\n"
                   . "— **Họ và tên:** " . $nv['ho_ten'] . "\n"
                   . "— **Mã nhân sự:** `" . $nv['ma_nv'] . "`\n"
                   . "— **Phòng ban:** " . ($nv['ten_pb'] ?? 'Chưa xếp khoa/phòng') . "\n"
                   . "— **Chức vụ:** " . ($nv['ten_cv'] ?? 'Chưa xếp chức danh') . "\n"
                   . "— **Email nội bộ:** " . ($nv['email'] ?? 'Chưa cập nhật') . "\n"
                   . "— **Số điện thoại:** " . ($nv['so_dien_thoai'] ?? 'Chưa cập nhật') . "\n"
                   . "— **Ngày vào trường:** " . date('d/m/Y', strtotime($nv['ngay_vao_lam'])) . "\n"
                   . "— **Lương cơ bản:** **" . number_format($nv['luong_co_ban']) . " VNĐ**";
        } else {
            $reply = "Không tìm thấy hồ sơ cán bộ tương ứng với tài khoản của bạn trên hệ thống.";
        }
    } else {
        $reply = "Bạn đang đăng nhập bằng tài khoản Quản trị hệ thống (`" . $username . "`). Vai trò: **" . strtoupper($vai_tro) . "**. Tài khoản này không có hồ sơ nhân sự liên kết.";
    }
    $suggestions = ["💰 Lương tháng này", "📄 Hợp đồng của tôi", "⏱️ Chấm công"];
}

// 3. HỢP ĐỒNG LAO ĐỘNG CỦA BẢN THÂN
elseif (preg_match('/(hợp đồng|hop dong|xem hợp đồng|hợp đồng lao động)/u', $message)) {
    if ($id_nv) {
        $stmt = $db->prepare("
            SELECT * 
            FROM hop_dong 
            WHERE id_nhan_vien = ? AND trang_thai = 'Đang hiệu lực' 
            LIMIT 1
        ");
        $stmt->execute([$id_nv]);
        $hd = $stmt->fetch();

        if ($hd) {
            $reply = "📄 **HỢP ĐỒNG LAO ĐỘNG CỦA BẠN**\n"
                   . "— **Số hợp đồng:** `" . $hd['ma_hd'] . "`\n"
                   . "— **Loại hợp đồng:** " . $hd['loai_hop_dong'] . "\n"
                   . "— **Ngày bắt đầu:** " . date('d/m/Y', strtotime($hd['ngay_bat_dau'])) . "\n"
                   . "— **Ngày hết hạn:** " . ($hd['ngay_ket_thuc'] ? date('d/m/Y', strtotime($hd['ngay_ket_thuc'])) : 'Hợp đồng không xác định thời hạn') . "\n"
                   . "— **Lương hợp đồng:** **" . number_format($hd['luong_hop_dong']) . " VNĐ**\n"
                   . "— **Trạng thái:** 🟢 **" . $hd['trang_thai'] . "**";
        } else {
            $reply = "Hệ thống chưa ghi nhận hợp đồng lao động nào đang có hiệu lực của bạn.";
        }
    } else {
        $reply = "Vui lòng đăng nhập bằng tài khoản nhân viên để tra cứu thông tin hợp đồng.";
    }
    $suggestions = ["👤 Hồ sơ cá nhân", "💰 Lương tháng này", "⏱️ Chấm công"];
}

// 4. LƯƠNG THÁNG NÀY & CHI TIẾT
elseif (preg_match('/(lương tháng này|chi tiết lương|xem lương|phiếu lương|luong)/u', $message) && !strpos($message, 'lịch sử') && !strpos($message, 'lịch sử lương')) {
    if ($id_nv) {
        $stmt = $db->prepare("
            SELECT * 
            FROM bang_luong 
            WHERE id_nhan_vien = ? 
            ORDER BY nam DESC, thang DESC 
            LIMIT 1
        ");
        $stmt->execute([$id_nv]);
        $luong = $stmt->fetch();

        if ($luong) {
            $reply = "💵 **CHI TIẾT PHIẾU LƯƠNG THÁNG " . $luong['thang'] . "/" . $luong['nam'] . "**\n"
                   . "— **Lương cơ bản:** " . number_format($luong['luong_co_ban']) . " VNĐ\n"
                   . "— **Ngày công thực tế:** " . $luong['so_ngay_lam'] . " / " . $luong['so_ngay_chuan'] . " ngày công\n"
                   . "— **Lương tính theo công:** " . number_format($luong['luong_theo_cong']) . " VNĐ\n"
                   . "— **Tổng các phụ cấp:** " . number_format($luong['phu_cap_an_trua'] + $luong['phu_cap_xang_xe'] + $luong['phu_cap_khac']) . " VNĐ *(Ăn trưa, xăng xe, khác)*\n"
                   . "— **Tiền thưởng (KPI/Khác):** " . number_format($luong['thuong_kpi'] + $luong['thuong_khac']) . " VNĐ\n"
                   . "— **Khấu trừ bảo hiểm:** " . number_format($luong['bao_hiem_xa_hoi'] + $luong['bao_hiem_y_te']) . " VNĐ\n"
                   . "— **Thuế TNCN tạm tính:** " . number_format($luong['thue_tncn']) . " VNĐ\n"
                   . "— **Khấu trừ khác (Phạt đi muộn...):** " . number_format($luong['phat_di_muon'] + $luong['khau_tru_khac']) . " VNĐ\n"
                   . "────────────────\n"
                   . "💰 **THỰC LĨNH THÁNG NÀY:** **" . number_format($luong['thuc_linh']) . " VNĐ**\n"
                   . "*(Trạng thái: **" . $luong['trang_thai'] . "**)*";
        } else {
            $reply = "Hiện tại chưa có dữ liệu bảng lương tháng mới nhất dành cho bạn trên hệ thống.";
        }
    } else {
        $reply = "Bạn vui lòng đăng nhập tài khoản nhân viên để tra cứu phiếu lương cá nhân.";
    }
    $suggestions = ["📅 Lịch sử lương", "⏱️ Chấm công", "🏖️ Đơn nghỉ phép"];
}

// 5. LỊCH SỬ LƯƠNG
elseif (preg_match('/(lịch sử lương|lịch sử thu nhập|lương 3 tháng|tra cứu lương)/u', $message)) {
    if ($id_nv) {
        $stmt = $db->prepare("
            SELECT thang, nam, thuc_linh, trang_thai 
            FROM bang_luong 
            WHERE id_nhan_vien = ? 
            ORDER BY nam DESC, thang DESC 
            LIMIT 3
        ");
        $stmt->execute([$id_nv]);
        $luongs = $stmt->fetchAll();

        if ($luongs) {
            $reply = "📊 **LỊCH SỬ THU NHẬP 3 THÁNG GẦN NHẤT**\n\n";
            foreach ($luongs as $l) {
                $reply .= "• **Tháng " . $l['thang'] . "/" . $l['nam'] . ":** **" . number_format($l['thuc_linh']) . " VNĐ** | *" . $l['trang_thai'] . "*\n";
            }
        } else {
            $reply = "Không tìm thấy dữ liệu lịch sử bảng lương của bạn trên hệ thống.";
        }
    } else {
        $reply = "Vui lòng đăng nhập tài khoản nhân viên để xem lịch sử nhận lương.";
    }
    $suggestions = ["💰 Lương tháng này", "⏱️ Chấm công", "🏖️ Đơn nghỉ phép"];
}

// 6. CHẤM CÔNG CỦA TÔI
elseif (preg_match('/(chấm công|ngày công|đi làm|tăng ca|di muon)/u', $message)) {
    if ($id_nv) {
        $stmt = $db->prepare("
            SELECT * 
            FROM cham_cong 
            WHERE id_nhan_vien = ? 
            ORDER BY nam DESC, thang DESC 
            LIMIT 1
        ");
        $stmt->execute([$id_nv]);
        $cc = $stmt->fetch();

        if ($cc) {
            $reply = "📅 **CHI TIẾT CÔNG LÀM VIỆC THÁNG " . $cc['thang'] . "/" . $cc['nam'] . "**\n"
                   . "— **Số ngày công làm việc thực tế:** **" . $cc['so_ngay_lam'] . " ngày**\n"
                   . "— **Số ngày công nghỉ hưởng phép:** " . $cc['so_ngay_phep'] . " ngày\n"
                   . "— **Số ngày nghỉ có lý do:** " . $cc['so_ngay_nghi'] . " ngày\n"
                   . "— **Số ngày vắng không phép:** " . $cc['so_ngay_vang'] . " ngày\n"
                   . "— **Số giờ làm tăng ca:** " . $cc['so_gio_tang_ca'] . " giờ\n"
                   . "— **Ghi chú chấm công:** *" . ($cc['ghi_chu'] ?: 'Không có ghi chú') . "*";
        } else {
            $reply = "Hệ thống chưa ghi nhận dữ liệu chấm công tháng này của bạn.";
        }
    } else {
        $reply = "Bạn vui lòng đăng nhập tài khoản nhân viên để tra cứu dữ liệu chấm công.";
    }
    $suggestions = ["💰 Lương tháng này", "🏖️ Đơn nghỉ phép", "👤 Hồ sơ cá nhân"];
}

// 7. ĐƠN XIN NGHỈ PHÉP
elseif (preg_match('/(nghỉ phép|đơn phép|xin nghỉ|phe nam)/u', $message)) {
    if ($id_nv) {
        $stmt = $db->prepare("
            SELECT ngay_bat_dau, ngay_ket_thuc, so_ngay, ly_do, loai_phep, trang_thai 
            FROM don_nghi_phep 
            WHERE id_nhan_vien = ? 
            ORDER BY ngay_gui DESC 
            LIMIT 3
        ");
        $stmt->execute([$id_nv]);
        $pheps = $stmt->fetchAll();

        if ($pheps) {
            $reply = "🏖️ **TRẠNG THÁI ĐƠN XIN NGHỈ PHÉP GẦN ĐÂY**\n\n";
            foreach ($pheps as $p) {
                $status_icon = "🟡";
                if ($p['trang_thai'] === 'Đã duyệt') $status_icon = "🟢";
                if ($p['trang_thai'] === 'Từ chối') $status_icon = "🔴";
                
                $reply .= "• **" . $p['loai_phep'] . " (" . $p['so_ngay'] . " ngày):** Từ ngày " . date('d/m/Y', strtotime($p['ngay_bat_dau'])) . " đến " . date('d/m/Y', strtotime($p['ngay_ket_thuc'])) . "\n"
                       . "   Lý do nghỉ: *" . $p['ly_do'] . "*\n"
                       . "   Trạng thái đơn: " . $status_icon . " **" . $p['trang_thai'] . "**\n\n";
            }
        } else {
            $reply = "Bạn chưa gửi đơn xin nghỉ phép nào gần đây trên hệ thống.";
        }
    } else {
        $reply = "Bạn vui lòng đăng nhập tài khoản nhân viên để xem lịch sử nghỉ phép.";
    }
    $suggestions = ["💡 Quy định nghỉ phép", "⏱️ Chấm công", "💰 Lương tháng này"];
}

// 8. TÌM KIẾM DANH BẠ ĐỒNG NGHIỆP (Độc đáo & Cực kỳ hữu ích!)
elseif (preg_match('/(danh bạ|danh ba|tìm nhân viên|tìm kiếm|liên hệ|sđt|email của)/u', $message)) {
    // Trích xuất từ khóa tìm kiếm nếu có (ví dụ: "danh bạ an" hoặc "tìm dung")
    $search_term = "";
    $words = explode(" ", $raw_message);
    // Lấy các từ sau từ khóa chính
    if (count($words) > 1) {
        $key_indexes = [];
        foreach ($words as $idx => $word) {
            $w_lower = mb_strtolower($word);
            if (in_array($w_lower, ['danh', 'bạ', 'ba', 'tìm', 'kiếm', 'liên', 'hệ', 'sđt', 'email'])) {
                $key_indexes[] = $idx;
            }
        }
        $search_words = [];
        foreach ($words as $idx => $word) {
            if (!in_array($idx, $key_indexes)) {
                $search_words[] = $word;
            }
        }
        $search_term = trim(implode(" ", $search_words));
    }

    if (empty($search_term)) {
        $reply = "📞 **TRA CỨU DANH BẠ NỘI BỘ TDU**\n\n"
               . "Bạn có thể tra cứu nhanh số điện thoại và email của đồng nghiệp bằng cách gõ:\n"
               . "👉 **danh bạ [Tên đồng nghiệp]** (Ví dụ: `danh bạ dung` hoặc `liên hệ an`)";
    } else {
        $stmt = $db->prepare("
            SELECT nv.ho_ten, nv.so_dien_thoai, nv.email, pb.ten_pb, cv.ten_cv 
            FROM nhan_vien nv 
            LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id 
            LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id 
            WHERE nv.ho_ten LIKE ? AND nv.trang_thai = 'Đang làm' 
            LIMIT 3
        ");
        $stmt->execute(['%' . $search_term . '%']);
        $contacts = $stmt->fetchAll();

        if ($contacts) {
            $reply = "🔍 **KẾT QUẢ TÌM KIẾM DANH BẠ CHO: \"" . htmlspecialchars($search_term) . "\"**\n\n";
            foreach ($contacts as $c) {
                $reply .= "👤 **" . $c['ho_ten'] . "**\n"
                       . "— **Phòng ban:** " . ($c['ten_pb'] ?? 'Chưa rõ') . " | **Chức vụ:** " . ($c['ten_cv'] ?? 'Chưa rõ') . "\n"
                       . "— **Số điện thoại:** `" . ($c['so_dien_thoai'] ?: 'Chưa cập nhật') . "`\n"
                       . "— **Email:** `" . ($c['email'] ?: 'Chưa cập nhật') . "`\n\n";
            }
        } else {
            $reply = "❌ Không tìm thấy cán bộ, giảng viên nào có tên khớp với từ khóa **\"" . htmlspecialchars($search_term) . "\"** trên danh bạ hệ thống.";
        }
    }
    $suggestions = ["📞 Tra danh bạ", "🎂 Sinh nhật cán bộ", "👤 Hồ sơ cá nhân"];
}

// LIÊN HỆ BAN GIÁM ĐỐC & PHÒNG KẾ TOÁN (Yêu cầu đặc biệt từ người dùng)
elseif (preg_match('/(giám đốc|giam doc|kế toán|ke toan|ban giám đốc|phòng kế toán|ban giam doc|phong ke toan)/u', $message)) {
    // Truy vấn Ban Giám Đốc
    $stmt = $db->query("
        SELECT nv.ho_ten, nv.so_dien_thoai, nv.email, pb.ten_pb, cv.ten_cv 
        FROM nhan_vien nv 
        LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id 
        LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id 
        WHERE cv.ma_cv = 'CV001' OR pb.ma_pb = 'PB001'
        ORDER BY cv.ma_cv ASC
    ");
    $directors = $stmt->fetchAll();

    // Truy vấn Phòng Kế Toán
    $stmt = $db->query("
        SELECT nv.ho_ten, nv.so_dien_thoai, nv.email, pb.ten_pb, cv.ten_cv 
        FROM nhan_vien nv 
        LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id 
        LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id 
        WHERE pb.ma_pb = 'PB003'
        ORDER BY cv.ma_cv ASC
    ");
    $accountants = $stmt->fetchAll();

    $reply = "📞 **ĐƯỜNG DÂY NÓNG LIÊN HỆ BAN GIÁM ĐỐC & KẾ TOÁN**\n\n";

    if ($directors) {
        $reply .= "👑 **BAN GIÁM ĐỐC (MANAGEMENT):**\n";
        foreach ($directors as $d) {
            $reply .= "• **" . $d['ho_ten'] . "** (" . $d['ten_cv'] . ")\n"
                   . "  — SĐT liên hệ: `" . ($d['so_dien_thoai'] ?: 'Chưa cập nhật') . "`\n"
                   . "  — Email: `" . ($d['email'] ?: 'Chưa cập nhật') . "`\n";
        }
        $reply .= "\n";
    }

    if ($accountants) {
        $reply .= "💵 **PHÒNG KẾ TOÁN (FINANCE & ACCOUNTING):**\n";
        foreach ($accountants as $a) {
            $reply .= "• **" . $a['ho_ten'] . "** (" . $a['ten_cv'] . ")\n"
                   . "  — SĐT liên hệ: `" . ($a['so_dien_thoai'] ?: 'Chưa cập nhật') . "`\n"
                   . "  — Email: `" . ($a['email'] ?: 'Chưa cập nhật') . "`\n";
        }
    }
    
    $reply .= "\n*Mọi thắc mắc về chế độ, lương thưởng hoặc kiến nghị, cán bộ có thể liên hệ trực tiếp qua số hotline/email trên hoặc gửi Đề xuất trực tuyến.*";
    $suggestions = ["👤 Hồ sơ cá nhân", "💰 Lương tháng này", "📞 Tra danh bạ"];
}

// 9. THỦ TỤC XIN NGHỈ PHÉP (FAQ)
elseif (preg_match('/(thủ tục nghỉ phép|quy định nghỉ phép|cách xin nghỉ)/u', $message)) {
    $reply = "🏖️ **HƯỚNG DẪN THỦ TỤC XIN NGHỈ PHÉP NỘI BỘ**\n\n"
           . "1. **Tạo đơn:** Cán bộ truy cập danh mục **Xin nghỉ phép** trên thanh Menu trái, sau đó nhấn **Tạo đơn mới**.\n"
           . "2. **Điền thông tin:** Nhập ngày bắt đầu, ngày kết thúc, loại phép (phép năm, phép ốm, phép không lương) và ghi rõ lý do xin nghỉ phép.\n"
           . "3. **Phê duyệt:** Đơn xin nghỉ phép sẽ tự động được gửi lên trực tuyến tới Trưởng phòng ban và Trưởng phòng Nhân sự duyệt trực tiếp trên hệ thống.\n"
           . "4. **Theo dõi:** Trạng thái đơn (Đang chờ, Đã duyệt hoặc Từ chối) sẽ được thông báo ngay tại giao diện này hoặc trang lịch sử phép.";
    $suggestions = ["🏖️ Đơn nghỉ phép", "⏱️ Chấm công", "⏰ Giờ làm việc"];
}

// 10. CÁCH TÍNH LƯƠNG (FAQ)
elseif (preg_match('/(cách tính lương|công thức tính lương|tính lương như thế nào)/u', $message)) {
    $reply = "🧮 **CÔNG THỨC VÀ QUY CHẾ TÍNH LƯƠNG TẠI TDU**\n\n"
           . "Công thức tính lương chuẩn của cán bộ, giảng viên:\n"
           . "`Lương thực lĩnh = Lương theo công + Phụ cấp + Thưởng - Khấu trừ - Thuế TNCN`\n\n"
           . "Trong đó:\n"
           . "• **Lương theo công** = `(Lương cơ bản * Số ngày làm việc thực tế) / 26 ngày công chuẩn`.\n"
           . "• **Tổng phụ cấp** = Phụ cấp ăn trưa + Phụ cấp xăng xe + Phụ cấp chuyên cần/khác (nếu có).\n"
           . "• **Khấu trừ bảo hiểm:** Cán bộ đóng 8% BHXH và 1.5% BHYT trên mức lương đóng bảo hiểm quy định.";
    $suggestions = ["💰 Lương tháng này", "📅 Lịch sử lương", "⏱️ Chấm công"];
}

// 11. GIỜ LÀM VIỆC (FAQ)
elseif (preg_match('/(giờ làm việc|thời gian làm việc|gio lam viec)/u', $message)) {
    $reply = "⏰ **QUY ĐỊNH GIỜ LÀM VIỆC HÀNH CHÍNH**\n\n"
           . "Giờ làm việc của cán bộ giảng viên Đại học Thành Đông như sau:\n"
           . "— **Buổi Sáng:** Từ **08:00** đến **11:30**\n"
           . "— **Buổi Chiều:** Từ **13:30** đến **17:00**\n"
           . "— **Lịch làm việc:** Từ Thứ Hai đến hết buổi Sáng Thứ Bảy hàng tuần.\n\n"
           . "*Lưu ý: Chấm công được tính tự động khi cán bộ giảng viên có mặt tại cơ quan và thực hiện chấm công đầu/cuối ngày.*";
    $suggestions = ["⏱️ Chấm công", "📞 Tra danh bạ", "💡 Quy định nghỉ phép"];
}

// 12. CHẾ ĐỘ BẢO HIỂM (FAQ)
elseif (preg_match('/(chế độ bảo hiểm|bảo hiểm xã hội|bhxh|bhyt)/u', $message)) {
    $reply = "🛡️ **QUY ĐỊNH VỀ CHẾ ĐỘ BẢO HIỂM NỘI BỘ**\n\n"
           . "Tỷ lệ đóng các khoản bảo hiểm bắt buộc theo lương đóng bảo hiểm:\n"
           . "1. **Cán bộ, Giảng viên đóng:**\n"
           . "   — BHXH: **8%** | BHYT: **1.5%** | BHTN: **1%** *(Tổng cộng khấu trừ lương: 10.5%)*\n"
           . "2. **Nhà trường hỗ trợ đóng:**\n"
           . "   — BHXH: **17.5%** | BHYT: **3%** | BHTN: **1%** *(Tổng cộng trích nộp: 21.5%)*";
    $suggestions = ["🧮 Cách tính lương", "💰 Lương tháng này", "👤 Hồ sơ cá nhân"];
}

// 13. THỐNG KÊ SINH NHẬT CÁN BỘ
elseif (preg_match('/(sinh nhật|mừng sinh nhật|bday)/u', $message)) {
    $stmt = $db->query("
        SELECT ho_ten, ngay_sinh 
        FROM nhan_vien 
        WHERE MONTH(ngay_sinh) = MONTH(CURRENT_DATE()) AND trang_thai = 'Đang làm' 
        ORDER BY DAY(ngay_sinh) ASC
    ");
    $bdays = $stmt->fetchAll();

    if ($bdays) {
        $reply = "🎂 **CÁN BỘ GIẢNG VIÊN MỪNG SINH NHẬT THÁNG " . date('m') . "**\n\n";
        foreach ($bdays as $b) {
            $reply .= "• **" . $b['ho_ten'] . "** — Sinh ngày: " . date('d/m', strtotime($b['ngay_sinh'])) . " 🎁\n";
        }
        $reply .= "\n*Mọi người hãy gửi những lời chúc nồng nhiệt nhất đến đồng nghiệp của chúng ta nhé!*";
    } else {
        $reply = "Tháng này trường chúng ta không có cán bộ nào sinh nhật. Chúc mọi người luôn vui vẻ!";
    }
    $suggestions = ["👤 Hồ sơ cá nhân", "⏱️ Chấm công", "💡 Truyền động lực"];
}

// 14. BÁO CÁO THỐNG KÊ (DÀNH CHO ADMIN / HR / KẾ TOÁN)
elseif (preg_match('/(thống kê|báo cáo|bao nhiêu người|nhân sự|phòng ban)/u', $message)) {
    if ($vai_tro === 'admin' || $vai_tro === 'hr' || $vai_tro === 'ketoan') {
        $total_nv = $db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai='Đang làm'")->fetchColumn();
        
        $stmt = $db->query("
            SELECT p.ten_pb, COUNT(n.id) as tong_so 
            FROM phong_ban p 
            LEFT JOIN nhan_vien n ON p.id = n.id_phong_ban AND n.trang_thai = 'Đang làm' 
            GROUP BY p.id
        ");
        $phong_bans = $stmt->fetchAll();

        $reply = "📊 **BÁO CÁO NHÂN SỰ TOÀN TRƯỜNG**\n"
               . "— **Tổng số nhân sự đang làm:** **" . $total_nv . " người**\n\n"
               . "💼 **Phân bộ cán bộ theo Khoa/Phòng ban:**\n";
        foreach ($phong_bans as $pb) {
            $reply .= "• " . $pb['ten_pb'] . ": **" . $pb['tong_so'] . " cán bộ**\n";
        }
    } else {
        $reply = "🔒 Rất tiếc, tính năng xem báo cáo thống kê nhân sự chung chỉ giới hạn riêng cho Ban Giám Hiệu, Phòng Nhân sự (HR) và Phòng Kế toán.";
    }
    $suggestions = ["🎂 Sinh nhật cán bộ", "⏱️ Chấm công", "💡 Truyền động lực"];
}

// 15. THỜI TIẾT
elseif (strpos($message, 'thời tiết') !== false) {
    $weathers = [
        "Nắng ấm nhẹ, trời rất trong và mát mẻ. Rất lý tưởng để giảng dạy và làm việc hiệu quả tại TDU!",
        "Trời mát mẻ dịu nhẹ, gió lùa hiu hiu qua cửa sổ. Một ngày tuyệt vời để hoàn thành mọi công việc tồn đọng đó!",
        "Hơi có chút oi bức ngoài trời. Cán bộ nhớ bổ sung nhiều nước và hạn chế ra nắng giờ trưa nhé!",
        "Có mây rải rác, không khí rất dễ chịu. Phù hợp để cán bộ tham gia các hoạt động ngoại khóa cùng sinh viên."
    ];
    $w = $weathers[array_rand($weathers)];
    $reply = "🌦️ **Thời tiết Đại học Thành Đông (Hải Dương) hôm nay:**\n"
           . "👉 *$w*\n"
           . "• Nhiệt độ trung bình khoảng: **27-31°C**\n"
           . "• Chỉ số UV: An toàn. Chúc bạn một ngày làm việc tràn đầy niềm vui!";
    $suggestions = ["💡 Truyền động lực", "👤 Hồ sơ cá nhân", "🎂 Sinh nhật cán bộ"];
}

// 16. TRUYỀN ĐỘNG LỰC
elseif (preg_match('/(động lực|khuyên|lời khuyên|mệt|chán)/u', $message)) {
    $quotes = [
        "Thành công không phải là điểm đến cuối cùng, thất bại cũng không phải là dấu chấm hết: Sự dũng cảm để đi tiếp mới là điều đáng quý nhất.",
        "Mỗi công việc vĩ đại đều bắt đầu từ những bước đi nhỏ bé nhất. Cán bộ Thành Đông ngày hôm nay đang gieo những hạt mầm tri thức quý giá!",
        "Cách tốt nhất để dự đoán tương lai là tự mình kiến tạo ra nó. Cố lên nhé, đóng góp của bạn luôn vô cùng quan trọng!",
        "Đừng làm việc chăm chỉ một cách mù quáng, hãy làm việc thông minh và tràn ngập niềm đam mê.",
        "Kiên trì là chìa khóa mở ra mọi cánh cửa thành công. Chúc bạn hôm nay thật nhiều niềm vui trong công tác!"
    ];
    $q = $quotes[array_rand($quotes)];
    $reply = "🌟 **LỜI KHUYÊN DÀNH CHO $ho_ten HÔM NAY:**\n\n"
           . "> *\"$q\"*\n\n"
           . "💪 Hãy hít một hơi thật sâu, giữ nụ cười trên môi và bắt đầu một ngày làm việc tuyệt vời nhé!";
    $suggestions = ["👤 Hồ sơ cá nhân", "💰 Lương tháng này", "🎂 Sinh nhật cán bộ"];
}

// 17. BẢN THÂN CHATBOT
elseif (preg_match('/(ai tạo ra|là ai|tên là gì|developer|tác giả)/u', $message)) {
    $reply = "🤖 Tôi là **Trợ lý ảo TDU** — trợ lý HR thông minh được phát triển bởi các kỹ sư công nghệ tại Đại học Thành Đông.\n\nNhiệm vụ của tôi là giúp cán bộ, giảng viên và quản trị viên tra cứu nhanh các thông tin nhân sự, tiền lương, chấm công và các quy chế nội bộ.";
    $suggestions = ["👤 Hồ sơ cá nhân", "💰 Lương tháng này", "🎂 Sinh nhật cán bộ"];
}

// MẶC ĐỊNH KHÔNG HIỂU
else {
    $reply = $default_reply;
    $suggestions = ["👤 Hồ sơ cá nhân", "💰 Lương tháng này", "⏱️ Chấm công", "🎂 Sinh nhật cán bộ"];
}

echo json_encode([
    'reply' => $reply,
    'suggestions' => $suggestions
]);
?>
