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
            if (\Core\Helpers\SessionHelper::hasPermission('banners') ||
                \Core\Helpers\SessionHelper::hasPermission('promociones')) {
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

        $permitidas = ['jpg','jpeg','png','webp','gif'];
        if (!in_array($ext, $permitidas, true)) {
            $_SESSION['flash_error'] = 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.';
            header('Location: ' . url('/banner/crear'));
            return;
        }

        // (Opcional) Límite de tamaño p.ej. 5MB
        if (!empty($_FILES['imagen']['size']) && $_FILES['imagen']['size'] > 5 * 1024 * 1024) {
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
}
