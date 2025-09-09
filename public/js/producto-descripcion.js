/**
 * Descripción de Producto - TecnoVedades
 * JavaScript principal para la página de descripción del producto
 */
(function() {
    'use strict';

    /**
     * Inicializa la galería de imágenes
     */
    function initGallery() {
        const mainImage = document.getElementById('main-product-image');
        const thumbs = document.querySelectorAll('.thumbnail-images .thumb');
        
        if (mainImage && thumbs.length > 0) {
            thumbs.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    mainImage.src = this.dataset.src || this.src;
                    thumbs.forEach(t => t.classList.remove('activo'));
                    this.classList.add('activo');
                });
            });
        }
    }

    /**
     * Inicializa los controles de cantidad
     */
    function initQuantityControls() {
        const qtyInput = document.getElementById('qty-input');
        const qtyIncrease = document.getElementById('qty-increase');
        const qtyDecrease = document.getElementById('qty-decrease');
        const formCantidad = document.getElementById('form-cantidad');
        const stockLimit = window.productStock || null;

        function setQty(val) {
            val = Math.max(1, Math.floor(parseInt(val) || 1));
            if (stockLimit !== null) val = Math.min(val, stockLimit);
            if (qtyInput) qtyInput.value = val;
            if (formCantidad) formCantidad.value = val;
        }

        if (qtyIncrease) {
            qtyIncrease.addEventListener('click', () => {
                const currentValue = parseInt(qtyInput.value) || 1;
                setQty(currentValue + 1);
            });
        }

        if (qtyDecrease) {
            qtyDecrease.addEventListener('click', () => {
                const currentValue = parseInt(qtyInput.value) || 1;
                setQty(currentValue - 1);
            });
        }

        if (qtyInput) {
            qtyInput.addEventListener('change', () => setQty(qtyInput.value));
            // Inicializar el valor del formulario
            if (formCantidad) formCantidad.value = qtyInput.value || '1';
        }
    }

    /**
     * Inicializa los elementos colapsables
     */
    function initCollapsibles() {
        document.querySelectorAll('.collapsible-header').forEach(header => {
            header.addEventListener('click', () => {
                const section = header.closest('.collapsible-section');
                
                // Si es la de especificaciones y está parcialmente visible, no hacer nada aquí
                if (section.classList.contains('partially-visible')) {
                    return;
                }

                header.classList.toggle('active');
                const content = header.nextElementSibling;
                const arrow = header.querySelector('.arrow');

                if (header.classList.contains('active')) {
                    content.style.display = 'block';
                    if (arrow) arrow.innerHTML = '&#9650;';
                } else {
                    content.style.display = 'none';
                    if (arrow) arrow.innerHTML = '&#9660;';
                }
            });
        });
    }

    /**
     * Inicializa la expansión de especificaciones
     */
    function initSpecsExpansion() {
        const specsSection = document.querySelector('.partially-visible');
        if (!specsSection) return;

        const viewMoreButton = specsSection.querySelector('.view-more-specs');
        const viewLessButton = specsSection.querySelector('.view-less-specs');
        const header = specsSection.querySelector('.collapsible-header');
        const content = specsSection.querySelector('.collapsible-content');

        const expandSpecs = function(e) {
            if (specsSection.classList.contains('partially-visible')) {
                specsSection.classList.remove('partially-visible');
                if (!header.classList.contains('active')) {
                    header.classList.add('active');
                }
                if (content) content.style.display = 'block';
                const arrow = header.querySelector('.arrow');
                if (arrow) arrow.innerHTML = '&#9650;';
                if (e) e.stopPropagation();
            }
        };

        const collapseSpecs = function(e) {
            if (!specsSection.classList.contains('partially-visible')) {
                specsSection.classList.add('partially-visible');
                if (header.classList.contains('active')) {
                    header.classList.remove('active');
                }
                if (content) content.style.display = '';
                const arrow = header.querySelector('.arrow');
                if (arrow) arrow.innerHTML = '&#9660;';
                if (e) e.stopPropagation();
            }
        };

        if (viewMoreButton) {
            viewMoreButton.addEventListener('click', expandSpecs);
        }
        if (viewLessButton) {
            viewLessButton.addEventListener('click', collapseSpecs);
        }
        if (header) {
            header.addEventListener('click', expandSpecs, true);
        }
    }

    /**
     * Inicializa el scroll suave para enlaces internos
     */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    /**
     * Inicializa toda la funcionalidad de la página
     */
    function init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initGallery();
                initQuantityControls();
                initCollapsibles();
                initSpecsExpansion();
                initSmoothScroll();
            });
        } else {
            initGallery();
            initQuantityControls();
            initCollapsibles();
            initSpecsExpansion();
            initSmoothScroll();
        }
    }

    // Inicializar
    init();

})();
