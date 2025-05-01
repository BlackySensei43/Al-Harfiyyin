<?php 
include 'includes/header.php';
include 'includes/config.php';
include 'includes/auth.php';

// Check if viewing another craftsman's posts
$craftsmanId = isset($_GET['craftsman']) ? (int)$_GET['craftsman'] : $_SESSION['user_id'];
$isOwnPosts = ($craftsmanId == $_SESSION['user_id']);

// Add new post
if (isset($_GET['action']) && $_GET['action'] == 'new' && $isOwnPosts && $_SESSION['user_type'] == 'craftsman') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $price = (float)$_POST['price'];
        
        // Handle file uploads
        $uploadedImages = [];
        if (!empty($_FILES['images']['name'][0])) {
            $targetDir = "assets/uploads/posts/";
            
            // Create directory if it doesn't exist
            if (!file_exists($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    $error = "لا يمكن إنشاء مجلد الرفع";
                    // Continue execution to display the form with errors
                } else {
                    // Set proper permissions for the directory
                    chmod($targetDir, 0777);
                }
            }
            
            // Verify directory is writable
            if (!is_writable($targetDir)) {
                $error = "مجلد الرفع لا يملك صلاحيات الكتابة";
                // Continue execution to display the form with errors
            } else {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    if(empty($tmpName)) continue;
                    
                    // Get file extension
                    $fileName = basename($_FILES['images']['name'][$key]);
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Check file extension
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if(!in_array($fileExtension, $allowedExtensions)) {
                        $error = "نوع الملف غير مسموح به للصورة $fileName. المسموح: JPG, JPEG, PNG, GIF, WEBP";
                        continue;
                    }
                    
                    // Generate unique file name
                    $newFileName = uniqid() . '_' . $fileName;
                    $targetFile = $targetDir . $newFileName;
                    
                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $uploadedImages[] = $targetFile;
                    } else {
                        $error = "حدث خطأ أثناء تحميل الصورة $fileName.";
                        error_log("Error uploading image: " . $_FILES['images']['error'][$key]);
                    }
                }
            }
        }
        
        if(!isset($error)) {
            $query = "INSERT INTO posts (craftsman_id, title, description, price, images) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $imagesJson = json_encode($uploadedImages);
            $stmt->bind_param("issds", $_SESSION['user_id'], $title, $description, $price, $imagesJson);
            
            if ($stmt->execute()) {
                header("Location: posts.php");
                exit();
            } else {
                $error = "حدث خطأ أثناء نشر العمل. يرجى المحاولة مرة أخرى.";
                error_log("Error inserting post in database: " . $conn->error);
            }
        }
    }
    
    ?>
    <section class="new-post">
        <h2>نشر عمل جديد</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="posts.php?action=new" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">عنوان العمل</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">وصف العمل</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">السعر (جنيه مصري)</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="images">صور العمل (يمكن اختيار أكثر من صورة)</label>
                <input type="file" id="images" name="images[]" multiple accept="image/*">
            </div>
            
            <button type="submit" class="btn">نشر العمل</button>
            <a href="posts.php" class="btn secondary">إلغاء</a>
        </form>
    </section>
    <?php
    include 'includes/footer.php';
    exit();
}

