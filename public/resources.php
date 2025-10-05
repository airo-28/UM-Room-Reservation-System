<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_once __DIR__.'/../lib/auth.php';
require_role(['admin']);
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php'; // only for csrf_field()
require_once __DIR__.'/../lib/helpers.php';

$rows = $pdo->query('SELECT id, name FROM resources ORDER BY name')->fetchAll();

$editId  = (int)($_GET['edit'] ?? 0);
$editRow = null;
if ($editId > 0) {
  $st = $pdo->prepare('SELECT id, name FROM resources WHERE id=?');
  $st->execute([$editId]);
  $editRow = $st->fetch();
}
?>
<div class="container">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <div class="row g-3">
    <div class="col-md-5" data-aos="fade-right">
      <div class="card card-shadow p-3">
        <h5 class="d-flex align-items-center justify-content-between">
          <span><i class="bi bi-boxes me-1"></i><?php echo $editRow ? 'Edit Resource' : 'Add Resource'; ?></span>
          <?php if ($editRow): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?php echo h(base_url('resources.php')); ?>">Clear</a>
          <?php endif; ?>
        </h5>
        <form method="post" action="<?php echo h(base_url('../actions/resource_crud.php')); ?>">
          <?php csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo $editRow ? (int)$editRow['id'] : ''; ?>">
          <div class="mb-2">
            <label class="form-label">Name</label>
            <input name="name" required class="form-control" value="<?php echo $editRow ? h($editRow['name']) : ''; ?>">
          </div>
          <button class="btn btn-primary"><?php echo $editRow ? 'Update' : 'Save'; ?></button>
        </form>
      </div>
    </div>

    <div class="col-md-7" data-aos="fade-left">
      <div class="card card-shadow p-3">
        <h5 class="mb-2">Resources</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Name</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td><?php echo h($r['name']); ?></td>
                  <td class="text-end">
                    <a class="btn btn-outline-primary btn-sm" href="?edit=<?php echo (int)$r['id']; ?>">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/resource_crud.php')); ?>" data-confirm="Delete this resource? This cannot be undone.">
                      <?php csrf_field(); ?>
                      <input type="hidden" name="delete_id" value="<?php echo (int)$r['id']; ?>">
                      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$rows): ?>
                <tr><td colspan="2" class="text-center text-muted">No resources found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
