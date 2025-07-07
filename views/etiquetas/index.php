<h2>Listado de Etiquetas</h2>
<?php $base = '/TECNOVEDADES-MASTER/'; ?>
<a href="<?= $base ?>index.php?url=etiqueta/crear">+ Nueva Etiqueta</a>
<ul>
<?php foreach ($etiquetas as $et): ?>
  <li>
    <?= htmlspecialchars($et['nombre']) ?> 
    <a href="/TECNOVEDADES-MASTER/index.php?url=etiqueta/editar/<?= $et['id'] ?>">[Editar]</a>
    <a href="/TECNOVEDADES-MASTER/index.php?url=etiqueta/eliminar/<?= $et['id'] ?>" onclick="return confirm('¿Eliminar esta etiqueta?')">[Eliminar]</a>
  </li>
<?php endforeach; ?>
</ul>