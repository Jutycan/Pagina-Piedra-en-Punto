// Función para mostrar/ocultar dropdowns
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        // Ocultar todos los dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
        // Mostrar el dropdown correspondiente
        const dropdown = this.nextElementSibling;
        if (dropdown) {
            dropdown.style.display = 'block';
            // Ajustamos la posición si está cerca del borde
            const rect = dropdown.getBoundingClientRect();
            if (rect.right > window.innerWidth) {
                dropdown.style.right = '0';
                dropdown.style.left = 'auto';
            }
        }
    });
});

// Cerrar dropdowns al hacer click fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.nav-link')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});