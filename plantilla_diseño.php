<!DOCTYPE html>
<html lang="es">
<?php include_once __DIR__ . '/../admin/includes/head.php'; ?>

<body>
    <div class="flex h-screen">
        <!-- Incluir navegación lateral fija -->
        <div class="fixed inset-y-0 left-0 z-50">
            <?php include_once __DIR__ . '/../admin/includes/navbar.php'; ?>
        </div>
         <div class="flex-1 ml-64 flex flex-col min-h-screen">

        <main class="flex-1 p-2 bg-gray-50 overflow-y-auto">
            <!-- Incluir header superior fijo -->
            <div class="sticky top-0 z-40">
                <?php include_once __DIR__ . '/../admin/includes/header.php'; ?>
            </div>

            <!-- Todo el contenido de la pagina-->

            <div class="mt-4">
                <?php include_once __DIR__ . '/../admin/includes/footer.php'; ?>
            </div>
        </main>
         </div>
    </div>
</body>

</html>