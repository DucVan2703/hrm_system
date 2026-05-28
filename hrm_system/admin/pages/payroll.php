<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireKetoan();
$pageTitle = 'Tính lương';
$db = getDB();

$thang = (int)($_GET['thang'] ?? date('n'));
$nam = (int)($_GET['nam'] ?? date('Y'));

// Xử lý tính lương hàng loạt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';

    if ($type === 'tinh_tat_ca') {
        $t = (int)$_POST['thang']; $n = (int)$_POST['nam'];
        // Lấy tất cả NV đang làm có chấm công
        $nvList = $db->prepare("
            SELECT nv.id, nv.luong_co_ban, cc.so_ngay_lam, cc.so_gio_tang_ca
            FROM nhan_vien nv
            JOIN cham_cong cc ON nv.id=cc.id_nhan_vien AND cc.thang=? AND cc.nam=?
            WHERE nv.trang_thai='Đang làm'
        ");
        $nvList->execute([$t,$n]);
        $count = 0;
        foreach ($nvList->fetchAll() as $nv) {
            $luong_theo_cong = tinhLuong($nv['luong_co_ban'], $nv['so_ngay_lam']);
            $bhxh = tinhBHXH($nv['luong_co_ban']);
            $bhyt = tinhBHYT($nv['luong_co_ban']);
            $pc_an = 730000; $pc_xe = 500000;
            $tong_thu_nhap = $luong_theo_cong + $pc_an + $pc_xe;
            $tncn = tinhTNCN($tong_thu_nhap - $bhxh - $bhyt);
            $tong_khau_tru = $bhxh + $bhyt + $tncn;
            $thuc_linh = $tong_thu_nhap - $tong_khau_tru;

            // Check existing
            $ex = $db->prepare("SELECT id,trang_thai FROM bang_luong WHERE id_nhan_vien=? AND thang=? AND nam=?");
            $ex->execute([$nv['id'],$t,$n]);
            $existing = $ex->fetch();

            if ($existing && $existing['trang_thai'] !== 'Nháp') continue; // Không ghi đè đã duyệt

            if ($existing) {
                $db->prepare("UPDATE bang_luong SET luong_co_ban=?,so_ngay_lam=?,luong_theo_cong=?,phu_cap_an_trua=?,phu_cap_xang_xe=?,bao_hiem_xa_hoi=?,bao_hiem_y_te=?,thue_tncn=?,tong_thu_nhap=?,tong_khau_tru=?,thuc_linh=? WHERE id_nhan_vien=? AND thang=? AND nam=?")
                    ->execute([$nv['luong_co_ban'],$nv['so_ngay_lam'],$luong_theo_cong,$pc_an,$pc_xe,$bhxh,$bhyt,$tncn,$tong_thu_nhap,$tong_khau_tru,$thuc_linh,$nv['id'],$t,$n]);
            } else {
                $db->prepare("INSERT INTO bang_luong (id_nhan_vien,thang,nam,luong_co_ban,so_ngay_lam,so_ngay_chuan,luong_theo_cong,phu_cap_an_trua,phu_cap_xang_xe,bao_hiem_xa_hoi,bao_hiem_y_te,thue_tncn,tong_thu_nhap,tong_khau_tru,thuc_linh,trang_thai) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'Nháp')")
                    ->execute([$nv['id'],$t,$n,$nv['luong_co_ban'],$nv['so_ngay_lam'],NGAY_CHUAN,$luong_theo_cong,$pc_an,$pc_xe,$bhxh,$bhyt,$tncn,$tong_thu_nhap,$tong_khau_tru,$thuc_linh]);
            }
            $count++;
        }
        setFlash('success', "Đã tính lương cho $count nhân viên tháng $t/$n!");
        header("Location: payroll.php?thang=$t&nam=$n"); exit();
    }

    if ($type === 'save_one') {
        $id_nv = (int)$_POST['id_nhan_vien'];
        $t = (int)$_POST['thang']; $n = (int)$_POST['nam'];
        $data = [
            'luong_co_ban' => (float)$_POST['luong_co_ban'],
            'so_ngay_lam' => (float)$_POST['so_ngay_lam'],
            'so_ngay_chuan' => (int)($_POST['so_ngay_chuan'] ?? NGAY_CHUAN),
            'luong_theo_cong' => (float)$_POST['luong_theo_cong'],
            'phu_cap_an_trua' => (float)$_POST['phu_cap_an_trua'],
            'phu_cap_xang_xe' => (float)$_POST['phu_cap_xang_xe'],
            'phu_cap_khac' => (float)$_POST['phu_cap_khac'],
            'thuong_kpi' => (float)$_POST['thuong_kpi'],
            'thuong_khac' => (float)$_POST['thuong_khac'],
            'phat_di_muon' => (float)$_POST['phat_di_muon'],
            'khau_tru_khac' => (float)$_POST['khau_tru_khac'],
            'bao_hiem_xa_hoi' => (float)$_POST['bao_hiem_xa_hoi'],
            'bao_hiem_y_te' => (float)$_POST['bao_hiem_y_te'],
            'thue_tncn' => (float)$_POST['thue_tncn'],
            'ghi_chu' => sanitize($_POST['ghi_chu'] ?? ''),
        ];
        $tong_thu = $data['luong_theo_cong'] + $data['phu_cap_an_trua'] + $data['phu_cap_xang_xe'] + $data['phu_cap_khac'] + $data['thuong_kpi'] + $data['thuong_khac'];
        $tong_khatr = $data['phat_di_muon'] + $data['khau_tru_khac'] + $data['bao_hiem_xa_hoi'] + $data['bao_hiem_y_te'] + $data['thue_tncn'];
        $data['tong_thu_nhap'] = $tong_thu;
        $data['tong_khau_tru'] = $tong_khatr;
        $data['thuc_linh'] = $tong_thu - $tong_khatr;

        $ex = $db->prepare("SELECT id FROM bang_luong WHERE id_nhan_vien=? AND thang=? AND nam=?");
        $ex->execute([$id_nv,$t,$n]);
        if ($ex->fetch()) {
            $sets = implode(',', array_map(fn($k) => "$k=:$k", array_keys($data)));
            $db->prepare("UPDATE bang_luong SET $sets WHERE id_nhan_vien=:id_nv AND thang=:t AND nam=:n")->execute(array_merge($data,['id_nv'=>$id_nv,'t'=>$t,'n'=>$n]));
        } else {
            $data['id_nhan_vien'] = $id_nv; $data['thang'] = $t; $data['nam'] = $n; $data['trang_thai'] = 'Nháp';
            $cols = implode(',', array_keys($data));
            $vals = implode(',', array_map(fn($k) => ":$k", array_keys($data)));
            $db->prepare("INSERT INTO bang_luong ($cols) VALUES ($vals)")->execute($data);
        }
        setFlash('success','Đã lưu bảng lương!');
        header("Location: payroll.php?thang=$t&nam=$n"); exit();
    }

    if ($type === 'duyet') {
        $id = (int)$_POST['id'];
        $db->prepare("UPDATE bang_luong SET trang_thai='Đã duyệt' WHERE id=?")->execute([$id]);
        setFlash('success','Đã duyệt bảng lương!');
        header("Location: payroll.php?thang=$thang&nam=$nam"); exit();
    }

    if ($type === 'duyet_tat_ca') {
        $t = (int)$_POST['thang']; $n = (int)$_POST['nam'];
        $db->prepare("UPDATE bang_luong SET trang_thai='Đã duyệt' WHERE thang=? AND nam=? AND trang_thai='Nháp'")->execute([$t,$n]);
        setFlash('success', "Đã duyệt toàn bộ bảng lương tháng $t/$n!");
        header("Location: payroll.php?thang=$t&nam=$n"); exit();
    }

    if ($type === 'thanh_toan_tat_ca') {
        $t = (int)$_POST['thang']; $n = (int)$_POST['nam'];
        $db->prepare("UPDATE bang_luong SET trang_thai='Đã thanh toán' WHERE thang=? AND nam=? AND trang_thai='Đã duyệt'")->execute([$t,$n]);
        setFlash('success', "Đã xác nhận thanh toán toàn bộ bảng lương tháng $t/$n!");
        header("Location: payroll.php?thang=$t&nam=$n"); exit();
    }
}

