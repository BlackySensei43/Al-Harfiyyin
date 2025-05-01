<?php
session_start();
include 'includes/config.php';
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['user_type'] == 'client') {
    $craftsmanId = (int)$_POST['craftsman_id'];
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    
    // Check if client already reviewed this craftsman
    $query = "SELECT id FROM reviews WHERE client_id = ? AND craftsman_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $_SESSION['user_id'], $craftsmanId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "لقد قمت بتقييم هذا الحرفي من قبل";
        header("Location: profile.php?id=" . $craftsmanId);
        exit();
    }
    
    // Add new review
    $query = "INSERT INTO reviews (craftsman_id, client_id, rating, comment) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $craftsmanId, $_SESSION['user_id'], $rating, $comment);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "تم إضافة التقييم بنجاح";
    } else {
        $_SESSION['error'] = "حدث خطأ أثناء إضافة التقييم";
    }
    
    header("Location: profile.php?id=" . $craftsmanId);
    exit();
}
?>