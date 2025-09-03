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
                        <li><a href="#">- Términos y condiciones</a></li>
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
</script>
