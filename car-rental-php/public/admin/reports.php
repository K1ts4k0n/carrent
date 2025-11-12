<?php
$title='รายงานแอดมิน';
include __DIR__.'/../partials/head.php';
if(!is_admin()){ echo '<div class="alert">สำหรับผู้ดูแลระบบเท่านั้น</div>'; include __DIR__.'/../partials/footer.php'; exit; }

// -------- Filters --------
$today = date('Y-m-d');
$start  = $_GET['start']  ?? date('Y-m-01'); // default: ตั้งแต่ต้นเดือนนี้
$end    = $_GET['end']    ?? $today;         // default: ถึงวันนี้
$status = $_GET['status'] ?? '';             // all
$carId  = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

// เงื่อนไขอ้างอิง bookings ด้วย alias b เสมอ (ใช้ได้ทั้งกรณี join / ไม่ join)
$params = [':start'=>$start, ':end'=>$end];
$whereB = " NOT( b.end_date < :start OR b.start_date > :end ) ";
if($status){
  $whereB .= " AND b.status = :status ";
  $params[':status'] = $status;
}
if($carId){
  $whereB .= " AND b.car_id = :car_id ";
  $params[':car_id'] = $carId;
}

// -------- KPIs --------

// 2.1 รายได้ที่ชำระแล้ว (ภายในช่วง)
$q = $pdo->prepare("
  SELECT IFNULL(SUM(b.total),0) s
  FROM bookings b
  WHERE $whereB AND b.payment_status='paid'
");
$q->execute($params); $kpi_income = (float)$q->fetch()['s'];

// 2.2 จำนวนการจองทั้งหมดในช่วง
$q = $pdo->prepare("SELECT COUNT(*) c FROM bookings b WHERE $whereB");
$q->execute($params); $kpi_count = (int)$q->fetch()['c'];

// 2.3 ค่าเฉลี่ยวันเช่าต่อบิล + Ticket เฉลี่ย
$q = $pdo->prepare("SELECT IFNULL(AVG(b.days),0) a, IFNULL(AVG(b.total),0) t FROM bookings b WHERE $whereB");
$q->execute($params);
$row = $q->fetch(); $kpi_avg_days = (float)$row['a']; $kpi_avg_ticket = (float)$row['t'];

// 2.4 ยอดค้างชำระ (unpaid) ในช่วง
$q = $pdo->prepare("SELECT IFNULL(SUM(b.total),0) s FROM bookings b WHERE $whereB AND b.payment_status='unpaid'");
$q->execute($params); $kpi_unpaid = (float)$q->fetch()['s'];

// 2.5 อัตราถูกปฏิเสธ/ยกเลิก
$q = $pdo->prepare("
  SELECT
    SUM(b.status='rejected') rj,
    SUM(b.status='cancelled') cc,
    COUNT(*) c
  FROM bookings b
  WHERE $whereB
");
$q->execute($params);
$tmp = $q->fetch();
$rate_cancel_reject = ($tmp['c']>0) ? round((($tmp['rj']+$tmp['cc'])/$tmp['c'])*100, 2) : 0;

// -------- Occupancy per car (approved เท่านั้น) --------
$periodDays = 1 + floor( (strtotime($end) - strtotime($start)) / 86400 );

$occ_sql = "
  SELECT c.id, c.title,
    IFNULL(SUM(
      CASE
        WHEN b.status='approved' THEN
          DATEDIFF(LEAST(b.end_date, :end), GREATEST(b.start_date, :start)) + 1
        ELSE 0
      END
    ),0) AS booked_days
  FROM cars c
  LEFT JOIN bookings b
    ON b.car_id = c.id
   AND NOT( b.end_date < :start OR b.start_date > :end )
  GROUP BY c.id, c.title
  ORDER BY booked_days DESC
";
$occ = $pdo->prepare($occ_sql);
$occ->execute([':start'=>$start, ':end'=>$end]);
$occupancy = $occ->fetchAll();

// -------- Top 5 รถขายดี (รายได้/วันเช่า) --------
$top_sql = "
  SELECT c.title,
         IFNULL(SUM(b.total),0) revenue,
         IFNULL(SUM(
           CASE WHEN b.status='approved' THEN
             DATEDIFF(LEAST(b.end_date, :end), GREATEST(b.start_date, :start)) + 1
           ELSE 0 END
         ),0) AS rented_days
  FROM cars c
  LEFT JOIN bookings b
    ON b.car_id = c.id
   AND NOT( b.end_date < :start OR b.start_date > :end )
  GROUP BY c.id
  ORDER BY revenue DESC
  LIMIT 5
";
$top = $pdo->prepare($top_sql);
$top->execute([':start'=>$start, ':end'=>$end]);
$topCars = $top->fetchAll();

// -------- Breakdown สถานะ --------
$bd = $pdo->prepare("
  SELECT b.status, COUNT(*) cnt
  FROM bookings b
  WHERE $whereB
  GROUP BY b.status
");
$bd->execute($params);
$statusRows = $bd->fetchAll();

// -------- รายการจองในช่วง (สำหรับตาราง + export) --------
$list = $pdo->prepare("
  SELECT b.*, c.title car_title, u.name uname
  FROM bookings b
  JOIN cars  c ON c.id=b.car_id
  JOIN users u ON u.id=b.user_id
  WHERE $whereB
  ORDER BY b.id DESC
");
$list->execute($params);
$bookings = $list->fetchAll();

// -------- Export CSV (ถ้ามี ?export=1) --------
if(isset($_GET['export']) && $_GET['export']=='1'){
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=bookings_'.$start.'_to_'.$end.'.csv');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['ID','ลูกค้า','รถ','เริ่ม','จบ','วัน','รวม','สถานะ','ชำระเงิน','สร้างเมื่อ']);
  foreach($bookings as $r){
    fputcsv($out, [$r['id'],$r['uname'],$r['car_title'],$r['start_date'],$r['end_date'],$r['days'],$r['total'],$r['status'],$r['payment_status'],$r['created_at']]);
  }
  fclose($out);
  exit;
}
?>

<h1>รายงาน</h1>

<form method="get" class="searchbar" style="margin-bottom:16px">
  <div class="field">
    <label>เริ่ม</label>
    <input class="input" type="date" name="start" value="<?= h($start) ?>">
  </div>
  <div class="field">
    <label>สิ้นสุด</label>
    <input class="input" type="date" name="end" value="<?= h($end) ?>">
  </div>
  <div class="field">
    <label>สถานะ</label>
    <select class="input" name="status">
      <?php foreach([''=>'ทั้งหมด','pending'=>'pending','approved'=>'approved','rejected'=>'rejected','cancelled'=>'cancelled','returned'=>'returned'] as $k=>$v): ?>
        <option value="<?= $k ?>" <?= $k===$status?'selected':'' ?>><?= $v ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label>รถ</label>
    <select class="input" name="car_id">
      <option value="0" <?= $carId===0?'selected':'' ?>>ทั้งหมด</option>
      <?php foreach($pdo->query("SELECT id, title FROM cars ORDER BY title") as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id']===$carId?'selected':'' ?>><?= h($c['title']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label>&nbsp;</label>
    <button class="btn btn-primary">กรอง</button>
    <a class="btn" href="?start=<?= h($start) ?>&end=<?= h($end) ?>&export=1">Export CSV</a>
  </div>
</form>

<!-- KPI Cards -->
<div class="grid cards" style="grid-template-columns:repeat(6,1fr); margin-bottom:16px">
  <div class="card"><div class="body"><div class="badge">รายได้ที่ชำระแล้ว</div><h2>฿<?= number_format($kpi_income,2) ?></h2></div></div>
  <div class="card"><div class="body"><div class="badge">จำนวนการจอง</div><h2><?= $kpi_count ?></h2></div></div>
  <div class="card"><div class="body"><div class="badge">Avg วัน/บิล</div><h2><?= number_format($kpi_avg_days,2) ?></h2></div></div>
  <div class="card"><div class="body"><div class="badge">Avg ฿/บิล</div><h2>฿<?= number_format($kpi_avg_ticket,2) ?></h2></div></div>
  <div class="card"><div class="body"><div class="badge">ค้างชำระ</div><h2>฿<?= number_format($kpi_unpaid,2) ?></h2></div></div>
  <div class="card"><div class="body"><div class="badge">Cancel/Reject Rate</div><h2><?= $rate_cancel_reject ?>%</h2></div></div>
</div>

<!-- Occupancy per car -->
<h2>อัตราการใช้งานรถ (Occupancy)</h2>
<table class="table">
  <thead><tr><th>รถ</th><th>วันที่ถูกจอง</th><th>วันทั้งหมดในช่วง</th><th>Occupancy</th></tr></thead>
  <tbody>
  <?php foreach($occupancy as $o):
    $occPct = $periodDays>0 ? round(($o['booked_days']/$periodDays)*100,2) : 0; ?>
    <tr>
      <td><?= h($o['title']) ?></td>
      <td><?= (int)$o['booked_days'] ?></td>
      <td><?= (int)$periodDays ?></td>
      <td><span class="badge"><?= $occPct ?>%</span></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<!-- Top cars -->
<h2 style="margin-top:16px">Top 5 รถรายได้สูงสุด</h2>
<table class="table">
  <thead><tr><th>รถ</th><th>รายได้ (ช่วง)</th><th>จำนวนวันที่เช่า (approved)</th></tr></thead>
  <tbody>
  <?php foreach($topCars as $t): ?>
    <tr>
      <td><?= h($t['title']) ?></td>
      <td>฿<?= number_format($t['revenue'],2) ?></td>
      <td><?= (int)$t['rented_days'] ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<!-- Status breakdown -->
<h2 style="margin-top:16px">สรุปตามสถานะ</h2>
<table class="table">
  <thead><tr><th>สถานะ</th><th>จำนวน</th></tr></thead>
  <tbody>
  <?php foreach($statusRows as $sr): ?>
    <tr><td><?= h($sr['status']) ?></td><td><?= (int)$sr['cnt'] ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>

<!-- Booking list -->
<h2 style="margin-top:16px">รายการจองในช่วง</h2>
<table class="table">
  <thead>
    <tr><th>ลูกค้า</th><th>รถ</th><th>ช่วงเวลา</th><th>วัน</th><th>รวม</th><th>สถานะ</th><th>ชำระเงิน</th></tr>
  </thead>
  <tbody>
    <?php foreach($bookings as $r): ?>
      <tr>
        <td><?= h($r['uname']) ?></td>
        <td><?= h($r['car_title']) ?></td>
        <td><?= h($r['start_date']) ?> → <?= h($r['end_date']) ?></td>
        <td><?= (int)$r['days'] ?></td>
        <td>฿<?= number_format($r['total'],2) ?></td>
        <td><span class="badge"><?= h($r['status']) ?></span></td>
        <td><span class="badge"><?= h($r['payment_status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php include __DIR__.'/../partials/footer.php'; ?>
