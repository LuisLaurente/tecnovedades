<?php $base = '/TECNOVEDADES-MASTER/public/'; ?>

<h2>Listado de Etiquetas</h2>

<a href="<?= $base ?>etiqueta/crear">Nueva Etiqueta</a>

<ul>
<?php foreach ($etiquetas as $et): ?>
  <li>
    <?= htmlspecialchars($et['nombre']) ?> 
    <a href="<?= $base ?>etiqueta/editar/<?= $et['id'] ?>">[Editar]</a>
    <a href="<?= $base ?>etiqueta/eliminar/<?= $et['id'] ?>" onclick="return confirm('¿Eliminar esta etiqueta?')">[Eliminar]</a>
  </li>
<?php endforeach; ?>
</ul>
