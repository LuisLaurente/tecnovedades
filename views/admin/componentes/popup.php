<?php
$popup = (new \Models\Popup())->obtener();
if ($popup && $popup['activo']) :
?>
<div id="popup-overlay" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 hidden modal-backdrop">
  <div id="popup-promocional" 
       class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-11/12 md:w-3/4 lg:w-1/2 p-8 text-center transform scale-90 opacity-0 transition-all duration-500 ease-out">
    
    <!-- Botón de cerrar -->
    <button class="cerrar-popup absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl font-bold" 
            aria-label="Cerrar popup">×</button>

    <!-- Texto -->
    <h2 class="text-3xl font-extrabold text-indigo-700 mb-4">🎉 ¡Promoción Especial!</h2>
    <p class="text-lg text-gray-700 leading-relaxed mb-6">
        <?= nl2br(htmlspecialchars($popup['texto'])) ?>
    </p>

    <!-- Imagen -->
    <?php if (!empty($popup['imagen'])): ?>
        <img src="<?= url('images/popup/' . $popup['imagen']) ?>" 
             class="mx-auto rounded-xl shadow-md max-h-[400px] object-contain mb-4">
    <?php endif; ?>

    <!-- Botón CTA -->
    <!-- <a href="<?= url('/') ?>" 
       class="inline-block mt-4 bg-indigo-600 text-white font-semibold px-6 py-3 rounded-lg shadow hover:bg-indigo-700 transition">
       🔥 Ver más
    </a> -->
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('popup-overlay');
    const popup = document.getElementById('popup-promocional');
    const cerrarBtn = popup.querySelector('.cerrar-popup');

    function mostrarPopupSiCorresponde() {
        const ahora = Date.now();
        const limite = localStorage.getItem('popupCerradoHasta');

        if (!limite || parseInt(limite) < ahora) {
            overlay.classList.remove("hidden");
            setTimeout(() => {
                popup.classList.remove("scale-90", "opacity-0");
                popup.classList.add("scale-100", "opacity-100");
            }, 50);
        }
    }

    cerrarBtn.addEventListener('click', function () {
        overlay.classList.add("hidden");
        const siguienteAparicion = Date.now() + (6 * 60 * 60 * 1000); 
        localStorage.setItem('popupCerradoHasta', siguienteAparicion.toString());
    });

    mostrarPopupSiCorresponde();
});
</script>

<?php endif; ?>