// Edit post
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) && $isOwnPosts && $_SESSION['user_type'] == 'craftsman') {
    $postId = (int)$_GET['id'];
    
    // Get post data
    $query = "SELECT * FROM posts WHERE id = ? AND craftsman_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $postId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        header("Location: posts.php");
        exit();
    }
    
    $post = $result->fetch_assoc();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $price = (float)$_POST['price'];
        
        // Handle file uploads
        $uploadedImages = json_decode($post['images'], true) ?: [];
        
        // Handle image deletions
        if (isset($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $imageToDelete) {
                if (($key = array_search($imageToDelete, $uploadedImages)) !== false) {
                    unset($uploadedImages[$key]);
                    // Optionally delete the file from server
                    if (file_exists($imageToDelete)) {
                        unlink($imageToDelete);
                    }
                }
            }
            $uploadedImages = array_values($uploadedImages); // Reindex array
        }
        
        // Add new images
        if (!empty($_FILES['images']['name'][0])) {
            $targetDir = "assets/uploads/posts/";
            
            // Create directory if it doesn't exist
            if (!file_exists($targetDir)) {
                if (!mkdir($targetDir, 0777, true)) {
                    $error = "لا يمكن إنشاء مجلد الرفع";
                    // Continue execution to display the form with errors
                } else {
                    // Set proper permissions for the directory
                    chmod($targetDir, 0777);
                }
            }
            
            // Verify directory is writable
            if (!is_writable($targetDir)) {
                $error = "مجلد الرفع لا يملك صلاحيات الكتابة";
                // Continue execution to display the form with errors
            } else {
                foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                    if(empty($tmpName)) continue;
                    
                    // Get file extension
                    $fileName = basename($_FILES['images']['name'][$key]);
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    // Check file extension
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if(!in_array($fileExtension, $allowedExtensions)) {
                        $error = "نوع الملف غير مسموح به للصورة $fileName. المسموح: JPG, JPEG, PNG, GIF, WEBP";
                        continue;
                    }
                    
                    // Generate unique file name
                    $newFileName = uniqid() . '_' . $fileName;
                    $targetFile = $targetDir . $newFileName;
                    
                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $uploadedImages[] = $targetFile;
                    } else {
                        $error = "حدث خطأ أثناء تحميل الصورة $fileName.";
                        error_log("Error uploading image: " . $_FILES['images']['error'][$key]);
                    }
                }
            }
        }
        
        if(!isset($error)) {
            $query = "UPDATE posts SET title = ?, description = ?, price = ?, images = ? 
                      WHERE id = ? AND craftsman_id = ?";
            $stmt = $conn->prepare($query);
            $imagesJson = json_encode($uploadedImages);
            $stmt->bind_param("ssdsii", $title, $description, $price, $imagesJson, $postId, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                header("Location: posts.php");
                exit();
            } else {
                $error = "حدث خطأ أثناء تحديث العمل. يرجى المحاولة مرة أخرى.";
                error_log("Error updating post in database: " . $conn->error);
            }
        }
    }
    
    ?>
    <section class="edit-post">
        <h2>تعديل العمل</h2>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="posts.php?action=edit&id=<?php echo $postId; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">عنوان العمل</label>
                <input type="text" id="title" name="title" value="<?php echo $post['title']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">وصف العمل</label>
                <textarea id="description" name="description" rows="5" required><?php echo $post['description']; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">السعر (جنيه مصري)</label>
                <input type="number" id="price" name="price" min="0" step="0.01" value="<?php echo $post['price']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>الصور الحالية</label>
                <div class="current-images">
                    <?php 
                    $images = json_decode($post['images']);
                    if (!empty($images)) {
                        foreach ($images as $image) {
                            echo '<div class="image-item">';
                            echo '<img src="' . $image . '" alt="صورة العمل">';
                            echo '<label><input type="checkbox" name="delete_images[]" value="' . $image . '"> حذف</label>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>لا يوجد صور.</p>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="images">إضافة صور جديدة (يمكن اختيار أكثر من صورة)</label>
                <input type="file" id="images" name="images[]" multiple accept="image/*">
            </div>
            
            <button type="submit" class="btn">حفظ التغييرات</button>
            <a href="posts.php" class="btn secondary">إلغاء</a>
        </form>
    </section>
    <?php
    include 'includes/footer.php';
    exit();
}

// Delete post
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && $isOwnPosts && $_SESSION['user_type'] == 'craftsman') {
    $postId = (int)$_GET['id'];
    
    // Get post to delete images
    $query = "SELECT images FROM posts WHERE id = ? AND craftsman_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $postId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $post = $result->fetch_assoc();
        $images = json_decode($post['images']);
        
        // Delete images from server
        if (!empty($images)) {
            foreach ($images as $image) {
                if (file_exists($image)) {
                    unlink($image);
                }
            }
        }
        
        // Delete post from database
        $query = "DELETE FROM posts WHERE id = ? AND craftsman_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $postId, $_SESSION['user_id']);
        $stmt->execute();
    }
    
    header("Location: posts.php");
    exit();
}

// View posts
$query = "SELECT * FROM posts WHERE craftsman_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $craftsmanId);
$stmt->execute();
$result = $stmt->get_result();
?>

<section class="posts">
    <h2><?php echo $isOwnPosts ? 'أعمالي المنشورة' : 'أعمال الحرفي'; ?></h2>
    
    <?php if($isOwnPosts && $_SESSION['user_type'] == 'craftsman'): ?>
        <div class="actions">
            <a href="posts.php?action=new" class="btn">نشر عمل جديد</a>
        </div>
    <?php endif; ?>
    
    <?php if($result->num_rows > 0): ?>
        <div class="posts-grid">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="post-card">
                    <?php 
                    $images = json_decode($row['images']);
                    if (!empty($images)): ?>
                        <div class="post-images">
                            <img src="<?php echo $images[0]; ?>" alt="<?php echo $row['title']; ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-details">
                        <h3><?php echo $row['title']; ?></h3>
                        <p><?php echo $row['description']; ?></p>
                        <p class="price"><?php echo $row['price']; ?> جنيه مصري</p>
                        
                        <?php if($isOwnPosts && $_SESSION['user_type'] == 'craftsman'): ?>
                            <div class="post-actions">
                                <a href="posts.php?action=edit&id=<?php echo $row['id']; ?>" class="btn small">تعديل</a>
                                <a href="posts.php?action=delete&id=<?php echo $row['id']; ?>" class="btn small danger" onclick="return confirm('هل أنت متأكد من حذف هذا العمل؟');">حذف</a>
                            </div>
                        <?php elseif($_SESSION['user_type'] == 'client'): ?>
                            <a href="messages.php?to=<?php echo $craftsmanId; ?>&post=<?php echo $row['id']; ?>" class="btn">تواصل مع الحرفي</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p><?php echo $isOwnPosts ? 'لم تنشر أي أعمال بعد.' : 'لا يوجد أعمال منشورة لهذا الحرفي.'; ?></p>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>