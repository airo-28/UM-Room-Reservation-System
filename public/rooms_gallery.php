<?php require_once __DIR__.'/../partials/head.php'; ?>
<?php require_once __DIR__.'/../partials/nav.php'; ?>
<div class="container my-3">
  <h3 class="mb-3">Rooms</h3>
  <?php require __DIR__.'/../config/db.php';
    $rows = $pdo->query('SELECT id,name,location,capacity,type,COALESCE(image_path, "") AS image_path, amenities, description FROM rooms WHERE is_active=1 ORDER BY name')->fetchAll();
  ?>
  <div class="row g-3">
    <?php foreach($rows as $r): ?>
      <div class="col-md-4" data-aos="fade-up">
        <div class="card room-card hover-lift h-100">
          <?php $img = $r['image_path'] ?: base_url('../assets/img/rooms/room'.(($r['id']%4)+1).'.jpg'); ?>
          <img src="<?php echo h($img); ?>" class="w-100" alt="room">
          <div class="p-3">
            <h6 class="mb-1"><?php echo h($r['name']); ?> • cap <?php echo (int)$r['capacity']; ?></h6>
            <div class="text-muted small mb-2"><?php echo h($r['location']); ?> • <?php echo h($r['type']); ?></div>
            <?php if($r['amenities']): ?><div class="small mb-2"><span class="badge badge-soft"><?php echo h($r['amenities']); ?></span></div><?php endif; ?>
            <?php if($r['description']): ?><div class="small text-muted"><?php echo h($r['description']); ?></div><?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
