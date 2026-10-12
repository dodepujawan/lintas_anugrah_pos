// Active menu management
$(document).ready(function() {
    const $menuLinks = $('.sneat-sidebar-menu .menu-link');

    $menuLinks.on('click', function(e) {
        const $this = $(this);

        // Skip menu toggle links
        if ($this.hasClass('menu-toggle') || $this.attr('data-bs-toggle')) return;

        e.preventDefault();

        // Remove active from all, add to clicked
        $menuLinks.removeClass('active');
        $this.addClass('active');

        // Activate parent if sub-menu item
        if ($this.closest('.sub-menu').length) {
            $this.closest('.sub-menu').prev('.menu-link').addClass('active');
        }

        // Close sidebar on mobile
        if (window.innerWidth < 992) {
            bootstrap.Offcanvas.getInstance($('#sneatSidebar')[0])?.hide();
        }
    });

    // Handle sub-menu toggle states
    $('.sub-menu').on('show.bs.collapse', function() {
        $(this).prev('.menu-link').addClass('active');
    }).on('hide.bs.collapse', function() {
        if (!$(this).find('.menu-link.active').length) {
            $(this).prev('.menu-link').removeClass('active');
        }
    });
});

// MODE VANILLA JS
// // Auto close sub-menus when closing sidebar
// document.getElementById('sneatSidebar').addEventListener('hidden.bs.offcanvas', function () {
//     // Collapse all sub-menus when sidebar closes
//     const subMenus = document.querySelectorAll('.sub-menu.show');
//     subMenus.forEach(menu => {
//         menu.classList.remove('show');
//     });
// });

// // Active menu management
// document.addEventListener('DOMContentLoaded', function() {
//     const menuLinks = document.querySelectorAll('.sneat-sidebar-menu .menu-link');

//     // Function to remove active class from all menu items
//     function removeActiveClasses() {
//         menuLinks.forEach(link => {
//             link.classList.remove('active');
//         });
//     }

//     // Function to set active menu
//     function setActiveMenu(clickedLink) {
//         removeActiveClasses();
//         clickedLink.classList.add('active');

//         // Jika yang diklik adalah sub-menu item, set parent menu juga sebagai active
//         if (clickedLink.closest('.sub-menu')) {
//             const parentMenu = clickedLink.closest('.sub-menu').previousElementSibling;
//             if (parentMenu && parentMenu.classList.contains('menu-link')) {
//                 parentMenu.classList.add('active');
//             }
//         }
//     }

//     // Add click event to all menu links
//     menuLinks.forEach(link => {
//         link.addEventListener('click', function(e) {
//             // Skip jika menu toggle (yang punya sub-menu)
//             if (this.classList.contains('menu-toggle') || this.hasAttribute('data-bs-toggle')) {
//                 return; // Biarkan Bootstrap handle collapse
//             }

//             e.preventDefault();
//             setActiveMenu(this);

//             // Optional: Close sidebar on mobile after click
//             if (window.innerWidth < 992) {
//                 const sidebar = document.getElementById('sneatSidebar');
//                 const bsOffcanvas = bootstrap.Offcanvas.getInstance(sidebar);
//                 if (bsOffcanvas) {
//                     bsOffcanvas.hide();
//                 }
//             }
//         });
//     });

//     // Handle Bootstrap collapse events untuk menu toggle
//     const collapseElements = document.querySelectorAll('.sub-menu');
//     collapseElements.forEach(collapse => {
//         collapse.addEventListener('show.bs.collapse', function() {
//             const trigger = this.previousElementSibling;
//             if (trigger && trigger.classList.contains('menu-link')) {
//                 trigger.classList.add('active');
//             }
//         });

//         collapse.addEventListener('hide.bs.collapse', function() {
//             const trigger = this.previousElementSibling;
//             if (trigger && trigger.classList.contains('menu-link')) {
//                 // Jangan remove active class jika ada sub-menu item yang active
//                 const hasActiveSubItem = this.querySelector('.menu-link.active');
//                 if (!hasActiveSubItem) {
//                     trigger.classList.remove('active');
//                 }
//             }
//         });
//     });
// });

// // Auto close sub-menus when closing sidebar
// document.getElementById('sneatSidebar').addEventListener('hidden.bs.offcanvas', function () {
//     // Collapse all sub-menus when sidebar closes
//     const subMenus = document.querySelectorAll('.sub-menu.show');
//     subMenus.forEach(menu => {
//         menu.classList.remove('show');
//     });
// });
