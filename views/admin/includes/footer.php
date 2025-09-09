<?php require_once __DIR__ . '/../componentes/popup.php'; ?>
<link rel="stylesheet" href="<?= url('css/footer.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> <!-- Font Awesome para iconos -->

<!-- Footer -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <!-- Sección Superior: Título e Información de Contacto -->
            <div class="footer-top-section">
                <div class="footer-header">
                    <h5 class="footer-title">BYTEBOX</h5>
                    <p class="footer-subtitle">Tu tienda online de confianza para productos tecnológicos y novedades</p>
                </div>
                <div class="footer-info-grid">
                    <div class="footer-info-item">
                        <p class="info-label">Servicio al Cliente</p>
                        <p class="info-value">+51 999 123 456</p>
                    </div>
                    <div class="footer-info-item">
                        <p class="info-label">Email</p>
                        <p class="info-value">info@bytebox.com</p>
                    </div>
                    <div class="footer-info-item">
                        <p class="info-label">Horario</p>
                        <p class="info-value">Lun - Sáb: 9:00 AM - 8:00 PM</p>
                    </div>
                </div>
            </div>

            <!-- Sección Media: Columnas de Enlaces -->
            <div class="footer-links-grid">
                <div class="footer-links-column">
                    <h6 class="links-title">Productos</h6>
                    <ul class="links-list">
                        <li><a href="#">- Ofertas</a></li>
                        <li><a href="#">- Novedades</a></li>
                        <li><a href="#">- Más vendidos</a></li>
                        <li><a href="#">- Destacados</a></li>
                    </ul>
                </div>
                <div class="footer-links-column">
                    <h6 class="links-title">Nosotros</h6>
                    <ul class="links-list">
                        <li><a href="#">- Contáctanos</a></li>
                        <li><a href="#" id="open-terms-footer">- Términos y condiciones</a></li>
                    </ul>
                </div>
                <div class="footer-links-column">
                    <h6 class="links-title">Tu cuenta</h6>
                    <ul class="links-list">
                        <li><a href="#">- Historial</a></li>
                        <li><a href="#">- Cupones</a></li>
                    </ul>
                </div>
            </div>

            <!-- Sección Inferior: Copyright y Redes Sociales -->
            <div class="footer-bottom">
                <p class="copyright">© 2025 Bytebox - Todos los derechos reservados</p>
                <div class="footer-meta">
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                    <span class="version">Versión 2.0</span>
                    <span id="footer-date" class="date"></span>
                </div>
            </div>
        </div>

        <!-- Libro de Reclamaciones (fuera del padding principal para ocupar todo el ancho ) -->
        <div class="footer-reclamo-wrapper">
            <a href="<?= url('reclamacion/formulario') ?>" class="footer-reclamo-link">
                <i class="fas fa-book"></i> Libro de Reclamaciones
            </a>
        </div>
    </div>
</footer>

