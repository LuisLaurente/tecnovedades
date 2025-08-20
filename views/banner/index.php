<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>
<link rel="stylesheet" href="<?= url('css/bannerAdmi.css') ?>">
<body>
<?php include_once __DIR__ . '/../admin/includes/header.php'; ?>

<div class="container">
    <h1>Gestión de Banners</h1>
    <p><a href="<?= url('/banner/crear') ?>" class="btn btn-primary">Nuevo Banner</a></p>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <?php if (empty($banners)): ?>
        <p>No hay banners. Sube el primero.</p>
    <?php else: ?>
        <form method="POST" action="<?= url('banner/ordenar') ?>">
            <table class="admin-productos-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Imagen</th>
                        <th>Orden</th>
                        <th>Activo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="sortable">
                    <?php foreach ($banners as $i => $b): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td>
                                <img src="<?= url($uploadDirUrl . $b['nombre_imagen']) ?>" alt="banner" style="max-width:260px;max-height:120px;object-fit:cover;">
                                <input type="hidden" name="orden[]" value="<?= (int)$b['id'] ?>">
                            </td>
                            <td><?= (int)$b['orden'] ?></td>
                            <td><?= $b['activo'] ? '✅' : '❌' ?></td>
                            <td>
                                <a class="btn" href="<?= url('banner/toggle/' . $b['id']) ?>">Activar/Desactivar</a>
                                <a class="btn btn-danger" href="<?= url('banner/eliminar/' . $b['id']) ?>" onclick="return confirm('¿Eliminar banner?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top:12px;">
                <button class="btn" type="submit">Guardar nuevo orden</button>
            </p>
        </form>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
</body>
