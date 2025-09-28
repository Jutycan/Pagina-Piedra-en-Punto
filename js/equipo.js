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

//------------------------------------------------------------------------------
//---------------------- JS: Activación Historia/Misión/Visión -----------------
//------------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    // Selecciona el contenedor que tiene el contenido animado
    const historiaContainer = document.querySelector(".historia-mision-vision-container");
    
    if (historiaContainer) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                // Si el elemento entra en la vista y no ha sido animado
                if (entry.isIntersecting && !entry.target.classList.contains("visible")) {
                    // Añade la clase 'visible' para iniciar la animación CSS
                    entry.target.classList.add("visible");
                    // Deja de observar el elemento
                    observer.unobserve(entry.target); 
                }
            });
        }, { 
            // Se activa cuando el 20% del contenedor es visible
            threshold: 0.2 
        });

        // Comienza a observar la sección
        observer.observe(historiaContainer);
    }
});

//------------------------------------------------------------------------------
//---------------------- -----JS: Nuestro Valores -----------------------------
//------------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    // 1. Selecciona todos los bloques de valor
    const valorCards = document.querySelectorAll(".valor-card");
    
    // Verifica si la pantalla es de móvil (<= 768px)
    const isMobile = window.matchMedia("(max-width: 768px)").matches;

    // Solo ejecuta la lógica si hay tarjetas y si estamos en móvil
    if (valorCards.length > 0 && isMobile) {
        
        // Función para manejar la activación
        const handleCardActivation = (entries) => {
            entries.forEach(entry => {
                const card = entry.target;

                // Si la tarjeta está INTERSECTING (es decir, en el foco central)
                if (entry.isIntersecting) {
                    
                    // Desactivar todas las demás tarjetas
                    valorCards.forEach(c => {
                        if (c !== card) {
                            c.classList.remove("active");
                        }
                    });

                    // Activar la tarjeta actual que intersecta la zona central
                    card.classList.add("active");
                } 
            });
        };

        // Configura el Intersection Observer para la detección central
        // rootMargin: '-50% 0px -50% 0px' fuerza la activación solo cuando el elemento 
        // pasa por el centro de la pantalla.
        const observer = new IntersectionObserver(handleCardActivation, {
            rootMargin: '-50% 0px -50% 0px', 
            threshold: 0 
        });

        // Observa cada tarjeta
        valorCards.forEach(card => {
            observer.observe(card);
        });
        
        // Activar la primera tarjeta al cargar (si está visible en móvil)
        if(valorCards.length > 0) {
            valorCards[0].classList.add("active");
        }
    }
});