// Danh sách lương
$stmt = $db->prepare("
    SELECT nv.id as nv_id, nv.ma_nv, nv.ho_ten, nv.luong_co_ban,
           pb.ten_pb, cv.ten_cv,
           cc.so_ngay_lam, cc.so_gio_tang_ca,
           bl.*
    FROM nhan_vien nv
    LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id
    LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id
    LEFT JOIN cham_cong cc ON nv.id=cc.id_nhan_vien AND cc.thang=? AND cc.nam=?
    LEFT JOIN bang_luong bl ON nv.id=bl.id_nhan_vien AND bl.thang=? AND bl.nam=?
    WHERE nv.trang_thai='Đang làm'
    ORDER BY cv.id ASC, nv.ma_nv ASC
");
$stmt->execute([$thang,$nam,$thang,$nam]);
$list = $stmt->fetchAll();

$flash = getFlash();
$months = range(1,12); $years = range(date('Y')-2, date('Y')+1);
?>
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tính lương - <?= APP_NAME ?></title>
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
        <div><h1>Tính lương</h1><p>Tính toán và quản lý bảng lương nhân viên theo tháng</p></div>
      </div>

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>"><i class="fas fa-info-circle"></i> <?= clean($flash['message']) ?></div>
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
            <a href="salary-sheet.php?thang=<?= $thang ?>&nam=<?= $nam ?>" class="btn btn-info"><i class="fas fa-table"></i> Xem bảng lương</a>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fas fa-calculator"></i> Bảng lương Tháng <?= $thang ?>/<?= $nam ?></span>
          <span style="font-size:12px;color:var(--gray);">
            Đã tính: <b><?= count(array_filter($list, fn($r) => $r['id'])) ?></b> / <?= count($list) ?> NV
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
                    <div><h4><?= clean($row['ho_ten']) ?></h4><p><?= clean($row['ma_nv']) ?></p></div>
                  </div>
                </td>
                <td><?= $row['so_ngay_lam'] ?? '—' ?>/<?= NGAY_CHUAN ?></td>
                <td><?= $row['id'] ? formatMoney($row['luong_theo_cong']) : '—' ?></td>
                <td><?= $row['id'] ? formatMoney($row['phu_cap_an_trua'] + $row['phu_cap_xang_xe'] + $row['phu_cap_khac']) : '—' ?></td>
                <td><?= $row['id'] ? formatMoney($row['thuong_kpi'] + $row['thuong_khac']) : '—' ?></td>
                <td><?= $row['id'] ? formatMoney($row['tong_khau_tru']) : '—' ?></td>
                <td><b style="color:var(--success);"><?= $row['id'] ? formatMoney($row['thuc_linh']) : '—' ?></b></td>
                <td><?= $row['id'] ? badgeTrangThaiLuong($row['trang_thai']) : '<span class="badge badge-secondary">Chưa tính</span>' ?></td>
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
                <td colspan="3" style="color:var(--success);font-size:15px;"><?= formatMoney($totalThucLinh) ?></td>
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
          <div class="form-group"><label class="form-label">Ngày chuẩn</label><input type="number" name="so_ngay_chuan" id="l_snc" class="form-control" value="<?= NGAY_CHUAN ?>" onchange="calcLuong()"></div>
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

<script src="../../assets/js/main.js"></script>
<script>
function calcLuong() {
  const lcb = parseFloat(document.getElementById('l_lcb').value) || 0;
  const snd = parseFloat(document.getElementById('l_snd').value) || 0;
  const snc = parseFloat(document.getElementById('l_snc').value) || <?= NGAY_CHUAN ?>;
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
  document.getElementById('l_snd').value = row.so_ngay_lam || <?= NGAY_CHUAN ?>;
  document.getElementById('l_snc').value = row.so_ngay_chuan || <?= NGAY_CHUAN ?>;
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
</body></html>
