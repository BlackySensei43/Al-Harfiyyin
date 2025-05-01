<?php 
include 'includes/header.php';
include 'includes/config.php';
include 'includes/auth.php';
?>

<section class="dashboard">
    <h2>لوحة التحكم</h2>
    
    <div class="dashboard-welcome">
        <p>مرحباً <?php echo $_SESSION['username']; ?>!</p>
        <p>أنت مسجل كـ <?php echo $_SESSION['user_type'] == 'client' ? 'عميل' : 'حرفي'; ?></p>
    </div>
    
    <?php if($_SESSION['user_type'] == 'client'): ?>
        <div class="search-craftsmen">
            <h3>ابحث عن حرفي</h3>
            <form method="GET" action="dashboard.php">
                <input type="hidden" name="type" value="craftsman">
                <div class="search-group">
                    <input type="text" name="search" placeholder="ابحث باسم الحرفي أو المهنة" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    <button type="submit" class="btn">بحث</button>
                </div>
            </form>
        </div>
        
        <?php
        $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
        $query = "SELECT u.id, u.full_name, u.profile_pic, cp.profession, cp.experience_years 
                  FROM users u 
                  JOIN craftsmen_profiles cp ON u.id = cp.user_id 
                  WHERE u.user_type = 'craftsman'";
        
        if (!empty($search)) {
            $query .= " AND (u.full_name LIKE '%$search%' OR cp.profession LIKE '%$search%')";
        }
        
        $result = $conn->query($query);
        
        if ($result->num_rows > 0) {
            echo '<div class="craftsmen-grid">';
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
            echo '</div>';
        } else {
            echo '<p>لا يوجد حرفيين متطابقين مع بحثك.</p>';
        }
        ?>
        
    <?php else: // Craftsman dashboard ?>
        <div class="craftsman-stats">
            <div class="stat-card">
                <h3>إجمالي الأعمال المنشورة</h3>
                <?php
                $query = "SELECT COUNT(*) as total FROM posts WHERE craftsman_id = " . $_SESSION['user_id'];
                $result = $conn->query($query);
                $total = $result->fetch_assoc()['total'];
                ?>
                <p><?php echo $total; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>متوسط التقييم</h3>
                <?php
                $query = "SELECT AVG(rating) as avg_rating FROM reviews WHERE craftsman_id = " . $_SESSION['user_id'];
                $result = $conn->query($query);
                $avg = $result->fetch_assoc()['avg_rating'];
                ?>
                <p><?php echo $avg ? number_format($avg, 1) : 'لا يوجد تقييمات بعد'; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>الرسائل الجديدة</h3>
                <?php
                $query = "SELECT COUNT(*) as unread FROM messages 
                          WHERE receiver_id = " . $_SESSION['user_id'] . " AND is_read = FALSE";
                $result = $conn->query($query);
                $unread = $result->fetch_assoc()['unread'];
                ?>
                <p><?php echo $unread; ?></p>
            </div>
        </div>
        
        <div class="recent-posts">
            <h3>أحدث أعمالك المنشورة</h3>
            <?php
            $query = "SELECT * FROM posts 
                      WHERE craftsman_id = " . $_SESSION['user_id'] . " 
                      ORDER BY created_at DESC LIMIT 3";
            $result = $conn->query($query);
            
            if ($result->num_rows > 0) {
                echo '<div class="posts-grid">';
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="post-card">';
                    $images = json_decode($row['images']);
                    if (!empty($images)) {
                        echo '<img src="' . $images[0] . '" alt="' . $row['title'] . '">';
                    }
                    echo '<h4>' . $row['title'] . '</h4>';
                    echo '<p>' . substr($row['description'], 0, 100) . '...</p>';
                    echo '<p class="price">' . $row['price'] . ' جنيه</p>';
                    echo '<a href="posts.php?action=edit&id=' . $row['id'] . '" class="btn">تعديل</a>';
                    echo '</div>';
                }
                echo '</div>';
                echo '<div class="see-more"><a href="posts.php" class="btn">عرض جميع الأعمال</a></div>';
            } else {
                echo '<p>لم تنشر أي أعمال بعد. <a href="posts.php?action=new" class="btn">نشر عمل جديد</a></p>';
            }
            ?>
        </div>
        
        <div class="recent-reviews">
            <h3>أحدث التقييمات</h3>
            <?php
            $query = "SELECT r.*, u.full_name 
                      FROM reviews r 
                      JOIN users u ON r.client_id = u.id 
                      WHERE r.craftsman_id = " . $_SESSION['user_id'] . " 
                      ORDER BY r.created_at DESC LIMIT 3";
            $result = $conn->query($query);
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="review-card">';
                    echo '<div class="review-header">';
                    echo '<h4>' . $row['full_name'] . '</h4>';
                    echo '<div class="rating">';
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $row['rating'] ? '★' : '☆';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '<p>' . $row['comment'] . '</p>';
                    echo '<small>' . date('Y-m-d', strtotime($row['created_at'])) . '</small>';
                    echo '</div>';
                }
                echo '<div class="see-more"><a href="reviews.php" class="btn">عرض جميع التقييمات</a></div>';
            } else {
                echo '<p>لا يوجد تقييمات بعد.</p>';
            }
            ?>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>