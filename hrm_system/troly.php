<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

require_once __DIR__ . '/config/database.php';

function reply_json($reply, $suggestions = [])
{
    echo json_encode([
        'reply' => $reply,
        'suggestions' => $suggestions,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_text($text)
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $map = [
        'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
        'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
        'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
        'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
        'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
        'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
        'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
        'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
        'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
        'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        'đ' => 'd',
    ];

    return strtr($text, $map);
}

function contains_any($text, array $keywords)
{
    foreach ($keywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

$payload = json_decode(file_get_contents('php://input'), true);
$rawMessage = $payload['message'] ?? $_POST['message'] ?? '';
$message = normalize_text($rawMessage);

$suggestions = [
    '👤 Hồ sơ của tôi',
    '💰 Lương tháng này',
    '⏱️ Chấm công',
    '📞 Danh bạ',
];

if ($message === '') {
    reply_json('Chào bạn! Tôi là trợ lý HR của Đại học Thành Đông. Bạn cần tra cứu hồ sơ, lương, chấm công hay danh bạ?', $suggestions);
}

try {
    $db = getDB();
} catch (Throwable $e) {
    reply_json('Hiện tôi chưa kết nối được dữ liệu nhân sự. Bạn vẫn có thể dùng các menu bên trái để xem hồ sơ, chấm công và bảng lương.', $suggestions);
}

$hoTen = $_SESSION['ho_ten'] ?? 'bạn';
$idNhanVien = $_SESSION['id_nhan_vien'] ?? null;
$vaiTro = $_SESSION['vai_tro'] ?? 'nhanvien';

try {
    if (contains_any($message, ['ho so', 'thong tin ca nhan', 'toi la ai', 'profile'])) {
        if (!$idNhanVien) {
            reply_json("Bạn đang đăng nhập bằng tài khoản {$vaiTro}. Tài khoản này chưa gắn với hồ sơ nhân viên.", ['💰 Lương tháng này', '⏱️ Chấm công']);
        }

        $stmt = $db->prepare("
            SELECT nv.*, pb.ten_pb, cv.ten_cv
            FROM nhan_vien nv
            LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
            LEFT JOIN chuc_vu cv ON nv.id_chuc_vu = cv.id
            WHERE nv.id = ?
        ");
        $stmt->execute([$idNhanVien]);
        $nv = $stmt->fetch();

        if (!$nv) {
            reply_json('Tôi chưa tìm thấy hồ sơ nhân viên gắn với tài khoản của bạn.', $suggestions);
        }

        reply_json(
            "👤 **Hồ sơ của bạn**\n"
            . "Họ tên: **{$nv['ho_ten']}**\n"
            . "Mã NV: `{$nv['ma_nv']}`\n"
            . "Phòng ban: " . ($nv['ten_pb'] ?: 'Chưa cập nhật') . "\n"
            . "Chức vụ: " . ($nv['ten_cv'] ?: 'Chưa cập nhật') . "\n"
            . "Email: " . ($nv['email'] ?: 'Chưa cập nhật') . "\n"
            . "Điện thoại: " . ($nv['so_dien_thoai'] ?: 'Chưa cập nhật'),
            ['💰 Lương tháng này', '⏱️ Chấm công', '📞 Danh bạ']
        );
    }

    if (contains_any($message, ['luong', 'phieu luong', 'thuc linh'])) {
        if (!$idNhanVien) {
            reply_json('Bạn cần đăng nhập bằng tài khoản nhân viên để tra cứu lương cá nhân.', ['👤 Hồ sơ của tôi', '⏱️ Chấm công']);
        }

        $stmt = $db->prepare("
            SELECT *
            FROM bang_luong
            WHERE id_nhan_vien = ?
            ORDER BY nam DESC, thang DESC
            LIMIT 1
        ");
        $stmt->execute([$idNhanVien]);
        $luong = $stmt->fetch();

        if (!$luong) {
            reply_json('Chưa có dữ liệu lương mới nhất của bạn trên hệ thống.', ['⏱️ Chấm công', '👤 Hồ sơ của tôi']);
        }

        reply_json(
            "💰 **Lương tháng {$luong['thang']}/{$luong['nam']}**\n"
            . "Lương cơ bản: " . number_format($luong['luong_co_ban'], 0, ',', '.') . " VNĐ\n"
            . "Ngày công: {$luong['so_ngay_lam']}/{$luong['so_ngay_chuan']}\n"
            . "Tổng thu nhập: " . number_format($luong['tong_thu_nhap'], 0, ',', '.') . " VNĐ\n"
            . "Tổng khấu trừ: " . number_format($luong['tong_khau_tru'], 0, ',', '.') . " VNĐ\n"
            . "Thực lĩnh: **" . number_format($luong['thuc_linh'], 0, ',', '.') . " VNĐ**\n"
            . "Trạng thái: {$luong['trang_thai']}",
            ['⏱️ Chấm công', '👤 Hồ sơ của tôi']
        );
    }

    if (contains_any($message, ['cham cong', 'ngay cong', 'tang ca', 'di lam'])) {
        if (!$idNhanVien) {
            reply_json('Bạn cần đăng nhập bằng tài khoản nhân viên để xem chấm công cá nhân.', ['👤 Hồ sơ của tôi', '💰 Lương tháng này']);
        }

        $stmt = $db->prepare("
            SELECT *
            FROM cham_cong
            WHERE id_nhan_vien = ?
            ORDER BY nam DESC, thang DESC
            LIMIT 1
        ");
        $stmt->execute([$idNhanVien]);
        $cc = $stmt->fetch();

        if (!$cc) {
            reply_json('Chưa có dữ liệu chấm công mới nhất của bạn.', ['💰 Lương tháng này', '👤 Hồ sơ của tôi']);
        }

        reply_json(
            "⏱️ **Chấm công tháng {$cc['thang']}/{$cc['nam']}**\n"
            . "Ngày làm: **{$cc['so_ngay_lam']}**\n"
            . "Ngày nghỉ: {$cc['so_ngay_nghi']}\n"
            . "Ngày phép: {$cc['so_ngay_phep']}\n"
            . "Ngày vắng: {$cc['so_ngay_vang']}\n"
            . "Giờ tăng ca: {$cc['so_gio_tang_ca']}",
            ['💰 Lương tháng này', '👤 Hồ sơ của tôi']
        );
    }

    if (contains_any($message, ['danh ba', 'lien he', 'so dien thoai', 'email'])) {
        $stmt = $db->query("
            SELECT nv.ho_ten, nv.so_dien_thoai, nv.email, pb.ten_pb
            FROM nhan_vien nv
            LEFT JOIN phong_ban pb ON nv.id_phong_ban = pb.id
            ORDER BY nv.id DESC
            LIMIT 5
        ");
        $contacts = $stmt->fetchAll();

        if (!$contacts) {
            reply_json('Danh bạ nội bộ hiện chưa có dữ liệu.', $suggestions);
        }

        $reply = "📞 **Danh bạ nội bộ gần đây**\n";
        foreach ($contacts as $contact) {
            $reply .= "\n**{$contact['ho_ten']}**"
                . "\nPhòng ban: " . ($contact['ten_pb'] ?: 'Chưa cập nhật')
                . "\nSĐT: " . ($contact['so_dien_thoai'] ?: 'Chưa cập nhật')
                . "\nEmail: " . ($contact['email'] ?: 'Chưa cập nhật') . "\n";
        }

        reply_json($reply, ['👤 Hồ sơ của tôi', '💰 Lương tháng này']);
    }

    if (contains_any($message, ['nghi phep', 'xin nghi', 'don phep', 'nghi', 'phep'])) {
        $leave_status_msg = "";
        if ($idNhanVien) {
            // Lấy danh sách đơn xin nghỉ gần nhất của nhân viên
            $stmt = $db->prepare("
                SELECT *
                FROM don_nghi_phep
                WHERE id_nhan_vien = ?
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute([$idNhanVien]);
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

        $instructions = "🏖️ **HƯỚNG DẪN THỦ TỤC XIN NGHỈ PHÉP**\n\n"
            . "1. Vào mục **Xin nghỉ phép** trên thanh Menu trái.\n"
            . "2. Chọn **Tạo đơn mới**, điền khoảng ngày nghỉ (từ ngày - đến ngày) và lý do cụ thể.\n"
            . "3. Nhấn nút **Gửi đơn**. Đơn sẽ tự động chuyển tới Quản trị viên/HR để duyệt trực tuyến.";

        if (!$idNhanVien) {
            $instructions .= "\n\n*Lưu ý: Bạn cần đăng nhập bằng tài khoản cán bộ nhân viên để gửi đơn nghỉ phép trực tuyến.*";
        }

        reply_json($instructions . $leave_status_msg, ['⏱️ Chấm công', '💰 Lương tháng này', '👤 Hồ sơ của tôi']);
    }

    // Kiểm tra các luật phản hồi động từ CSDL (bảng chatbot_faq)
    try {
        $rules = $db->query("SELECT * FROM chatbot_faq ORDER BY id DESC")->fetchAll();
        foreach ($rules as $rule) {
            $keywords = array_map('trim', explode(',', $rule['keywords']));
            $matched = false;
            foreach ($keywords as $kw) {
                $kw_norm = normalize_text($kw);
                if ($kw_norm !== '' && strpos($message, $kw_norm) !== false) {
                    $matched = true;
                    break;
                }
            }
            if ($matched) {
                $ruleSuggestions = [];
                if (!empty($rule['suggestions'])) {
                    $ruleSuggestions = array_map('trim', explode(',', $rule['suggestions']));
                }
                if (empty($ruleSuggestions)) {
                    $ruleSuggestions = $suggestions;
                }
                reply_json($rule['reply'], $ruleSuggestions);
            }
        }
    } catch (Throwable $faq_error) {
        // Bỏ qua lỗi CSDL
    }
} catch (Throwable $e) {
    reply_json('Tôi chưa đọc được dữ liệu cho yêu cầu này. Bạn thử lại sau hoặc mở trực tiếp menu tương ứng bên trái.', $suggestions);
}

reply_json(
    "Tôi chưa hiểu rõ câu hỏi của bạn. Bạn có thể chọn nhanh một mục bên dưới để tôi tra cứu.",
    $suggestions
);
