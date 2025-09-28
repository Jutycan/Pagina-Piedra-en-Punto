//------------------------------------------------------------------------------
//---------------------------HEADER--------------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.header nav');
    
    // Abrir/cerrar menú principal en móvil
    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });

    // En móviles, todos los enlaces del menú principal navegan directamente
    document.querySelectorAll('.header nav ul li a').forEach(link => {
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                // Cierra el menú móvil al hacer clic en cualquier enlace
                navMenu.classList.remove('active');
            }
        });
    });
});

//------------------------------------------------------------------------------
//-----------------------------Activación Quiénes Somos-------------------------
//-----------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    // 1. Selecciona el contenedor que tiene el contenido animado en móvil.
    const quienesSomosContent = document.querySelector(".quienes-somos-content");
    
    if (quienesSomosContent) {
        // 2. Define el Intersection Observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                // Comprueba si la sección está entrando en la vista y si aún no ha sido animada
                if (entry.isIntersecting && !entry.target.classList.contains("visible")) {
                    // 3. Añade la clase 'visible' para iniciar la animación CSS
                    entry.target.classList.add("visible");
                    
                    // Opcional: Deja de observar el elemento una vez que la animación se ha disparado
                    // Esto evita que se recalcule si el usuario se desplaza fuera y vuelve a entrar.
                    observer.unobserve(entry.target); 
                }
            });
        }, { 
            // 4. Threshold: Determina qué porcentaje del elemento debe estar visible 
            // para disparar el evento (usaremos 20%)
            threshold: 0.2 
        });

        // 5. Comienza a observar la sección
        observer.observe(quienesSomosContent);
    }
});