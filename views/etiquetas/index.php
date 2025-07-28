<h2>Listado de Etiquetas</h2>

<a href="<?= url('etiqueta/crear') ?>">Nueva Etiqueta</a>

<ul>
<?php foreach ($etiquetas as $et): ?>
  <li>
    <?= htmlspecialchars($et['nombre']) ?> 
    <a href="<?= url('etiqueta/editar/' . $et['id']) ?>">[Editar]</a>
    <a href="<?= url('etiqueta/eliminar/' . $et['id']) ?>" onclick="return confirm('¿Eliminar esta etiqueta?')">[Eliminar]</a>
  </li>
<?php endforeach; ?>
</ul>
