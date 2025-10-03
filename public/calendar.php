<?php require_once __DIR__.'/../partials/head.php'; ?>
<?php require_once __DIR__.'/../partials/nav.php'; require_role(['admin']); require_once __DIR__.'/../lib/csrf.php'; verify_csrf(); ?>
<?php
require __DIR__.'/../config/db.php';
$rows = $pdo->query('
  SELECT r.*, u.full_name, rm.name AS room_name
  FROM reservations r
  JOIN users u ON u.id=r.user_id
  JOIN rooms rm ON rm.id=r.room_id
  ORDER BY date DESC, start_time DESC
')->fetchAll();
?>
<div class="container">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <div class="card card-shadow p-3" data-aos="fade-up">
    <h5 class="mb-2"><i class="bi bi-calendar3 me-1"></i>Reservations</h5>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Room</th>
            <th>User</th>
            <th>Purpose</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $r): ?>
            <tr>
              <td><?php echo h($r['date']); ?></td>
              <td><?php echo h(substr($r['start_time'],0,5)); ?>–<?php echo h(substr($r['end_time'],0,5)); ?></td>
              <td><?php echo h($r['room_name']); ?></td>
              <td><?php echo h($r['full_name']); ?></td>
              <td class="small"><?php echo h($r['purpose']); ?></td>
              <td><?php echo h($r['status']); ?></td>
              <td class="text-end">
                <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/reservation_update_status.php')); ?>" data-confirm="Approve this reservation?">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                  <input type="hidden" name="status" value="approved">
                  <button class="btn btn-success btn-sm"><i class="bi bi-check2"></i></button>
                </form>
                <form class="d-inline" method="post" action="<?php echo h(base_url('../actions/reservation_update_status.php')); ?>" data-confirm="Reject this reservation?">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                  <input type="hidden" name="status" value="rejected">
                  <button class="btn btn-danger btn-sm"><i class="bi bi-x"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
