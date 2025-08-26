//------------------------------------------------------------------------------
//---------------------------HEADER--------------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.header nav');
    const submenuItems = document.querySelectorAll('.header nav ul li.submenu > a');

    // Abrir/cerrar menú principal
    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });

    // Manejar clics en los enlaces de submenú (solo en móvil)
    submenuItems.forEach(link => {
        link.addEventListener('click', function (e) {
            if (window.innerWidth <= 768) {
                // Evita la navegación para abrir el submenú
                e.preventDefault();

                // Cierra otros submenús abiertos
                document.querySelectorAll('.header nav ul li.submenu').forEach(item => {
                    if (item.querySelector('a') !== this) {
                        item.classList.remove('submenu-open');
                    }
                });

                // Abre o cierra el submenú actual
                this.parentElement.classList.toggle('submenu-open');
            }
        });
    });

    // Asegura que los enlaces dentro del submenú SÍ funcionen
    document.querySelectorAll('.header nav ul li ul a').forEach(link => {
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                navMenu.classList.remove('active');
                document.querySelectorAll('.header nav ul li.submenu').forEach(item => {
                    item.classList.remove('submenu-open');
                });
            }
        });
    });
});