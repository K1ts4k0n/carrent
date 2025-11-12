<?php
$title='แดชบอร์ดแอดมิน'; include __DIR__.'/../partials/head.php';
if(!is_admin()){ echo '<div class="alert">สำหรับผู้ดูแลระบบเท่านั้น</div>'; include __DIR__.'/../partials/footer.php'; exit; }

$totalCars = $pdo->query("SELECT COUNT(*) c FROM cars")->fetch()['c'];
$totalBookings = $pdo->query("SELECT COUNT(*) c FROM bookings")->fetch()['c'];
$income = $pdo->query("SELECT IFNULL(SUM(total),0) s FROM bookings WHERE payment_status='paid'")->fetch()['s'];
$pending = $pdo->query("SELECT COUNT(*) c FROM bookings WHERE status='pending'")->fetch()['c'];

$latest = $pdo->query("SELECT b.*, u.name uname, c.title car FROM bookings b
  JOIN users u ON u.id=b.user_id JOIN cars c ON c.id=b.car_id ORDER BY b.id DESC LIMIT 10")->fetchAll();
?>
<h1>แดชบอร์ด</h1>
<div class="grid cards" style="grid-template-columns:repeat(4,1fr)">
  <div class="card"><div class="body"><div class="badge">รถทั้งหมด</div><h2><?= (int)$totalCars ?></h2></div></div>
  <div class="card"><div class="body"><div class="badge">การจอง</div><h2><?= (int)$totalBookings ?></h2></div></div>
  <div class="card"><div class="body"><div class="badge">รออนุมัติ</div><h2><?= (int)$pending ?></h2></div></div>
  <div class="card"><div class="body"><div class="badge">รายได้ที่ชำระแล้ว</div><h2>฿<?= money($income) ?></h2></div></div>
</div>

<h2 style="margin-top:16px">คำขอจองล่าสุด</h2>
<table class="table">
  <thead><tr><th>ลูกค้า</th><th>รถ</th><th>ช่วงเวลา</th><th>รวม</th><th>สถานะ</th><th>การทำงาน</th></tr></thead>
  <tbody>
  <?php foreach($latest as $r): ?>
    <tr>
      <td><?= h($r['uname']) ?></td>
      <td><?= h($r['car']) ?></td>
      <td><?= h($r['start_date']) ?> → <?= h($r['end_date']) ?></td>
      <td>฿<?= money($r['total']) ?></td>
      <?php
$status = $r['status'];
$cls = 'badge';
if ($status === 'approved') $cls .= ' badge-approved';
elseif ($status === 'rejected') $cls .= ' badge-rejected';
elseif ($status === 'pending') $cls .= ' badge-pending';
elseif ($status === 'cancelled' || $status === 'returned') $cls .= ' badge-cancelled';
?>
<td><span class="<?= $cls ?>"><?= h($status) ?></span></td>

      <td style="display:flex;gap:8px">
        <form method="post" action="update_status.php">
  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
  <input type="hidden" name="status" value="approved">
  <button class="btn btn-approve">อนุมัติ</button>
</form>

        <form method="post" action="update_status.php"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="status" value="rejected"><button class="btn btn-danger">ปฏิเสธ</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<h2>จัดการรถ</h2>
<a class="btn btn-primary" href="car_form.php">+ เพิ่มรถ</a>
<table class="table" style="margin-top:10px">
  <thead><tr><th>ชื่อ</th><th>ประเภท</th><th>ราคา/วัน</th><th>สถานะ</th><th>Action</th></tr></thead>
  <tbody>
  <?php foreach($pdo->query("SELECT * FROM cars ORDER BY id DESC") as $c): ?>
    <tr>
      <td><?= h($c['title']) ?></td><td><?= h($c['type']) ?></td><td>฿<?= money($c['price_per_day']) ?></td><td><?= h($c['status']) ?></td>
      <td><a class="btn" href="car_form.php?id=<?= (int)$c['id'] ?>">แก้ไข</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php include __DIR__.'/../partials/footer.php'; ?>
