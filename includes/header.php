<?php
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userType = $isLoggedIn ? $_SESSION['user_type'] : '';

// no cache storage
// Prevent caching of the page
// this is important for security reasons
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صلة الحرفيين - تواصل مع الحرفيين المحترفين</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php"><img src="assets/images/logo.png" alt="صلة الحرفيين"></a>
            </div>
            <div class="theme-toggle" id="theme-toggle">
                <svg class="theme-toggle-icon light-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                    <circle cx="12" cy="12" r="5" fill="currentColor"/>
                    <path fill="currentColor" d="M12,1 L12,3 M12,21 L12,23 M4.22,4.22 L5.64,5.64 M18.36,18.36 L19.78,19.78 M1,12 L3,12 M21,12 L23,12 M4.22,19.78 L5.64,18.36 M18.36,5.64 L19.78,4.22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="10s" repeatCount="indefinite" additive="sum"/>
                </svg>
                <svg class="theme-toggle-icon dark-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M12,21 C7.02943725,21 3,16.9705627 3,12 C3,7.02943725 7.02943725,3 12,3 C16.9705627,3 21,7.02943725 21,12 C21,16.9705627 16.9705627,21 12,21 Z" fill="none" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M18,12 C18,7.58172 14.4183,4 10,4 C10,4 11,8 11,12 C11,16 10,20 10,20 C14.4183,20 18,16.4183 18,12 Z" fill="currentColor"/>
                    <circle cx="8" cy="9" r="1" fill="currentColor" opacity="0.4"/>
                    <circle cx="8" cy="15" r="1" fill="currentColor" opacity="0.4"/>
                </svg>
            </div>
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">الرئيسية</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">لوحة التحكم</a></li>
                        <li><a href="messages.php">الرسائل</a></li>
                        <?php if($_SESSION['user_type'] == 'craftsman'): ?>
                            <li><a href="posts.php">إدارة أعمالي</a></li>
                        <?php endif; ?>
                        <li><a href="profile.php">الملف الشخصي</a></li>
                        <li><a href="logout.php">تسجيل الخروج</a></li>
                    <?php else: ?>
                        <li><a href="login.php">تسجيل الدخول</a></li>
                        <li><a href="register.php">إنشاء حساب</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <div class="menu-overlay"></div>
    </header>
    <main class="container">