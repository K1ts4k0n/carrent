<?php
require_once __DIR__.'/../db.php'; require_once __DIR__.'/../helpers.php';
if(!is_admin()){ redirect(APP_URL.'/index.php'); }
$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? 'pending';
$ok = in_array($status, ['approved','rejected','cancelled','returned']);
if($id && $ok){
  $stmt = $pdo->prepare("UPDATE bookings SET status=? WHERE id=?");
  $stmt->execute([$status,$id]);
}
redirect(APP_URL.'/admin/dashboard.php');
