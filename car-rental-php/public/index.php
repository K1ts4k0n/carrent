<?php $title='จองรถง่าย ๆ เริ่มต้นวันนี้'; include __DIR__.'/partials/head.php'; ?>
<section class="hero">
  <h1>ระบบเช่ารถออนไลน์ — สวย หรู ใช้ง่าย</h1>
  <p>ค้นหารถที่เหมาะกับทริปของคุณ เลือกวันรับ-คืน ระบบจะคำนวณราคาอัตโนมัติและตรวจสอบคิวว่างแบบเรียลไทม์</p>
  <div class="field" style="margin-top:18px;">
    <a class="btn btn-primary" href="<?= APP_URL ?>/cars.php">เริ่มค้นหารถ</a>
    <span class="badge">ทดสอบแอดมิน: admin@carrent.local / admin123</span>
  </div>
</section>

<h2 style="margin-top:24px">ฟีเจอร์เด่น</h2>
<div class="grid cards">
  <div class="card"><div class="media"><img src="assets/img/placeholder-car.svg" alt="" style="width:60%"></div><div class="body"><h3>ค้นหารถว่าง</h3><p class="muted">กรองตามประเภท/ที่นั่ง/ราคา และช่วงวันที่ต้องการ</p></div></div>
  <div class="card"><div class="media"></div><div class="body"><h3>ยืนยัน/ปฏิเสธการจอง</h3><p>สำหรับผู้ดูแลระบบ พร้อมสถานะชำระเงิน</p></div></div>
  <div class="card"><div class="media"></div><div class="body"><h3>รายงาน</h3><p>รายได้ การจอง และสถานะรถ</p></div></div>
</div>
<?php include __DIR__.'/partials/footer.php'; ?>
