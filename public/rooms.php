<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_once __DIR__.'/../lib/auth.php';
require_role(['admin']);
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';   // only for csrf_field()
require_once __DIR__.'/../lib/helpers.php';

$term = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;
$off  = ($page - 1) * $per;

$where  = '1=1';
$params = [];
if ($term !== '') {
  $where  = '(name LIKE ? OR location LIKE ? OR type LIKE ? OR amenities LIKE ? OR description LIKE ?)';
  $like   = '%'.$term.'%';
  $params = [$like,$like,$like,$like,$like];
}

$total = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE $where");
$total->execute($params);
$cnt   = (int)$total->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE $where ORDER BY name LIMIT $per OFFSET $off");
$stmt->execute($params);
$rows  = $stmt->fetchAll();

$pages = max(1, (int)ceil($cnt / $per));

$editId  = (int)($_GET['edit'] ?? 0);
$editRow = null;
if ($editId) {
  $s = $pdo->prepare('SELECT * FROM rooms WHERE id=?');
  $s->execute([$editId]);
  $editRow = $s->fetch();
}
?>
<div class="container">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <div class="row g-3">
    <!-- Left: Add/Edit Form -->
    <div class="col-md-5" data-aos="fade-right">
      <div class="card card-shadow p-3">
        <h5 class="d-flex align-items-center justify-content-between">
          <span><i class="bi bi-door-open me-1"></i><?php echo $editRow ? 'Edit Room' : 'Add Room'; ?></span>
          <?php if ($editRow): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?php echo h(base_url('rooms.php')); ?>">Clear</a>
          <?php endif; ?>
        </h5>

        <form method="post" enctype="multipart/form-data" action="<?php echo h(base_url('../actions/room_crud.php')); ?>">
          <?php csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo $editRow ? (int)$editRow['id'] : ''; ?>">

          <div class="mb-2"><label class="form-label">Name</label>
            <input name="name" required class="form-control" value="<?php echo $editRow ? h($editRow['name']) : ''; ?>">
          </div>
          <div class="mb-2"><label class="form-label">Location</label>
            <input name="location" required class="form-control" value="<?php echo $editRow ? h($editRow['location']) : ''; ?>">
          </div>
          <div class="mb-2"><label class="form-label">Capacity</label>
            <input name="capacity" type="number" min="1" required class="form-control" value="<?php echo $editRow ? (int)$editRow['capacity'] : ''; ?>">
          </div>
          <div class="mb-2"><label class="form-label">Type</label>
            <select name="type" class="form-select" required>
              <?php $type = $editRow['type'] ?? 'collab'; ?>
              <option value="collab"    <?php echo $type==='collab'?'selected':''; ?>>Collaboration</option>
              <option value="lab"       <?php echo $type==='lab'?'selected':''; ?>>Laboratory</option>
              <option value="classroom" <?php echo $type==='classroom'?'selected':''; ?>>Classroom</option>
              <option value="other"     <?php echo $type==='other'?'selected':''; ?>>Other</option>
            </select>
          </div>
          <div class="mb-2"><label class="form-label">Open Time</label>
            <input type="time" name="open_time" class="form-control" value="<?php echo h(substr($editRow['open_time'] ?? '08:00:00',0,5)); ?>" required>
          </div>
          <div class="mb-2"><label class="form-label">Close Time</label>
            <input type="time" name="close_time" class="form-control" value="<?php echo h(substr($editRow['close_time'] ?? '21:00:00',0,5)); ?>" required>
          </div>
          <div class="mb-2"><label class="form-label">Amenities</label>
            <input name="amenities" class="form-control" value="<?php echo $editRow ? h($editRow['amenities']) : ''; ?>">
          </div>
          <div class="mb-2"><label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo $editRow ? h($editRow['description']) : ''; ?></textarea>
          </div>
          <div class="mb-2"><label class="form-label">Image</label>
            <input type="file" name="image" accept="image/*" class="form-control">
            <?php if (!empty($editRow['image_path'])): ?>
              <div class="small text-muted mt-1">Current: <a href="<?php echo h($editRow['image_path']); ?>" target="_blank">view</a></div>
            <?php endif; ?>
          </div>
          <div class="form-check mb-3">
            <?php $act = (int)($editRow['is_active'] ?? 1); ?>
            <input class="form-check-input" type="checkbox" name="is_active" id="ractive" <?php echo $act ? 'checked' : ''; ?>>
            <label class="form-check-label" for="ractive">Active</label>
          </div>
          <button class="btn btn-primary"><?php echo $editRow ? 'Update' : 'Save'; ?></button>
        </form>
      </div>
    </div>

    <!-- Right: Rooms Table -->
    <div class="col-md-7" data-aos="fade-left">
      <div class="card card-shadow p-3">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Rooms</h5>
          <form class="d-flex gap-2" method="get">
            <input class="form-control form-control-sm" type="search" name="q" value="<?php echo h($term); ?>" placeholder="Search rooms">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th style="width:80px;"></th>
                <th>Name</th>
                <th>Type</th>
                <th>Cap</th>
                <th>Location</th>
                <th>Amenities</th>
                <th>Description</th>
                <th>Hours</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td>
                    <?php $img = $r['image_path'] ?: base_url('../assets/img/rooms/room'.(($r['id']%4)+1).'.jpg'); ?>
                    <img src="<?php echo h($img); ?>" class="w-100 rounded">
                  </td>
                  <td><?php echo h($r['name']); ?></td>
                  <td><?php echo h($r['type']); ?></td>
                  <td><?php echo (int)$r['capacity']; ?></td>
                  <td><?php echo h($r['location']); ?></td>
                  <td class="small"><?php echo h($r['amenities']); ?></td>
                  <td class="small text-muted" style="max-width:220px;"><?php echo h($r['description']); ?></td>
                  <td class="small"><?php echo h(substr($r['open_time'],0,5)); ?>–<?php echo h(substr($r['close_time'],0,5)); ?></td>
                  <td><?php echo !empty($r['is_active']) ? 'Active' : 'Disabled'; ?></td>
                  <td class="text-nowrap text-end">
                    <a class="btn btn-outline-primary btn-sm" href="?edit=<?php echo (int)$r['id']; ?>"><i class="bi bi-pencil-square"></i></a>
                    <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/room_crud.php')); ?>" data-confirm="Delete this room? This cannot be undone.">
                      <?php csrf_field(); ?>
                      <input type="hidden" name="delete_id" value="<?php echo (int)$r['id']; ?>">
                      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <nav class="mt-2">
          <ul class="pagination pagination-sm mb-0">
            <?php for ($i=1; $i<=$pages; $i++): ?>
              <li class="page-item <?php echo $i===$page ? 'active' : ''; ?>">
                <a class="page-link" href="?q=<?php echo urlencode($term); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
