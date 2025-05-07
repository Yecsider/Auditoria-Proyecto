// navigation.js - Funcionalidad para el menú móvil
document.addEventListener('DOMContentLoaded', function() {
    // Toggle del menú móvil
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });
    }
    
    // Dropdowns en móvil
    const dropdowns = document.querySelectorAll('.dropdown > a');
    
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle('active');
                
                // Cerrar otros dropdowns abiertos
                dropdowns.forEach(function(otherDropdown) {
                    if (otherDropdown !== dropdown && otherDropdown.parentElement.classList.contains('active')) {
                        otherDropdown.parentElement.classList.remove('active');
                    }
                });
            }
        });
    });
    
    // Cerrar menú al hacer clic en un enlace
    const navItems = document.querySelectorAll('.nav-menu a');
    
    navItems.forEach(function(item) {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                navLinks.classList.remove('active');
            }
        });
    });
});