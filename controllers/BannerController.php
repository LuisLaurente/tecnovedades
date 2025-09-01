<?php

namespace Controllers;

require_once __DIR__ . '/BaseController.php';

class BannerController extends BaseController
{
    private string $uploadDirFs;   // Ruta en disco (filesystem)
    private string $uploadDirUrl;  // Ruta pública (para vistas)

    public function __construct()
    {
        // /controllers → sube al root y apunta a /public/uploads/banners
        $root = dirname(__DIR__);
        $this->uploadDirFs  = $root . '/public/uploads/banners/';
        $this->uploadDirUrl = 'uploads/banners/';

        if (!is_dir($this->uploadDirFs)) {
            @mkdir($this->uploadDirFs, 0777, true);
        }
    }

    /* ==========================
       Utilitarios internos
       ========================== */

    private function conn()
    {
        return \Core\Database::getInstance()->getConnection();
    }

    private function hasAccess(): bool
    {
        // Permite si tiene permiso explícito de banners o de promociones (marketing)
        if (class_exists('\Core\Helpers\SessionHelper')) {
            if (
                \Core\Helpers\SessionHelper::hasPermission('banners') ||
                \Core\Helpers\SessionHelper::hasPermission('promociones')
            ) {
                return true;
            }
        }
        return false;
    }

    private function deny()
    {
        http_response_code(403);
        $view403 = dirname(__DIR__) . '/views/errors/403.php';
        if (file_exists($view403)) {
            require $view403;
        } else {
            echo '403 - Acceso denegado';
        }
    }

    /* ==========================
       Acciones
       ========================== */

