<?php require_once __DIR__.'/../partials/head.php'; ?>
<?php require_once __DIR__.'/../partials/nav.php'; require_role(['admin']); require_once __DIR__.'/../lib/csrf.php'; verify_csrf(); ?>
<?php
require __DIR__.'/../config/db.php';
$rows = $pdo->query('SELECT * FROM resources ORDER BY name')->fetchAll();
?>
<div class="container">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <div class="row g-3">
    <div class="col-md-5" data-aos="fade-right">
      <div class="card card-shadow p-3">
        <h5><i class="bi bi-boxes me-1"></i>Add / Edit Resource</h5>
        <form method="post" action="<?php echo h(base_url('../actions/resource_crud.php')); ?>">
          <?php csrf_field(); ?>
          <input type="hidden" name="id" value="">
          <div class="mb-2"><label class="form-label">Name</label><input name="name" required class="form-control"></div>
          <button class="btn btn-primary">Save</button>
        </form>
      </div>
    </div>
    <div class="col-md-7" data-aos="fade-left">
      <div class="card card-shadow p-3">
        <h5 class="mb-2">Resources</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Name</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
              <?php foreach($rows as $r): ?>
                <tr>
                  <td><?php echo h($r['name']); ?></td>
                  <td class="text-end">
                    <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/resource_crud.php')); ?>" data-confirm="Delete this resource?">
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
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
