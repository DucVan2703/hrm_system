<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireKetoan();
$pageTitle = 'Phụ cấp / Thưởng / Phạt';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    if ($type === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $ten = sanitize($_POST['ten_phu_cap']); $sotien = (float)$_POST['so_tien']; $mota = sanitize($_POST['mo_ta'] ?? '');
        if ($id > 0) {
            $db->prepare("UPDATE phu_cap SET ten_phu_cap=?,so_tien=?,mo_ta=? WHERE id=?")->execute([$ten,$sotien,$mota,$id]);
            setFlash('success','Cập nhật thành công!');
        } else {
            $ma = 'PC'.str_pad($db->query("SELECT COALESCE(MAX(CAST(SUBSTRING(ma_pc,3) AS UNSIGNED)),0)+1 FROM phu_cap WHERE ma_pc LIKE 'PC%'")->fetchColumn(),3,'0',STR_PAD_LEFT);
            $db->prepare("INSERT INTO phu_cap (ten_phu_cap,ma_pc,so_tien,mo_ta) VALUES (?,?,?,?)")->execute([$ten,$ma,$sotien,$mota]);
            setFlash('success','Thêm thành công!');
        }
        header('Location: allowances.php'); exit();
    }
    if ($type === 'delete') {
        $db->prepare("DELETE FROM phu_cap WHERE id=?")->execute([(int)$_POST['id']]);
        setFlash('success','Đã xóa!');
        header('Location: allowances.php'); exit();
    }
}

$list = $db->query("SELECT * FROM phu_cap ORDER BY id")->fetchAll();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Phụ cấp - <?= APP_NAME ?></title>
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
        <div><h1>Phụ cấp / Thưởng / Phạt</h1><p>Quản lý các khoản phụ cấp, thưởng và khấu trừ</p></div>
        <button class="btn btn-primary" onclick="openModal('modalPC')"><i class="fas fa-plus"></i> Thêm khoản</button>
      </div>

      <?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?></div><?php endif; ?>

      <!-- Gợi ý -->
      <div class="grid-3" style="margin-bottom:20px;">
        <?php
        $cards = [
          ['🍱', 'Phụ cấp ăn trưa', '730.000 đ/tháng', 'info'],
          ['⛽', 'Phụ cấp xăng xe', '500.000 đ/tháng', 'warning'],
          ['🏆', 'Thưởng KPI', 'Theo hiệu suất', 'success'],
          ['📱', 'PC điện thoại', '300.000 đ/tháng', 'primary'],
          ['🏠', 'PC nhà ở', '1.000.000 đ/tháng', 'primary'],
          ['⚠️', 'Phạt đi muộn', 'Theo quy định', 'danger'],
        ];
        foreach ($cards as [$icon, $name, $desc, $cls]): ?>
        <div class="stat-card <?= $cls ?>" style="flex-direction:column;align-items:flex-start;padding:14px 16px;cursor:pointer;gap:6px;">
          <span style="font-size:20px;"><?= $icon ?></span>
          <div>
            <p style="font-weight:600;font-size:13px;"><?= $name ?></p>
            <p style="font-size:12px;color:var(--gray);"><?= $desc ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="card">
        <div class="card-header"><span class="card-title"><i class="fas fa-gift"></i> Danh sách phụ cấp / thưởng</span></div>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Mã</th><th>Tên khoản</th><th>Số tiền</th><th>Mô tả</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
            <tbody>
              <?php foreach ($list as $pc): ?>
              <tr>
                <td><span class="badge badge-secondary"><?= clean($pc['ma_pc']) ?></span></td>
                <td><b><?= clean($pc['ten_phu_cap']) ?></b></td>
                <td style="color:var(--success);font-weight:700;"><?= formatMoney($pc['so_tien']) ?></td>
                <td style="color:var(--gray);"><?= clean($pc['mo_ta'] ?: '—') ?></td>
                <td><?= $pc['trang_thai'] ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge badge-secondary">Tắt</span>' ?></td>
                <td>
                  <div style="display:flex;gap:4px;">
                    <button class="btn btn-sm btn-info btn-icon" onclick='openEditPC(<?= json_encode($pc,JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-icon" onclick="confirmDelPC(<?= $pc['id'] ?>,'<?= addslashes($pc['ten_phu_cap']) ?>')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modalPC" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title" id="modalPCTitle"><i class="fas fa-gift"></i> Thêm khoản phụ cấp</span>
      <button class="modal-close" onclick="closeModal('modalPC')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="type" value="save">
      <input type="hidden" name="id" id="pc_id" value="0">
      <div class="modal-body">
        <div class="form-group"><label class="form-label required">Tên khoản</label><input type="text" name="ten_phu_cap" id="pc_ten" class="form-control" required></div>
        <div class="form-group"><label class="form-label required">Số tiền (đ)</label><input type="number" name="so_tien" id="pc_sotien" class="form-control" min="0"></div>
        <div class="form-group"><label class="form-label">Mô tả</label><textarea name="mo_ta" id="pc_mota" class="form-control"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalPC')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalDelPC" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header"><span class="modal-title" style="color:var(--danger);"><i class="fas fa-trash"></i> Xóa khoản</span><button class="modal-close" onclick="closeModal('modalDelPC')"><i class="fas fa-times"></i></button></div>
    <div class="modal-body"><p>Xóa khoản: <b id="delPCName"></b>?</p></div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('modalDelPC')">Hủy</button>
      <form method="POST" style="display:inline;"><input type="hidden" name="type" value="delete"><input type="hidden" name="id" id="delPCId"><button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Xóa</button></form>
    </div>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
function openEditPC(pc) {
  document.getElementById('modalPCTitle').innerHTML = '<i class="fas fa-edit"></i> Sửa khoản';
  document.getElementById('pc_id').value = pc.id;
  document.getElementById('pc_ten').value = pc.ten_phu_cap;
  document.getElementById('pc_sotien').value = pc.so_tien;
  document.getElementById('pc_mota').value = pc.mo_ta || '';
  openModal('modalPC');
}
function confirmDelPC(id, name) {
  document.getElementById('delPCId').value = id;
  document.getElementById('delPCName').textContent = name;
  openModal('modalDelPC');
}
</script>
</body></html>
