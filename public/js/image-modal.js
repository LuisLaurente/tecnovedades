/**
 * Modal de Imagen - TecnoVedades
 * Funcionalidad para abrir imágenes en modal con soporte móvil
 */
(function() {
    'use strict';

    // Variables del modal
    const imageModal = document.getElementById('image-modal');
    const modalImage = document.getElementById('modal-image');
    const closeImageModal = document.getElementById('close-image-modal');
    const mainImage = document.getElementById('main-product-image');

    // Variables para gestos táctiles
    let touchStartY = 0;
    let touchStartX = 0;

    /**
     * Abre el modal con la imagen especificada
     * @param {string} imageSrc - URL de la imagen
     * @param {string} imageAlt - Texto alternativo de la imagen
     */
    function openImageModal(imageSrc, imageAlt) {
        if (!imageModal || !modalImage) return;
        
        modalImage.src = imageSrc;
        modalImage.alt = imageAlt || '';
        imageModal.style.display = 'flex';
        
        // Prevenir scroll del body y fijar posición
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        document.body.style.height = '100%';
        
        // Enfocar el modal para accesibilidad
        imageModal.focus();
    }

    /**
     * Cierra el modal de imagen
     */
    function closeModal() {
        if (!imageModal) return;
        
        imageModal.style.display = 'none';
        
        // Restaurar scroll del body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        document.body.style.height = '';
    }

    /**
     * Inicializa los event listeners del modal
     */
    function initModal() {
        // Abrir modal al hacer click en la imagen principal
        if (mainImage && imageModal && modalImage) {
            mainImage.addEventListener('click', function() {
                openImageModal(this.src, this.alt);
            });
        }

        // Cerrar con botón X
        if (closeImageModal) {
            closeImageModal.addEventListener('click', closeModal);
        }

        // Cerrar al hacer click en el overlay (fondo)
        if (imageModal) {
            imageModal.addEventListener('click', function(e) {
                if (e.target === imageModal) {
                    closeModal();
                }
            });
        }

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && imageModal && imageModal.style.display === 'flex') {
                closeModal();
            }
        });
    }

    /**
     * Inicializa los gestos táctiles para móviles
     */
    function initTouchGestures() {
        if (!imageModal) return;

        // Detectar swipe down para cerrar en móviles
        imageModal.addEventListener('touchstart', function(e) {
            touchStartY = e.touches[0].clientY;
            touchStartX = e.touches[0].clientX;
        }, { passive: true });

        imageModal.addEventListener('touchend', function(e) {
            if (!e.changedTouches || e.changedTouches.length === 0) return;
            
            const touchEndY = e.changedTouches[0].clientY;
            const touchEndX = e.changedTouches[0].clientX;
            const deltaY = touchEndY - touchStartY;
            const deltaX = touchEndX - touchStartX;
            
            // Si el swipe es hacia abajo y mayor a 100px, cerrar modal
            if (deltaY > 100 && Math.abs(deltaX) < 50) {
                closeModal();
            }
        }, { passive: true });

        // Mejorar el área de toque del botón cerrar en móviles
        if (closeImageModal) {
            closeImageModal.addEventListener('touchstart', function(e) {
                e.stopPropagation(); // Evitar conflictos con otros eventos táctiles
                this.style.transform = 'scale(0.95)';
            }, { passive: false });

            closeImageModal.addEventListener('touchend', function(e) {
                e.stopPropagation();
                this.style.transform = 'scale(1)';
                closeModal();
            }, { passive: false });
        }
    }

    /**
     * Inicializa toda la funcionalidad del modal
     */
    function init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initModal();
                initTouchGestures();
            });
        } else {
            initModal();
            initTouchGestures();
        }
    }

    // Inicializar
    init();

    // Exportar funciones para uso externo si es necesario
    window.ImageModal = {
        open: openImageModal,
        close: closeModal
    };

})();
