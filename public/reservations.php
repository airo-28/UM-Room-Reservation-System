<?php
require_once __DIR__ . '/../partials/head.php';
require_once __DIR__ . '/../partials/nav.php';
require_login();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/csrf.php';
require_once __DIR__ . '/../lib/helpers.php';

$u = user();

// Load all active rooms
$rooms = $pdo->query("
  SELECT id, name, location, capacity, amenities, description, image_path
  FROM rooms
  WHERE COALESCE(is_active,1)=1
  ORDER BY name ASC
")->fetchAll();

// Generate hourly slots from 8am–9pm
$slots = [];
for ($h = 8; $h < 21; $h++) {
  $slots[] = sprintf("%02d:00-%02d:00", $h, $h + 1);
}
?>
<div class="container my-4">
  <?php require __DIR__ . '/../partials/flash.php'; ?>
  <div class="row g-3">
    <!-- Reservation Form -->
    <div class="col-lg-5">
      <div class="card card-shadow">
        <div class="card-body p-4">
          <h4 class="mb-3"><i class="bi bi-calendar-plus me-2"></i>New Reservation</h4>
          <form method="post" action="<?php echo h(base_url('../actions/reservation_create.php')); ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="start_time" id="start_time">
            <input type="hidden" name="end_time" id="end_time">

            <div class="mb-3">
              <label class="form-label">Room</label>
              <select class="form-select" name="room_id" id="room_id" required>
                <option value="">Select room…</option>
                <?php foreach ($rooms as $r): ?>
                  <option value="<?php echo (int)$r['id']; ?>"
                          data-amenities="<?php echo h($r['amenities']); ?>"
                          data-location="<?php echo h($r['location']); ?>"
                          data-cap="<?php echo (int)$r['capacity']; ?>"
                          data-img="<?php echo h($r['image_path'] ?: base_url('../assets/img/rooms/room' . (($r['id'] % 4) + 1) . '.jpg')); ?>"
                          data-desc="<?php echo h($r['description']); ?>">
                    <?php echo h($r['name']); ?> (Cap <?php echo (int)$r['capacity']; ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Date</label>
              <input type="date" name="date" id="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Time Slot</label>
              <select class="form-select" name="slot" id="slot" required>
                <option value="">Select slot…</option>
                <?php foreach ($slots as $s): ?>
                  <option value="<?php echo h($s); ?>"><?php echo h($s); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Slots are hourly from 08:00 to 21:00.</div>
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

    <!-- Room Preview -->
    <div class="col-lg-7">
      <div class="card card-shadow">
        <div class="card-body p-4">
          <h5 class="mb-3"><i class="bi bi-building me-2"></i>Room Preview</h5>
          <div class="d-flex gap-3 align-items-start">
            <img id="room_img" src="<?php echo h(base_url('../assets/img/rooms/preview.jpg')); ?>" alt="room"
                 style="width:180px;height:120px;object-fit:cover;border-radius:8px;">
            <div>
              <div class="fw-semibold" id="room_name">Select a room</div>
              <div class="text-muted small mt-1" id="room_loc">Location: —</div>
              <div class="text-muted small" id="room_cap">Capacity: —</div>
              <div class="small mt-2"><span class="fw-semibold">Amenities:</span> <span id="room_am">—</span></div>
              <div class="small text-muted mt-2" id="room_desc">Description will appear here.</div>
            </div>
          </div>
          <hr class="my-3">
          <div class="small text-muted">
            Tip: Select a room and time slot to auto-fill the required start and end time fields.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
// Convert slot string ("08:00-09:00") into start/end hidden fields
const slotSelect = document.getElementById('slot');
const sField = document.getElementById('start_time');
const eField = document.getElementById('end_time');
slotSelect.addEventListener('change', () => {
  const val = slotSelect.value || '';
  if (!val.includes('-')) { sField.value = ''; eField.value = ''; return; }
  const parts = val.split('-');
  sField.value = parts[0] + ':00';
  eField.value = parts[1] + ':00';
});

// Live room preview
const roomSel = document.getElementById('room_id');
const img = document.getElementById('room_img');
const nm = document.getElementById('room_name');
const lc = document.getElementById('room_loc');
const cp = document.getElementById('room_cap');
const am = document.getElementById('room_am');
const ds = document.getElementById('room_desc');

roomSel.addEventListener('change', () => {
  const opt = roomSel.options[roomSel.selectedIndex];
  if (!opt || !opt.value) {
    nm.textContent = 'Select a room';
    lc.textContent = 'Location: —';
    cp.textContent = 'Capacity: —';
    am.textContent = '—';
    ds.textContent = 'Description will appear here.';
    return;
  }
  nm.textContent = opt.textContent;
  lc.textContent = 'Location: ' + (opt.dataset.location || '—');
  cp.textContent = 'Capacity: ' + (opt.dataset.cap || '—');
  am.textContent = opt.dataset.amenities || '—';
  ds.textContent = opt.dataset.desc || '';
  img.src = opt.dataset.img || '<?php echo h(base_url("../assets/img/rooms/room1.jpg")); ?>';
});
</script>
