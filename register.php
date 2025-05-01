<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = password_hash(sanitize($_POST['password']), PASSWORD_DEFAULT);
    $email = sanitize($_POST['email']);
    $userType = sanitize($_POST['user_type']);
    $fullName = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    
    // Check if username or email already exists
    $checkQuery = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "اسم المستخدم أو البريد الإلكتروني موجود بالفعل";
    } else {
        $query = "INSERT INTO users (username, password, email, user_type, full_name, phone) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $username, $password, $email, $userType, $fullName, $phone);
        
        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            
            // If craftsman, create craftsman profile
            if ($userType == 'craftsman') {
                $profession = sanitize($_POST['profession']);
                $description = sanitize($_POST['description']);
                $experience = sanitize($_POST['experience']);
                
                $profileQuery = "INSERT INTO craftsmen_profiles (user_id, profession, description, experience_years) 
                                 VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($profileQuery);
                $stmt->bind_param("issi", $userId, $profession, $description, $experience);
                $stmt->execute();
            }
            
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = $userType;
            
            header("Location: profile.php");
            exit();
        } else {
            $error = "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.";
        }
    }
}
?>

<section class="auth-form">
    <h2>إنشاء حساب جديد</h2>
    <?php if(isset($error)): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="register.php" method="POST" id="registerForm">
        <div class="form-group">
            <label for="user_type">نوع الحساب</label>
            <select id="user_type" name="user_type" required>
                <option value="">اختر نوع الحساب</option>
                <option value="client">عميل</option>
                <option value="craftsman">حرفي</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="full_name">الاسم بالكامل</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>
        
        <div class="form-group">
            <label for="username">اسم المستخدم</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="email">البريد الإلكتروني</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="phone">رقم الهاتف</label>
            <input type="tel" id="phone" name="phone" required>
        </div>
        
        <div class="form-group">
            <label for="password">كلمة المرور</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">تأكيد كلمة المرور</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div id="craftsmanFields" style="display: none;">
            <div class="form-group">
                <label for="profession">المهنة</label>
                <select id="profession" name="profession">
                    <option value="">اختر المهنة</option>
                    <option value="سباك">سباك</option>
                    <option value="كهربائي">كهربائي</option>
                    <option value="نجار">نجار</option>
                    <option value="ميكانيكي">ميكانيكي</option>
                    <option value="دهان">دهان</option>
                    <option value="بناء">بناء</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="experience">سنوات الخبرة</label>
                <input type="number" id="experience" name="experience" min="0">
            </div>
            
            <div class="form-group">
                <label for="description">وصف مختصر عنك</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
        </div>
        
        <button type="submit" class="btn">إنشاء الحساب</button>
    </form>
    <div class="auth-links">
        <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
    </div>
</section>

<script>
document.getElementById('user_type').addEventListener('change', function() {
    const craftsmanFields = document.getElementById('craftsmanFields');
    if (this.value === 'craftsman') {
        craftsmanFields.style.display = 'block';
        document.getElementById('profession').setAttribute('required', '');
    } else {
        craftsmanFields.style.display = 'none';
        document.getElementById('profession').removeAttribute('required');
    }
});

document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('كلمة المرور وتأكيدها غير متطابقين');
    }
});
</script>

<?php include 'includes/footer.php'; ?>