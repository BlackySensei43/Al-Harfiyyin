<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/config.php';

// Function to sanitize input (assuming this is defined in config.php, but adding here for safety)
if (!function_exists('sanitize')) {
    function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}

// Create a debug log function
function logDebug($message) {
    file_put_contents('upload_debug.log', date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

// Log request information
logDebug("Request method: " . $_SERVER['REQUEST_METHOD']);
logDebug("POST data: " . print_r($_POST, true));
logDebug("FILES data: " . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle profile picture deletion
    if (isset($_POST['delete_photo'])) {
        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : $_SESSION['user_id'];
        logDebug("Processing delete photo request for user ID: $userId");
        
        // Get current user's profile picture
        $query = "SELECT profile_pic FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentUser = $result->fetch_assoc();
        
        // Delete the physical file if it exists
        if (!empty($currentUser['profile_pic'])) {
            $oldPicPath = $currentUser['profile_pic'];
            if (file_exists($oldPicPath)) {
                unlink($oldPicPath);
                logDebug("Deleted physical file: $oldPicPath");
            }
        }
        
        // Update the database
        $query = "UPDATE users SET profile_pic = NULL WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            logDebug("Successfully set profile_pic to NULL in database");
            if ($userId == $_SESSION['user_id']) {
                $_SESSION['profile_pic'] = '';
            }
            $_SESSION['success'] = "تم حذف صورة الملف الشخصي بنجاح";
            
            // Return JSON response for AJAX request
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => true]);
                exit;
            }
        } else {
            logDebug("Error deleting profile picture from database: " . $conn->error);
            $_SESSION['error'] = "حدث خطأ أثناء حذف الصورة";
            
            // Return JSON response for AJAX request
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حذف الصورة']);
                exit;
            }
        }
        header("Location: profile.php");
        exit();
    }
    
    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        logDebug("Processing profile picture upload");
        
        $targetDir = "assets/uploads/profile_pics/";
        
        // Ensure upload directory exists
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                logDebug("Failed to create upload directory: $targetDir");
                $_SESSION['error'] = "لا يمكن إنشاء مجلد الرفع";
                header("Location: profile.php?action=edit");
                exit();
            }
            chmod($targetDir, 0777);
            logDebug("Created directory: $targetDir with permissions 0777");
        }
        
        // Verify directory is writable
        if (!is_writable($targetDir)) {
            logDebug("Upload directory is not writable: $targetDir");
            $_SESSION['error'] = "مجلد الرفع لا يملك صلاحيات الكتابة";
            header("Location: profile.php?action=edit");
            exit();
        }
        
        // Check file MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileMimeType = mime_content_type($_FILES['profile_pic']['tmp_name']);
        
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            logDebug("Invalid MIME type: $fileMimeType");
            $_SESSION['error'] = "نوع الملف غير مسموح به. المسموح: JPG, JPEG, PNG, GIF, WEBP";
            header("Location: profile.php?action=edit");
            exit();
        }
        
        // Check file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExtension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            logDebug("Invalid file extension: $fileExtension");
            $_SESSION['error'] = "امتداد الملف غير مسموح به. المسموح: JPG, JPEG, PNG, GIF, WEBP";
            header("Location: profile.php?action=edit");
            exit();
        }
        
        // Check file size (5MB max)
        if ($_FILES['profile_pic']['size'] > 5 * 1024 * 1024) {
            logDebug("File too large: " . $_FILES['profile_pic']['size'] . " bytes");
            $_SESSION['error'] = "حجم الصورة كبير جداً (الحد الأقصى 5MB)";
            header("Location: profile.php?action=edit");
            exit();
        }
        
        // Create unique filename
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $fileName = $userId . '_' . time() . '.' . $fileExtension;
        $targetFile = $targetDir . $fileName;
        $relativePath = $targetDir . $fileName; // This is what we'll store in the database
        
        logDebug("Preparing to upload file to: $targetFile");
        
        // Get current user's profile picture before updating
        $query = "SELECT profile_pic FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentUser = $result->fetch_assoc();
        
        // Move the uploaded file
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
            logDebug("File successfully uploaded to: $targetFile");
            
            // Delete old profile picture if it exists
            if (!empty($currentUser['profile_pic'])) {
                $oldPicPath = $currentUser['profile_pic'];
                if (file_exists($oldPicPath)) {
                    unlink($oldPicPath);
                    logDebug("Deleted old profile picture: $oldPicPath");
                }
            }
            
            // Update database with the new path
            $sql = "UPDATE users SET profile_pic = ? WHERE id = ?";
            logDebug("Preparing SQL: $sql with params: '$relativePath', $userId");
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                logDebug("Prepare statement failed: " . $conn->error);
                $_SESSION['error'] = "خطأ في إعداد الاستعلام: " . $conn->error;
                header("Location: profile.php");
                exit();
            }
            
            $stmt->bind_param("si", $relativePath, $userId);
            
            if (!$stmt->execute()) {
                logDebug("Execute statement failed: " . $stmt->error);
                $_SESSION['error'] = "خطأ في تحديث قاعدة البيانات: " . $stmt->error;
                header("Location: profile.php");
                exit();
            }
            
            // Check if the update was successful
            logDebug("Database update complete. Affected rows: " . $stmt->affected_rows);
            
            if ($stmt->affected_rows <= 0) {
                // Test direct query as a fallback
                logDebug("No rows affected. Trying direct query.");
                $escapedPath = $conn->real_escape_string($relativePath);
                $directQuery = "UPDATE users SET profile_pic = '$escapedPath' WHERE id = $userId";
                
                if ($conn->query($directQuery)) {
                    logDebug("Direct query succeeded. Affected rows: " . $conn->affected_rows);
                    if ($userId == $_SESSION['user_id']) {
                        $_SESSION['profile_pic'] = $relativePath;
                    }
                    $_SESSION['success'] = "تم تحديث صورة الملف الشخصي بنجاح";
                } else {
                    logDebug("Direct query failed: " . $conn->error);
                    $_SESSION['error'] = "لم يتم تحديث قاعدة البيانات. تأكد من وجود المستخدم.";
                }
            } else {
                // Update was successful
                if ($userId == $_SESSION['user_id']) {
                    $_SESSION['profile_pic'] = $relativePath;
                }
                $_SESSION['success'] = "تم تحديث صورة الملف الشخصي بنجاح";
            }
            
            // Verify the update by checking the database
            $verifyQuery = "SELECT profile_pic FROM users WHERE id = ?";
            $verifyStmt = $conn->prepare($verifyQuery);
            $verifyStmt->bind_param("i", $userId);
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            $verifiedUser = $verifyResult->fetch_assoc();
            
            logDebug("Verification check - Profile pic in database: " . ($verifiedUser['profile_pic'] ?? 'NULL'));
            
            header("Location: profile.php");
            exit();
            
        } else {
            // Upload failed
            $errorCode = $_FILES['profile_pic']['error'];
            logDebug("File upload failed. Error code: $errorCode");
            
            $_SESSION['error'] = "حدث خطأ أثناء تحميل الصورة. رمز الخطأ: $errorCode";
            header("Location: profile.php");
            exit();
        }
    }
    
    // Handle other profile updates
    if (isset($_POST['full_name'])) {
        logDebug("Processing profile information update");
        
        $required = ['full_name', 'email', 'phone', 'current_password'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "جميع الحقول المطلوبة يجب تعبئتها";
                header("Location: profile.php?action=edit");
                exit();
            }
        }
        
        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $currentPassword = sanitize($_POST['current_password'] ?? '');
        $userId = $_SESSION['user_id'];
        
        logDebug("Updating profile for user ID: $userId");
        
        // Verify current password
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            logDebug("User not found in database");
            $_SESSION['error'] = "المستخدم غير موجود";
            header("Location: profile.php?action=edit");
            exit();
        }

        $user = $result->fetch_assoc();
        if (!password_verify($currentPassword, $user['password'] ?? '')) {
            logDebug("Invalid current password provided");
            $_SESSION['error'] = "كلمة المرور الحالية غير صحيحة";
            header("Location: profile.php?action=edit");
            exit();
        }
        
        // Update user info
        $query = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $fullName, $email, $phone, $address, $userId);
        
        if (!$stmt->execute()) {
            logDebug("Failed to update user information: " . $stmt->error);
            $_SESSION['error'] = "فشل تحديث معلومات المستخدم";
            header("Location: profile.php?action=edit");
            exit();
        }
        
        logDebug("User information updated. Affected rows: " . $stmt->affected_rows);
        
        // Update craftsman profile if applicable
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'craftsman') {
            $profession = sanitize($_POST['profession'] ?? '');
            $description = sanitize($_POST['description'] ?? '');
            $experience = (int)($_POST['experience'] ?? 0);
            $hourlyRate = (float)($_POST['hourly_rate'] ?? 0);
            
            $query = "UPDATE craftsmen_profiles SET profession = ?, description = ?, experience_years = ?, hourly_rate = ? 
                    WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssidi", $profession, $description, $experience, $hourlyRate, $userId);
            
            if (!$stmt->execute()) {
                logDebug("Failed to update craftsman profile: " . $stmt->error);
            } else {
                logDebug("Craftsman profile updated. Affected rows: " . $stmt->affected_rows);
            }
        }
        
        $_SESSION['success'] = "تم تحديث الملف الشخصي بنجاح";
        header("Location: profile.php");
        exit();
    }
}

// Redirect to profile page if accessed directly
if (!isset($_SERVER['HTTP_REFERER'])) {
    header("Location: profile.php");
    exit();
}
?>