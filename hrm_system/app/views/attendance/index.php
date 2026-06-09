<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Helper::clean($pageTitle) ?></title>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.attendance-toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.attendance-note{color:var(--gray);font-size:13px;margin-top:8px}.cc-input{width:86px;min-width:76px;padding:9px 10px;border:1px solid #dbe4ef;border-radius:10px;background:#fff;text-align:center;font-weight:600}.cc-note{min-width:170px;text-align:left}.cc-actions{display:flex;gap:8px;justify-content:flex-end;align-items:center}.attendance-table th,.attendance-table td{vertical-align:middle}.attendance-table tbody tr:hover{background:#f8fbff}.bulk-save-bar{position:sticky;bottom:0;background:#ffffff;border-top:1px solid #e6edf5;padding:14px 18px;display:flex;justify-content:space-between;gap:12px;align-items:center;box-shadow:0 -8px 20px rgba(15,23,42,.06);z-index:2}.quick-fill{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.quick-fill .btn{white-space:nowrap}@media(max-width:768px){.cc-input{width:72px;padding:8px}.cc-note{min-width:130px}.bulk-save-bar{align-items:flex-start;flex-direction:column}.cc-actions{justify-content:flex-start}}
</style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/../layouts/sidebar.php'; ?>
  <div class="main-content">
    <?php require __DIR__ . '/../layouts/header.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div>
          <h1><?= Helper::clean($pageTitle) ?></h1>
          <p><?= $onlyMine ? 'Theo dõi công cá nhân theo từng tháng' : 'Admin/HR chấm công toàn bộ nhân viên theo tháng' ?></p>
        </div>
      </div>

      <?php $flash = $_SESSION['flash'] ?? null; if ($flash): unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= Helper::clean($flash['type']) ?>"><i class="fas fa-info-circle"></i> <?= Helper::clean($flash['message']) ?></div>
      <?php endif; ?>

      <!-- Thống kê chấm công cá nhân của nhân viên -->
      <?php if ($onlyMine && !empty($rows)): 
        $myRow = $rows[0]; 
      ?>
      <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card primary">
          <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= floatval($myRow['so_ngay_lam'] ?? 0) ?></div>
            <div class="stat-label">Ngày làm việc</div>
          </div>
        </div>
        <div class="stat-card success">
          <div class="stat-icon"><i class="fas fa-umbrella-beach"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= intval($myRow['so_ngay_phep'] ?? 0) ?></div>
            <div class="stat-label">Nghỉ phép năm</div>
          </div>
        </div>
        <div class="stat-card danger">
          <div class="stat-icon"><i class="fas fa-user-times"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= intval($myRow['so_ngay_vang'] ?? 0) ?></div>
            <div class="stat-label">Nghỉ không phép (Vắng)</div>
          </div>
        </div>
        <div class="stat-card info">
          <div class="stat-icon"><i class="fas fa-business-time"></i></div>
          <div class="stat-info">
            <div class="stat-value"><?= floatval($myRow['so_gio_tang_ca'] ?? 0) ?></div>
            <div class="stat-label">Giờ tăng ca (OT)</div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="card" style="margin-bottom:16px;">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" class="attendance-toolbar">
            <select name="thang" class="filter-input">
              <?php foreach ($months as $m): ?>
                <option value="<?= $m ?>" <?= $m == $thang ? 'selected' : '' ?>>Tháng <?= $m ?></option>
              <?php endforeach; ?>
            </select>
            <select name="nam" class="filter-input">
              <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $y == $nam ? 'selected' : '' ?>><?= $y ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-primary"><i class="fas fa-search"></i> Xem</button>
          </form>
          <?php if (!$onlyMine): ?>
            <div class="attendance-note"><i class="fas fa-circle-info"></i> Có thể nhập trực tiếp từng dòng rồi bấm <b>Lưu chấm công toàn bộ</b>. Nút Sửa vẫn dùng để cập nhật riêng một nhân viên.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fas fa-calendar-check"></i> Chấm công tháng <?= $thang ?>/<?= $nam ?></span>
          <span style="font-size:12px;color:var(--gray);">Tổng: <b><?= count($rows) ?></b></span>
        </div>

        <?php if (!$onlyMine): ?>
        <form method="POST" id="bulkAttendanceForm">
          <input type="hidden" name="bulk_attendance" value="1">
          <input type="hidden" name="thang" value="<?= $thang ?>">
          <input type="hidden" name="nam" value="<?= $nam ?>">
        <?php endif; ?>

        <div class="table-responsive">
          <table class="table attendance-table">
            <thead>
              <tr>
                <th>Nhân viên</th>
                <th>Phòng ban</th>
                <th>Ngày làm</th>
                <th>Nghỉ</th>
                <th>Phép</th>
                <th>Vắng</th>
                <th>Tăng ca</th>
                <th>Ghi chú</th>
                <?php if (!$onlyMine): ?><th style="text-align:right;">Thao tác</th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="<?= $onlyMine ? 8 : 9 ?>" style="text-align:center;color:var(--gray);padding:24px;">Không có nhân viên để chấm công</td></tr>
              <?php endif; ?>

              <?php foreach ($rows as $row): ?>
                <?php $idNhanVien = (int)($row['id_nhan_vien'] ?? 0); ?>
                <tr>
                  <td>
                    <div class="nv-info">
                      <?php if (!empty($row['hinh_anh'])): ?>
                        <img src="<?= BASE_URL . '/' . $row['hinh_anh'] ?>" alt="Ảnh thẻ" style="width:34px; height:34px; border-radius:50%; object-fit:cover; flex-shrink:0; border:1px solid var(--border);">
                      <?php else: ?>
                        <div class="avatar"><?= Helper::clean(mb_substr($row['ho_ten'] ?? 'N', 0, 1)) ?></div>
                      <?php endif; ?>
                      <div><h4><?= Helper::clean($row['ho_ten'] ?? '') ?></h4><p><?= Helper::clean($row['ma_nv'] ?? '') ?></p></div>
                    </div>
                  </td>
                  <td><?= Helper::clean($row['ten_pb'] ?? '') ?></td>

                  <?php if (!$onlyMine): ?>
                    <td><input class="cc-input" type="number" step="0.5" min="0" name="attendance[<?= $idNhanVien ?>][so_ngay_lam]" value="<?= Helper::clean($row['so_ngay_lam'] ?? 26) ?>"></td>
                    <td><input class="cc-input" type="number" min="0" name="attendance[<?= $idNhanVien ?>][so_ngay_nghi]" value="<?= Helper::clean($row['so_ngay_nghi'] ?? 0) ?>"></td>
                    <td><input class="cc-input" type="number" min="0" name="attendance[<?= $idNhanVien ?>][so_ngay_phep]" value="<?= Helper::clean($row['so_ngay_phep'] ?? 0) ?>"></td>
                    <td><input class="cc-input" type="number" min="0" name="attendance[<?= $idNhanVien ?>][so_ngay_vang]" value="<?= Helper::clean($row['so_ngay_vang'] ?? 0) ?>"></td>
                    <td><input class="cc-input" type="number" step="0.5" min="0" name="attendance[<?= $idNhanVien ?>][so_gio_tang_ca]" value="<?= Helper::clean($row['so_gio_tang_ca'] ?? 0) ?>"></td>
                    <td><input class="cc-input cc-note" type="text" name="attendance[<?= $idNhanVien ?>][ghi_chu]" value="<?= Helper::clean($row['ghi_chu'] ?? '') ?>"></td>
                    <td>
                      <div class="cc-actions">
                        <button type="button" class="btn btn-sm btn-info" onclick='openCC(<?= json_encode($row, JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i> Sửa</button>
                      </div>
                    </td>
                  <?php else: ?>
                    <td><?= Helper::clean($row['so_ngay_lam'] ?? 0) ?></td>
                    <td><?= Helper::clean($row['so_ngay_nghi'] ?? 0) ?></td>
                    <td><?= Helper::clean($row['so_ngay_phep'] ?? 0) ?></td>
                    <td><?= Helper::clean($row['so_ngay_vang'] ?? 0) ?></td>
                    <td><?= Helper::clean($row['so_gio_tang_ca'] ?? 0) ?></td>
                    <td><?= Helper::clean($row['ghi_chu'] ?? '') ?></td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php if (!$onlyMine): ?>
          <div class="bulk-save-bar">
            <div class="quick-fill">
              <button type="button" class="btn btn-outline" onclick="fillWorkDays(26)"><i class="fas fa-wand-magic-sparkles"></i> Điền 26 công</button>
              <button type="button" class="btn btn-outline" onclick="fillWorkDays(0)"><i class="fas fa-eraser"></i> Xóa ngày làm</button>
            </div>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Lưu chấm công toàn bộ</button>
          </div>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (!$onlyMine): ?>
<div class="modal-overlay" id="ccModal" style="display:none;">
  <div class="modal">
    <div class="modal-header"><span class="modal-title"><i class="fas fa-calendar-check"></i> Cập nhật chấm công</span><button class="modal-close" onclick="closeModal('ccModal')"><i class="fas fa-times"></i></button></div>
    <form method="POST">
      <input type="hidden" name="id_nhan_vien" id="cc_id_nhan_vien">
      <input type="hidden" name="thang" value="<?= $thang ?>">
      <input type="hidden" name="nam" value="<?= $nam ?>">
      <div class="modal-body">
        <div class="form-row col-2">
          <div class="form-group"><label class="form-label">Ngày làm</label><input class="form-control" type="number" step="0.5" name="so_ngay_lam" id="cc_so_ngay_lam"></div>
          <div class="form-group"><label class="form-label">Ngày nghỉ</label><input class="form-control" type="number" name="so_ngay_nghi" id="cc_so_ngay_nghi"></div>
          <div class="form-group"><label class="form-label">Ngày phép</label><input class="form-control" type="number" name="so_ngay_phep" id="cc_so_ngay_phep"></div>
          <div class="form-group"><label class="form-label">Ngày vắng</label><input class="form-control" type="number" name="so_ngay_vang" id="cc_so_ngay_vang"></div>
          <div class="form-group"><label class="form-label">Giờ tăng ca</label><input class="form-control" type="number" step="0.5" name="so_gio_tang_ca" id="cc_so_gio_tang_ca"></div>
        </div>
        <div class="form-group"><label class="form-label">Ghi chú</label><textarea class="form-control" name="ghi_chu" id="cc_ghi_chu"></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('ccModal')">Hủy</button><button class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button></div>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="<?= Helper::asset('js/main.js') ?>"></script>
<script>
function openCC(row) {
  ['id_nhan_vien','so_ngay_lam','so_ngay_nghi','so_ngay_phep','so_ngay_vang','so_gio_tang_ca','ghi_chu'].forEach(function(k) {
    const el = document.getElementById('cc_' + k);
    if (el) el.value = row[k] ?? (k === 'so_ngay_lam' ? 26 : 0);
  });
  openModal('ccModal');
}
function fillWorkDays(value) {
  document.querySelectorAll('input[name$="[so_ngay_lam]"]').forEach(function(input) {
    input.value = value;
  });
}
</script>
</body>
</html>
