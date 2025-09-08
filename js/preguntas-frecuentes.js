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
//---------------------------FAQS--------------------------------------------
//-----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    const categoryButtons = document.querySelectorAll('.faq-category-btn');
    const faqItems = document.querySelectorAll('.faq-item');
    const faqGroups = document.querySelectorAll('.faq-group');

    // Función para mostrar el grupo de preguntas correcto
    function showCategory(category) {
        faqGroups.forEach(group => {
            group.classList.remove('active');
            if (group.id === category) {
                group.classList.add('active');
            }
        });
    }

    // Función para cerrar todos los acordeones
    function closeAllAccordions() {
        faqItems.forEach(item => {
            item.classList.remove('active');
        });
    }

    // Evento para los botones de categoría
    categoryButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Actualiza la clase 'active' en los botones
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            // Cierra todos los acordeones y muestra el grupo de preguntas de la categoría
            closeAllAccordions();
            const category = button.dataset.category;
            showCategory(category);
        });
    });

    // Evento para los ítems del acordeón
    faqItems.forEach(item => {
        const header = item.querySelector('.faq-header');
        header.addEventListener('click', () => {
            // Si el ítem ya está activo, lo cierra
            if (item.classList.contains('active')) {
                item.classList.remove('active');
            } else {
                // Si no, cierra todos los demás y abre este
                closeAllAccordions();
                item.classList.add('active');
            }
        });
    });

    // Cierra todos los acordeones al cargar la página
    closeAllAccordions();
});