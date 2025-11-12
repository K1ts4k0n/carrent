<?php
$title='สมัครสมาชิก'; include __DIR__.'/../partials/head.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name = $_POST['name'] ?? '';
  $email = $_POST['email'] ?? '';
  $pass = $_POST['password'] ?? '';
  if(!$name || !$email || !$pass){ $err='กรอกข้อมูลให้ครบถ้วน'; }
  else{
    try{
      $stmt = $pdo->prepare("INSERT INTO users(name,email,password_hash) VALUES(?,?,?)");
      $stmt->execute([$name,$email,hash('sha256',$pass)]);
      echo "<script>showToast('สมัครสำเร็จ! เข้าสู่ระบบได้เลย');</script>";
    }catch(Exception $e){ $err='อีเมลนี้ถูกใช้แล้ว'; }
  }
}
?>
<h1>สมัครสมาชิก</h1>
<?php if(isset($err)): ?><div class="alert"><?= $err ?></div><?php endif; ?>
<form method="post" class="form-2col">
  <div class="field"><label>ชื่อ</label><input class="input" name="name" required></div>
  <div class="field"><label>อีเมล</label><input class="input" name="email" type="email" required></div>
  <div class="field"><label>รหัสผ่าน</label><input class="input" type="password" name="password" required></div>
  <div style="grid-column:1/3"><button class="btn btn-primary">สมัครสมาชิก</button></div>
</form>
<?php include __DIR__.'/../partials/footer.php'; ?>
