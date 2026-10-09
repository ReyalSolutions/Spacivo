<!-- GLOBAL TOAST STACK — rendered once for every admin page -->
<div id="toast-stack"></div>

  <script src="assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/sidebarmenu.js"></script>
  <script src="assets/js/app.min.js"></script>
  <script src="assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script>
    function toggleSubmenu(toggleEl, submenuId) {
      const submenu = document.getElementById(submenuId);
      const chevron = toggleEl.querySelector('.ti-chevron-down');
      if (!submenu) return;
      if (submenu.style.maxHeight && submenu.style.maxHeight !== '0px') {
        submenu.style.maxHeight = '0px';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
      } else {
        submenu.style.maxHeight = submenu.scrollHeight + 'px';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
      }
    }
  </script>

  <!-- Global Toast Notification Engine -->
  <script src="/tenant/public/assets/js/toast.js"></script>

