<?php
$title='รายละเอียดรถ';
include __DIR__.'/partials/head.php';

$id = (int)($_GET['id'] ?? 0);
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM cars WHERE id=?");
$stmt->execute([$id]);
$car = $stmt->fetch();
if(!$car){ echo '<div class="alert">ไม่พบบันทึกรถ</div>'; include __DIR__.'/partials/footer.php'; exit; }

function days_between($s,$e){ $a=strtotime($s); $b=strtotime($e); if(!$a||!$b||$b<$a) return 0; return 1+ (int)(($b-$a)/86400); }

$days = $start && $end ? days_between($start,$end) : 0;
$total = $days ? $days * (float)$car['price_per_day'] : 0;

// check availability (no overlap with existing bookings except cancelled/rejected/returned)
$available = true;
if($days){
  $q = $pdo->prepare("SELECT COUNT(*) c FROM bookings WHERE car_id=? AND status IN ('pending','approved') AND NOT( end_date < ? OR start_date > ? )");
  $q->execute([$id, $start, $end]);
  $available = ($q->fetch()['c']==0);
}
?>
<div class="grid" style="grid-template-columns:1.1fr .9fr; gap:22px">
  <div class="card">
    <div class="media"><img src="<?= h($car['img_path']) ?>" alt="" style="width:80%"></div>
    <div class="body">
      <h2><?= h($car['title']) ?></h2>
      <div class="badge"><?= h($car['brand']) ?> • <?= h($car['type']) ?> • <?= (int)$car['seats'] ?> ที่นั่ง</div>
      <p>ราคา/วัน: <b>฿<?= money($car['price_per_day']) ?></b></p>
      <p>สถานะ: <?= $car['status']==='available' ? '<span class="badge">พร้อมให้เช่า</span>' : '<span class="badge">อยู่ระหว่างซ่อมบำรุง</span>' ?></p>
    </div>
  </div>
  <div class="card">
    <div class="body">
      <h3>คำนวณค่าเช่า</h3>
      <form method="post" action="create_booking.php" class="grid" style="grid-template-columns:1fr 1fr; gap:12px">
  <input type="hidden" name="car_id" value="<?= (int)$car['id'] ?>">

  <div class="field">
    <label>รับรถ</label>
    <input class="input" type="date" id="start" name="start"
           value="<?= h($start) ?>" min="<?= date('Y-m-d') ?>" required>
  </div>

  <div class="field">
    <label>คืนรถ</label>
    <input class="input" type="date" id="end" name="end"
           value="<?= h($end) ?>" required>
  </div>

  <div class="field">
    <label>จำนวนวัน</label>
    <input class="input" id="days" value="<?= $days ?>" readonly>
  </div>

  <div class="field">
    <label>รวม (ประมาณ)</label>
    <input class="input" id="total" value="฿<?= money($total) ?>" readonly>
  </div>

  <div id="dateAlert" class="alert" style="grid-column:1/3; display:<?= $days>0?'none':'block' ?>;">
    โปรดเลือกวันที่ให้ถูกต้อง
  </div>

  <div style="grid-column:1/3; display:flex; gap:10px">
    <a class="btn" href="<?= APP_URL ?>/cars.php">กลับไปเลือกคันอื่น</a>
    <button id="submitBtn" class="btn btn-primary" <?= (!$days||!$available)?'disabled':'' ?>>ยืนยันการจอง</button>
  </div>
</form>

    </div>
  </div>
</div>
<script>
(function(){
  const pricePerDay = <?= (float)$car['price_per_day'] ?>;
  const startEl = document.getElementById('start');
  const endEl   = document.getElementById('end');
  const daysEl  = document.getElementById('days');
  const totalEl = document.getElementById('total');
  const alertEl = document.getElementById('dateAlert');
  const submit  = document.getElementById('submitBtn');

  // ตั้งค่าขั้นต่ำของวันเริ่มเป็นวันนี้
  const today = new Date(); today.setHours(0,0,0,0);
  const toYMD = d => d.toISOString().slice(0,10);

  if(!startEl.value){
    startEl.value = toYMD(today);
  }
  // ถ้ายังไม่ได้ใส่ end → ตั้งให้เท่ากับ start (เช่าขั้นต่ำ 1 วัน)
  if(!endEl.value){
    endEl.value = startEl.value;
  }
  startEl.min = toYMD(today);
  endEl.min   = startEl.value;

  function compute(){
    // ถ้า end ก่อน start → บังคับให้เท่ากับ start
    if(endEl.value < startEl.value){
      endEl.value = startEl.value;
    }
    // คำนวณจำนวนวันแบบรวมวันรับและวันคืน (ขั้นต่ำ 1 วัน)
    const s = new Date(startEl.value + 'T00:00:00');
    const e = new Date(endEl.value   + 'T00:00:00');
    const diffDays = Math.floor((e - s) / 86400000) + 1;

    const valid = isFinite(diffDays) && diffDays > 0;
    daysEl.value  = valid ? diffDays : 0;
    totalEl.value = valid ? '฿' + (diffDays * pricePerDay).toFixed(2) : '฿0.00';

    alertEl.style.display = valid ? 'none' : 'block';
    submit.disabled = !valid;
    // ปรับ min ของ end ทุกครั้งที่เปลี่ยน start
    endEl.min = startEl.value;
  }

  // เมื่อเปลี่ยน start ให้ตั้ง end ให้อัตโนมัติถ้า end < start
  startEl.addEventListener('change', () => {
    if(endEl.value < startEl.value){
      endEl.value = startEl.value; // อย่างน้อยวันเดียว
    }
    compute();
  });
  endEl.addEventListener('change', compute);

  // คำนวณครั้งแรกเมื่อโหลดหน้า
  compute();
})();
</script>

<?php include __DIR__.'/partials/footer.php'; ?>
