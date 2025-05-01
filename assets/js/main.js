document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
        });
    }
    
    // Image preview for file uploads
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const previewId = this.getAttribute('data-preview');
                if (previewId) {
                    const preview = document.getElementById(previewId);
                    if (preview) {
                        preview.src = URL.createObjectURL(this.files[0]);
                        preview.style.display = 'block';
                    }
                }
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('الرجاء ملء جميع الحقول المطلوبة');
            }
        });
    });
    
    // Messages auto-scroll to bottom
    const messagesList = document.querySelector('.messages-list');
    if (messagesList) {
        messagesList.scrollTop = messagesList.scrollHeight;
    }
    
    // Rating input
    const ratingInputs = document.querySelectorAll('.rating-input input');
    ratingInputs.forEach(input => {
        input.addEventListener('change', function() {
            const labels = this.parentElement.querySelectorAll('label');
            labels.forEach((label, index) => {
                if (index < this.value) {
                    label.style.color = '#ffc107';
                } else {
                    label.style.color = '#ddd';
                }
            });
        });
    });
});