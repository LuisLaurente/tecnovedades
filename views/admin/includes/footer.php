<?php require_once __DIR__ . '/../componentes/popup.php'; ?>
<link rel="stylesheet" href="<?= url('css/footer.css') ?>">
<!-- Footer -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <!-- Información principal -->
            <div class="footer-header">
                <h5 class="footer-title">Tienda Tecnovedades</h5>
                <p class="footer-subtitle">Tu tienda online de confianza para productos tecnológicos y novedades</p>
            </div>

            <!-- Información de la tienda -->
            <div class="footer-info-grid">
                <div class="footer-info-item">
                    <p class="info-label">Servicio al Cliente</p>
                    <p class="info-value">+51 999 123 456</p>
                </div>
                <div class="footer-info-item">
                    <p class="info-label">Email</p>
                    <p class="info-value">info@tecnovedades.com</p>
                </div>
                <div class="footer-info-item">
                    <p class="info-label">Horario</p>
                    <p class="info-value">Lun - Sáb: 9:00 AM - 8:00 PM</p>
                </div>
            </div>

            <!-- Footer bottom -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p class="copyright">© 2025 Tecnovedades - Todos los derechos reservados</p>
                    <div class="footer-meta">
                        <span class="version">Versión 2.0</span>
                        <span id="footer-date" class="date"></span>
                    </div>
                </div>
                 <!-- Enlace al Libro de Reclamaciones -->
                <div class="footer-reclamo mt-3 text-center">
                    <a href="<?= url('reclamacion/formulario') ?>" style="color: #666; text-decoration: none; font-size: 14px;" onmouseover="this.style.color='#444'" onmouseout="this.style.color='#666'">
                        📋 Libro de Reclamaciones
                    </a>
                </div>
            </div>
        </div>
    </div>
    
</footer>

<!-- Script para el footer -->
<script>
    // Actualizar fecha en el footer
    function updateFooterDate() {
        const now = new Date();
        const dateString = now.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const footerDateElement = document.getElementById('footer-date');
        if (footerDateElement) {
            footerDateElement.textContent = dateString;
        }
    }

    // Inicializar fecha del footer
    updateFooterDate();
</script>