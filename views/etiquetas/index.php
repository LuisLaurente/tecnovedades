<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

<body>
    <div class="flex h-screen">
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
        <div class="flex-1 ml-64 flex flex-col min-h-screen">
            <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
                <div class="sticky top-0 z-40">
                    <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
                </div>
                <div class="flex-1 p-6 bg-gray-50 overflow-y-auto">
                    <div class="max-w-6xl mx-auto">
                      <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-2xl font-bold text-gray-800">Listado de Etiquetas</h2>
                                <button id="openCrearModal" 
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 transition cursor-pointer">
                                    + Nueva Etiqueta
                                </button>

                            </div>

                            <div class="bg-white shadow rounded-lg overflow-hidden">
                                <table class="min-w-full border-collapse">
                                    <thead class="bg-gray-100 text-gray-700 text-sm uppercase font-semibold">
                                        <tr>
                                            <th class="px-6 py-3 text-left">ID</th>
                                            <th class="px-6 py-3 text-left">Nombre</th>
                                            <th class="px-6 py-3 text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php if (!empty($etiquetas)): ?>
                                            <?php foreach ($etiquetas as $et): ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-3 text-gray-600"><?= $et['id'] ?></td>
                                                    <td class="px-6 py-3 text-gray-800"><?= htmlspecialchars($et['nombre']) ?></td>
                                                    <td class="px-6 py-3 text-center space-x-2">
                                                        <button 
                                                            class="editarBtn px-3 py-1 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600 transition cursor-pointer"
                                                            data-id="<?= $et['id'] ?>" data-nombre="<?= htmlspecialchars($et['nombre']) ?>">
                                                            Editar
                                                        </button>
                                                        <a href="<?= url('etiqueta/eliminar/' . $et['id']) ?>"
                                                        onclick="return confirm('¿Eliminar esta etiqueta?')"
                                                        class="px-3 py-1 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition">
                                                            Eliminar
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                                    No hay etiquetas registradas.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="mt-4">
                    <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
                </div>
            </main>
        </div>
    </div>
    <!-- Modal Crear -->
<div id="crearModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 modal-backdrop">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="closeCrearModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">&times;</button>
    <h2 class="text-xl font-semibold mb-4">Crear Etiqueta</h2>
    <form method="POST" action="<?= url('etiqueta/crear') ?>">
      <input type="text" name="nombre" 
        value="<?= htmlspecialchars($nombre ?? '') ?>" 
        placeholder="Nombre de la etiqueta"
        class="w-full border border-gray-300 rounded-md p-2 mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition cursor-pointer">Guardar</button>
    </form>
    <?php if (!empty($errores)): ?>
      <ul class="mt-4 text-red-600 list-disc list-inside">
        <?php foreach ($errores as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Editar -->
<div id="editarModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 modal-backdrop">
  <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
    <button id="closeEditarModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">&times;</button>
    <h2 class="text-xl font-semibold mb-4">Editar Etiqueta</h2>
    <form method="POST" id="editarForm" action="">
      <label for="nombreEditar" class="block mb-1 font-medium">Nombre:</label>
      <input id="nombreEditar" type="text" name="nombre" 
        value="" 
        class="w-full border border-gray-300 rounded-md p-2 mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition cursor-pointer">Actualizar</button>
    </form>
    <div id="erroresEditar" class="mt-4 text-red-600 list-disc list-inside"></div>
  </div>
</div>
<script>
  // Modal Crear
  const openCrearBtn = document.getElementById('openCrearModal');
  const crearModal = document.getElementById('crearModal');
  const closeCrearBtn = document.getElementById('closeCrearModal');

  openCrearBtn.addEventListener('click', () => {
    crearModal.classList.remove('hidden');
  });
  closeCrearBtn.addEventListener('click', () => {
    crearModal.classList.add('hidden');
  });
  crearModal.addEventListener('click', (e) => {
    if (e.target === crearModal) crearModal.classList.add('hidden');
  });

  // Modal Editar
  const editarModal = document.getElementById('editarModal');
  const closeEditarBtn = document.getElementById('closeEditarModal');
  const editarForm = document.getElementById('editarForm');
  const nombreInput = document.getElementById('nombreEditar');

  document.querySelectorAll('.editarBtn').forEach(button => {
    button.addEventListener('click', () => {
      const id = button.getAttribute('data-id');
      const nombre = button.getAttribute('data-nombre');
      // Abrir modal
      editarModal.classList.remove('hidden');
      // Setear valores en el formulario
      nombreInput.value = nombre;
      // Cambiar action del formulario para incluir id en URL
      editarForm.action = '<?= url("etiqueta/editar") ?>/' + id;
      // Limpiar errores previos
      document.getElementById('erroresEditar').innerHTML = '';
    });
  });

  closeEditarBtn.addEventListener('click', () => {
    editarModal.classList.add('hidden');
  });
  editarModal.addEventListener('click', (e) => {
    if (e.target === editarModal) editarModal.classList.add('hidden');
  });
</script>

</body>
</html>

