<?php
function is_logged_in(){ return isset($_SESSION['user']); }
function is_admin(){ return is_logged_in() && $_SESSION['user']['role'] === 'admin'; }
function redirect($path){ header('Location: ' . $path); exit; }
function money($num){ return number_format((float)$num, 2); }
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>