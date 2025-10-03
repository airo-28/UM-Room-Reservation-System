<?php if(!empty($_SESSION['flash'])): ?>
  <div class="container mt-3">
    <?php foreach($_SESSION['flash'] as $k=>$f): ?>
      <div class="alert alert-<?php echo h($f['t']); ?>" data-aos="fade-in"><?php echo h($f['m']); ?></div>
    <?php endforeach; $_SESSION['flash']=[]; ?>
  </div>
<?php endif; ?>
