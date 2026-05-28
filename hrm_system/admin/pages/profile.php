<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin();

// Đảm bảo là Admin/HR/Ketoan
if (!isHR() && !isKetoan() && !isAdmin()) { header('Location: '.BASE_URL.'/employee/index.php'); exit(); }

$pageTitle = 'Hồ sơ cá nhân';
$db = getDB();
$id_nv = $_SESSION['id_nhan_vien'] ?? 0;

if ($id_nv <= 0) {
    echo "Tài khoản của bạn chưa được liên kết với hồ sơ nhân viên nào. Vui lòng liên hệ quản trị viên.";
    exit();
}

$stmt = $db->prepare("SELECT nv.*, pb.ten_pb, cv.ten_cv FROM nhan_vien nv LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id WHERE nv.id=?");
$stmt->execute([$id_nv]);
$nv = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sdt = sanitize($_POST['so_dien_thoai'] ?? '');
    $diachi = sanitize($_POST['dia_chi'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    
    // Xử lý upload chữ ký
    $chu_ky = $nv['chu_ky'];
    if (isset($_FILES['chu_ky']) && $_FILES['chu_ky']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['chu_ky']['name'], PATHINFO_EXTENSION);
        $filename = 'sig_' . $id_nv . '_' . time() . '.' . $ext;
        $target = __DIR__ . '/../../assets/uploads/signatures/' . $filename;
        if (move_uploaded_file($_FILES['chu_ky']['tmp_name'], $target)) {
            $chu_ky = 'assets/uploads/signatures/' . $filename;
        }
    }

    $db->prepare("UPDATE nhan_vien SET so_dien_thoai=?,dia_chi=?,email=?,chu_ky=? WHERE id=?")->execute([$sdt,$diachi,$email,$chu_ky,$id_nv]);
    setFlash('success','Cập nhật hồ sơ và chữ ký thành công!');
    header('Location: profile.php'); exit();
}

