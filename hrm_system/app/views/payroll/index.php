<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tính lương - Đại Học Thành Đông</title>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout">
  <?php 
  $current_page = 'payroll';
  require __DIR__ . '/../layouts/sidebar.php'; 
  ?>
  <div class="main-content">
    <?php require __DIR__ . '/../layouts/header.php'; ?>
    <div class="page-content">
      <div class="page-header">
        <div><h1>Tính lương</h1><p>Tính toán và quản lý bảng lương nhân viên theo tháng</p></div>
      </div>

      <?php 
      $flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
      if ($flash): 
          unset($_SESSION['flash']);
      ?>
      <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= Helper::clean($flash['message']) ?></div>
      <?php endif; ?>

      <!-- Filter + Action -->
      <div class="card" style="margin-bottom:16px;">
        <div class="card-body" style="padding:14px 20px;">
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <form method="GET" style="display:flex;gap:8px;align-items:center;">
              <select name="thang" class="filter-input">
                <?php foreach ($months as $m): ?><option value="<?= $m ?>" <?= $m==$thang?'selected':'' ?>>Tháng <?= $m ?></option><?php endforeach; ?>
              </select>
              <select name="nam" class="filter-input">
                <?php foreach ($years as $y): ?><option value="<?= $y ?>" <?= $y==$nam?'selected':'' ?>><?= $y ?></option><?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Xem</button>
            </form>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="type" value="tinh_tat_ca">
              <input type="hidden" name="thang" value="<?= $thang ?>">
              <input type="hidden" name="nam" value="<?= $nam ?>">
              <button type="submit" class="btn btn-success" onclick="return confirm('Tính lương tự động cho tất cả nhân viên tháng <?= $thang ?>/<?= $nam ?>?')">
                <i class="fas fa-calculator"></i> Tính lương tự động
              </button>
            </form>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="type" value="duyet_tat_ca">
              <input type="hidden" name="thang" value="<?= $thang ?>">
              <input type="hidden" name="nam" value="<?= $nam ?>">
              <button type="submit" class="btn btn-warning" onclick="return confirm('Duyệt toàn bộ bảng lương đã tính trong tháng <?= $thang ?>/<?= $nam ?>?')">
                <i class="fas fa-check-double"></i> Duyệt tất cả
              </button>
            </form>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="type" value="thanh_toan_tat_ca">
              <input type="hidden" name="thang" value="<?= $thang ?>">
              <input type="hidden" name="nam" value="<?= $nam ?>">
              <button type="submit" class="btn btn-primary" onclick="return confirm('Xác nhận đã thanh toán lương cho toàn bộ cán bộ tháng <?= $thang ?>/<?= $nam ?>?')">
                <i class="fas fa-money-check-alt"></i> Thanh toán tất cả
              </button>
            </form>
            <a href="<?= Helper::route('salary-sheet?thang=' . $thang . '&nam=' . $nam) ?>" class="btn btn-info"><i class="fas fa-table"></i> Xem bảng lương</a>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fas fa-calculator"></i> Bảng lương Tháng <?= $thang ?>/<?= $nam ?></span>
          <span style="font-size:12px;color:var(--gray);">
            Đã tính: <b><?= count(array_filter($list, function($r) { return !empty($r['id']); })) ?></b> / <?= count($list) ?> NV
          </span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th width="50">STT</th>
                <th>Nhân viên</th>
                <th>Ngày công</th>
                <th>Lương theo công</th>
                <th>Phụ cấp</th>
                <th>Thưởng</th>
                <th>Khấu trừ</th>
                <th>Thực lĩnh</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php $stt = 1; foreach ($list as $row): ?>
              <tr>
                <td style="text-align:center;color:var(--gray);"><?= $stt++ ?></td>
                <td>
                  <div class="nv-info">
                    <div class="avatar"><?= mb_substr($row['ho_ten'],0,1) ?></div>
                    <div><h4><?= Helper::clean($row['ho_ten']) ?></h4><p><?= Helper::clean($row['ma_nv']) ?></p></div>
                  </div>
                </td>
                <td><?= $row['so_ngay_lam'] ?? '—' ?>/<?= $ngay_chuan ?></td>
                <td><?= $row['id'] ? Helper::formatMoney($row['luong_theo_cong']) : '—' ?></td>
                <td><?= $row['id'] ? Helper::formatMoney($row['phu_cap_an_trua'] + $row['phu_cap_xang_xe'] + $row['phu_cap_khac']) : '—' ?></td>
                <td><?= $row['id'] ? Helper::formatMoney($row['thuong_kpi'] + $row['thuong_khac']) : '—' ?></td>
                <td><?= $row['id'] ? Helper::formatMoney($row['tong_khau_tru']) : '—' ?></td>
                <td><b style="color:var(--success);"><?= $row['id'] ? Helper::formatMoney($row['thuc_linh']) : '—' ?></b></td>
                <td><?= $row['id'] ? Helper::badgeTrangThaiLuong($row['trang_thai']) : '<span class="badge badge-secondary">Chưa tính</span>' ?></td>
                <td>
                  <div style="display:flex;gap:4px;">
                    <button class="btn btn-sm btn-info btn-icon" data-tooltip="Tính/sửa lương" onclick='openLuong(<?= json_encode($row,JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                    <?php if ($row['id'] && $row['trang_thai'] === 'Nháp'): ?>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="type" value="duyet">
                      <input type="hidden" name="id" value="<?= $row['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-success btn-icon" data-tooltip="Duyệt" onclick="return confirm('Duyệt lương nhân viên này?')"><i class="fas fa-check"></i></button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <?php
            $totalThucLinh = array_sum(array_column($list, 'thuc_linh'));
            ?>
            <tfoot>
              <tr style="background:var(--bg);font-weight:700;">
                <td colspan="7">TỔNG QUỸ LƯƠNG</td>
                <td colspan="3" style="color:var(--success);font-size:15px;"><?= Helper::formatMoney($totalThucLinh) ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal sửa lương -->
