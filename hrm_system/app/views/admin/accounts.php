<?php
// Group accounts by department
$systemAccounts = [];
$deptAccounts = [];

foreach ($rows as $row) {
    if (empty($row['id_phong_ban'])) {
        $systemAccounts[] = $row;
    } else {
        $deptId = $row['id_phong_ban'];
        if (!isset($deptAccounts[$deptId])) {
            $deptAccounts[$deptId] = [
                'name' => $row['ten_pb'],
                'list' => []
            ];
        }
        $deptAccounts[$deptId]['list'][] = $row;
    }
}

// Sort departments alphabetically
uasort($deptAccounts, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// Stats
$totalAccounts = count($rows);
$activeAccounts = 0;
$lockedAccounts = 0;
foreach ($rows as $row) {
    if ((int)($row['trang_thai'] ?? 1) === 1) {
        $activeAccounts++;
    } else {
        $lockedAccounts++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Helper::clean($pageTitle) ?></title>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* CSS bổ sung dành riêng cho trang Accounts */
.dept-card {
  margin-bottom: 16px;
  border: 1px solid var(--border);
  transition: var(--transition);
}
.dept-card-header {
  cursor: pointer;
  background: var(--white);
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  user-select: none;
}
.dept-card-header:hover {
  background: rgba(5, 150, 105, 0.02);
}
.dept-title-area {
  display: flex;
  align-items: center;
  gap: 12px;
}
.dept-card .toggle-icon {
  transition: var(--transition);
  color: var(--gray-light);
}
.dept-card.collapsed .toggle-icon {
  transform: rotate(180deg);
}
.dept-card-body {
  transition: max-height 0.3s ease;
  max-height: 2000px;
  overflow: hidden;
  border-top: 1px solid var(--border);
}
.dept-card.collapsed .dept-card-body {
  max-height: 0;
  padding-top: 0;
  padding-bottom: 0;
  border-top-color: transparent;
}
.stats-compact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}
.stat-compact-card {
  background: var(--white);
  border-radius: 12px;
  padding: 16px 20px;
  box-shadow: var(--shadow);
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid rgba(0, 0, 0, 0.02);
}
.stat-compact-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
}
.stat-compact-icon.primary { background: rgba(5, 150, 105, 0.08); color: var(--primary); }
.stat-compact-icon.success { background: rgba(16, 185, 129, 0.08); color: var(--success); }
.stat-compact-icon.danger { background: rgba(239, 68, 68, 0.08); color: var(--danger); }
.stat-compact-info h3 { font-size: 18px; font-weight: 700; color: var(--dark); line-height: 1.2; }
.stat-compact-info p { font-size: 11px; color: var(--gray); }
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
          <p>Phân cấp quản lý tài khoản và quyền truy cập theo phòng ban</p>
        </div>
        <div class="page-actions">
          <button class="btn btn-primary" onclick="openCreate()"><i class="fas fa-plus"></i> Thêm tài khoản</button>
        </div>
      </div>

      <?php $flash = $_SESSION['flash'] ?? null; if ($flash): unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= Helper::clean($flash['type']) ?>"><i class="fas fa-info-circle"></i> <?= Helper::clean($flash['message']) ?></div>
      <?php endif; ?>

      <!-- Thống kê nhanh -->
      <div class="stats-compact-grid">
        <div class="stat-compact-card">
          <div class="stat-compact-icon primary"><i class="fas fa-user-shield"></i></div>
          <div class="stat-compact-info">
            <h3><?= $totalAccounts ?></h3>
            <p>Tổng số tài khoản</p>
          </div>
        </div>
        <div class="stat-compact-card">
          <div class="stat-compact-icon success"><i class="fas fa-user-check"></i></div>
          <div class="stat-compact-info">
            <h3><?= $activeAccounts ?></h3>
            <p>Đang hoạt động</p>
          </div>
        </div>
        <div class="stat-compact-card">
          <div class="stat-compact-icon danger"><i class="fas fa-user-slash"></i></div>
          <div class="stat-compact-info">
            <h3><?= $lockedAccounts ?></h3>
            <p>Tạm khóa</p>
          </div>
        </div>
      </div>

      <!-- Danh sách tài khoản hệ thống (không thuộc phòng ban) -->
      <div class="card dept-card" id="dept-card-system">
        <div class="card-header dept-card-header" onclick="toggleDept('system')">
          <div class="dept-title-area">
            <span class="card-title"><i class="fas fa-laptop-code"></i> Tài khoản hệ thống / Quản trị viên</span>
            <span class="badge badge-secondary"><?= count($systemAccounts) ?> tài khoản</span>
          </div>
          <div class="dept-actions">
            <i class="fas fa-chevron-up toggle-icon"></i>
          </div>
        </div>
        <div class="dept-card-body">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th width="60">STT</th>
                  <?php foreach ($columns as $key => $label): ?><th><?= Helper::clean($label) ?></th><?php endforeach; ?>
                  <th width="110">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($systemAccounts)): ?>
                  <tr><td colspan="<?= count($columns) + 2 ?>"><div class="empty-state"><i class="fas fa-user-lock"></i><h3>Không có tài khoản</h3></div></td></tr>
                <?php else: $i = 1; foreach ($systemAccounts as $row): ?>
                  <tr>
                    <td><?= $i++ ?></td>
                    <?php foreach ($columns as $key => $label): ?>
                      <td>
                        <?php if ($key === 'trang_thai'): ?>
                          <span class="badge <?= (int)$row[$key] === 1 ? 'badge-success' : 'badge-secondary' ?>"><?= (int)$row[$key] === 1 ? 'Hoạt động' : 'Tạm khóa' ?></span>
                        <?php elseif ($key === 'vai_tro'): ?>
                          <?php
                            $roleMap = [
                              'admin' => ['Quản trị viên', 'badge-danger'],
                              'hr' => ['Nhân sự', 'badge-info'],
                              'ketoan' => ['Kế toán', 'badge-warning'],
                              'nhanvien' => ['Nhân viên', 'badge-primary'],
                            ];
                            $role = $roleMap[$row[$key] ?? 'nhanvien'] ?? ['Nhân viên', 'badge-primary'];
                          ?>
                          <span class="badge <?= $role[1] ?>"><?= Helper::clean($role[0]) ?></span>
                        <?php else: ?>
                          <?= Helper::clean($row[$key] ?? 'Hệ thống') ?>
                        <?php endif; ?>
                      </td>
                    <?php endforeach; ?>
                    <td>
                      <div style="display:flex;gap:4px;">
                        <button class="btn btn-sm btn-info btn-icon" data-tooltip="Sửa" onclick='openEdit(<?= json_encode($row, JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                        <form method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa tài khoản này?')" style="display:inline;">
                          <input type="hidden" name="type" value="delete">
                          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                          <button class="btn btn-sm btn-danger btn-icon" data-tooltip="Xóa"><i class="fas fa-trash"></i></button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Danh sách tài khoản theo từng Phòng Ban -->
      <?php foreach ($deptAccounts as $deptId => $deptData): ?>
        <div class="card dept-card" id="dept-card-<?= $deptId ?>">
          <div class="card-header dept-card-header" onclick="toggleDept('<?= $deptId ?>')">
            <div class="dept-title-area">
              <span class="card-title"><i class="fas fa-building"></i> <?= Helper::clean($deptData['name']) ?></span>
              <span class="badge badge-primary"><?= count($deptData['list']) ?> tài khoản</span>
            </div>
            <div class="dept-actions">
              <i class="fas fa-chevron-up toggle-icon"></i>
            </div>
          </div>
          <div class="dept-card-body">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th width="60">STT</th>
                    <?php foreach ($columns as $key => $label): ?><th><?= Helper::clean($label) ?></th><?php endforeach; ?>
                    <th width="110">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; foreach ($deptData['list'] as $row): ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <?php foreach ($columns as $key => $label): ?>
                        <td>
                          <?php if ($key === 'trang_thai'): ?>
                            <span class="badge <?= (int)$row[$key] === 1 ? 'badge-success' : 'badge-secondary' ?>"><?= (int)$row[$key] === 1 ? 'Hoạt động' : 'Tạm khóa' ?></span>
                          <?php elseif ($key === 'vai_tro'): ?>
                            <?php
                              $roleMap = [
                                'admin' => ['Quản trị viên', 'badge-danger'],
                                'hr' => ['Nhân sự', 'badge-info'],
                                'ketoan' => ['Kế toán', 'badge-warning'],
                                'nhanvien' => ['Nhân viên', 'badge-primary'],
                              ];
                              $role = $roleMap[$row[$key] ?? 'nhanvien'] ?? ['Nhân viên', 'badge-primary'];
                            ?>
                            <span class="badge <?= $role[1] ?>"><?= Helper::clean($role[0]) ?></span>
                          <?php else: ?>
                            <?= Helper::clean($row[$key] ?? '') ?>
                          <?php endif; ?>
                        </td>
                      <?php endforeach; ?>
                      <td>
                        <div style="display:flex;gap:4px;">
                          <button class="btn btn-sm btn-info btn-icon" data-tooltip="Sửa" onclick='openEdit(<?= json_encode($row, JSON_UNESCAPED_UNICODE) ?>)'><i class="fas fa-edit"></i></button>
                          <form method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa tài khoản này?')" style="display:inline;">
                            <input type="hidden" name="type" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button class="btn btn-sm btn-danger btn-icon" data-tooltip="Xóa"><i class="fas fa-trash"></i></button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>
