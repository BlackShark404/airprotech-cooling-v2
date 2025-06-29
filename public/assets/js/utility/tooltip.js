document.addEventListener('DOMContentLoaded', function() {
    // Add a class to disable transitions during initial load
    document.body.classList.add('no-transition');

    // Sidebar toggle functionality
    const sidebarToggler = document.getElementById('sidebarToggler');
    const toggleIcon = sidebarToggler.querySelector('i');

    // Check localStorage for saved state and apply it immediately
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
        toggleIcon.classList.remove('fa-chevron-left');
        toggleIcon.classList.add('fa-chevron-right');
    } else {
        document.body.classList.remove('sidebar-collapsed');
        toggleIcon.classList.remove('fa-chevron-right');
        toggleIcon.classList.add('fa-chevron-left');
    }

    // Re-enable transitions after initial state is applied
    setTimeout(function() {
        document.body.classList.remove('no-transition');
    }, 50);

    sidebarToggler.addEventListener('click', function() {
        document.body.classList.toggle('sidebar-collapsed');

        // Save state to localStorage
        localStorage.setItem('sidebarCollapsed', document.body.classList.contains('sidebar-collapsed'));

        // Change toggle icon
        if (document.body.classList.contains('sidebar-collapsed')) {
            toggleIcon.classList.remove('fa-chevron-left');
            toggleIcon.classList.add('fa-chevron-right');
        } else {
            toggleIcon.classList.remove('fa-chevron-right');
            toggleIcon.classList.add('fa-chevron-left');
        }
    });

    // Responsive sidebar toggle
    if (window.innerWidth < 768) {
        document.body.classList.remove('sidebar-collapsed');

        // For mobile, we also need to check if sidebar is expanded
        if (localStorage.getItem('sidebarExpanded') === 'true') {
            document.body.classList.add('sidebar-expanded');
        }

        sidebarToggler.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-expanded');
            // Save mobile expanded state separately
            localStorage.setItem('sidebarExpanded', document.body.classList.contains('sidebar-expanded'));
        });
    }

    // Optimize tooltip initialization
    var tooltipOptions = {
        delay: {
            show: 300,
            hide: 100
        },
        trigger: 'hover',
        boundary: 'window'
    };

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl, tooltipOptions);
    });
});