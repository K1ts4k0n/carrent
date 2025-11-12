<?php
require_once __DIR__.'/../db.php';
require_once __DIR__.'/../helpers.php';  // ✅ เพิ่มบรรทัดนี้
session_destroy();
redirect(APP_URL.'/index.php');