    </main>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section about">
                    <h3>عن منصة الحرفيين</h3>
                    <p>منصة تواصل بين الحرفيين المحترفين والعملاء في مصر. نسعى لتسهيل عملية العثور على حرفيين موثوقين.</p>
                    <div class="social-media">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-section links">
                    <h3>روابط سريعة</h3>
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
                </div>
                <div class="footer-section contact">
                    <h3>اتصل بنا</h3>
                    <p><i class="fas fa-envelope"></i> البريد الإلكتروني: ahmedmadkour.business@gmail.com</p>
                    <p><i class="fas fa-phone"></i> الهاتف: 01556535452</p>
                    <p><i class="fas fa-map-marker-alt"></i> العنوان: القاهرة، مصر</p>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="footer-bottom">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> منصة الحرفيين. جميع الحقوق محفوظة.</p>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" integrity="sha512-fD9DI5bZwQxOi7MhYWnnNPlvXdp/2Pj3XSTRrFs5FQa4mizyGLnJcN6tuvUS6LbmgN1ut+XGSABKvjN0H6Aoow==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>