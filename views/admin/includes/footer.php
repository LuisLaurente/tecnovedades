<!-- Footer simplificado -->
<footer class="bg-white border-t border-gray-200 mt-10">
    <div class="py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Información principal -->
            <div class="text-center mb-6">
                <h5 class="text-lg font-semibold text-gray-800 mb-1">Tienda Tecnovedades</h5>
                <p class="text-gray-600 text-sm">Tu tienda online de confianza para productos tecnológicos y novedades</p>
            </div>

            <!-- Información de la tienda -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-800">Servicio al Cliente</p>
                    <p class="text-sm text-gray-600">+51 999 123 456</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-800">Email</p>
                    <p class="text-sm text-gray-600">info@tecnovedades.com</p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-800">Horario</p>
                    <p class="text-sm text-gray-600">Lun - Sáb: 9:00 AM - 8:00 PM</p>
                </div>
            </div>

            <!-- Footer bottom -->
            <div class="border-t border-gray-200 pt-4">
                <div class="flex flex-col md:flex-row justify-between items-center text-center">
                    <p class="text-sm text-gray-600 mb-2 md:mb-0">© 2025 Tecnovedades - Todos los derechos reservados</p>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <span>Versión 2.0</span>
                        <span id="footer-date"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Script simplificado para el footer -->
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