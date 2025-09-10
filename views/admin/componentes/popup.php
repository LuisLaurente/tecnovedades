<?php
$popup = (new \Models\Popup())->obtener();
if ($popup && $popup['activo']) :
?>
<div id="popup-promocional" style="
    position: fixed;
    left: 50%;
    top: 30%;
    transform: translateX(-50%);
    background: #ffffff;
    padding: 25px 30px;
    border-radius: 12px;
    border: 1px solid #ccc;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    z-index: 9999;
    text-align: center;
    font-family: 'Segoe UI', sans-serif;
    max-width: 400px;
    width: 90%;
">
    <!-- Botón de cerrar -->
    <button class="cerrar-popup" style="
        position: absolute;
        top: 8px;
        right: 12px;
        background: transparent;
        border: none;
        font-size: 1.2rem;
        color: #999;
        cursor: pointer;
        font-weight: bold;
    " aria-label="Cerrar popup">×</button>

    <!-- Texto -->
    <p style="font-size: 1.1rem; color: #333; line-height: 1.4;">
        <?= nl2br(htmlspecialchars($popup['texto'])) ?>
    </p>

    <!-- Imagen -->
    <?php if (!empty($popup['imagen'])): ?>
        <img src="<?= url('images/popup/' . $popup['imagen']) ?>" 
             style="display: block; margin: 15px auto; max-width: 100%; height: auto; border-radius: 10px;">
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const popup = document.getElementById('popup-promocional');
        const cerrarBtn = popup.querySelector('.cerrar-popup');

        // Función para comprobar si mostrar el popup
        function mostrarPopupSiCorresponde() {
            const ahora = Date.now();
            const limite = localStorage.getItem('popupCerradoHasta');

            if (!limite || parseInt(limite) < ahora) {
                popup.style.display = 'block';
            } else {
                popup.style.display = 'none';
            }
        }

        // Ejecutar al cargar
        mostrarPopupSiCorresponde();

        // Asignar acción al botón de cerrar
        cerrarBtn.addEventListener('click', function () {
            popup.style.display = 'none';
            const siguienteAparicion = Date.now() + (6 * 60 * 60 * 1000); // ⏱️ 6 horas 6 * 60 * 60 * 1000
            localStorage.setItem('popupCerradoHasta', siguienteAparicion.toString());
        });

        // Verificar periódicamente si ya pasó el tiempo
        setInterval(mostrarPopupSiCorresponde, 1000); // ⏱️ revisa cada segundo
    });
</script>

<?php endif; ?>