<!-- Modal de términos y condiciones -->
<div id="terms-modal-footer" class="modal-overlay" style="display: none;">
    <div class="modal-content-footer">
        <!-- Header del modal -->
        <div class="modal-header-footer">
            <div class="modal-title-section">
                <h2 class="modal-title">📋 Términos y Condiciones</h2>
                <p class="modal-subtitle">Términos de uso y políticas de nuestra tienda</p>
            </div>
            <button type="button" id="close-terms-modal-footer" class="modal-close-btn">
                &times;
            </button>
        </div>
        
        <!-- Contenido del modal -->
        <div class="modal-body-footer">
            <div class="terms-content">
                <div class="terms-section">
                    <h3 class="terms-title">
                        <span class="terms-number">1</span>
                        Información General
                    </h3>
                    <p class="terms-text">
                        Bienvenido a <strong>ByteBox</strong>. Al utilizar nuestro sitio web y realizar compras, 
                        usted acepta estar sujeto a los siguientes términos y condiciones de uso y venta.
                    </p>
                </div>
                
                <div class="terms-section">
                    <h3 class="terms-title">
                        <span class="terms-number">2</span>
                        Productos y Precios
                    </h3>
                    <div class="terms-text">
                        <ul class="terms-list">
                            <li>Todos los precios están expresados en soles peruanos (S/) e incluyen IGV</li>
                            <li>Los precios están sujetos a cambios sin previo aviso</li>
                            <li>Los productos están sujetos a disponibilidad de stock</li>
                            <li>Nos reservamos el derecho de limitar las cantidades de compra por cliente</li>
                            <li>Las imágenes de productos son referenciales y pueden variar ligeramente</li>
                        </ul>
                    </div>
                </div>
                
                <div class="terms-section">
                    <h3 class="terms-title">
                        <span class="terms-number">3</span>
                        Política de Envío
                    </h3>
                    <div class="terms-text">
                        <ul class="terms-list">
                            <li><strong>Envío gratuito</strong> a todo el Perú en compras mayores a S/ 100</li>
                            <li>Tiempo de entrega: 2-5 días hábiles en Lima, 3-7 días en provincias</li>
                            <li>Horarios de entrega: Lunes a Viernes de 9:00 AM a 6:00 PM</li>
                            <li>El cliente debe estar presente en el momento de la entrega</li>
                            <li>Realizamos hasta 2 intentos de entrega gratuitos</li>
                        </ul>
                    </div>
                </div>
                
                <div class="terms-section">
                    <h3 class="terms-title">
                        <span class="terms-number">4</span>
                        Política de Devoluciones y Cambios
                    </h3>
                    <div class="terms-text">
                        <ul class="terms-list">
                            <li>Plazo para devoluciones: <strong>30 días</strong> calendarios desde la recepción</li>
                            <li>Los productos deben estar en perfecto estado, sin uso y con embalaje original</li>
                            <li>No se aceptan devoluciones de productos personalizados o de uso íntimo</li>
                            <li>Los gastos de envío para devoluciones corren por cuenta del cliente</li>
                            <li>El reembolso se realizará por el mismo medio de pago utilizado</li>
                        </ul>
                    </div>
                </div>
                
                <div class="terms-section">
                    <h3 class="terms-title">
                        <span class="terms-number">5</span>
                        Protección de Datos Personales
                    </h3>
                    <div class="terms-text">
                        <ul class="terms-list">
                            <li>Respetamos su privacidad conforme a la Ley de Protección de Datos Personales</li>
                            <li>Sus datos serán utilizados únicamente para procesar pedidos y mejorar servicios</li>
                            <li>No compartimos información personal con terceros sin su consentimiento</li>
                            <li>Puede solicitar la eliminación de sus datos contactándonos</li>
                            <li>Utilizamos medidas de seguridad para proteger su información</li>
                        </ul>
                    </div>
                </div>
                
                <div class="terms-section">
                    <h3 class="terms-title">
                        <span class="terms-number">6</span>
                        Garantías y Responsabilidad
                    </h3>
                    <div class="terms-text">
                        <ul class="terms-list">
                            <li>Ofrecemos garantía del fabricante en todos nuestros productos</li>
                            <li>No nos hacemos responsables por daños causados por mal uso del producto</li>
                            <li>Nuestra responsabilidad se limita al valor del producto adquirido</li>
                            <li>En caso de productos defectuosos, procederemos al cambio o reembolso</li>
                            <li>La garantía no cubre daños por agua, golpes o manipulación incorrecta</li>
                        </ul>
                    </div>
                </div>
                
                <div class="terms-section">
                    <h3 class="terms-title">
                        <span class="terms-number">7</span>
                        Formas de Pago
                    </h3>
                    <div class="terms-text">
                        <ul class="terms-list">
                            <li>Aceptamos tarjetas de crédito y débito (Visa, Mastercard)</li>
                            <li>Transferencias bancarias y pagos en efectivo contra entrega</li>
                            <li>Todos los pagos son procesados de forma segura</li>
                            <li>En caso de pago contra entrega, se acepta cambio hasta S/ 50</li>
                        </ul>
                    </div>
                </div>
                
                <div class="terms-section">
                    <h3 class="terms-title">
                        <span class="terms-number">8</span>
                        Contacto y Soporte
                    </h3>
                    <div class="terms-text">
                        <p>Para consultas, reclamos o soporte técnico:</p>
                        <ul class="terms-list">
                            <li><strong>Email:</strong> info@bytebox.com</li>
                            <li><strong>Teléfono:</strong> +51 999 123 456</li>
                            <li><strong>Horario de atención:</strong> Lunes a Sábado 9:00 AM - 8:00 PM</li>
                            <li><strong>Libro de reclamaciones:</strong> Disponible en nuestra tienda online</li>
                        </ul>
                    </div>
                </div>
                
                <div class="terms-section">
                    <h3 class="terms-title">
                        <span class="terms-number">9</span>
                        Modificaciones de Términos
                    </h3>
                    <p class="terms-text">
                        Nos reservamos el derecho de modificar estos términos y condiciones en cualquier momento. 
                        Los cambios entrarán en vigor inmediatamente después de su publicación en el sitio web. 
                        Le recomendamos revisar periódicamente esta página.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Footer del modal -->
        <div class="modal-footer-terms">
            <p class="modal-footer-text">
                <i class="fas fa-info-circle"></i>
                Última actualización: Septiembre 2025
            </p>
            <button type="button" id="close-terms-btn-footer" class="btn-close-terms">
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- Script para el footer (sin cambios) -->
<script>
    function updateFooterDate() {
        const now = new Date();
        const dateString = now.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
        const footerDateElement = document.getElementById('footer-date');
        if (footerDateElement) {
            footerDateElement.textContent = dateString;
        }
    }
    updateFooterDate();

    // Modal de términos y condiciones
    document.addEventListener('DOMContentLoaded', function() {
        const termsModal = document.getElementById('terms-modal-footer');
        const openTermsLink = document.getElementById('open-terms-footer');
        const closeTermsModal = document.getElementById('close-terms-modal-footer');
        const closeTermsBtn = document.getElementById('close-terms-btn-footer');

        // Abrir modal
        if (openTermsLink) {
            openTermsLink.addEventListener('click', function(e) {
                e.preventDefault();
                termsModal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Prevenir scroll del body
            });
        }

        // Función para cerrar modal
        function closeModal() {
            termsModal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Restaurar scroll del body
        }

        // Cerrar modal con X
        if (closeTermsModal) {
            closeTermsModal.addEventListener('click', closeModal);
        }
        
        // Cerrar modal con botón
        if (closeTermsBtn) {
            closeTermsBtn.addEventListener('click', closeModal);
        }

        // Cerrar modal al hacer clic fuera
        if (termsModal) {
            termsModal.addEventListener('click', function(e) {
                if (e.target === termsModal) {
                    closeModal();
                }
            });
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && termsModal.style.display === 'flex') {
                closeModal();
            }
        });
    });
