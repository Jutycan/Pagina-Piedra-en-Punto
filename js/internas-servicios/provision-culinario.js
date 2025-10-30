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

// -------------------------------------- ----------------------------------------
//------------------------- Estilos del Banner ----------------------------------
// -------------------------------------- ------------------------------------ -----
document.addEventListener('DOMContentLoaded', () => {
    // Ejecutar solo si el ancho de la ventana es de un dispositivo móvil
    if (window.innerWidth <= 768) {
            
        /* ================================================= */
        /* 1. Ciclo de Animación del Banner Superior (Cada 5s) */
        /* ================================================= */
        const bannerSuperior = document.querySelector('.banner-superior');

        // Alterna la clase 'activo' que aplica el efecto de oscurecimiento y color de texto
        function toggleBannerSuperiorState() {
            bannerSuperior.classList.toggle('activo');
        }

        // Inicia el ciclo.
        // NOTA: Si quieres que el efecto se active inmediatamente, puedes llamar a la función aquí.
        // Si quieres que inicie después de 5s, el setInterval es suficiente.
        setInterval(toggleBannerSuperiorState, 5000);


        /* ================================================= */
        /* 2. Efecto de Revelado del Banner Inferior (Visibilidad) */
        /* ================================================= */
        const bannerInferior = document.querySelector('.banner-inferior');
            
        // 1. Inicializar en estado 'inicial' (Imagen visible, Texto oculto)
        bannerInferior.classList.add('inicial');

        // 2. Configurar el observador
        const observerOptions = {
            root: null, // El viewport es el root
            rootMargin: '0px',
            threshold: 0.1 // Se activa cuando el 10% del elemento es visible
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // El usuario está en el section: REVELAR (quitar imagen, mostrar texto)
                    entry.target.classList.remove('inicial');
                    entry.target.classList.add('revelado');
                } else {
                    // El usuario salió del section: OCULTAR (mostrar imagen, quitar texto)
                    entry.target.classList.remove('revelado');
                    entry.target.classList.add('inicial');
                }
            });
        }, observerOptions);

        // 3. Empezar a observar
        observer.observe(bannerInferior);
    }
});

// ----------------------------------------------------------------------------- 
//----------------------Estilos de Beneficios------------------------------------- 
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const benefitCards = document.querySelectorAll('.benefit-card');
    const isMobile = window.innerWidth <= 768;

    // Desactivamos el 'is-featured' para móvil
    if (isMobile) {
        benefitCards.forEach(card => {
            card.classList.remove('is-featured');
        });
    }

    if (isMobile) {
        const observerCallback = (entries) => {
            entries.forEach(entry => {
                const card = entry.target;
                
                // Si la tarjeta está entrando o ya está visible en el umbral del 50%
                if (entry.isIntersecting) {
                    // Desactivar todas las tarjetas primero
                    benefitCards.forEach(c => c.classList.remove('is-active-mobile'));
                    
                    // Activar la tarjeta que está en el centro
                    card.classList.add('is-active-mobile');
                }
            });
        };

        const observerOptions = {
            // Root Margin ajustado para que el "centro" de la tarjeta sea el centro del viewport
            rootMargin: '-40% 0px -40% 0px', // Ajustado a 40% para una mejor detección de centro
            threshold: 0.0 
        };

        const observer = new IntersectionObserver(observerCallback, observerOptions);

        benefitCards.forEach(card => {
            observer.observe(card);
        });

    } else {
        // Lógica para Escritorio: Manejo de la tarjeta "destacada" y el hover
        // Esto solo es para asegurar el comportamiento si se requiere JS para el featured
        
        benefitCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                // Si haces hover, desactiva temporalmente el featured si existe
                const featured = document.querySelector('.benefit-card.is-featured:not(:hover)');
                if (featured) {
                    featured.style.transform = 'translateY(0)';
                    featured.style.backgroundColor = 'var(--color-gris-card)';
                }
            });
            card.addEventListener('mouseleave', () => {
                // Al salir, asegura que el featured vuelva a su estado activo
                const featured = document.querySelector('.benefit-card.is-featured');
                if (featured) {
                    featured.style.transform = 'translateY(-10px)';
                    featured.style.backgroundColor = 'var(--color-verde-destacado)';
                }
            });
        });
    }

    // Nota: El evento de resize para recargar la página no es ideal en producción.
    // Lo he eliminado. Los estilos y JS ahora deberían adaptarse de forma dinámica.
});

