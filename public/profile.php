<?php require_once __DIR__.'/../partials/head.php'; ?>
<?php require_once __DIR__.'/../partials/nav.php'; require_login(); require_once __DIR__.'/../lib/csrf.php'; verify_csrf(); ?>
<div class="container" style="max-width:560px;">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <div class="card card-shadow p-3" data-aos="fade-up">
    <h5><i class="bi bi-person-gear me-1"></i>My Profile</h5>
    <?php $u=user(); ?>
    <form method="post" action="<?php echo h(base_url('../actions/profile_update.php')); ?>">
      <?php csrf_field(); ?>
      <div class="mb-3"><label class="form-label">Full name</label><input name="full_name" class="form-control" value="<?php echo h($u['name']); ?>" required></div>
      <button class="btn btn-primary">Save</button>
    </form>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
