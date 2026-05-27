            </main> <!-- end content area -->
        </div> <!-- end row -->
    </div> <!-- end container-fluid -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- اسکریپت Dark Mode -->
    <script>
    (function() {
        const html = document.documentElement;
        const toggleBtn = document.getElementById('darkModeToggle');
        const icon = toggleBtn ? toggleBtn.querySelector('i') : null;

        // خواندن تنظیمات از کوکی (یا پیش‌فرض dark)
        function getTheme() {
            const match = document.cookie.match(/theme=([^;]+)/);
            return match ? match[1] : 'dark'; // dark default
        }

        function setTheme(theme) {
            html.setAttribute('data-bs-theme', theme);
            document.cookie = `theme=${theme};path=/;max-age=31536000`; // 1 year
            if (icon) {
                icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }

        // اعمال اولیه
        setTheme(getTheme());

        // رویداد کلیک
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const current = html.getAttribute('data-bs-theme');
                const newTheme = current === 'dark' ? 'light' : 'dark';
                setTheme(newTheme);
            });
        }
    })();
    </script>
    <!-- انیمیشن‌های ورود برای المان‌های دارای کلاس fade-in-up -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // اعمال کلاس fade-in-up به عناصر مورد نظر
    const animatedElements = document.querySelectorAll('.card, .stat-card, .alert, h1, h2, .table-responsive');
    animatedElements.forEach((el, index) => {
        el.classList.add('fade-in-up');
        // افزودن تأخیر بر اساس موقعیت
        el.style.animationDelay = (index * 0.08) + 's';
    });
});
</script>
</body>
</html>