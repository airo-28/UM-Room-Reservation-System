<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_login();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

$u = user();

// Load rooms
$stmt = $pdo->query("SELECT id, name, location, capacity FROM rooms ORDER BY name ASC");
$rooms = $stmt->fetchAll();

// Generate hourly slots
$slots = [];
for ($h = 8; $h < 21; $h++) {
  $slots[] = sprintf("%02d:00-%02d:00", $h, $h+1);
}
?>
<div class="container my-4">
  <?php require __DIR__.'/../partials/flash.php'; ?>
  <div class="row">
    <div class="col-lg-6 mx-auto">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="mb-3"><i class="bi bi-calendar-plus me-2"></i>New Reservation</h4>
          <form method="post" action="<?php echo h(base_url('../actions/reservation_create.php')); ?>">
            <?php csrf_field(); ?>
            <div class="mb-3">
              <label class="form-label">Room</label>
              <select class="form-select" name="room_id" required>
                <option value="">Select room…</option>
                <?php foreach($rooms as $r): ?>
                  <option value="<?php echo (int)$r['id']; ?>">
                    <?php echo h($r['name'].' (Cap '.$r['capacity'].')'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Date</label>
              <input type="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Time Slot</label>
              <select class="form-select" name="slot" required>
                <option value="">Select slot…</option>
                <?php foreach($slots as $s): ?>
                  <option value="<?php echo h($s); ?>"><?php echo h($s); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Purpose</label>
              <textarea name="purpose" class="form-control" rows="3" placeholder="e.g. Group study session" required></textarea>
            </div>
            <button class="btn btn-primary w-100">Reserve</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