<div class="modal-overlay" id="modalLuong" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title" id="modalLuongTitle"><i class="fas fa-calculator"></i> Tính lương</span>
      <button class="modal-close" onclick="closeModal('modalLuong')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" id="formLuong">
      <input type="hidden" name="type" value="save_one">
      <input type="hidden" name="id_nhan_vien" id="l_nv_id">
      <input type="hidden" name="thang" value="<?= $thang ?>">
      <input type="hidden" name="nam" value="<?= $nam ?>">
      <div class="modal-body">
        <div class="form-row col-3">
          <div class="form-group"><label class="form-label">Lương cơ bản</label><input type="number" name="luong_co_ban" id="l_lcb" class="form-control" onchange="calcLuong()"></div>
          <div class="form-group"><label class="form-label">Số ngày làm</label><input type="number" name="so_ngay_lam" id="l_snd" class="form-control" step="0.5" onchange="calcLuong()"></div>
          <div class="form-group"><label class="form-label">Ngày chuẩn</label><input type="number" name="so_ngay_chuan" id="l_snc" class="form-control" value="<?= $ngay_chuan ?>" onchange="calcLuong()"></div>
        </div>
        <hr style="margin:8px 0;">
        <div class="form-row col-3">
          <div class="form-group"><label class="form-label">Lương theo công</label><input type="number" name="luong_theo_cong" id="l_ltc" class="form-control" onchange="calcTotal()"></div>
          <div class="form-group"><label class="form-label">PC ăn trưa</label><input type="number" name="phu_cap_an_trua" id="l_pcat" class="form-control" value="730000" onchange="calcTotal()"></div>
          <div class="form-group"><label class="form-label">PC xăng xe</label><input type="number" name="phu_cap_xang_xe" id="l_pcxx" class="form-control" value="500000" onchange="calcTotal()"></div>
        </div>
        <div class="form-row col-3">
          <div class="form-group"><label class="form-label">PC khác</label><input type="number" name="phu_cap_khac" id="l_pck" class="form-control" value="0" onchange="calcTotal()"></div>
          <div class="form-group"><label class="form-label">Thưởng KPI</label><input type="number" name="thuong_kpi" id="l_tkpi" class="form-control" value="0" onchange="calcTotal()"></div>
          <div class="form-group"><label class="form-label">Thưởng khác</label><input type="number" name="thuong_khac" id="l_tk" class="form-control" value="0" onchange="calcTotal()"></div>
        </div>
        <div class="form-row col-3">
          <div class="form-group"><label class="form-label">Phạt / khấu trừ</label><input type="number" name="phat_di_muon" id="l_phat" class="form-control" value="0" onchange="calcTotal()"></div>
          <div class="form-group"><label class="form-label">BHXH (8%)</label><input type="number" name="bao_hiem_xa_hoi" id="l_bhxh" class="form-control" onchange="calcTotal()"></div>
          <div class="form-group"><label class="form-label">BHYT (1.5%)</label><input type="number" name="bao_hiem_y_te" id="l_bhyt" class="form-control" onchange="calcTotal()"></div>
        </div>
        <div class="form-row col-2">
          <div class="form-group"><label class="form-label">Khấu trừ khác</label><input type="number" name="khau_tru_khac" id="l_ktk" class="form-control" value="0" onchange="calcTotal()"></div>
          <div class="form-group"><label class="form-label">Thuế TNCN</label><input type="number" name="thue_tncn" id="l_tncn" class="form-control" onchange="calcTotal()"></div>
        </div>
        <div style="background:var(--bg);border-radius:8px;padding:14px;margin-top:8px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:600;">THỰC LĨNH</span>
            <span id="thuc_linh_preview" style="font-size:20px;font-weight:800;color:var(--success);">0 đ</span>
          </div>
        </div>
        <div class="form-group" style="margin-top:12px;">
          <label class="form-label">Ghi chú</label>
          <textarea name="ghi_chu" id="l_ghichu" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalLuong')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu bảng lương</button>
      </div>
    </form>
  </div>
