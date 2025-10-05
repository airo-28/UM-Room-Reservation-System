<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_login();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';

$term = trim($_GET['q'] ?? '');
$args = [];
$where = '1=1';
if ($term !== '') {
  $where = '(rm.name LIKE ? OR rm.location LIKE ? OR rm.type LIKE ?)';
  $like  = '%'.$term.'%';
  $args  = [$like, $like, $like];
}

$stmt = $pdo->prepare("
  SELECT rm.id, rm.name, rm.location, rm.capacity, rm.type,
         COALESCE(rm.image_path,'') AS image_path,
         TIME_FORMAT(rm.open_time,'%H:%i') AS open_t,
         TIME_FORMAT(rm.close_time,'%H:%i') AS close_t
  FROM rooms rm
  WHERE $where AND rm.is_active=1
  ORDER BY rm.name
");
$stmt->execute($args);
$rooms = $stmt->fetchAll();
?>
<style>
  body {
    background: linear-gradient(180deg, #fff5f5 0%, #fff0e1 100%);
  }
  .section-bar {
    font-weight: 600;
    color: #b71c1c;
  }
  .btn-accent {
    background-color: #d32f2f;
    color: #fff;
    border: none;
  }
  .btn-accent:hover {
    background-color: #ffb300;
    color: #000;
  }
  .room-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 18px;
  }
  .room-card {
    position: relative;
    border: 0;
    overflow: hidden;
    border-radius: 1rem;
    box-shadow: 0 8px 28px rgba(0,0,0,.15);
    background: #fff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .room-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px rgba(0,0,0,.25);
  }
  .room-card img {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
    transition: transform 0.35s ease;
  }
  .room-card:hover img {
    transform: scale(1.08);
  }
  .room-card .overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(198,40,40,.7) 100%);
    opacity: 0;
    transition: opacity 0.25s ease;
  }
  .room-card:hover .overlay {
    opacity: 1;
  }
  .room-card .label {
    position: absolute;
    left: 12px;
    bottom: 12px;
    right: 12px;
    color: #fff;
    text-shadow: 0 2px 8px rgba(0,0,0,.35);
  }
  .room-card .label .title {
    font-weight: 700;
    font-size: 1.05rem;
    line-height: 1.2;
  }
  .room-card .label .meta {
    font-size: .85rem;
    opacity: .95;
  }
</style>

<div class="container my-4">
  <?php require __DIR__.'/../partials/flash.php'; ?>

  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="section-bar"><i class="bi bi-images me-2"></i>Room Showcase</div>
    <form class="d-flex gap-2" method="get">
      <input class="form-control form-control-sm" type="search" name="q" placeholder="Search rooms" value="<?php echo h($term); ?>">
      <button class="btn btn-sm btn-accent">Search</button>
    </form>
  </div>

  <?php if($rooms): ?>
    <div class="room-grid">
      <?php foreach($rooms as $rm): ?>
        <?php $img = $rm['image_path'] ?: base_url('../assets/img/rooms/placeholder.jpg'); ?>
        <div class="room-card" data-aos="zoom-in">
          <img src="<?php echo h($img); ?>" alt="<?php echo h($rm['name']); ?>" loading="lazy">
          <div class="overlay"></div>
          <div class="label">
            <div class="title"><?php echo h($rm['name']); ?></div>
            <div class="meta">
              <i class="bi bi-geo-alt me-1"></i><?php echo h($rm['location']); ?> •
              <i class="bi bi-people me-1 ms-1"></i>Cap <?php echo (int)$rm['capacity']; ?> •
              <i class="bi bi-clock me-1 ms-1"></i><?php echo h($rm['open_t']); ?>–<?php echo h($rm['close_t']); ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="card card-shadow p-4 text-center"><div class="text-muted">No rooms found.</div></div>
  <?php endif; ?>
</div>

<?php require_once __DIR__.'/../partials/footer.php'; ?>
