<?php require_once __DIR__ . '/../db.php'; require_once __DIR__ . '/../helpers.php'; ?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title ?? 'ระบบเช่ารถ | CarRent') ?></title>
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/styles.css">
</head>
<body>
<?php $uri = $_SERVER['REQUEST_URI'] ?? ''; ?>
<div class="nav">
  <div class="nav-inner">
    <a href="<?= APP_URL ?>/index.php" class="brand">
      <span class="brand-badge">CR</span>
      <span>CarRent</span>
      <span class="badge">PHP</span>
    </a>
    <div class="nav-links">
      <a href="<?= APP_URL ?>/cars.php"
         class="<?= strpos($uri, '/cars.php')!==false ? 'active' : '' ?>">รถทั้งหมด</a>

      <a href="<?= APP_URL ?>/my-bookings.php"
         class="<?= strpos($uri, '/my-bookings.php')!==false ? 'active' : '' ?>">การจองของฉัน</a>

      <?php if(is_admin()): ?>
        <a href="<?= APP_URL ?>/admin/dashboard.php"
           class="<?= strpos($uri, '/admin/dashboard.php')!==false ? 'active' : '' ?>">แอดมิน</a>
        <a href="<?= APP_URL ?>/admin/reports.php"
           class="<?= strpos($uri, '/admin/reports.php')!==false ? 'active' : '' ?>">รายงาน</a>
      <?php endif; ?>
    </div>

    <div class="nav-links">
      <?php if(is_logged_in()): ?>
        <span class="badge">สวัสดี, <?= h($_SESSION['user']['name']) ?></span>
        <a class="btn" href="<?= APP_URL ?>/auth/logout.php">ออกจากระบบ</a>
      <?php else: ?>
        <a class="btn" href="<?= APP_URL ?>/auth/login.php">เข้าสู่ระบบ</a>
        <a class="btn btn-primary" href="<?= APP_URL ?>/auth/register.php">สมัครสมาชิก</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<div class="container">
