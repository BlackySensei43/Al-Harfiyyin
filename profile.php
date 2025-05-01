<?php 
include 'includes/header.php';
include 'includes/config.php';
include 'includes/auth.php';

// Check if viewing another profile
$profileId = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$isOwnProfile = ($profileId == $_SESSION['user_id']);

// Get user data
$query = "SELECT u.*, cp.profession, cp.description, cp.experience_years, cp.hourly_rate 
          FROM users u 
          LEFT JOIN craftsmen_profiles cp ON u.id = cp.user_id 
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $profileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$user = $result->fetch_assoc();
$isCraftsman = ($user['user_type'] == 'craftsman');

// عرض رسائل النجاح والخطأ
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<section class="profile">
    <div class="profile-header">
        <div class="profile-image-container">
            <div class="profile-image-wrapper">
                <img src="<?php echo $user['profile_pic'] ?: 'assets/images/default-profile.png'; ?>" 
                    alt="صورة <?php echo $user['full_name']; ?>"
                    id="profileImagePreview"
                    class="profile-image">
        
                <?php if($isOwnProfile): ?>
                <div class="image-actions">
                    <label for="profilePicUpload" class="btn small change-photo-btn">
                        <i class="fas fa-camera"></i> تغيير الصورة
                    </label>
                    <form id="photoForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="file" id="profilePicUpload" name="profile_pic" accept="image/jpeg,image/png,image/webp" required hidden>
                        <input type="hidden" name="update_photo" value="1">
                        <button type="submit" class="btn small save-photo-btn" style="display: none;">
                            <i class="fas fa-check"></i> حفظ
                        </button>
                    </form>
                    <?php if($user['profile_pic']): ?>
                        <form action="update_profile.php" method="POST" style="display: inline;">
                            <input type="hidden" name="delete_photo" value="1">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn small danger delete-photo-btn">
                                <i class="fas fa-trash"></i> حذف
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-info">
            <h2><?php echo $user['full_name']; ?></h2>
            <?php if($isCraftsman): ?>
                <p class="profession"><?php echo $user['profession']; ?></p>
                <p class="experience"><?php echo $user['experience_years']; ?> سنوات خبرة</p>
                <?php if($user['hourly_rate']): ?>
                    <p class="rate"><?php echo $user['hourly_rate']; ?> جنيه/ساعة</p>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="profile-actions">
                <?php if(!$isOwnProfile && $isCraftsman && $_SESSION['user_type'] == 'client'): ?>
                    <a href="messages.php?to=<?php echo $user['id']; ?>" class="btn">إرسال رسالة</a>
                <?php endif; ?>
                
                <?php if($isOwnProfile): ?>
                    <a href="profile.php?action=edit" class="btn">تعديل الملف الشخصي</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if($isCraftsman): ?>
        <div class="profile-section">
            <h3>نبذة عني</h3>
            <p><?php echo $user['description'] ?: 'لا يوجد وصف.'; ?></p>
        </div>
        
        <div class="profile-section">
            <h3>أعمالي</h3>
            <?php
            $query = "SELECT * FROM posts WHERE craftsman_id = ? ORDER BY created_at DESC LIMIT 3";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $profileId);
            $stmt->execute();
            $result = $stmt->get_result();
            
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
                    echo '<a href="post.php?id=' . $row['id'] . '" class="btn">عرض التفاصيل</a>';
                    echo '</div>';
                }
                echo '</div>';
                echo '<div class="see-more"><a href="posts.php?craftsman=' . $profileId . '" class="btn">عرض جميع الأعمال</a></div>';
            } else {
                echo '<p>لا يوجد أعمال منشورة.</p>';
            }
            ?>
        </div>
        
        <div class="profile-section">
            <h3>تقييمات العملاء</h3>
            <?php
            $query = "SELECT r.*, u.full_name 
                      FROM reviews r 
                      JOIN users u ON r.client_id = u.id 
                      WHERE r.craftsman_id = ? 
                      ORDER BY r.created_at DESC LIMIT 3";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $profileId);
            $stmt->execute();
            $result = $stmt->get_result();
            
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
                echo '<div class="see-more"><a href="reviews.php?craftsman=' . $profileId . '" class="btn">عرض جميع التقييمات</a></div>';
            } else {
                echo '<p>لا يوجد تقييمات بعد.</p>';
            }
            ?>
            
            <?php if(!$isOwnProfile && $_SESSION['user_type'] == 'client'): ?>
                <div class="add-review">
                    <h4>أضف تقييمك</h4>
                    <form action="add_review.php" method="POST">
                        <input type="hidden" name="craftsman_id" value="<?php echo $profileId; ?>">
                        <div class="form-group">
                            <label>التقييم</label>
                            <div class="rating-input">
                                <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                                <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                                <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                                <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                                <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">التعليق</label>
                            <textarea id="comment" name="comment" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn">إرسال التقييم</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if($isOwnProfile && isset($_GET['action']) && $_GET['action'] == 'edit'): ?>
        <div class="edit-profile-form">
            <h3>تعديل الملف الشخصي</h3>
            <form action="update_profile.php" method="POST">
                <div class="form-group">
                    <label for="full_name">الاسم بالكامل</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">رقم الهاتف</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="address">العنوان</label>
                    <textarea id="address" name="address" rows="2"><?php echo $user['address']; ?></textarea>
                </div>
                
                <?php if($isCraftsman): ?>
                    <div class="form-group">
                        <label for="profession">المهنة</label>
                        <select id="profession" name="profession" required>
                            <option value="سباك" <?php echo $user['profession'] == 'سباك' ? 'selected' : ''; ?>>سباك</option>
                            <option value="كهربائي" <?php echo $user['profession'] == 'كهربائي' ? 'selected' : ''; ?>>كهربائي</option>
                            <option value="نجار" <?php echo $user['profession'] == 'نجار' ? 'selected' : ''; ?>>نجار</option>
                            <option value="ميكانيكي" <?php echo $user['profession'] == 'ميكانيكي' ? 'selected' : ''; ?>>ميكانيكي</option>
                            <option value="دهان" <?php echo $user['profession'] == 'دهان' ? 'selected' : ''; ?>>دهان</option>
                            <option value="بناء" <?php echo $user['profession'] == 'بناء' ? 'selected' : ''; ?>>بناء</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">سنوات الخبرة</label>
                        <input type="number" id="experience" name="experience" min="0" value="<?php echo $user['experience_years']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hourly_rate">سعر الساعة (جنيه)</label>
                        <input type="number" id="hourly_rate" name="hourly_rate" min="0" value="<?php echo $user['hourly_rate']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">وصف مختصر عنك</label>
                        <textarea id="description" name="description" rows="3"><?php echo $user['description']; ?></textarea>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="current_password">كلمة المرور الحالية (للتأكيد)</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <button type="submit" class="btn">حفظ التغييرات</button>
            </form>
        </div>
    <?php endif; ?>
</section>

<!-- استدعاء ملف JavaScript المنفصل لمعالجة رفع الصور -->
<script src="assets/js/profile-photo.js"></script>

<?php include 'includes/footer.php'; ?>