</div>

<!-- Modal Thêm/Sửa giống simple_crud nhưng có thiết kế cao cấp hơn -->
<div class="modal-overlay" id="crudModal" style="display:none;">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title" id="modalTitle"><i class="fas <?= Helper::clean($icon ?? 'fa-pen') ?>"></i> Thêm mới</span>
      <button class="modal-close" onclick="closeModal('crudModal')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="type" value="save">
      <input type="hidden" name="id" id="field_id" value="0">
      <div class="modal-body">
        <div class="form-row col-2">
          <?php foreach ($fields as $field): $type = $field['type'] ?? 'text'; ?>
            <div class="form-group" style="<?= $type === 'textarea' ? 'grid-column:1/-1;' : '' ?>">
              <label class="form-label <?= !empty($field['required']) ? 'required' : '' ?>"><?= Helper::clean($field['label']) ?></label>
              <?php if ($type === 'select'): ?>
                <select class="form-control" name="<?= Helper::clean($field['name']) ?>" id="field_<?= Helper::clean($field['name']) ?>">
                  <?php foreach (($field['options'] ?? []) as $value => $text): ?>
                    <option value="<?= Helper::clean($value) ?>"><?= Helper::clean($text) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php elseif ($type === 'textarea'): ?>
                <textarea class="form-control" name="<?= Helper::clean($field['name']) ?>" id="field_<?= Helper::clean($field['name']) ?>"></textarea>
              <?php else: ?>
                <input class="form-control" type="<?= Helper::clean($type) ?>" name="<?= Helper::clean($field['name']) ?>" id="field_<?= Helper::clean($field['name']) ?>" step="<?= Helper::clean($field['step'] ?? '1') ?>" <?= !empty($field['required']) ? 'required' : '' ?>>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('crudModal')">Hủy</button>
        <button class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
      </div>
    </form>
  </div>
</div>

<script src="<?= Helper::asset('js/main.js') ?>"></script>
<script>
const fieldNames = <?= json_encode(array_column($fields, 'name')) ?>;
const fieldTypes = <?= json_encode(array_column($fields, 'type', 'name')) ?>;

function toggleDept(deptId) {
  const card = document.getElementById('dept-card-' + deptId);
  if (card) {
    card.classList.toggle('collapsed');
  }
}

function openCreate() {
  document.getElementById('modalTitle').innerHTML = '<i class="fas <?= Helper::clean($icon ?? 'fa-plus') ?>"></i> Thêm mới tài khoản';
  document.getElementById('field_id').value = 0;
  fieldNames.forEach(name => {
    const el = document.getElementById('field_' + name);
    if (el) el.value = '';
  });
  openModal('crudModal');
}

function openEdit(row) {
  document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Cập nhật tài khoản';
  document.getElementById('field_id').value = row.id || 0;
  fieldNames.forEach(name => {
    const el = document.getElementById('field_' + name);
    if (el) el.value = fieldTypes[name] === 'password' ? '' : (row[name] ?? '');
  });
  openModal('crudModal');
}
</script>
</body>
</html>
