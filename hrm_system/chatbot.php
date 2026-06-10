<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Support both JSON input and standard POST form data to bypass firewalls
$data = json_decode(file_get_contents('php://input'), true);
$raw_message = $data['message'] ?? $_POST['message'] ?? '';
$message = mb_strtolower(trim($raw_message));

$reply = "";
$suggestions = [];

// Khởi tạo phản hồi mặc định
$default_reply = "Xin lỗi, tôi chưa hiểu rõ ý bạn. Bạn có thể thử hỏi về: 'lương', 'nghỉ phép', 'chấm công', 'thời tiết' hoặc 'truyền động lực' nhé.";

// Kiểm tra phiên đăng nhập để cá nhân hóa
$id_nv = $_SESSION['id_nhan_vien'] ?? null;
$ho_ten = $_SESSION['ho_ten'] ?? 'bạn';
$vai_tro = $_SESSION['vai_tro'] ?? 'Nhân viên';

$db = getDB();

// LOGIC XỬ LÝ CÂU HỎI
if (empty($message)) {
    $reply = "Chào $ho_ten! Tôi có thể giúp gì cho bạn hôm nay?";
} 
// Chào hỏi
elseif (preg_match('/(chào|hi|hello|ơi)/u', $message)) {
    $reply = "Chào $ho_ten! Chúc bạn một ngày làm việc tràn đầy năng lượng tại Đại học Thành Đông. Bạn cần tôi hỗ trợ gì không?";
    $suggestions = ["Lương tháng này", "Thời tiết hôm nay", "Truyền động lực"];
}
// Hỏi về lương
elseif (strpos($message, 'lương') !== false) {
    if (strpos($message, 'tháng này') !== false || strpos($message, 'bao nhiêu') !== false) {
        if ($id_nv) {
            $stmt = $db->prepare("SELECT thuc_linh FROM bang_luong WHERE id_nhan_vien = ? AND thang = ? AND nam = ?");
            $stmt->execute([$id_nv, date('n'), date('Y')]);
            $thuc_linh = $stmt->fetchColumn();
            
            if ($thuc_linh) {
                $reply = "Lương thực lĩnh tháng " . date('m/Y') . " của bạn là: **" . number_format($thuc_linh) . " VNĐ**. Bạn có thể xem chi tiết trong mục 'Bảng lương'.";
            } else {
                $reply = "Hiện tại chưa có dữ liệu lương tháng này của bạn. Thông thường bảng lương sẽ có vào cuối tháng.";
            }
        } else {
            $reply = "Bạn vui lòng đăng nhập để xem thông tin lương cá nhân nhé.";
        }
    } else {
        $reply = "Bạn có thể xem lịch sử lương và chi tiết các khoản phụ cấp tại mục **Cá nhân > Bảng lương**. Hệ thống hiển thị đầy đủ các khoản khấu trừ và thưởng đấy!";
    }
}
// Hỏi về thời tiết (Giả lập hoặc lấy dữ liệu cơ bản)
elseif (strpos($message, 'thời tiết') !== false) {
    $weathers = ["Nắng đẹp, trời xanh", "Có mây rải rác, mát mẻ", "Hơi oi bức, bạn nhớ uống nhiều nước nhé", "Trời dịu mát, rất thích hợp để làm việc hiệu quả"];
    $w = $weathers[array_rand($weathers)];
    $reply = "Thời tiết tại Hải Dương hôm nay: **$w**. Nhiệt độ khoảng 28-32°C. Chúc bạn một ngày làm việc thoải mái!";
}
// Truyền động lực
elseif (strpos($message, 'động lực') !== false || strpos($message, 'khuyên') !== false) {
    $quotes = [
        "Thành công không phải là chìa khóa mở cửa hạnh phúc. Hạnh phúc mới là chìa khóa dẫn tới thành công.",
        "Đừng làm việc chăm chỉ, hãy làm việc thông minh!",
        "Cách duy nhất để làm tốt một việc là yêu việc mình làm.",
        "Mỗi ngày là một cơ hội mới để bạn trở nên tốt hơn ngày hôm qua.",
        "Kiên trì là bí mật của mọi thành công."
    ];
    $q = $quotes[array_rand($quotes)];
    $reply = "Lời khuyên cho $ho_ten hôm nay: \n\n> *\"$q\"*\n\nCố gắng lên nhé, bạn đang làm rất tốt!";
}
// Thống kê (Dành cho Admin/HR)
elseif ((strpos($message, 'thống kê') !== false || strpos($message, 'bao nhiêu người') !== false) && ($vai_tro === 'Admin' || $vai_tro === 'HR')) {
    $total_nv = $db->query("SELECT COUNT(*) FROM nhan_vien WHERE trang_thai='Đang làm'")->fetchColumn();
    $today_cc = $db->prepare("SELECT COUNT(*) FROM cham_cong WHERE thang=? AND nam=?");
    $today_cc->execute([date('n'), date('Y')]);
    $cc_count = $today_cc->fetchColumn();
    $reply = "Báo cáo nhanh cho Quản trị viên:\n- Tổng số nhân sự đang làm: **$total_nv**\n- Số người đã chấm công tháng này: **$cc_count**\nBạn cần xem báo cáo chi tiết hơn không?";
}
// Hỏi về nghỉ phép
elseif (strpos($message, 'nghỉ') !== false || strpos($message, 'phép') !== false) {
    $leave_status_msg = "";
    if ($id_nv) {
        // Lấy danh sách đơn xin nghỉ gần nhất của nhân viên
        $stmt = $db->prepare("
            SELECT *
            FROM don_nghi_phep
            WHERE id_nhan_vien = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$id_nv]);
        $last_leave = $stmt->fetch();

        if ($last_leave) {
            $status_label = $last_leave['trang_thai'];
            $leave_status_msg = "\n\n📄 **Đơn xin nghỉ gần nhất của bạn:**"
                . "\n— Thời gian: **" . date('d/m/Y', strtotime($last_leave['ngay_bat_dau'])) . "** đến **" . date('d/m/Y', strtotime($last_leave['ngay_ket_thuc'])) . "**"
                . "\n— Số ngày nghỉ: **" . $last_leave['so_ngay'] . " ngày**"
                . "\n— Lý do: " . ($last_leave['ly_do'] ?: 'Không có')
                . "\n— Trạng thái duyệt: **" . $status_label . "**"
                . ($last_leave['ghi_chu_duyet'] ? "\n— Phản hồi từ Admin/HR: *\"" . $last_leave['ghi_chu_duyet'] . "\"*" : "");
        }
    }

    $reply = "🏖️ **HƯỚNG DẪN THỦ TỤC XIN NGHỈ PHÉP**\n\n"
        . "1. Vào mục **Xin nghỉ phép** trên thanh Menu trái.\n"
        . "2. Chọn **Tạo đơn mới**, điền khoảng ngày nghỉ (từ ngày - đến ngày) và lý do cụ thể.\n"
        . "3. Nhấn nút **Gửi đơn**. Đơn sẽ tự động chuyển tới Quản trị viên/HR để duyệt trực tuyến.";

    if (!$id_nv) {
        $reply .= "\n\n*Lưu ý: Bạn cần đăng nhập bằng tài khoản cán bộ nhân viên để gửi đơn nghỉ phép trực tuyến.*";
    }

    $reply .= $leave_status_msg;
    $suggestions = ['⏱️ Chấm công', '💰 Lương tháng này', '👤 Hồ sơ cá nhân'];
}
// Vui vẻ/Tán gẫu
elseif (strpos($message, 'ai tạo ra') !== false || strpos($message, 'là ai') !== false) {
    $reply = "Tôi là **Trợ lý ảo TDU**, được phát triển để hỗ trợ cán bộ giảng viên Đại học Thành Đông quản lý công việc và lương thưởng một cách dễ dàng nhất!";
}
// Mặc định hoặc tìm kiếm trong chatbot_faq
else {
    try {
        $rules = $db->query("SELECT * FROM chatbot_faq ORDER BY id DESC")->fetchAll();
        $matched_rule = null;
        foreach ($rules as $rule) {
            $keywords = array_map('trim', explode(',', $rule['keywords']));
            foreach ($keywords as $kw) {
                $kw_norm = mb_strtolower(trim($kw));
                if ($kw_norm !== '' && strpos($message, $kw_norm) !== false) {
                    $matched_rule = $rule;
                    break 2;
                }
            }
        }
        if ($matched_rule) {
            $reply = $matched_rule['reply'];
            if (!empty($matched_rule['suggestions'])) {
                $suggestions = array_map('trim', explode(',', $matched_rule['suggestions']));
            }
        } else {
            $reply = $default_reply;
        }
    } catch (Throwable $faq_error) {
        $reply = $default_reply;
    }
}

echo json_encode([
    'reply' => $reply,
    'suggestions' => $suggestions
]);
?>
