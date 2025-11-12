<?php
require_once __DIR__.'/../db.php'; require_once __DIR__.'/../helpers.php';
if(!is_logged_in()){ redirect(APP_URL.'/auth/login.php'); }
$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("UPDATE bookings SET payment_status='paid' WHERE id=? AND user_id=?");
$stmt->execute([$id, $_SESSION['user']['id']]);
redirect(APP_URL.'/my-bookings.php');
