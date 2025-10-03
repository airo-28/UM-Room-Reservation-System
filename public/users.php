<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_once __DIR__.'/../lib/auth.php';
require_role(['admin']);
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

$editId  = (int)($_GET['edit'] ?? 0);
$editRow = null;
if ($editId) {
  $s = $pdo->prepare('SELECT * FROM users WHERE id=?');
  $s->execute([$editId]);
  $editRow = $s->fetch();
}

$users = $pdo->query('SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<div class="container">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <div class="row g-3">
    <!-- Edit/Add Form -->
    <div class="col-md-5" data-aos="fade-right">
      <div class="card card-shadow p-3">
        <h5 class="d-flex align-items-center justify-content-between">
          <span><i class="bi bi-person-gear me-1"></i><?php echo $editRow ? 'Edit User' : 'Add User'; ?></span>
          <?php if ($editRow): ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?php echo h(base_url('users.php')); ?>">Clear</a>
          <?php endif; ?>
        </h5>

        <form method="post" action="<?php echo h(base_url('../actions/user_crud.php')); ?>">
          <?php csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo $editRow ? (int)$editRow['id'] : ''; ?>">

          <div class="mb-2">
            <label class="form-label">Full Name</label>
            <input name="full_name" class="form-control" required value="<?php echo $editRow ? h($editRow['full_name']) : ''; ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?php echo $editRow ? h($editRow['email']) : ''; ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <?php $role = $editRow['role'] ?? 'user'; ?>
              <option value="user" <?php echo $role==='user'?'selected':''; ?>>User</option>
              <option value="admin" <?php echo $role==='admin'?'selected':''; ?>>Admin</option>
            </select>
          </div>
          <button class="btn btn-primary"><?php echo $editRow ? 'Update' : 'Save'; ?></button>
        </form>
      </div>
    </div>

    <!-- User List -->
    <div class="col-md-7" data-aos="fade-left">
      <div class="card card-shadow p-3">
        <h5 class="mb-2"><i class="bi bi-people me-1"></i>Users</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td><?php echo h($u['full_name']); ?></td>
                  <td><?php echo h($u['email']); ?></td>
                  <td><?php echo h($u['role']); ?></td>
                  <td><?php echo h($u['created_at']); ?></td>
                  <td class="text-end">
                    <a class="btn btn-outline-primary btn-sm" href="?edit=<?php echo (int)$u['id']; ?>"><i class="bi bi-pencil-square"></i></a>
                    <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/user_crud.php')); ?>" data-confirm="Delete this user?">
                      <?php csrf_field(); ?>
                      <input type="hidden" name="delete_id" value="<?php echo (int)$u['id']; ?>">
                      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$users): ?>
                <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
