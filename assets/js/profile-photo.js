document.addEventListener('DOMContentLoaded', function() {
    // عناصر رفع الصورة
    const profilePicUpload = document.getElementById('profilePicUpload');
    const profileImagePreview = document.getElementById('profileImagePreview');
    const savePhotoBtn = document.querySelector('.save-photo-btn');
    const photoForm = document.getElementById('photoForm');
    
    // معالجة تغيير الصورة
    if (profilePicUpload) {
        profilePicUpload.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // التحقق من نوع الملف
                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('نوع الملف غير مسموح به. يرجى استخدام JPEG أو PNG أو WEBP فقط.');
                    this.value = '';
                    return;
                }
                
                // التحقق من حجم الملف (5MB كحد أقصى)
                if (file.size > 5 * 1024 * 1024) {
                    alert('حجم الملف كبير جدًا. الحد الأقصى هو 5 ميجابايت.');
                    this.value = '';
                    return;
                }
                
                // عرض معاينة الصورة
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImagePreview.src = e.target.result;
                    savePhotoBtn.style.display = 'inline-block';
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // معالجة إرسال نموذج الصورة - using regular form submission instead of AJAX
    if (photoForm) {
        savePhotoBtn.addEventListener('click', function() {
            // Show loading indicator directly on the button
            const originalText = savePhotoBtn.innerHTML;
            savePhotoBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الرفع...';
            savePhotoBtn.disabled = true;
            
            // Submit the form directly (no AJAX)
            photoForm.submit();
        });
    }
    
    // معالجة حذف الصورة
    const deletePhotoBtn = document.querySelector('.delete-photo-btn');
    if (deletePhotoBtn) {
        deletePhotoBtn.addEventListener('click', function() {
            if (confirm('هل أنت متأكد من حذف صورتك الشخصية؟')) {
                const userId = this.getAttribute('data-userid');
                
                // Create a form and submit it directly
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'update_profile.php';
                
                // Add delete_photo field
                const deletePhotoField = document.createElement('input');
                deletePhotoField.type = 'hidden';
                deletePhotoField.name = 'delete_photo';
                deletePhotoField.value = '1';
                form.appendChild(deletePhotoField);
                
                // Add user_id field
                const userIdField = document.createElement('input');
                userIdField.type = 'hidden';
                userIdField.name = 'user_id';
                userIdField.value = userId;
                form.appendChild(userIdField);
                
                // Add current_password field (empty)
                const currentPasswordField = document.createElement('input');
                currentPasswordField.type = 'hidden';
                currentPasswordField.name = 'current_password';
                currentPasswordField.value = '';
                form.appendChild(currentPasswordField);
                
                // Append form to body and submit
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
});