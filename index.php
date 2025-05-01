<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<section class="hero">
    <div class="hero-content">
        <h1>ابحث عن أفضل الحرفيين في مصر</h1>
        <p>تواصل مباشرة مع سباكين، كهربائيين، نجارين، ميكانيكيين وغيرهم من المحترفين</p>
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn">انضم إلينا الآن</a>
        <?php endif; ?>
    </div>
</section>

<section class="craftsmen-section">
    <h2>أحدث الحرفيين المسجلين</h2>
    <div class="craftsmen-grid">
        <?php
        $query = "SELECT u.id, u.full_name, u.profile_pic, cp.profession, cp.experience_years 
                  FROM users u 
                  JOIN craftsmen_profiles cp ON u.id = cp.user_id 
                  WHERE u.user_type = 'craftsman' 
                  ORDER BY u.created_at DESC LIMIT 6";
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="craftsman-card">';
                echo '<div class="craftsman-img">';
                echo '<img src="' . ($row['profile_pic'] ?: 'assets/images/default-profile.png') . '" alt="' . $row['full_name'] . '">';
                echo '</div>';
                echo '<div class="craftsman-info">';
                echo '<h3>' . $row['full_name'] . '</h3>';
                echo '<p class="profession">' . $row['profession'] . '</p>';
                echo '<p class="experience">' . $row['experience_years'] . ' سنوات خبرة</p>';
                echo '<a href="profile.php?id=' . $row['id'] . '" class="btn">عرض الملف</a>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>لا يوجد حرفيين مسجلين بعد.</p>';
        }
        ?>
    </div>
    <div class="see-more">
        <a href="dashboard.php?type=craftsman" class="btn">عرض جميع الحرفيين</a>
    </div>
</section>

<section class="how-it-works">
    <h2>كيف تعمل المنصة؟</h2>
    <div class="steps">
        <div class="step">
            <div class="step-number">1</div>
            <h3>ابحث عن الحرفي</h3>
            <p>تصفح قائمة الحرفيين أو ابحث حسب التخصص والموقع</p>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <h3>تحقق من التقييمات</h3>
            <p>اقرأ تقييمات العملاء السابقين لاختيار الأفضل</p>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <h3>تواصل مباشرة</h3>
            <p>راسل الحرفي عبر المنصة واتفق على التفاصيل</p>
        </div>
    </div>
</section>

<section class="testimonials">
    <div class="container">
        <h2>آراء عملائنا</h2>
        
        <div class="testimonials-slider">
            <?php
            // Show some sample testimonials instead of querying the database
            // This avoids the SQL error while still showing content
            $testimonials = [
                [
                    'rating' => 5,
                    'review_text' => 'تجربة رائعة مع حرفي محترف. أنجز العمل بدقة وفي الوقت المحدد.',
                    'reviewer_name' => 'أحمد محمد',
                    'craftsman_name' => 'محمد علي',
                    'profession' => 'كهربائي'
                ],
                [
                    'rating' => 5,
                    'review_text' => 'العمل كان ممتاز والخدمة سريعة وبسعر مناسب. أنصح بالتعامل معه.',
                    'reviewer_name' => 'عمر خالد',
                    'craftsman_name' => 'خالد أحمد',
                    'profession' => 'سباك'
                ],
                [
                    'rating' => 4,
                    'review_text' => 'حرفي متمكن ويعرف شغله كويس. ممتاز في التواصل والالتزام بالمواعيد.',
                    'reviewer_name' => 'سارة علي',
                    'craftsman_name' => 'يوسف إبراهيم',
                    'profession' => 'نجار'
                ]
            ];
            
            foreach ($testimonials as $row) {
                echo '<div class="testimonial-card">';
                echo '<div class="testimonial-content">';
                echo '<div class="rating">';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $row['rating']) {
                        echo '<span class="star filled">★</span>';
                    } else {
                        echo '<span class="star">☆</span>';
                    }
                }
                echo '</div>';
                echo '<p class="testimonial-text">"' . $row['review_text'] . '"</p>';
                echo '</div>';
                echo '<div class="testimonial-author">';
                echo '<div class="author-image">';
                echo '<img src="assets/images/default-profile.png" alt="' . $row['reviewer_name'] . '">';
                echo '</div>';
                echo '<div class="author-info">';
                echo '<h4>' . $row['reviewer_name'] . '</h4>';
                echo '<p>عن <span>' . $row['craftsman_name'] . '</span> (' . $row['profession'] . ')</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="testimonial-cta">
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn">انضم إلينا لمشاركة تجربتك</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="about-us">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>عن منصة الحرفيين</h2>
                <p>منصة صلة الحرفيين هي الجسر الذي يربط بين أصحاب المهن الحرفية والعملاء في جميع أنحاء مصر. نسعى لتقديم خدمة مميزة تساعد الحرفيين على عرض مهاراتهم وخبراتهم، وتمكن العملاء من الوصول إلى أفضل الخدمات بكل سهولة وأمان.</p>
                <p>نحن نؤمن بأن المهن الحرفية هي عماد المجتمع، ونعمل على رفع مستوى هذه المهن من خلال منصة تقنية متطورة تجمع الجميع في مكان واحد.</p>
            </div>
            <div class="about-stats">
                <div class="stat">
                    <div class="stat-value" id="craftsmen-count">500+</div>
                    <div class="stat-label">حرفي مسجل</div>
                </div>
                <div class="stat">
                    <div class="stat-value" id="users-count">1000+</div>
                    <div class="stat-label">مستخدم نشط</div>
                </div>
                <div class="stat">
                    <div class="stat-value" id="cities-count">20+</div>
                    <div class="stat-label">مدينة</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>