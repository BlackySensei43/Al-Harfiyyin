<?php 
include 'includes/header.php';
include 'includes/config.php';
include 'includes/auth.php';

// Check if viewing another craftsman's reviews
$craftsmanId = isset($_GET['craftsman']) ? (int)$_GET['craftsman'] : $_SESSION['user_id'];
$isOwnReviews = ($craftsmanId == $_SESSION['user_id']);

// Add new review (only clients can add reviews to craftsmen)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating']) && !$isOwnReviews && $_SESSION['user_type'] == 'client') {
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    
    // Check if client already reviewed this craftsman
    $checkQuery = "SELECT id FROM reviews WHERE client_id = ? AND craftsman_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $_SESSION['user_id'], $craftsmanId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "لقد قمت بتقييم هذا الحرفي من قبل.";
    } else {
        $query = "INSERT INTO reviews (craftsman_id, client_id, rating, comment) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiis", $craftsmanId, $_SESSION['user_id'], $rating, $comment);
        
        if ($stmt->execute()) {
            header("Location: reviews.php?craftsman=" . $craftsmanId);
            exit();
        } else {
            $error = "حدث خطأ أثناء إضافة التقييم. يرجى المحاولة مرة أخرى.";
        }
    }
}

// Get reviews
$query = "SELECT r.*, u.full_name, u.profile_pic 
          FROM reviews r
          JOIN users u ON r.client_id = u.id
          WHERE r.craftsman_id = ?
          ORDER BY r.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $craftsmanId);
$stmt->execute();
$reviews = $stmt->get_result();

// Get craftsman info
$query = "SELECT u.full_name, cp.profession 
          FROM users u
          JOIN craftsmen_profiles cp ON u.id = cp.user_id
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $craftsmanId);
$stmt->execute();
$craftsman = $stmt->get_result()->fetch_assoc();
?>

<section class="reviews">
    <h2>تقييمات <?php echo $isOwnReviews ? 'الخاصة بي' : $craftsman['full_name'] . ' (' . $craftsman['profession'] . ')'; ?></h2>
    
    <?php if(isset($error)): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if(!$isOwnReviews && $_SESSION['user_type'] == 'client'): ?>
        <div class="add-review">
            <h3>أضف تقييمك</h3>
            <form action="reviews.php?craftsman=<?php echo $craftsmanId; ?>" method="POST">
                <div class="form-group">
                    <label>التقييم</label>
                    <div class="rating-input">
                        <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
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
    
    <div class="reviews-list">
        <?php if($reviews->num_rows > 0): ?>
            <?php while($review = $reviews->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <img src="<?php echo $review['profile_pic'] ?: 'assets/images/default-profile.png'; ?>" alt="<?php echo $review['full_name']; ?>">
                            <h4><?php echo $review['full_name']; ?></h4>
                        </div>
                        <div class="rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p><?php echo $review['comment']; ?></p>
                    <small><?php echo date('Y-m-d', strtotime($review['created_at'])); ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p><?php echo $isOwnReviews ? 'لا يوجد تقييمات لك بعد.' : 'لا يوجد تقييمات لهذا الحرفي بعد.'; ?></p>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>