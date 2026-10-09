<?php
$csrfToken = Csrf::token();
?>
</main>

<?php 
$currentUrl = $_GET['url'] ?? '';
$isLoggedIn = !empty($_SESSION['user_id']);
?>

<?php if ($isLoggedIn): ?>
    <div class="bottom-nav">
        <a href="/tenant/?url=tenant/dashboard" class="nav-item <?= ($currentUrl === 'tenant/dashboard') ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="/tenant/?url=tenant/bookings" class="nav-item <?= ($currentUrl === 'tenant/bookings') ? 'active' : '' ?>">
            <i class="fas fa-house-user"></i>
            <span>My Bhouse</span>
        </a>
        <a href="/tenant/?url=boarding/explore" class="nav-item nav-item-center <?= (strpos($currentUrl, 'boarding/explore') === 0) ? 'active' : '' ?>">
            <div class="center-btn">
                <i class="fas fa-search"></i>
            </div>
            <span>Browse</span>
        </a>
        <a href="/tenant/?url=tenant/payments" class="nav-item <?= ($currentUrl === 'tenant/payments') ? 'active' : '' ?>">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Payments</span>
        </a>
        <a href="/tenant/?url=tenant/profile" class="nav-item <?= ($currentUrl === 'tenant/profile') ? 'active' : '' ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Hide loader after page is fully loaded
    setTimeout(function() {
        $('#page-loader').addClass('hidden');
    }, 400); // Small delay for smooth feel

    // Show loader on link click (Navigation)
    $('a').on('click', function(e) {
        const href = $(this).attr('href');
        const target = $(this).attr('target');
        
        // Only trigger for internal links, NOT including anchor jumps/hashes
        if (href && 
            href.indexOf('/tenant/') !== -1 && 
            !href.includes('#') && 
            !href.startsWith('javascript:') &&
            target !== '_blank' &&
            !$(this).hasClass('no-loader')) {
            
            $('#page-loader').removeClass('hidden');
        }
    });

    // Handle AJAX events globally if needed
    $(document).ajaxStart(function() {
        // Option: we could show a smaller progress bar here
    });
    // Profile Dropdown Toggle
    $('#profile-trigger').on('click', function(e) {
        e.stopPropagation();
        $('#profile-dropdown').toggleClass('show');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#profile-dropdown').length && !$(e.target).closest('#profile-trigger').length) {
            $('#profile-dropdown').removeClass('show');
        }
    });
});
</script>

<footer class="app-footer">
    <div class="container">© <?= date('Y') ?> StayHub</div>
</footer>

<script src="/tenant/public/assets/js/app.js"></script>
<script src="/tenant/public/assets/js/toast.js"></script>
<script>
    window.APP_BASE_URL = '/tenant/?url=';
    window.CSRF_TOKEN = <?= json_encode($csrfToken) ?>;

    $(document).ready(function() {
        // Shortcut Manager Logic
        const defaultShortcuts = ['browse', 'bookings', 'favorites', 'support'];
        let userShortcuts = JSON.parse(localStorage.getItem('stayhub_shortcuts')) || defaultShortcuts;

        function renderShortcuts() {
            const container = $('#shortcuts-container');
            if (container.length === 0) return;

            let visibleCount = 0;
            $('.action-item[data-shortcut]').each(function() {
                const id = $(this).data('shortcut');
                if (userShortcuts.includes(id)) {
                    $(this).css('display', 'flex');
                    visibleCount++;
                } else {
                    $(this).css('display', 'none');
                }
            });

            // Show "Add" button if room for more (max 5)
            if (visibleCount < 5) {
                $('#add-shortcut-btn').css('display', 'flex');
            } else {
                $('#add-shortcut-btn').css('display', 'none');
            }
        }

        function updateModalState() {
            $('.toggle-item').removeClass('selected');
            userShortcuts.forEach(id => {
                $(`.toggle-item[data-id="${id}"]`).addClass('selected');
            });
        }

        // Initial Render
        renderShortcuts();

        // Modal Controls
        $('#manage-shortcuts-btn, #add-shortcut-btn').on('click', function() {
            updateModalState();
            $('#shortcutModal').addClass('active');
        });

        $('#close-modal').on('click', function() {
            $('#shortcutModal').removeClass('active');
        });

        $(document).on('click', '.toggle-item', function() {
            $(this).toggleClass('selected');
        });

        $('#save-shortcuts').on('click', function() {
            const newShortcuts = [];
            $('.toggle-item.selected').each(function() {
                newShortcuts.push($(this).data('id'));
            });

            userShortcuts = newShortcuts;
            localStorage.setItem('stayhub_shortcuts', JSON.stringify(userShortcuts));
            
            renderShortcuts();
            $('#shortcutModal').removeClass('active');
            
            Swal.fire({
                icon: 'success',
                title: 'Shortcuts updated successfully',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000
            });
        });

        // Close modal on outside click
        $(window).on('click', function(e) {
            if ($(e.target).is('#shortcutModal')) {
                $('#shortcutModal').removeClass('active');
            }
        });

        <?php if (isset($_SESSION['flash_success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: <?= json_encode($_SESSION['flash_success']) ?>,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: <?= json_encode($_SESSION['flash_error']) ?>,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    });
</script>
</body>
</html>

