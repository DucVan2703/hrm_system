<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
requireHR();

$pageTitle = 'Quản lý nhân viên';
$db = getDB();
$flash = getFlash();

// Xử lý thêm/sửa/xóa
$action = $_GET['action'] ?? '';
$edit_id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';

    if ($type === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'ho_ten' => sanitize($_POST['ho_ten'] ?? ''),
            'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
            'gioi_tinh' => $_POST['gioi_tinh'] ?? 'Nam',
            'cccd' => sanitize($_POST['cccd'] ?? ''),
            'so_dien_thoai' => sanitize($_POST['so_dien_thoai'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'dia_chi' => sanitize($_POST['dia_chi'] ?? ''),
            'id_phong_ban' => (int)($_POST['id_phong_ban'] ?? 0) ?: null,
            'id_chuc_vu' => (int)($_POST['id_chuc_vu'] ?? 0) ?: null,
            'luong_co_ban' => (float)str_replace(',', '', $_POST['luong_co_ban'] ?? 0),
            'ngay_vao_lam' => $_POST['ngay_vao_lam'] ?? null,
            'trang_thai' => $_POST['trang_thai'] ?? 'Đang làm',
        ];
        if (empty($data['ho_ten'])) {
            setFlash('danger', 'Họ tên không được để trống!');
        } else {
            // Xử lý upload chữ ký
            if (isset($_FILES['chu_ky']) && $_FILES['chu_ky']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['chu_ky']['name'], PATHINFO_EXTENSION);
                $filename = 'sig_' . ($id > 0 ? $id : 'new') . '_' . time() . '.' . $ext;
                $target = __DIR__ . '/../../assets/uploads/signatures/' . $filename;
                if (move_uploaded_file($_FILES['chu_ky']['tmp_name'], $target)) {
                    $data['chu_ky'] = 'assets/uploads/signatures/' . $filename;
                }
            }

            if ($id > 0) {
                $cols = [];
                foreach ($data as $k => $v) { $cols[] = "$k=:$k"; }
                $sql = "UPDATE nhan_vien SET " . implode(',', $cols) . " WHERE id=:id";
                $data['id'] = $id;
                $db->prepare($sql)->execute($data);
                setFlash('success', 'Cập nhật nhân viên thành công!');
            } else {
                $data['ma_nv'] = genMaNV();
                $cols = implode(',', array_keys($data));
                $vals = ':' . implode(',:', array_keys($data));
                $sql = "INSERT INTO nhan_vien ($cols) VALUES ($vals)";
                $db->prepare($sql)->execute($data);
                setFlash('success', 'Thêm nhân viên thành công! Mã NV: ' . $data['ma_nv']);
            }
        }
        header('Location: employees.php'); exit();
    }

    if ($type === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM nhan_vien WHERE id = ?")->execute([$id]);
        setFlash('success', 'Đã xóa nhân viên!');
        header('Location: employees.php'); exit();
    }
}

// Lọc + tìm kiếm
$search = sanitize($_GET['search'] ?? '');
$pb_filter = (int)($_GET['pb'] ?? 0);
$tt_filter = sanitize($_GET['tt'] ?? '');
$page_num = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;

$where = []; $params = [];
if ($search) { $where[] = "(nv.ho_ten LIKE ? OR nv.ma_nv LIKE ? OR nv.email LIKE ?)"; $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]); }
if ($pb_filter) { $where[] = "nv.id_phong_ban = ?"; $params[] = $pb_filter; }
if ($tt_filter) { $where[] = "nv.trang_thai = ?"; $params[] = $tt_filter; }
$whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countSql = "SELECT COUNT(*) FROM nhan_vien nv $whereStr";
$stmt = $db->prepare($countSql); $stmt->execute($params);
$total = $stmt->fetchColumn();
$pg = paginate($total, $per_page, $page_num);

$sql = "SELECT nv.*, pb.ten_pb, cv.ten_cv FROM nhan_vien nv LEFT JOIN phong_ban pb ON nv.id_phong_ban=pb.id LEFT JOIN chuc_vu cv ON nv.id_chuc_vu=cv.id $whereStr ORDER BY cv.id ASC, nv.id DESC LIMIT {$pg['per_page']} OFFSET {$pg['offset']}";
$stmt = $db->prepare($sql); $stmt->execute($params);
$list = $stmt->fetchAll();

$phong_bans = $db->query("SELECT * FROM phong_ban WHERE trang_thai=1 ORDER BY ten_pb")->fetchAll();
$chuc_vus = $db->query("SELECT * FROM chuc_vu WHERE trang_thai=1 ORDER BY ten_cv")->fetchAll();

