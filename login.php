<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    
    $query = "SELECT id, username, password, user_type FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "اسم المستخدم أو كلمة المرور غير صحيحة";
        }
    } else {
        $error = "اسم المستخدم أو كلمة المرور غير صحيحة";
    }
}

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    list($user_id, $token) = explode(':', $_COOKIE['remember_me']);
    
    $query = "SELECT id, username, password, user_type FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (hash('sha256', $user['password']) === $token) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            setcookie('remember_me', '', time() - 3600, "/");
        }
    }
}
?>

<section class="auth-form">
    <h2>تسجيل الدخول</h2>
    <?php if(isset($error)): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">اسم المستخدم</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">كلمة المرور</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="remember"> تذكرني
            </label>
        </div>
        <button type="submit" class="btn">تسجيل الدخول</button>
    </form>
    <div class="auth-links">
        <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
        <p><a href="forgot-password.php">نسيت كلمة المرور؟</a></p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>