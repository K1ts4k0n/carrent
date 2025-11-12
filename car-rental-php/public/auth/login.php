<?php
$title='เข้าสู่ระบบ'; include __DIR__.'/../partials/head.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email = $_POST['email'] ?? '';
  $pass = $_POST['password'] ?? '';
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
  $stmt->execute([$email]);
  $u = $stmt->fetch();
  if($u && hash('sha256',$pass)===$u['password_hash']){
    $_SESSION['user'] = ['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role'],'email'=>$u['email']];
    redirect(APP_URL.'/index.php');
  } else { $err='อีเมลหรือรหัสผ่านไม่ถูกต้อง'; }
}
?>
<h1>เข้าสู่ระบบ</h1>
<?php if(isset($err)): ?><div class="alert"><?= $err ?></div><?php endif; ?>
<form method="post" class="form-2col">
  <div class="field"><label>อีเมล</label><input class="input" name="email" required></div>
  <div class="field"><label>รหัสผ่าน</label><input class="input" type="password" name="password" required></div>
  <div style="grid-column:1/3">
    <button class="btn btn-primary">เข้าสู่ระบบ</button>
  </div>
</form>
<?php include __DIR__.'/../partials/footer.php'; ?>
