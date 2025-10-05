<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_once __DIR__.'/../lib/auth.php';
require_role(['admin']);
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

$term = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 10;
$off  = ($page - 1) * $per;

$where = '1=1';
$args  = [];
if ($term !== '') {
  $where = '(title LIKE ? OR body LIKE ? OR severity LIKE ?)';
  $like  = '%'.$term.'%';
  $args  = [$like,$like,$like];
}

$total = $pdo->prepare("SELECT COUNT(*) FROM announcements WHERE $where");
$total->execute($args);
$count = (int)$total->fetchColumn();
$pages = max(1, (int)ceil($count / $per));

$stmt = $pdo->prepare("
  SELECT *
  FROM announcements
  WHERE $where
  ORDER BY COALESCE(starts_at,created_at) DESC
  LIMIT $per OFFSET $off
");
$stmt->execute($args);
$rows = $stmt->fetchAll();

$editId = (int)($_GET['edit'] ?? 0);
$edit = null;
if ($editId) {
  $s = $pdo->prepare("SELECT * FROM announcements WHERE id=? LIMIT 1");
  $s->execute([$editId]);
  $edit = $s->fetch();
}
?>
<div class="container">
  <?php require __DIR__.'/../partials/flash.php'; ?>

  <div class="row g-3">
    <div class="col-md-5" data-aos="fade-right">
      <div class="card card-shadow p-3">
        <h5 class="d-flex align-items-center justify-content-between">
          <span><i class="bi bi-megaphone me-1"></i><?php echo $edit ? 'Edit Announcement' : 'Add Announcement'; ?></span>
          <?php if ($edit): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?php echo h(base_url('announcements.php')); ?>">Clear</a>
          <?php endif; ?>
        </h5>
        <form method="post" action="<?php echo h(base_url('../actions/announcement_crud.php')); ?>">
          <?php csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo $edit ? (int)$edit['id'] : ''; ?>">
          <div class="mb-2">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required value="<?php echo $edit ? h($edit['title']) : ''; ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Body</label>
            <textarea name="body" class="form-control" rows="4" required><?php echo $edit ? h($edit['body']) : ''; ?></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Severity</label>
            <?php $sev = $edit ? ($edit['severity'] ?? 'info') : 'info'; ?>
            <select name="severity" class="form-select" required>
              <option value="update"    <?php echo $sev==='update'?'selected':''; ?>>Update</option>
              <option value="info"      <?php echo $sev==='info'?'selected':''; ?>>Info</option>
              <option value="notice"    <?php echo $sev==='notice'?'selected':''; ?>>Notice</option>
              <option value="important" <?php echo $sev==='important'?'selected':''; ?>>Important</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Active Window</label>
            <div class="row g-2">
              <div class="col-6">
                <input type="datetime-local" name="starts_at" class="form-control"
                  value="<?php echo $edit && !empty($edit['starts_at']) ? date('Y-m-d\TH:i', strtotime($edit['starts_at'])) : ''; ?>">
                <div class="form-text">Starts</div>
              </div>
              <div class="col-6">
                <input type="datetime-local" name="ends_at" class="form-control"
                  value="<?php echo $edit && !empty($edit['ends_at']) ? date('Y-m-d\TH:i', strtotime($edit['ends_at'])) : ''; ?>">
                <div class="form-text">Ends</div>
              </div>
            </div>
          </div>
          <div class="form-check mb-3">
            <?php $ia = $edit ? (int)$edit['is_active'] : 1; ?>
            <input class="form-check-input" type="checkbox" id="ia" name="is_active" <?php echo $ia ? 'checked' : ''; ?>>
            <label class="form-check-label" for="ia">Active</label>
          </div>
          <button class="btn btn-primary"><?php echo $edit ? 'Update' : 'Save'; ?></button>
        </form>
      </div>
    </div>

    <div class="col-md-7" data-aos="fade-left">
      <div class="card card-shadow p-3">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-0">Announcements</h5>
          <form class="d-flex gap-2" method="get">
            <input class="form-control form-control-sm" type="search" name="q" value="<?php echo h($term); ?>" placeholder="Search">
            <button class="btn btn-sm btn-outline-secondary">Search</button>
          </form>
        </div>
        <div class="table-responsive mt-2">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Title</th>
                <th>Severity</th>
                <th>Window</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $a): ?>
                <?php
                  $color = [
                    'update'    => 'bg-success',
                    'info'      => 'bg-info text-dark',
                    'notice'    => 'bg-warning text-dark',
                    'important' => 'bg-danger'
                  ][$a['severity']] ?? 'bg-secondary';

                  $win   = [];
                  if (!empty($a['starts_at'])) { $win[] = date('M j, Y H:i', strtotime($a['starts_at'])); }
                  if (!empty($a['ends_at']))   { $win[] = date('M j, Y H:i', strtotime($a['ends_at'])); }
                  $winStr = $win ? implode(' - ', $win) : '—';
                ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?php echo h($a['title']); ?></div>
                    <div class="text-muted small text-truncate" style="max-width:320px;"><?php echo h($a['body']); ?></div>
                  </td>
                  <td><span class="badge rounded-pill <?php echo $color; ?>"><?php echo h(ucfirst($a['severity'])); ?></span></td>
                  <td class="small text-muted"><?php echo h($winStr); ?></td>
                  <td><?php echo ((int)$a['is_active']) ? 'Active' : 'Disabled'; ?></td>
                  <td class="text-end text-nowrap">
                    <a class="btn btn-outline-primary btn-sm" href="?edit=<?php echo (int)$a['id']; ?>"><i class="bi bi-pencil-square"></i></a>
                    <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/announcement_crud.php')); ?>" data-confirm="Delete this announcement?">
                      <?php csrf_field(); ?>
                      <input type="hidden" name="delete_id" value="<?php echo (int)$a['id']; ?>">
                      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="5" class="text-center text-muted">No announcements found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <nav class="mt-2">
          <ul class="pagination pagination-sm mb-0">
            <?php for($i=1;$i<=$pages;$i++): ?>
              <li class="page-item <?php echo $i===$page?'active':''; ?>">
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
