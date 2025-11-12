<?php
$title='การจองของฉัน'; include __DIR__.'/partials/head.php';
if(!is_logged_in()){ echo '<div class="alert">โปรดเข้าสู่ระบบก่อน</div>'; include __DIR__.'/partials/footer.php'; exit; }
$stmt = $pdo->prepare("SELECT b.*, c.title car_title FROM bookings b JOIN cars c ON c.id=b.car_id WHERE b.user_id=? ORDER BY b.id DESC");
$stmt->execute([$_SESSION['user']['id']]);
$rows = $stmt->fetchAll();
?>
<h1>การจองของฉัน</h1>
<table class="table">
  <thead><tr><th>รถ</th><th>ช่วงเวลา</th><th>วัน</th><th>รวม</th><th>สถานะ</th><th>ชำระเงิน</th></tr></thead>
  <tbody>
    <?php foreach($rows as $r): ?>
    <tr>
      <td><?= h($r['car_title']) ?></td>
      <td><?= h($r['start_date']) ?> → <?= h($r['end_date']) ?></td>
      <td><?= (int)$r['days'] ?></td>
      <td>฿<?= money($r['total']) ?></td>
      <?php
$status = $r['status'];
$cls = 'badge';
if ($status === 'approved') $cls .= ' badge-approved';
elseif ($status === 'rejected') $cls .= ' badge-rejected';
elseif ($status === 'pending')  $cls .= ' badge-pending';
elseif ($status === 'cancelled' || $status === 'returned') $cls .= ' badge-cancelled';
?>
<td><span class="<?= $cls ?>"><?= h($status) ?></span></td>

      <td>
  <?php if ($r['payment_status'] === 'paid'): ?>
    <span class="badge badge-paid">จ่ายแล้ว</span>
  <?php elseif ($r['payment_status'] === 'refunded'): ?>
    <span class="badge badge-refunded">คืนเงินแล้ว</span>
  <?php else: /* unpaid */ ?>
    <span class="badge badge-unpaid" style="margin-right:8px">ยังไม่จ่าย</span>
    <form method="post" action="api/pay.php" style="display:inline">
      <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
      <button class="btn btn-primary">ชำระเงิน</button>
    </form>
  <?php endif; ?>
</td>

    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__.'/partials/footer.php'; ?>
