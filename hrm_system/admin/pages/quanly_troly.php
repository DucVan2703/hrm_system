<?php
require_once __DIR__ . '/../../config/database.php';
// Kiểm tra nếu file functions.php tồn tại thì require, nếu không thì tự khai báo các hàm cần thiết để tránh lỗi trắng trang
if (file_exists(__DIR__ . '/../../includes/functions.php')) {
    require_once __DIR__ . '/../../includes/functions.php';
} else {
    // Tự định nghĩa các hàm cơ bản nếu thiếu file includes/functions.php
    if (!function_exists('requireOnlyAdmin')) {
        function requireOnlyAdmin() {
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            if (!isset($_SESSION['user_id']) || ($_SESSION['vai_tro'] ?? '') !== 'admin') {
                header('Location: ../../login.php');
                exit();
            }
        }
    }
    if (!function_exists('sanitize')) {
        function sanitize($data) {
            return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
        }
    }
    if (!function_exists('clean')) {
        function clean($data) {
            return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
        }
    }
    if (!function_exists('setFlash')) {
        function setFlash($type, $message) {
            $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        }
    }
    if (!function_exists('getFlash')) {
        function getFlash() {
            if (isset($_SESSION['flash'])) {
                $flash = $_SESSION['flash'];
                unset($_SESSION['flash']);
                return $flash;
            }
            return null;
        }
    }
    if (!function_exists('APP_NAME')) {
        define('APP_NAME', 'Đại Học Thành Đông');
    }
}

requireOnlyAdmin(); // Giám đốc/Admin được quyền vào

$pageTitle = 'Quản lý Trợ lý ảo TDU';
$db = getDB();
$error = '';
$success = '';

// Xử lý XÓA luật
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("DELETE FROM chatbot_faq WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Đã xóa luật phản hồi thành công!');
    header('Location: quanly_troly.php');
    exit();
}

// Xử lý THÊM / CẬP NHẬT luật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $keywords = sanitize($_POST['keywords'] ?? '');
    $reply = sanitize($_POST['reply'] ?? '');
    $suggestions = sanitize($_POST['suggestions'] ?? '');
    $nguoi_tao = sanitize($_POST['nguoi_tao'] ?? 'Giám đốc');

    if (empty($keywords) || empty($reply)) {
        $error = 'Vui lòng nhập đầy đủ các từ khóa và nội dung câu trả lời!';
    } else {
        if ($id) {
            // Cập nhật
            $stmt = $db->prepare("UPDATE chatbot_faq SET keywords = ?, reply = ?, suggestions = ?, nguoi_tao = ? WHERE id = ?");
            $stmt->execute([$keywords, $reply, $suggestions, $nguoi_tao, $id]);
            setFlash('success', 'Đã cập nhật luật phản hồi thành công!');
        } else {
            // Thêm mới
            $stmt = $db->prepare("INSERT INTO chatbot_faq (keywords, reply, suggestions, nguoi_tao) VALUES (?, ?, ?, ?)");
            $stmt->execute([$keywords, $reply, $suggestions, $nguoi_tao]);
            setFlash('success', 'Đã thêm luật phản hồi mới thành công!');
        }
        header('Location: quanly_troly.php');
        exit();
    }
}

$stmt = $db->query("SELECT * FROM chatbot_faq ORDER BY id DESC");
$list = $stmt->fetchAll();
$flash = getFlash();

