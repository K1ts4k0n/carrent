<?php
$title='เลือกรถที่ใช่';
include __DIR__.'/partials/head.php';

$type = $_GET['type'] ?? '';
$seats = (int)($_GET['seats'] ?? 0);
$min = (float)($_GET['min'] ?? 0);
$max = (float)($_GET['max'] ?? 0);
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

$sql = "SELECT * FROM cars WHERE status='available'";
$params = [];
if($type){ $sql .= " AND type=?"; $params[]=$type; }
if($seats){ $sql .= " AND seats>=?"; $params[]=$seats; }
if($min){ $sql .= " AND price_per_day>=?"; $params[]=$min; }
if($max){ $sql .= " AND price_per_day<=?"; $params[]=$max; }

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();
?>
<h1>ค้นหารถที่ว่าง</h1>
<form class="searchbar" method="get">
  <div class="field">
    <label>ประเภท</label>
    <select name="type">
      <option value="">ทั้งหมด</option>
      <?php foreach(['Sedan','SUV','Hatchback','Pickup','EV'] as $t): ?>
        <option value="<?= $t ?>" <?= $t===$type?'selected':'' ?>><?= $t ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field"><label>ที่นั่ง ≥</label><input class="input" type="number" name="seats" value="<?= $seats?:'' ?>"></div>
  <div class="field"><label>ราคา/วัน ขั้นต่ำ</label><input class="input" type="number" step="0.01" name="min" value="<?= $min?:'' ?>"></div>
  <div class="field"><label>สูงสุด</label><input class="input" type="number" step="0.01" name="max" value="<?= $max?:'' ?>"></div>
  <div class="field"><label>&nbsp;</label><button class="btn btn-primary" type="submit">ค้นหา</button></div>
</form>

<div class="grid cards" style="margin-top:16px">
<?php foreach($cars as $c): ?>
  <div class="card">
    <div class="media"><img src="<?= h($c['img_path']) ?>" alt="" style="width:70%"></div>
    <div class="body">
      <h3><?= h($c['title']) ?></h3>
      <div class="badge"><?= h($c['brand']) ?> • <?= h($c['type']) ?> • <?= (int)$c['seats'] ?> ที่นั่ง</div>
      <p style="margin:10px 0">฿<?= money($c['price_per_day']) ?>/วัน</p>
      <form method="get" action="car.php" style="display:flex; gap:8px;flex-wrap:wrap">
        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
        <input class="input" type="date" name="start" value="<?= h($start) ?>">
        <input class="input" type="date" name="end" value="<?= h($end) ?>">
        <button class="btn btn-primary">ดูรายละเอียด & จอง</button>
      </form>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php include __DIR__.'/partials/footer.php'; ?>