// Hợp đồng hiện tại
$hd = $db->prepare("SELECT * FROM hop_dong WHERE id_nhan_vien=? AND trang_thai='Đang hiệu lực' LIMIT 1");
$hd->execute([$id_nv]); $hd = $hd->fetch();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hồ sơ - <?= APP_NAME ?></title>
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
        <div><h1>Hồ sơ cá nhân</h1><p>Thông tin cá nhân và hợp đồng lao động</p></div>
      </div>

      <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-check-circle"></i> <?= clean($flash['message']) ?></div><?php endif; ?>

      <div class="grid-2">
        <!-- Thông tin cá nhân -->
        <div class="card">
          <div class="card-header">
            <span class="card-title"><i class="fas fa-id-card"></i> Thông tin cá nhân</span>
            <button class="btn btn-primary btn-sm" onclick="openModal('modalEdit')"><i class="fas fa-edit"></i> Cập nhật</button>
          </div>
          <div class="card-body">
            <div style="text-align:center;margin-bottom:20px;">
              <div class="avatar avatar-xl" style="margin:0 auto 12px;"><?= mb_substr($nv['ho_ten'],0,1) ?></div>
              <h2 style="font-size:18px;font-weight:800;"><?= clean($nv['ho_ten']) ?></h2>
              <p style="color:var(--gray);"><?= clean($nv['ten_cv'] ?? '') ?> — <?= clean($nv['ten_pb'] ?? '') ?></p>
              <span class="badge badge-success" style="margin-top:6px;"><?= clean($nv['trang_thai']) ?></span>
            </div>
            <table style="width:100%;border-collapse:collapse;">
              <?php $info = [
                ['Mã nhân viên', $nv['ma_nv']],
                ['Ngày sinh', formatDate($nv['ngay_sinh'])],
                ['Giới tính', $nv['gioi_tinh']],
                ['CCCD', $nv['cccd'] ?: '—'],
                ['Điện thoại', $nv['so_dien_thoai'] ?: '—'],
                ['Email', $nv['email'] ?: '—'],
                ['Địa chỉ', $nv['dia_chi'] ?: '—'],
                ['Ngày vào làm', formatDate($nv['ngay_vao_lam'])],
              ];
              foreach ($info as [$k,$v]): ?>
              <tr>
                <td style="padding:8px 0;color:var(--gray);font-size:13px;width:130px;"><?= $k ?></td>
                <td style="padding:8px 0;font-size:13px;font-weight:500;border-bottom:1px solid var(--border);"><?= clean($v) ?></td>
              </tr>
              <?php endforeach; ?>
            </table>
            
            <div style="margin-top:24px;border-top:1px dashed var(--border);padding-top:16px;">
              <p style="font-size:13px;color:var(--gray);font-weight:700;margin-bottom:10px;"><i class="fas fa-signature"></i> Chữ ký điện tử</p>
              <?php if ($nv['chu_ky']): ?>
                <img src="<?= BASE_URL . '/' . $nv['chu_ky'] ?>" alt="Signature" style="max-height:80px;display:block;margin-bottom:10px;filter:contrast(1.2);">
              <?php else: ?>
                <div style="height:60px;background:#f8fafc;border:1px dashed #cbd5e1;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;border-radius:6px;margin-bottom:10px;">Chưa có chữ ký</div>
              <?php endif; ?>
              <p style="font-size:11px;color:var(--gray);">Dùng để hiển thị trên bảng lương và các văn bản nội bộ.</p>
            </div>
          </div>
        </div>

        <div>
          <!-- Hợp đồng -->
          <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><span class="card-title"><i class="fas fa-file-contract"></i> Hợp đồng lao động</span></div>
            <div class="card-body">
              <?php if ($hd): ?>
              <table style="width:100%;border-collapse:collapse;">
                <?php $hdInfo = [
                  ['Mã hợp đồng', $hd['ma_hd']],
                  ['Loại hợp đồng', $hd['loai_hop_dong']],
                  ['Ngày ký', formatDate($hd['ngay_bat_dau'])],
                  ['Ngày kết thúc', $hd['ngay_ket_thuc'] ? formatDate($hd['ngay_ket_thuc']) : 'Không thời hạn'],
                  ['Lương hợp đồng', formatMoney($hd['luong_hop_dong'])],
                  ['Trạng thái', $hd['trang_thai']],
                ];
                foreach ($hdInfo as [$k,$v]): ?>
                <tr><td style="padding:7px 0;color:var(--gray);font-size:13px;width:140px;"><?= $k ?></td><td style="padding:7px 0;font-size:13px;font-weight:500;border-bottom:1px solid var(--border);"><?= clean($v) ?></td></tr>
                <?php endforeach; ?>
              </table>
              <?php else: ?><div class="empty-state"><i class="fas fa-file-contract"></i><h3>Chưa có hợp đồng hiệu lực</h3></div><?php endif; ?>
            </div>
          </div>

          <!-- Lương cơ bản -->
          <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-money-bill-wave"></i> Thông tin lương</span></div>
            <div class="card-body">
              <div style="text-align:center;padding:16px 0;">
                <p style="color:var(--gray);font-size:13px;margin-bottom:6px;">Lương cơ bản</p>
                <p style="font-size:28px;font-weight:800;color:var(--primary);"><?= formatMoney($nv['luong_co_ban']) ?></p>
                <p style="font-size:12px;color:var(--gray);margin-top:6px;">/tháng (<?= NGAY_CHUAN ?> ngày công)</p>
              </div>
              <table style="width:100%;border-collapse:collapse;margin-top:8px;">
                <tr><td style="padding:6px 0;color:var(--gray);font-size:13px;">BHXH (8%)</td><td style="text-align:right;font-size:13px;color:var(--danger);">- <?= formatMoney($nv['luong_co_ban']*0.08) ?></td></tr>
                <tr><td style="padding:6px 0;color:var(--gray);font-size:13px;">BHYT (1.5%)</td><td style="text-align:right;font-size:13px;color:var(--danger);">- <?= formatMoney($nv['luong_co_ban']*0.015) ?></td></tr>
                <tr style="border-top:1px solid var(--border);"><td style="padding:8px 0;font-weight:700;">Ước tính thực lĩnh*</td><td style="text-align:right;font-weight:700;color:var(--success);"><?= formatMoney($nv['luong_co_ban'] * (1 - 0.08 - 0.015)) ?></td></tr>
              </table>
              <p style="font-size:11px;color:var(--gray);margin-top:8px;">*Chưa tính thuế TNCN và các khoản phụ cấp</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal cập nhật -->
<div class="modal-overlay" id="modalEdit" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title"><i class="fas fa-edit"></i> Cập nhật thông tin</span>
      <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Số điện thoại</label>
          <input type="text" name="so_dien_thoai" class="form-control" value="<?= clean($nv['so_dien_thoai'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= clean($nv['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Địa chỉ</label>
          <textarea name="dia_chi" class="form-control"><?= clean($nv['dia_chi'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Chữ ký điện tử (Tải ảnh mẫu chữ ký)</label>
          <input type="file" name="chu_ky" class="form-control" accept="image/*">
          <p style="font-size:11px;color:var(--gray);margin-top:4px;">Nên dùng ảnh PNG nền trong suốt hoặc ảnh chụp chữ ký trên giấy trắng.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
      </div>
    </form>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
</body></html>
