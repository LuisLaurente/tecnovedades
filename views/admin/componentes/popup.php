<?php
$popup = (new \Models\Popup())->obtener();
if ($popup && $popup['activo']) :
?>
<div id="popup-promocional" style="
    position: fixed;
    top: 10%;
    left: 50%;
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
    <button onclick="document.getElementById('popup-promocional').style.display='none'" style="
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
<?php endif; ?>
