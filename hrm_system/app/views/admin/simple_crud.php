<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Helper::clean($pageTitle) ?></title>
<link rel="stylesheet" href="<?= Helper::asset('css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
          <p>Quản lý dữ liệu hệ thống rõ ràng và an toàn</p>
        </div>
        <div class="page-actions">
          <button class="btn btn-primary" onclick="openCreate()"><i class="fas fa-plus"></i> Thêm mới</button>
        </div>
      </div>

      <?php $flash = $_SESSION['flash'] ?? null; if ($flash): unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= Helper::clean($flash['type']) ?>"><i class="fas fa-info-circle"></i> <?= Helper::clean($flash['message']) ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <span class="card-title"><i class="fas <?= Helper::clean($icon ?? 'fa-table') ?>"></i> <?= Helper::clean($tableTitle ?? $pageTitle) ?></span>
          <span style="font-size:12px;color:var(--gray);">Tổng: <b><?= count($rows) ?></b></span>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th width="60">STT</th>
                <?php foreach ($columns as $key => $label): ?><th><?= Helper::clean($label) ?></th><?php endforeach; ?>
                <th width="110">Thao tac</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($rows)): ?>
                <tr><td colspan="<?= count($columns) + 2 ?>"><div class="empty-state"><i class="fas <?= Helper::clean($icon ?? 'fa-table') ?>"></i><h3>Chưa có dữ liệu</h3></div></td></tr>
              <?php else: $i = 1; foreach ($rows as $row): ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <?php foreach ($columns as $key => $label): ?>
                    <td>
                      <?php if (in_array($key, $moneyColumns ?? [], true)): ?>
                        <?= Helper::formatMoney($row[$key] ?? 0) ?>
                      <?php elseif ($key === 'trang_thai' && isset($row[$key]) && ($row[$key] === 1 || $row[$key] === '1' || $row[$key] === 0 || $row[$key] === '0')): ?>
                        <span class="badge <?= (int)$row[$key] === 1 ? 'badge-success' : 'badge-secondary' ?>"><?= (int)$row[$key] === 1 ? 'Hoạt động' : 'Tạm khóa' ?></span>
                      <?php elseif ($key === 'trang_thai' && in_array($row[$key], ['Chờ duyệt', 'Đã duyệt', 'Từ chối', 'Không duyệt', 'Từ chối'], true)): ?>
                        <?= Helper::badgeDonPhep($row[$key]) ?>
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
                      <form method="POST" onsubmit="return confirm('Bạn chắc chắn muốn xóa bản ghi này?')" style="display:inline;">
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
  </div>
</div>

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
function openCreate() {
  document.getElementById('modalTitle').innerHTML = '<i class="fas <?= Helper::clean($icon ?? 'fa-plus') ?>"></i> Thêm mới';
  document.getElementById('field_id').value = 0;
  fieldNames.forEach(name => {
    const el = document.getElementById('field_' + name);
    if (el) el.value = '';
  });
  openModal('crudModal');
}
function openEdit(row) {
  document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Cập nhật';
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
