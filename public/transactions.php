<?php
require_once __DIR__.'/../partials/head.php';
require_once __DIR__.'/../partials/nav.php';
require_role(['admin']);
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/csrf.php';
require_once __DIR__.'/../lib/helpers.php';

$term   = trim($_GET['q'] ?? '');
$actor  = trim($_GET['actor'] ?? '');   // admin|user
$action = trim($_GET['action'] ?? '');  // approved|rejected|canceled|created|updated
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 15;
$off    = ($page-1) * $per;

$where = '1=1';
$args  = [];

if ($term !== '') {
  $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR rm.name LIKE ? OR rl.note LIKE ?)";
  $like = '%'.$term.'%';
  array_push($args, $like, $like, $like, $like);
}
if ($actor !== '' && in_array($actor, ['admin','user'], true)) {
  $where .= " AND rl.actor_role = ?";
  $args[] = $actor;
}
if ($action !== '' && in_array($action, ['approved','rejected','canceled','created','updated'], true)) {
  $where .= " AND rl.action = ?";
  $args[] = $action;
}

$count = $pdo->prepare("
  SELECT COUNT(*)
  FROM reservation_logs rl
  JOIN reservations r ON r.id = rl.reservation_id
  JOIN users u ON u.id = rl.actor_user_id
  JOIN rooms rm ON rm.id = r.room_id
  WHERE $where
");
$count->execute($args);
$total = (int)$count->fetchColumn();
$pages = max(1, (int)ceil($total / $per));

$st = $pdo->prepare("
  SELECT rl.*, u.full_name AS actor_name, u.email AS actor_email,
         r.date, r.start_time, r.end_time, r.status AS current_status,
         rm.name AS room_name
  FROM reservation_logs rl
  JOIN reservations r ON r.id = rl.reservation_id
  JOIN users u ON u.id = rl.actor_user_id
  JOIN rooms rm ON rm.id = r.room_id
  WHERE $where
  ORDER BY rl.created_at DESC, rl.id DESC
  LIMIT $per OFFSET $off
");
$st->execute($args);
$rows = $st->fetchAll();
?>
<div class="container">
  <?php require __DIR__.'/../partials/flash.php'; ?>

  <div class="card card-shadow p-3" data-aos="fade-up">
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Transactions</h5>
      <form class="row row-cols-lg-auto g-2 align-items-center" method="get">
        <div class="col">
          <input type="search" name="q" class="form-control form-control-sm" placeholder="Search actor/room/note" value="<?php echo h($term); ?>">
        </div>
        <div class="col">
          <select name="actor" class="form-select form-select-sm">
            <option value="">Actor</option>
            <option value="admin" <?php echo $actor==='admin'?'selected':''; ?>>Admin</option>
            <option value="user"  <?php echo $actor==='user'?'selected':''; ?>>User</option>
          </select>
        </div>
        <div class="col">
          <select name="action" class="form-select form-select-sm">
            <option value="">Action</option>
            <option value="approved" <?php echo $action==='approved'?'selected':''; ?>>Approved</option>
            <option value="rejected" <?php echo $action==='rejected'?'selected':''; ?>>Rejected</option>
            <option value="canceled" <?php echo $action==='canceled'?'selected':''; ?>>Canceled</option>
            <option value="created"  <?php echo $action==='created'?'selected':''; ?>>Created</option>
            <option value="updated"  <?php echo $action==='updated'?'selected':''; ?>>Updated</option>
          </select>
        </div>
        <div class="col">
          <button class="btn btn-sm btn-outline-secondary">Filter</button>
        </div>
      </form>
    </div>

    <div class="table-responsive mt-2">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>When</th>
            <th>Action</th>
            <th>From → To</th>
            <th>Reservation</th>
            <th>Room</th>
            <th>Actor</th>
            <th>Note</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $x): ?>
            <?php
              $badge = ($x['action']==='approved') ? 'bg-success'
                     : (($x['action']==='rejected') ? 'bg-danger'
                     : (($x['action']==='canceled') ? 'bg-secondary'
                     : 'bg-info text-dark'));
              $when = date('Y-m-d H:i', strtotime($x['created_at']));
            ?>
            <tr>
              <td class="small text-muted"><?php echo h($when); ?></td>
              <td><span class="badge rounded-pill <?php echo $badge; ?>"><?php echo h(ucfirst($x['action'])); ?></span></td>
              <td class="small"><?php echo h(($x['from_status'] ?? '—').' → '.$x['to_status']); ?></td>
              <td class="small"><?php echo h($x['date'].' '.substr($x['start_time'],0,5).'–'.substr($x['end_time'],0,5)); ?></td>
              <td class="small"><?php echo h($x['room_name']); ?></td>
              <td class="small">
                <?php echo h($x['actor_name']); ?>
                <div class="text-muted"><?php echo h($x['actor_role']); ?></div>
              </td>
              <td class="small text-truncate" style="max-width:240px;"><?php echo h($x['note'] ?? ''); ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if(!$rows): ?>
            <tr><td colspan="7" class="text-center text-muted">No transactions.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <nav class="mt-2">
      <ul class="pagination pagination-sm mb-0">
        <?php for($i=1;$i<=$pages;$i++): ?>
          <li class="page-item <?php echo $i===$page?'active':''; ?>">
            <a class="page-link" href="?q=<?php echo urlencode($term); ?>&actor=<?php echo urlencode($actor); ?>&action=<?php echo urlencode($action); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
</div>
<?php require_once __DIR__.'/../partials/footer.php'; ?>
