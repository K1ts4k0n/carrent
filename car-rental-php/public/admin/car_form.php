<?php
$title='ฟอร์มรถ'; include __DIR__.'/../partials/head.php';
if(!is_admin()){ echo '<div class="alert">สำหรับผู้ดูแลระบบเท่านั้น</div>'; include __DIR__.'/../partials/footer.php'; exit; }

$id = (int)($_GET['id'] ?? 0);
$car = ['title'=>'','brand'=>'','seats'=>5,'type'=>'Sedan','price_per_day'=>1000,'status'=>'available','img_path'=>'assets/img/placeholder-car.svg'];
if($id){
  $stmt = $pdo->prepare("SELECT * FROM cars WHERE id=?"); $stmt->execute([$id]); $car=$stmt->fetch();
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $data = [$_POST['title'], $_POST['brand'], (int)$_POST['seats'], $_POST['type'], (float)$_POST['price_per_day'], $_POST['status'], $_POST['img_path'] ?: 'assets/img/placeholder-car.svg'];
  if($id){
    $q = $pdo->prepare("UPDATE cars SET title=?,brand=?,seats=?,type=?,price_per_day=?,status=?,img_path=? WHERE id=?");
    $q->execute(array_merge($data, [$id]));
  }else{
    $q = $pdo->prepare("INSERT INTO cars(title,brand,seats,type,price_per_day,status,img_path) VALUES(?,?,?,?,?,?,?)");
    $q->execute($data);
  }
  redirect(APP_URL.'/admin/dashboard.php');
}
?>
<h1><?= $id? 'แก้ไขรถ' : 'เพิ่มรถ' ?></h1>
<form method="post" class="form-2col">
  <div class="field"><label>ชื่อรุ่น</label><input class="input" name="title" value="<?= h($car['title']) ?>" required></div>
  <div class="field"><label>ยี่ห้อ</label><input class="input" name="brand" value="<?= h($car['brand']) ?>" required></div>
  <div class="field"><label>ที่นั่ง</label><input class="input" name="seats" type="number" value="<?= (int)$car['seats'] ?>"></div>
  <div class="field"><label>ประเภท</label>
    <select name="type" class="input">
      <?php foreach(['Sedan','SUV','Hatchback','Pickup','EV','Sport'] as $t): ?>
        <option value="<?= $t ?>" <?= $t===$car['type']?'selected':'' ?>><?= $t ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field"><label>ราคา/วัน</label><input class="input" name="price_per_day" type="number" step="0.01" value="<?= h($car['price_per_day']) ?>"></div>
  <div class="field"><label>สถานะ</label>
    <select name="status" class="input">
      <?php foreach(['available','maintenance'] as $s): ?>
        <option value="<?= $s ?>" <?= $s===$car['status']?'selected':'' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field" style="grid-column:1/3"><label>รูปภาพ (พาธไฟล์)</label><input class="input" name="img_path" value="<?= h($car['img_path']) ?>"></div>
  <div style="grid-column:1/3;display:flex;gap:10px"><button class="btn btn-primary">บันทึก</button><a class="btn" href="dashboard.php">ยกเลิก</a></div>
</form>
<?php include __DIR__.'/../partials/footer.php'; ?>
