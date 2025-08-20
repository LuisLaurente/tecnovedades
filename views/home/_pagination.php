<?php
// views/home/_pagination.php
// Espera: $paginaActual (int), $totalPaginas (int), $currentQuery (array)
if (!isset($paginaActual)) $paginaActual = 1;
if (!isset($totalPaginas)) $totalPaginas = 1;
if (!isset($currentQuery)) $currentQuery = [];
?>

<?php if ($totalPaginas > 1): ?>
<nav class="pagination" aria-label="Paginación de productos">
    <div class="pagination-inner">
        <?php
        // Link anterior
        if ($paginaActual > 1) {
            $qs = $currentQuery;
            $qs['pagina'] = $paginaActual - 1;
            $hrefPrev = url('home/index') . '?' . http_build_query($qs);
            echo "<a href=\"{$hrefPrev}\" class=\"page-link prev\" data-page=\"" . ($paginaActual - 1) . "\">Anterior</a>";
        }

        // Números simples (puedes mejorar para mostrar ventana)
        for ($i = 1; $i <= $totalPaginas; $i++) {
            $qs = $currentQuery;
            $qs['pagina'] = $i;
            $href = url('home/index') . '?' . http_build_query($qs);
            $class = ($i == $paginaActual) ? 'page-link active' : 'page-link';
            echo "<a href=\"{$href}\" class=\"{$class}\" data-page=\"{$i}\">{$i}</a> ";
        }

        // Link siguiente
        if ($paginaActual < $totalPaginas) {
            $qs = $currentQuery;
            $qs['pagina'] = $paginaActual + 1;
            $hrefNext = url('home/index') . '?' . http_build_query($qs);
            echo "<a href=\"{$hrefNext}\" class=\"page-link next\" data-page=\"" . ($paginaActual + 1) . "\">Siguiente</a>";
        }
        ?>
    </div>
</nav>
<?php endif; ?>