// Dữ liệu edit
$edit_nv = null;
if ($edit_id > 0) {
    $stmt = $db->prepare("SELECT * FROM nhan_vien WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_nv = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Nhân viên - <?= APP_NAME ?></title>
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
          <h1>Quản lý nhân viên</h1>
          <p>Danh sách và quản lý thông tin toàn bộ nhân viên</p>
        </div>
        <div class="page-actions">
          <a href="employee-export.php?search=<?= urlencode($search) ?>&pb=<?= $pb_filter ?>&tt=<?= urlencode($tt_filter) ?>" class="btn btn-outline">
            <i class="fas fa-file-excel"></i> Xuất Excel
          </a>
          <button class="btn btn-primary" onclick="openModal('modalNV')">
            <i class="fas fa-user-plus"></i> Thêm nhân viên
          </button>
        </div>
      </div>

      <?php $f = getFlash(); if ($f || $flash): $fl = $f ?: $flash; ?>
      <div class="alert alert-<?= $fl['type'] ?>">
        <i class="fas fa-<?= $fl['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
        <?= clean($fl['message']) ?>
      </div>
      <?php endif; ?>

      <!-- FILTERS -->
      <div class="card" style="margin-bottom:16px;">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            <input type="text" name="search" class="filter-input search-input" placeholder="🔍 Tìm tên, mã NV, email..." value="<?= clean($search) ?>">
            <select name="pb" class="filter-input">
              <option value="">Tất cả phòng ban</option>
              <?php foreach ($phong_bans as $pb): ?>
              <option value="<?= $pb['id'] ?>" <?= $pb_filter == $pb['id'] ? 'selected' : '' ?>><?= clean($pb['ten_pb']) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="tt" class="filter-input">
              <option value="">Tất cả trạng thái</option>
              <option value="Đang làm" <?= $tt_filter === 'Đang làm' ? 'selected' : '' ?>>Đang làm</option>
              <option value="Nghỉ việc" <?= $tt_filter === 'Nghỉ việc' ? 'selected' : '' ?>>Nghỉ việc</option>
              <option value="Tạm nghỉ" <?= $tt_filter === 'Tạm nghỉ' ? 'selected' : '' ?>>Tạm nghỉ</option>
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Lọc</button>
            <a href="employees.php" class="btn btn-outline"><i class="fas fa-times"></i> Xóa lọc</a>
          </form>
        </div>
      </div>

      <!-- TABLE -->
      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fas fa-users"></i> Danh sách nhân viên</span>
          <span style="font-size:12px;color:var(--gray);">Tổng: <b><?= $total ?></b> nhân viên</span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th width="50">STT</th>
                <th>Nhân viên</th>
                <th>Phòng ban</th>
                <th>Chức vụ</th>
                <th>Điện thoại</th>
                <th>Lương cơ bản</th>
                <th>Ngày vào làm</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($list)): ?>
              <tr><td colspan="9">
                <div class="empty-state"><i class="fas fa-users"></i><h3>Không tìm thấy nhân viên</h3></div>
              </td></tr>
              <?php else: $stt = $pg['offset'] + 1; foreach ($list as $nv): ?>
              <tr>
                <td style="text-align:center;color:var(--gray);"><?= $stt++ ?></td>
                <td>
                  <div class="nv-info">
                    <div class="avatar"><?= mb_substr($nv['ho_ten'], 0, 1) ?></div>
                    <div><h4><?= clean($nv['ho_ten']) ?></h4><p><?= clean($nv['ma_nv']) ?></p></div>
                  </div>
                </td>
                <td><?= clean($nv['ten_pb'] ?? '—') ?></td>
                <td><?= clean($nv['ten_cv'] ?? '—') ?></td>
                <td><?= clean($nv['so_dien_thoai'] ?? '—') ?></td>
                <td><b><?= formatMoney($nv['luong_co_ban']) ?></b></td>
                <td><?= formatDate($nv['ngay_vao_lam']) ?></td>
                <td><?= badgeTrangThaiNV($nv['trang_thai']) ?></td>
                <td>
                  <div style="display:flex;gap:4px;">
                    <a href="employee-detail.php?id=<?= $nv['id'] ?>" class="btn btn-sm btn-outline btn-icon" data-tooltip="Xem chi tiết"><i class="fas fa-eye"></i></a>
                    <button class="btn btn-sm btn-info btn-icon" data-tooltip="Sửa" onclick='openEditNV(<?= json_encode($nv, JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger btn-icon" data-tooltip="Xóa" onclick="confirmDelete(<?= $nv['id'] ?>, '<?= addslashes($nv['ho_ten']) ?>')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($pg['total_pages'] > 1): ?>
        <div class="card-footer">
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:13px;color:var(--gray);">Hiển thị <?= count($list) ?> / <?= $total ?> nhân viên</span>
            <div class="pagination">
              <?php for ($i = 1; $i <= $pg['total_pages']; $i++): ?>
              <a href="?search=<?= urlencode($search) ?>&pb=<?= $pb_filter ?>&tt=<?= urlencode($tt_filter) ?>&page=<?= $i ?>" class="page-btn <?= $i == $page_num ? 'active' : '' ?>"><?= $i ?></a>
              <?php endfor; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- MODAL THÊM/SỬA NHÂN VIÊN -->
<div class="modal-overlay" id="modalNV" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title" id="modalNVTitle"><i class="fas fa-user-plus"></i> Thêm nhân viên</span>
      <button class="modal-close" onclick="closeModal('modalNV')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="type" value="save">
      <input type="hidden" name="id" id="nv_id" value="0">
      <div class="modal-body">
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label required">Họ và tên</label>
            <input type="text" name="ho_ten" id="nv_ho_ten" class="form-control" placeholder="Nguyễn Văn A" required>
          </div>
          <div class="form-group">
            <label class="form-label required">Ngày sinh</label>
            <input type="date" name="ngay_sinh" id="nv_ngay_sinh" class="form-control">
          </div>
        </div>
        <div class="form-row col-3">
          <div class="form-group">
            <label class="form-label">Giới tính</label>
            <select name="gioi_tinh" id="nv_gioi_tinh" class="form-control">
              <option>Nam</option><option>Nữ</option><option>Khác</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Số CCCD</label>
            <input type="text" name="cccd" id="nv_cccd" class="form-control" placeholder="012345678901">
          </div>
          <div class="form-group">
            <label class="form-label">Số điện thoại</label>
            <input type="text" name="so_dien_thoai" id="nv_sdt" class="form-control" placeholder="0912345678">
          </div>
        </div>
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="nv_email" class="form-control" placeholder="email@company.vn">
          </div>
          <div class="form-group">
            <label class="form-label">Địa chỉ</label>
            <input type="text" name="dia_chi" id="nv_dia_chi" class="form-control" placeholder="Địa chỉ...">
          </div>
        </div>
        <div class="form-row col-2">
          <div class="form-group">
            <label class="form-label required">Phòng ban</label>
            <select name="id_phong_ban" id="nv_phong_ban" class="form-control">
              <option value="">-- Chọn phòng ban --</option>
              <?php foreach ($phong_bans as $pb): ?>
              <option value="<?= $pb['id'] ?>"><?= clean($pb['ten_pb']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label required">Chức vụ</label>
            <select name="id_chuc_vu" id="nv_chuc_vu" class="form-control">
              <option value="">-- Chọn chức vụ --</option>
              <?php foreach ($chuc_vus as $cv): ?>
              <option value="<?= $cv['id'] ?>"><?= clean($cv['ten_cv']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row col-3">
          <div class="form-group">
            <label class="form-label required">Lương cơ bản (đ)</label>
            <input type="number" name="luong_co_ban" id="nv_luong" class="form-control" placeholder="10000000" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Ngày vào làm</label>
            <input type="date" name="ngay_vao_lam" id="nv_ngay_vl" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Trạng thái</label>
            <select name="trang_thai" id="nv_trang_thai" class="form-control">
              <option>Đang làm</option><option>Tạm nghỉ</option><option>Nghỉ việc</option>
            </select>
          </div>
        </div>
        <div class="form-group" style="margin-top:10px;">
          <label class="form-label">Chữ ký điện tử (Tải lên ảnh chữ ký)</label>
          <input type="file" name="chu_ky" class="form-control" accept="image/*">
          <p style="font-size:11px;color:var(--gray);margin-top:4px;">Tải lên ảnh chữ ký (PNG/JPG) để dùng trong bảng lương.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalNV')">Hủy</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu nhân viên</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL XÓA -->
<div class="modal-overlay" id="modalDelete" style="display:none;">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title" style="color:var(--danger);"><i class="fas fa-trash"></i> Xác nhận xóa</span>
      <button class="modal-close" onclick="closeModal('modalDelete')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <p>Bạn có chắc chắn muốn xóa nhân viên <b id="deleteNVName"></b>?</p>
      <p style="font-size:12px;color:var(--danger);margin-top:8px;"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeModal('modalDelete')">Hủy</button>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="type" value="delete">
        <input type="hidden" name="id" id="deleteNVId">
        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Xóa</button>
      </form>
    </div>
  </div>
</div>

<script src="../../assets/js/main.js"></script>
<script>
function openEditNV(nv) {
  document.getElementById('modalNVTitle').innerHTML = '<i class="fas fa-edit"></i> Sửa nhân viên: ' + nv.ho_ten;
  document.getElementById('nv_id').value = nv.id;
  document.getElementById('nv_ho_ten').value = nv.ho_ten || '';
  document.getElementById('nv_ngay_sinh').value = nv.ngay_sinh || '';
  document.getElementById('nv_gioi_tinh').value = nv.gioi_tinh || 'Nam';
  document.getElementById('nv_cccd').value = nv.cccd || '';
  document.getElementById('nv_sdt').value = nv.so_dien_thoai || '';
  document.getElementById('nv_email').value = nv.email || '';
  document.getElementById('nv_dia_chi').value = nv.dia_chi || '';
  document.getElementById('nv_phong_ban').value = nv.id_phong_ban || '';
  document.getElementById('nv_chuc_vu').value = nv.id_chuc_vu || '';
  document.getElementById('nv_luong').value = nv.luong_co_ban || '';
  document.getElementById('nv_ngay_vl').value = nv.ngay_vao_lam || '';
  document.getElementById('nv_trang_thai').value = nv.trang_thai || 'Đang làm';
  openModal('modalNV');
}

function confirmDelete(id, name) {
  document.getElementById('deleteNVId').value = id;
  document.getElementById('deleteNVName').textContent = name;
  openModal('modalDelete');
}
</script>
</body>
</html>