    public function index()
    {
        if (!$this->hasAccess()) return $this->deny();

        $conn = $this->conn();
        $banners = [];

        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->query("SELECT * FROM banners ORDER BY orden ASC, id DESC");
                $banners = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } elseif ($conn instanceof \mysqli) {
                $sql = "SELECT * FROM banners ORDER BY orden ASC, id DESC";
                if ($res = $conn->query($sql)) {
                    while ($row = $res->fetch_assoc()) $banners[] = $row;
                    $res->free();
                }
            }
        } catch (\Throwable $e) {
            error_log('BannerController::index error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No se pudieron cargar los banners.';
        }

        // Variables para la vista
        $uploadDirUrl = $this->uploadDirUrl;

        require_once dirname(__DIR__) . '/views/banner/index.php';
    }

    public function crear()
    {
        if (!$this->hasAccess()) return $this->deny();

        require_once dirname(__DIR__) . '/views/banner/form.php';
    }

    public function guardar()
    {
        
        if (!$this->hasAccess()) return $this->deny();

        // (Opcional) CSRF: validar si ya usas tokens en tus formularios
        // if (!\Core\Helpers\CsrfHelper::validarToken($_POST['csrf'] ?? '')) { ... }

        if (empty($_FILES['imagen']['name']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Subida de imagen no válida.';
            header('Location: ' . url('/banner/crear'));
            return;
        }

        $tmpPath  = $_FILES['imagen']['tmp_name'];
        $origName = $_FILES['imagen']['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $permitidas, true)) {
            $_SESSION['flash_error'] = 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.';
            header('Location: ' . url('/banner/crear'));
            return;
        }

        // (Opcional) Límite de tamaño p.ej. 5MB
        if (!empty($_FILES['imagen']['size']) && $_FILES['imagen']['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'La imagen supera el límite de 5MB.';
            header('Location: ' . url('/banner/crear'));
            return;
        }

        $safeName = uniqid('banner_', true) . '.' . $ext;
        $destFs   = $this->uploadDirFs . $safeName;

        if (!@move_uploaded_file($tmpPath, $destFs)) {
            $_SESSION['flash_error'] = 'No se pudo guardar el archivo subido.';
            header('Location: ' . url('/banner/crear'));
            return;
        }

        $orden  = isset($_POST['orden']) ? (int)$_POST['orden'] : 0;
        $activo = isset($_POST['activo']) ? 1 : 0;

        $conn = $this->conn();

        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->prepare("INSERT INTO banners (nombre_imagen, orden, activo) VALUES (:img, :ord, :act)");
                $stmt->execute([':img' => $safeName, ':ord' => $orden, ':act' => $activo]);
            } elseif ($conn instanceof \mysqli) {
                $imgEsc = $conn->real_escape_string($safeName);
                $conn->query("INSERT INTO banners (nombre_imagen, orden, activo) VALUES ('$imgEsc', $orden, $activo)");
            }
        } catch (\Throwable $e) {
            // Si la inserción falla, intenta limpiar el archivo
            if (file_exists($destFs)) @unlink($destFs);
            error_log('BannerController::guardar error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Error al guardar el banner.';
            header('Location: ' . url('/banner/crear'));
            return;
        }

        header('Location: ' . url('/banner'));
    }

    public function toggle($id = null)
    {
        if (!$this->hasAccess()) return $this->deny();
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID inválido.';
            header('Location: ' . url('/banner'));
            return;
        }

        $conn = $this->conn();
        try {
            if ($conn instanceof \PDO) {
                $conn->prepare("UPDATE banners SET activo = NOT activo WHERE id = ?")->execute([$id]);
            } elseif ($conn instanceof \mysqli) {
                $conn->query("UPDATE banners SET activo = NOT activo WHERE id = $id");
            }
        } catch (\Throwable $e) {
            error_log('BannerController::toggle error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No se pudo cambiar el estado.';
        }

        header('Location: ' . url('/banner'));
    }

    public function eliminar($id = null)
    {
        if (!$this->hasAccess()) return $this->deny();
        $id = (int)($id ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID inválido.';
            header('Location: ' . url('/banner'));
            return;
        }

        $conn = $this->conn();
        $imagen = null;

        try {
            if ($conn instanceof \PDO) {
                $stmt = $conn->prepare("SELECT nombre_imagen FROM banners WHERE id = ?");
                $stmt->execute([$id]);
                $imagen = $stmt->fetchColumn();

                $del = $conn->prepare("DELETE FROM banners WHERE id = ?");
                $del->execute([$id]);
            } elseif ($conn instanceof \mysqli) {
                $res = $conn->query("SELECT nombre_imagen FROM banners WHERE id = $id LIMIT 1");
                if ($res && $row = $res->fetch_assoc()) $imagen = $row['nombre_imagen'];
                $conn->query("DELETE FROM banners WHERE id = $id");
            }
        } catch (\Throwable $e) {
            error_log('BannerController::eliminar error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No se pudo eliminar el banner.';
            header('Location: ' . url('/banner'));
            return;
        }

        if ($imagen && file_exists($this->uploadDirFs . $imagen)) {
            @unlink($this->uploadDirFs . $imagen);
        }

        header('Location: ' . url('/banner'));
    }

    public function ordenar()
    {
        if (!$this->hasAccess()) return $this->deny();

        // Espera POST['orden'] = [id1, id2, id3, ...] en el orden deseado
        $lista = $_POST['orden'] ?? null;
        if (!is_array($lista) || empty($lista)) {
            $_SESSION['flash_error'] = 'No se recibió un orden válido.';
            header('Location: ' . url('/banner'));
            return;
        }

        $conn = $this->conn();

        try {
            $pos = 0;
            if ($conn instanceof \PDO) {
                $stmt = $conn->prepare("UPDATE banners SET orden = :pos WHERE id = :id");
                foreach ($lista as $id) {
                    $id = (int)$id;
                    $stmt->execute([':pos' => $pos, ':id' => $id]);
                    $pos++;
                }
            } elseif ($conn instanceof \mysqli) {
                foreach ($lista as $id) {
                    $id = (int)$id;
                    $conn->query("UPDATE banners SET orden = $pos WHERE id = $id");
                    $pos++;
                }
            }
        } catch (\Throwable $e) {
            error_log('BannerController::ordenar error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No se pudo guardar el nuevo orden.';
        }

        header('Location: ' . url('/banner'));
    }
    /**
     * Actualiza la imagen de un banner (AJAX).
     * Devuelve JSON: { ok: true, data: { nombre_imagen: '...' } }
     */
    public function actualizar_imagen($id = null)
    {
        // Responder siempre JSON
        header('Content-Type: application/json; charset=utf-8');

        // Permisos
        if (!$this->hasAccess()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Acceso denegado']);
            return;
        }

        // Aceptar id por ruta o por POST (fallback)
        $id = (int)($id ?? 0);
        if ($id <= 0 && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
        }
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'ID inválido']);
            return;
        }

        // Validar archivo
        if (empty($_FILES['imagen']['name']) || ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'No se recibió archivo válido.']);
            return;
        }

        $tmpPath  = $_FILES['imagen']['tmp_name'];
        $origName = $_FILES['imagen']['name'];
        $size     = $_FILES['imagen']['size'] ?? 0;

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $permitidas, true)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Formato no permitido.']);
            return;
        }

        // Límite de tamaño: 5MB
        if (!empty($size) && $size > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'La imagen supera el límite de 5MB.']);
            return;
        }

        // Asegurar directorio de destino
        if (!is_dir($this->uploadDirFs)) {
            if (!@mkdir($this->uploadDirFs, 0777, true)) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'message' => 'No se pudo preparar el directorio de subidas.']);
                return;
            }
        }

        // Nombre seguro y ruta final
        $safeName = uniqid('banner_', true) . '.' . $ext;
        $destFs   = $this->uploadDirFs . $safeName;

        // Obtener banner actual desde el modelo
        $banner = null;
        try {
            $banner = \Models\Banner::obtenerPorId($id);
        } catch (\Throwable $e) {
            error_log('BannerController::actualizar_imagen - modelo obtenerPorId threw: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Error interno al buscar el banner.']);
            return;
        }

        if (!$banner) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Banner no encontrado.']);
            return;
        }

        $oldImage = $banner['nombre_imagen'] ?? null;

        // Mover archivo subido
        if (!@move_uploaded_file($tmpPath, $destFs)) {
            error_log('BannerController::actualizar_imagen move_uploaded_file failed: ' . print_r($_FILES['imagen'], true));
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'No se pudo guardar el archivo en el servidor.']);
            return;
        }
        @chmod($destFs, 0644);

        // Actualizar BD mediante el modelo (si falla, eliminar archivo nuevo)
        try {
            $ok = \Models\Banner::actualizarImagen($id, $safeName);
            if (!$ok) {
                // intentar extraer info de error desde la conexión
                $dbError = '';
                try {
                    $connDbg = $this->conn();
                    if ($connDbg instanceof \PDO) {
                        $info = $connDbg->errorInfo();
                        $dbError = is_array($info) ? implode(' | ', $info) : (string)$info;
                    } elseif ($connDbg instanceof \mysqli) {
                        $dbError = $connDbg->error ?? '';
                    }
                } catch (\Throwable $e) {
                    $dbError = 'No se pudo obtener error de DB: ' . $e->getMessage();
                }

                // limpiar archivo nuevo si fue creado
                if (file_exists($destFs)) @unlink($destFs);

                error_log("BannerController::actualizar_imagen - update failed for id={$id}. DB error: {$dbError}");

                http_response_code(500);
                // 'debug' solo mientras depuras en local; eliminar en producción
                echo json_encode(['ok' => false, 'message' => 'Fallo al actualizar la base de datos.', 'debug' => $dbError]);
                return;
            }
        } catch (\Throwable $e) {
            if (file_exists($destFs)) @unlink($destFs);
            error_log('BannerController::actualizar_imagen EXCEPTION: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Error interno al actualizar', 'debug' => $e->getMessage()]);
            return;
        }

        // Borrar imagen anterior si existía y es distinta
        if (!empty($oldImage) && $oldImage !== $safeName) {
            $oldFs = $this->uploadDirFs . $oldImage;
            if (file_exists($oldFs)) {
                @unlink($oldFs);
            }
        }

        // Responder OK con nombre de archivo para que el frontend actualice la miniatura
        echo json_encode(['ok' => true, 'data' => ['nombre_imagen' => $safeName]]);
        return;
    }

    /**
     * Wrapper para rutas con guión: /banner/actualizar-imagen -> Router convierte a actualizarImagen()
     */
    public function actualizarImagen($id = null)
    {
        return $this->actualizar_imagen($id);
    }
}