</div>

<script src="<?= Helper::asset('js/main.js') ?>"></script>
<script>
function calcLuong() {
  const lcb = parseFloat(document.getElementById('l_lcb').value) || 0;
  const snd = parseFloat(document.getElementById('l_snd').value) || 0;
  const snc = parseFloat(document.getElementById('l_snc').value) || <?= $ngay_chuan ?>;
  const ltc = snc > 0 ? (lcb / snc * snd) : 0;
  document.getElementById('l_ltc').value = Math.round(ltc);
  document.getElementById('l_bhxh').value = Math.round(lcb * 0.08);
  document.getElementById('l_bhyt').value = Math.round(lcb * 0.015);
  calcTotal();
}

function calcTotal() {
  const ids = ['l_ltc','l_pcat','l_pcxx','l_pck','l_tkpi','l_tk'];
  const kIds = ['l_phat','l_ktk','l_bhxh','l_bhyt','l_tncn'];
  const thu = ids.reduce((s,id) => s + (parseFloat(document.getElementById(id).value)||0), 0);
  const ktr = kIds.reduce((s,id) => s + (parseFloat(document.getElementById(id).value)||0), 0);
  const thucLinh = thu - ktr;
  document.getElementById('thuc_linh_preview').textContent = new Intl.NumberFormat('vi-VN').format(Math.round(thucLinh)) + ' đ';
}

function openLuong(row) {
  document.getElementById('modalLuongTitle').innerHTML = '<i class="fas fa-calculator"></i> Tính lương: ' + row.ho_ten;
  document.getElementById('l_nv_id').value = row.nv_id;
  document.getElementById('l_lcb').value = row.luong_co_ban || 0;
  document.getElementById('l_snd').value = row.so_ngay_lam || <?= $ngay_chuan ?>;
  document.getElementById('l_snc').value = row.so_ngay_chuan || <?= $ngay_chuan ?>;
  document.getElementById('l_ltc').value = row.luong_theo_cong || 0;
  document.getElementById('l_pcat').value = row.phu_cap_an_trua || 730000;
  document.getElementById('l_pcxx').value = row.phu_cap_xang_xe || 500000;
  document.getElementById('l_pck').value = row.phu_cap_khac || 0;
  document.getElementById('l_tkpi').value = row.thuong_kpi || 0;
  document.getElementById('l_tk').value = row.thuong_khac || 0;
  document.getElementById('l_phat').value = row.phat_di_muon || 0;
  document.getElementById('l_ktk').value = row.khau_tru_khac || 0;
  document.getElementById('l_bhxh').value = row.bao_hiem_xa_hoi || Math.round((row.luong_co_ban||0)*0.08);
  document.getElementById('l_bhyt').value = row.bao_hiem_y_te || Math.round((row.luong_co_ban||0)*0.015);
  document.getElementById('l_tncn').value = row.thue_tncn || 0;
  document.getElementById('l_ghichu').value = row.ghi_chu || '';
  if (!row.id) calcLuong(); else calcTotal();
  openModal('modalLuong');
}
</script>
</body>
</html>
