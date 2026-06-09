<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helper::clean($pageTitle) ?> - Đại Học Thành Đông</title>
    <link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout">
    <?php 
    $current_page = 'chatbot';
    require __DIR__ . '/../layouts/sidebar.php'; 
    ?>
    <div class="main-content">
        <?php require __DIR__ . '/../layouts/header.php'; ?>
        <div class="page-content">
            <div class="page-header">
                <div>
                    <h1>Quản lý Trợ lý ảo TDU</h1>
                    <p>Giám đốc điều khiển và cấu hình trực tiếp các luật câu hỏi/phản hồi của chatbot</p>
                </div>
            </div>

            <?php 
            $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
            if ($flash): 
                unset($_SESSION['flash']);
            ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fas fa-info-circle"></i> <?= Helper::clean($flash['message']) ?>
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
                    <form method="POST" action="<?= Helper::route('chatbot') ?>">
                        <div class="card-body">
                            <?php if ($editFaq): ?>
                                <input type="hidden" name="id" value="<?= $editFaq['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label required">Từ khóa kích hoạt</label>
                                <input type="text" name="keywords" class="form-control" 
                                       placeholder="Ví dụ: giờ làm, ca làm, thời gian" 
                                       value="<?= Helper::clean($editFaq['keywords'] ?? '') ?>" required>
                                <small style="color:var(--gray); font-size:11px;">Phân cách các từ khóa bằng dấu phẩy (,)</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Nội dung phản hồi (Reply)</label>
                                <textarea name="reply" class="form-control" rows="6" 
                                          placeholder="Nhập nội dung trả lời (Hỗ trợ định dạng Markdown: **chữ đậm**, xuống dòng...)" required><?= Helper::clean($editFaq['reply'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Gợi ý nhanh (Suggestions)</label>
                                <input type="text" name="suggestions" class="form-control" 
                                       placeholder="Ví dụ: 👤 Hồ sơ, 💰 Lương, ⏱️ Chấm công" 
                                       value="<?= Helper::clean($editFaq['suggestions'] ?? '') ?>">
                                <small style="color:var(--gray); font-size:11px;">Các gợi ý nhanh hiển thị dưới dạng nút bấm, phân cách bằng dấu phẩy (,)</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Người cấu hình</label>
                                <input type="text" name="nguoi_tao" class="form-control" 
                                       value="<?= Helper::clean($editFaq['nguoi_tao'] ?? 'Giám đốc') ?>">
                            </div>
                        </div>
                        <div class="card-footer" style="padding: 16px 20px; border-top: 1px solid var(--border); background: var(--bg); display:flex; gap:10px;">
                            <button type="submit" class="btn btn-primary" style="flex:1; justify-content:center;">
                                <i class="fas fa-save"></i> <?= $editFaq ? 'Lưu thay đổi' : 'Thêm mới' ?>
                            </button>
                            <?php if ($editFaq): ?>
                                <a href="<?= Helper::route('chatbot') ?>" class="btn btn-secondary" style="flex:1; justify-content:center; text-align:center;">Hủy</a>
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
                                                        <?= Helper::clean(trim($kw)) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </td>
                                            <td style="padding: 12px; vertical-align: top; white-space: pre-wrap; font-size:12.5px; line-height:1.5;">
                                                <?= Helper::clean($faq['reply']) ?>
                                                <?php if (!empty($faq['suggestions'])): ?>
                                                    <div style="margin-top: 8px; display:flex; gap: 4px; flex-wrap:wrap;">
                                                        <span style="font-size:11px; color:var(--gray); font-weight:500;">Gợi ý:</span>
                                                        <?php 
                                                        $sugs = explode(',', $faq['suggestions']);
                                                        foreach ($sugs as $sug):
                                                        ?>
                                                            <span style="font-size:10px; background:#f1f5f9; padding: 1px 6px; border-radius:10px; border:1px solid #e2e8f0; color:#475569;">
                                                                <?= Helper::clean(trim($sug)) ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 12px; vertical-align: top; font-size: 13px;">
                                                <span style="font-weight: 600; color: #475569;"><i class="fas fa-user-cog" style="font-size:11px; margin-right:4px;"></i> <?= Helper::clean($faq['nguoi_tao']) ?></span>
                                            </td>
                                            <td style="padding: 12px; text-align: center; vertical-align: top; display:flex; gap:5px; justify-content:center;">
                                                <a href="<?= Helper::route('chatbot?action=edit&id=' . $faq['id']) ?>" class="btn btn-secondary btn-sm" style="padding:4px 8px;" data-tooltip="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                                <a href="<?= Helper::route('chatbot?action=delete&id=' . $faq['id']) ?>" class="btn btn-danger btn-sm" style="padding:4px 8px;" onclick="return confirm('Bạn có chắc chắn muốn xóa luật này không?')" data-tooltip="Xóa"><i class="fas fa-trash"></i></a>
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
<script src="<?= Helper::asset('js/main.js') ?>"></script>
</body>
</html>
