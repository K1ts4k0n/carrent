<?php
require_once __DIR__.'/db.php'; require_once __DIR__.'/helpers.php';
if(!is_logged_in()){ redirect(APP_URL.'/auth/login.php'); }

$car_id = (int)($_POST['car_id'] ?? 0);
$start = $_POST['start'] ?? '';
$end = $_POST['end'] ?? '';

if(!$car_id || !$start || !$end){ redirect(APP_URL.'/cars.php'); }

// fetch car
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id=? AND status='available'");
$stmt->execute([$car_id]);
$car = $stmt->fetch();
if(!$car){ redirect(APP_URL.'/cars.php'); }

$days = 1 + (int)((strtotime($end)-strtotime($start))/86400);
if($days<=0){ redirect(APP_URL.'/car.php?id='.$car_id.'&start='.$start.'&end='.$end); }

// availability check again
$q = $pdo->prepare("SELECT COUNT(*) c FROM bookings WHERE car_id=? AND status IN ('pending','approved') AND NOT( end_date < ? OR start_date > ? )");
$q->execute([$car_id, $start, $end]);
if($q->fetch()['c']>0){ redirect(APP_URL.'/car.php?id='.$car_id.'&start='.$start.'&end='.$end); }

$total = $days * (float)$car['price_per_day'];
$ins = $pdo->prepare("INSERT INTO bookings(user_id,car_id,start_date,end_date,days,total) VALUES(?,?,?,?,?,?)");
$ins->execute([$_SESSION['user']['id'], $car_id, $start, $end, $days, $total]);
redirect(APP_URL.'/my-bookings.php');
?>
