<?php
// هذا الملف في المجلد الرئيسي (بجانب index.php)
require_once 'includes/config.php';
session_start();
session_destroy();
header("Location: index.php");
exit();
?>
