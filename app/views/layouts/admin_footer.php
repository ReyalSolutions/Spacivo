            </div>
        </main>
    </div>

    <!-- Bootstrap 5 Bundle (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
    function confirmLogout(formId) {
        Swal.fire({
            title: 'Terminate Session?',
            text: 'Are you sure you want to log out of the management console?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Stay',
            reverseButtons: true,
            background: '#ffffff'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    $(document).ready(function() {
        const $wrapper = $('.admin-wrapper');
        const $sidebar = $('.admin-sidebar');
        const $toggle = $('#sidebarToggle');

        // Remove the no-transition class if it exists (placed by header to prevent flicker)
        setTimeout(() => {
            $('body').removeClass('no-transition');
        }, 100);

        // Sidebar Toggle Logic
        $toggle.on('click', function(e) {
            e.preventDefault();
            if ($(window).width() <= 768) {
                // Mobile: Toggle full visibility
                $sidebar.toggleClass('show');
                $('#sidebarOverlay').toggleClass('show');
            } else {
                // Desktop: Toggle collapsed state
                $wrapper.toggleClass('collapsed');
                const isCollapsed = $wrapper.hasClass('collapsed');
                localStorage.setItem('adminSidebarCollapsed', isCollapsed);
            }
        });

        // Close sidebar when clicking outside or on overlay
        $('#sidebarOverlay').on('click', function() {
            $sidebar.removeClass('show');
            $(this).removeClass('show');
        });

        $(document).on('click', function(e) {
            if ($(window).width() <= 768 && 
                !$sidebar.is(e.target) && 
                $sidebar.has(e.target).length === 0 && 
                !$toggle.is(e.target) && 
                $toggle.has(e.target).length === 0) {
                $sidebar.removeClass('show');
                $('#sidebarOverlay').removeClass('show');
            }
        });

        // Handle window resize
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                $sidebar.removeClass('show');
            }
        });

        // Global Manual Dropdown Init (Fix for multi-page consistency)
        const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    });
    </script>
    
    <script src="/tenant/public/assets/js/app.js"></script>
</body>
</html>