// Lấy thông tin luật cần sửa
$editFaq = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM chatbot_faq WHERE id = ?");
    $stmt->execute([$editId]);
    $editFaq = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        <div class="page-content">
            <div class="page-header">
                <div>
                    <h1>Quản lý Trợ lý ảo TDU</h1>
                    <p>Giám đốc điều khiển và cấu hình trực tiếp các luật câu hỏi/phản hồi của chatbot</p>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= clean($error) ?>
                </div>
            <?php endif; ?>

            <div class="grid-2" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: start;">
                <!-- FORM THÊM / SỬA -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">
                            <i class="fas <?= $editFaq ? 'fa-edit' : 'fa-plus' ?>"></i> 
                            <?= $editFaq ? 'Cập nhật luật' : 'Thêm luật phản hồi' ?>
                        </span>
                    </div>
                    <form method="POST">
                        <div class="card-body">
                            <?php if ($editFaq): ?>
                                <input type="hidden" name="id" value="<?= $editFaq['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label required">Từ khóa kích hoạt</label>
                                <input type="text" name="keywords" class="form-control" 
                                       placeholder="Ví dụ: giờ làm, ca làm, thời gian" 
                                       value="<?= clean($editFaq['keywords'] ?? '') ?>" required>
                                <small style="color:var(--gray); font-size:11px;">Phân cách các từ khóa bằng dấu phẩy (,)</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Nội dung phản hồi (Reply)</label>
                                <textarea name="reply" class="form-control" rows="6" 
                                          placeholder="Nhập nội dung trả lời (Hỗ trợ định dạng Markdown: **chữ đậm**, xuống dòng...)" required><?= clean($editFaq['reply'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Gợi ý nhanh (Suggestions)</label>
                                <input type="text" name="suggestions" class="form-control" 
                                       placeholder="Ví dụ: 👤 Hồ sơ, 💰 Lương, ⏱️ Chấm công" 
                                       value="<?= clean($editFaq['suggestions'] ?? '') ?>">
                                <small style="color:var(--gray); font-size:11px;">Các gợi ý nhanh hiển thị dưới dạng nút bấm, phân cách bằng dấu phẩy (,)</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Người cấu hình</label>
                                <input type="text" name="nguoi_tao" class="form-control" 
                                       value="<?= clean($editFaq['nguoi_tao'] ?? 'Giám đốc') ?>">
                            </div>
                        </div>
                        <div class="card-footer" style="padding: 16px 20px; border-top: 1px solid var(--border); background: var(--bg); display:flex; gap:10px;">
                            <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                                <i class="fas fa-save"></i> <?= $editFaq ? 'Lưu thay đổi' : 'Thêm mới' ?>
                            </button>
                            <?php if ($editFaq): ?>
                                <a href="quanly_troly.php" class="btn btn-secondary" style="flex:1; justify-content:center; text-align:center;">Hủy</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- DANH SÁCH CÁC LUẬT HIỆN TẠI -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title"><i class="fas fa-list"></i> Danh sách luật đang hoạt động</span>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border); background: var(--bg);">
                                    <th style="padding: 12px; text-align: left; width: 25%;">Từ khóa</th>
                                    <th style="padding: 12px; text-align: left; width: 45%;">Nội dung trả lời</th>
                                    <th style="padding: 12px; text-align: left; width: 15%;">Người tạo</th>
                                    <th style="padding: 12px; text-align: center; width: 15%;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($list)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 24px; color: var(--gray);">
                                            Chưa có luật phản hồi nào được tạo. Hãy tạo luật đầu tiên ở bảng bên trái!
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($list as $faq): ?>
                                        <tr style="border-bottom: 1px solid var(--border);">
                                            <td style="padding: 12px; vertical-align: top;">
                                                <?php 
                                                $kws = explode(',', $faq['keywords']);
                                                foreach ($kws as $kw): 
                                                ?>
                                                    <span style="display:inline-block; padding: 2px 8px; background: rgba(5,150,105,0.08); color: var(--primary); border-radius: 12px; font-size: 11px; margin: 2px; font-weight:600;">
                                                        <?= clean(trim($kw)) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </td>
                                            <td style="padding: 12px; vertical-align: top; white-space: pre-wrap; font-size:12.5px; line-height:1.5;">
                                                <?= clean($faq['reply']) ?>
                                                <?php if (!empty($faq['suggestions'])): ?>
                                                    <div style="margin-top: 8px; display:flex; gap: 4px; flex-wrap:wrap;">
                                                        <span style="font-size:11px; color:var(--gray); font-weight:500;">Gợi ý:</span>
                                                        <?php 
                                                        $sugs = explode(',', $faq['suggestions']);
                                                        foreach ($sugs as $sug):
                                                        ?>
                                                            <span style="font-size:10px; background:#f1f5f9; padding: 1px 6px; border-radius:10px; border:1px solid #e2e8f0; color:#475569;">
                                                                <?= clean(trim($sug)) ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 12px; vertical-align: top; font-size: 13px;">
                                                <span style="font-weight: 600; color: #475569;"><i class="fas fa-user-cog" style="font-size:11px; margin-right:4px;"></i> <?= clean($faq['nguoi_tao']) ?></span>
                                            </td>
                                            <td style="padding: 12px; text-align: center; vertical-align: top; display:flex; gap:5px; justify-content:center;">
                                                <a href="quanly_troly.php?action=edit&id=<?= $faq['id'] ?>" class="btn btn-secondary btn-sm" style="padding:4px 8px;" data-tooltip="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                                <a href="quanly_troly.php?action=delete&id=<?= $faq['id'] ?>" class="btn btn-danger btn-sm" style="padding:4px 8px;" onclick="return confirm('Bạn có chắc chắn muốn xóa luật này không?')" data-tooltip="Xóa"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