</script>

<!-- Estilos para el modal de términos -->
<style>
    /* Importar fuentes si no están disponibles */
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(27, 27, 27, 0.8);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeInModal 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(5px);
    }

    .modal-content-footer {
        background: #ffffff;
        border-radius: 20px;
        width: 100%;
        max-width: 900px;
        max-height: 90vh;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        animation: slideInModal 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .modal-content-footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #2ac1db, #363993);
        animation: shimmerModal 3s ease-in-out infinite;
    }

    .modal-header-footer {
        background: linear-gradient(135deg, #1b1b1b 0%, #2d2d2d 50%, #1b1b1b 100%);
        color: #ffffff;
        padding: 30px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        position: relative;
        overflow: hidden;
    }

    .modal-header-footer::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(ellipse at top, rgba(42, 193, 219, 0.05) 0%, transparent 70%);
        pointer-events: none;
    }

    .modal-title-section {
        flex: 1;
        position: relative;
        z-index: 1;
    }

    .modal-title {
        font-family: 'Orbitron', sans-serif;
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .modal-subtitle {
        font-family: 'Outfit', sans-serif;
        font-size: 1rem;
        opacity: 0.9;
        margin: 0;
        font-weight: 300;
        color: #2ac1db;
    }

    .modal-close-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(42, 193, 219, 0.3);
        color: #ffffff;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
        padding: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 1;
    }

    .modal-close-btn:hover {
        background: rgba(42, 193, 219, 0.2);
        border-color: #2ac1db;
        transform: scale(1.05);
    }

    .modal-body-footer {
        flex: 1;
        overflow-y: auto;
        padding: 0;
        max-height: calc(90vh - 220px);
        background: #ffffff;
    }

    .terms-content {
        padding: 40px;
        font-family: 'Outfit', sans-serif;
    }

    .terms-section {
        margin-bottom: 35px;
        padding-bottom: 25px;
        border-bottom: 2px solid #f0f0f0;
        position: relative;
    }

    .terms-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .terms-title {
        font-family: 'Orbitron', sans-serif;
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 20px;
        color: #1b1b1b;
        display: flex;
        align-items: center;
        gap: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .terms-number {
        background: linear-gradient(135deg, #2ac1db, #363993);
        color: #ffffff;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        font-weight: bold;
        flex-shrink: 0;
        box-shadow: 0 4px 15px rgba(42, 193, 219, 0.3);
        position: relative;
    }

    .terms-number::after {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 14px;
        background: linear-gradient(135deg, #2ac1db, #363993);
        z-index: -1;
        opacity: 0.3;
    }

    .terms-text {
        color: #4a4d50;
        line-height: 1.8;
        font-size: 1rem;
        text-align: justify;
        margin: 0;
        font-weight: 400;
    }

    .terms-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .terms-list li {
        padding: 12px 0;
        padding-left: 30px;
        position: relative;
        color: #4a4d50;
        line-height: 1.7;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .terms-list li:hover {
        color: #1b1b1b;
        padding-left: 35px;
    }

    .terms-list li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #2ac1db;
        font-weight: bold;
        font-size: 1.1rem;
        top: 12px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(42, 193, 219, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    .modal-footer-terms {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 25px 40px;
        border-top: 2px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        position: relative;
    }

    .modal-footer-terms::before {
        content: '';
        position: absolute;
        top: 0;
        left: 20%;
        right: 20%;
        height: 1px;
        background: linear-gradient(90deg, transparent, #2ac1db, transparent);
    }

    .modal-footer-text {
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        font-family: 'Outfit', sans-serif;
        font-weight: 500;
    }

    .modal-footer-text i {
        color: #2ac1db;
        font-size: 1rem;
    }

    .btn-close-terms {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: #ffffff;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 600;
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        position: relative;
        overflow: hidden;
    }

    .btn-close-terms::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-close-terms:hover {
        background: linear-gradient(135deg, #495057, #343a40);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }

    .btn-close-terms:hover::before {
        left: 100%;
    }

    /* Animaciones mejoradas */
    @keyframes fadeInModal {
        from { 
            opacity: 0;
        }
        to { 
            opacity: 1;
        }
    }

    @keyframes slideInModal {
        from { 
            transform: translateY(-60px) scale(0.95);
            opacity: 0;
        }
        to { 
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    @keyframes shimmerModal {
        0%, 100% { 
            opacity: 1; 
            transform: scaleX(1); 
        }
        50% { 
            opacity: 0.8; 
            transform: scaleX(0.98); 
        }
    }

    /* Scrollbar personalizada */
    .modal-body-footer::-webkit-scrollbar {
        width: 8px;
    }

    .modal-body-footer::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .modal-body-footer::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #2ac1db, #363993);
        border-radius: 4px;
    }

    .modal-body-footer::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #1fa8c1, #2d2d75);
    }

    /* Responsive mejorado */
    @media (max-width: 768px) {
        .modal-overlay {
            padding: 15px;
        }

        .modal-content-footer {
            max-height: 95vh;
            border-radius: 15px;
        }

        .modal-header-footer {
            padding: 25px 20px;
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }

        .modal-title {
            font-size: 1.6rem;
            justify-content: center;
        }

        .modal-subtitle {
            text-align: center;
        }

        .terms-content {
            padding: 25px 20px;
        }

        .terms-title {
            font-size: 1.2rem;
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .terms-number {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }

        .modal-footer-terms {
            padding: 20px;
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .btn-close-terms {
            width: 100%;
            padding: 15px;
        }

        .terms-list li {
            padding-left: 25px;
        }

        .terms-list li:hover {
            padding-left: 28px;
        }
    }

    @media (max-width: 480px) {
        .modal-title {
            font-size: 1.4rem;
        }

        .terms-content {
            padding: 20px 15px;
        }

        .modal-header-footer {
            padding: 20px 15px;
        }

        .modal-footer-terms {
            padding: 15px;
        }
    }
</style>
