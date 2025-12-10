<?php 
// layout/footer.php 
$userAvatar = $_SESSION['user']['avatar'] ?? null;
?>
</div> <!-- End #main-content -->

<footer class="footer border-top bg-white mt-4 d-none d-md-block">
    <div class="container text-center">
        Platform kursus dengan alur:
        <em>Materi → Soal → Naik ke materi berikutnya</em>.
    </div>
</footer>

<!-- Mobile Bottom Nav -->
<div class="mobile-bottom-nav">
    <a href="index.php" class="nav-item active" data-page="home">
        <span class="nav-icon">🏠</span>
        <span>Home</span>
    </a>
    <a href="index.php" class="nav-item" data-page="materi">
        <span class="nav-icon">📚</span>
        <span>Materi</span>
    </a>
    <a href="index.php?page=messages" class="nav-item" data-page="messages">
        <span class="nav-icon">💬</span>
        <span>Pesan</span>
    </a>
    <a href="index.php?page=notifications" class="nav-item" data-page="notifications">
        <span class="nav-icon">🔔</span>
        <span>Notifikasi</span>
    </a>
    <a href="index.php?page=profile" class="nav-item" data-page="profile">
        <?php if (!empty($userAvatar) && file_exists('uploads/' . $userAvatar)): ?>
             <img src="uploads/<?= htmlspecialchars($userAvatar) ?>" class="rounded-circle" style="width: 24px; height: 24px; object-fit: cover; margin: 0 auto 2px; display: block;">
        <?php else: ?>
            <span class="nav-icon">👤</span>
        <?php endif; ?>
        <span>Akun</span>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.mobile-bottom-nav .nav-item');
    const mainContent = document.getElementById('main-content');

    // Function to handle navigation
    function handleNavigation(url, clickedItem) {
        // Update active state
        navItems.forEach(nav => nav.classList.remove('active'));
        if(clickedItem) clickedItem.classList.add('active');

        // Add ajax param
        const separator = url.includes('?') ? '&' : '?';
        const fetchUrl = url + separator + 'ajax=1';

        // Fetch content
        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                mainContent.innerHTML = html;
                // Update URL without reload
                window.history.pushState({}, '', url);
                
                // Scroll to top
                window.scrollTo(0, 0);
            })
            .catch(err => console.error('Error loading page:', err));
    }

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            handleNavigation(url, this);
        });
    });
});
</script>
</body>
</html>
