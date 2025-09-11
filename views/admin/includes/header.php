<?php
// header.php (completo)
// Asegurarnos session + $cantidadEnCarrito disponible
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($cantidadEnCarrito)) {
  $cantidadEnCarrito = 0;
  if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
      $cantidadEnCarrito += (int)($item['cantidad'] ?? 0);
    }
  }
}

// Función para verificar si el usuario es un cliente (rol usuario)
function isClienteHeader()
{
  $userRole = \Core\Helpers\SessionHelper::getRole();

  // Si el rol es un array, obtener el nombre
  if (is_array($userRole) && isset($userRole['nombre'])) {
    return $userRole['nombre'] === 'usuario';
  }

  // Si es una cadena, verificar directamente
  if (is_string($userRole)) {
    return $userRole === 'usuario';
  }

  // Verificar por permisos - los clientes solo tienen 'perfil'
  $userPermissions = \Core\Helpers\SessionHelper::getPermissions();
  if (is_array($userPermissions)) {
    // Cliente típico: solo tiene permiso de 'perfil' y no tiene permisos administrativos
    return in_array('perfil', $userPermissions) &&
      !in_array('usuarios', $userPermissions) &&
      !in_array('productos', $userPermissions);
  }

  return false;
}

/* -----------------------
   CARGA DINÁMICA DE CATEGORÍAS (con id_padre y tree)
   ----------------------- */
$allCategories = [];
$categoriasTree = [];
$parentCategories = [];

try {
  if (class_exists('\Models\Categoria') && method_exists('\Models\Categoria', 'obtenerTodas')) {
    // Idealmente este método debe devolver id_padre; si no, usa el fallback SQL.
    $raw = \Models\Categoria::obtenerTodas();
  } else {
    $db = \Core\Database::getInstance()->getConnection();
    // IMPORTANTE: aquí asumo que la columna padre se llama `id_padre`. Cámbiala si tu columna tiene otro nombre.
    $stmt = $db->prepare("SELECT id, nombre, IFNULL(slug, id) AS slug, activo, COALESCE(id_padre, 0) AS id_padre, orden FROM categorias WHERE activo = 1 ORDER BY orden ASC, nombre ASC");
    $stmt->execute();
    $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Normalizar
  if (!is_array($raw)) {
    $raw = json_decode(json_encode($raw), true) ?: [];
  }
  $allCategories = $raw;

  // Agrupar por id_padre
  $itemsByParent = [];
  foreach ($allCategories as $c) {
    $pid = isset($c['id_padre']) && ($c['id_padre'] !== '') ? (int)$c['id_padre'] : 0;
    $itemsByParent[$pid][] = $c;
  }

  // Builder recursivo (closure)
  $buildTree = function ($parentId) use (&$itemsByParent, &$buildTree) {
    $branch = [];
    if (!isset($itemsByParent[$parentId])) return [];
    foreach ($itemsByParent[$parentId] as $item) {
      $children = $buildTree((int)$item['id']);
      if (!empty($children)) $item['children'] = $children;
      $branch[] = $item;
    }
    return $branch;
  };

  // Tree de nivel superior (asumo id_padre = 0 para padres)
  $categoriasTree = $buildTree(0);
  $parentCategories = $itemsByParent[0] ?? [];
} catch (\Throwable $e) {
  error_log("Header: error cargando categorías - " . $e->getMessage());
  $allCategories = $categoriasTree = $parentCategories = [];
}


// Helper para generar URL de categoría. Ajusta la ruta si tu routing es distinto.
// Ejemplos comunes: 'categoria/ver/{slug}' o 'producto/categoria/{slug}'
function categoria_url($cat)
{
  // Priorizar id si existe (tu URL actual usa ?categoria=6), 
  // si no, usar slug (por si prefieres buscar por slug).
  $identifier = '';
  if (isset($cat['id']) && $cat['id'] !== '') {
    $identifier = $cat['id'];
  } elseif (isset($cat['slug']) && $cat['slug'] !== '') {
    $identifier = $cat['slug'];
  }

  // Construir URL usando el helper url() para respetar el base path (/TECNOVEDADES/public en local)
  // Resultado ejemplo: http://localhost/TECNOVEDADES/public/home/busqueda?categoria=6
  return url('home/busqueda') . '?categoria=' . rawurlencode($identifier);
}
?>

<link rel="stylesheet" href="<?= url('css/header.css') ?>">
<!--
<div class="top-bar">
  <div class="top-bar-content">
    <nav class="top-links">
      <a href="#" class="top-link">Empresa</a>
      <a href="#" class="top-link">Novedades</a>
      <a href="#" class="top-link">Atención al Cliente</a>
      <a href="#" class="top-link">Contacto</a>
    </nav>
    <div class="social-icons">
      <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
      <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
      <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
    </div>
  </div>
</div>
-->
<div class="sticky-header-wrapper">  <!-- <<-- AÑADIR ESTE DIV ENVOLVENTE -->
<header class="main-header">
  <div class="header-content">

    <!-- Left: logo -->
    <div class="header-left ml-[15px]">
      <a href="<?= url('home/index') ?>" class="logo-link" aria-label="Bytebox home">
        <img src="<?= url('images/Logo_Horizontal2_Versi_nPrincipal.png') ?>" alt="Bytebox" class="logo-image">
      </a>
    </div>

    <!-- Center: search -->
    <nav class="main-nav">
      <form class="search-form" action="<?= url('producto/busqueda') ?>" method="GET" role="search" autocomplete="off">
        <input
          type="search"
          name="q"
          class="search-input"
          placeholder="Buscar productos..."
          value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : '' ?>"
          aria-label="Buscar productos"
          spellcheck="false"
          autocapitalize="off"
          autocomplete="off" />
        <div id="autocomplete-results" class="autocomplete-results" role="listbox" aria-expanded="false"></div>
        <button type="submit" class="search-button" aria-label="Buscar">
          <!-- ícono lupa -->
          <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
          </svg>
        </button>
      </form>
    </nav>

    <!-- Perfil: contenedor envuelve botón + dropdown (para evitar gaps) -->
    <div class="user-profile-container" id="userProfileContainer">
      <button class="user-profile-button" id="userProfileButton" aria-haspopup="true" aria-expanded="false" aria-label="Abrir menú de usuario">
        <!-- Icono persona minimalista -->
        <svg class="user-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4 0-7 2-7 4v2h14v-2c0-2-3-4-7-4z" />
        </svg>
        <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>

      <div class="profile-dropdown" id="profileDropdown" role="menu" aria-hidden="true">
        <div class="dropdown-options">
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?= url('/auth/profile') ?>" class="dropdown-item" role="menuitem">
              <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4 0-7 2-7 4v2h14v-2c0-2-3-4-7-4z" />
              </svg>
              <span class="dropdown-item-text">Mi Cuenta</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= url('/usuario/pedidos') ?>" class="dropdown-item" role="menuitem">
              <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l9-4 9 4" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7M8 11V7" />
              </svg>
              <span class="dropdown-item-text">Mis Pedidos</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= url('/auth/logout') ?>" class="dropdown-item logout-item" role="menuitem">
              <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7" />
              </svg>
              <span class="dropdown-item-text">Cerrar Sesión</span>
            </a>
          <?php else: ?>
            <a href="<?= url('/auth/login') ?>" class="dropdown-item login-item" role="menuitem">
              <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 12h14M12 5l7 7-7 7" />
              </svg>
              <span class="dropdown-item-text">Iniciar Sesión</span>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Carrito (a la derecha del perfil) -->
    <div class="cart-section">
      <a href="<?= url('carrito/ver') ?>" class="cart-button" aria-label="Ver carrito">
        <!-- Icono carrito minimalista -->
        <svg class="cart-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="32" height="32" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2 6h2l2 12h12l2-8H6M16 18a2 2 0 11-4 0 2 2 0 014 0zm-6 0a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <?php if ($cantidadEnCarrito > 0): ?>
          <span class="cart-badge" id="cartBadge" aria-live="polite" aria-atomic="true">
            <?= $cantidadEnCarrito ?>
          </span>
        <?php endif; ?>
      </a>
    </div>

  </div>
  
</header>
<div class="categories-bar">
    <div class="categories-content">
      <div class="all-categories-dropdown-container">
        <button class="all-categories-button ml-[15px]" id="allCategoriesButton" aria-haspopup="true" aria-expanded="false">
          Todas las Categorías
          <svg class="dropdown-arrow-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>

        <div class="categories-dropdown" id="categoriesDropdown" role="menu" aria-hidden="true">
          <?php
          $cols = 3;
          $topLevel = $categoriasTree; // elementos de primer nivel (cada uno puede traer 'children')
          $total = count($topLevel);

          if ($total === 0) {
            echo '<div class="category-column"><div class="category-empty">No hay categorías</div></div>';
          } else {
            $perCol = (int) ceil($total / $cols);
            $chunks = array_chunk($topLevel, $perCol);

            // closure recursivo para renderizar children
            $render_children = function ($children) use (&$render_children) {
              $html = '<ul class="subcategory-list">';
              foreach ($children as $ch) {
                $name = htmlspecialchars($ch['nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8');
                $href = htmlspecialchars(categoria_url($ch), ENT_QUOTES, 'UTF-8');
                $html .= "<li class=\"subcategory-item\"><a href=\"{$href}\">{$name}</a>";
                if (!empty($ch['children'])) {
                  $html .= $render_children($ch['children']);
                }
                $html .= "</li>";
              }
              $html .= '</ul>';
              return $html;
            };

            foreach ($chunks as $i => $chunk) {
              echo '<div class="category-column">';

              // 🔹 Solo en la primera columna agregamos "Todas las categorías"
              if ($i === 0) {
                echo '<div class="category-item-with-children">';
                echo '<a href="' . url("home/busqueda") . '" class="category-item parent font-semibold text-blue-600">Todas las categorías</a>';
                echo '</div>';

              }
              // 🔹 Renderizamos el resto de categorías normalmente
              foreach ($chunk as $c) {
                $nombre = htmlspecialchars($c['nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8');
                $href = htmlspecialchars(categoria_url($c), ENT_QUOTES, 'UTF-8');
                echo "<div class=\"category-item-with-children\">";
                echo "<a href=\"{$href}\" class=\"category-item parent\">{$nombre}</a>";
                if (!empty($c['children'])) {
                  echo $render_children($c['children']);
                }
                echo "</div>";
              }
              echo '</div>';
            }
          }
          ?>
        </div>

      </div>

      <nav class="category-links">
        <?php
        // Mostrar las primeras N categorías en la barra superior (p. ej. 8)
        $topN = 8;
        $top = !empty($categoriasTree) ? array_slice($categoriasTree, 0, $topN) : array_slice($allCategories, 0, $topN);
        if (empty($top)) {
          // Fallback: enlace estático opcional
          echo '<a href="#" class="category-link">SIN CATEGORÍAS</a>';
        } else {
          foreach ($top as $t) {
            $label = mb_strtoupper(trim($t['nombre'] ?? ''), 'UTF-8');
            $href = htmlspecialchars(categoria_url($t), ENT_QUOTES, 'UTF-8');
            echo "<a href=\"{$href}\" class=\"category-link\">{$label}</a>";
          }
        }
        ?>
      </nav>
    </div>
  </div>
</div> <!-- <<-- CERRAR EL DIV ENVOLVENTE -->
<!-- Exponer BASE_URL para JS -->
<script>
  const BASE_URL = "<?= rtrim(url(''), '/') ?>";
</script>

<!-- Autocomplete + dropdown profile behavior -->
<script>
  document.addEventListener('DOMContentLoaded', function() {

    /* ---------- Autocomplete ---------- */
    const input = document.querySelector('.search-input');
    const resultsContainer = document.getElementById('autocomplete-results');
    let debounceTimeout;

    // small helper to escape text for innerHTML injection
    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/[&<>"']/g, s => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [s]));
    }

    function hideResults() {
      resultsContainer.style.display = 'none';
      resultsContainer.innerHTML = '';
      resultsContainer.setAttribute('aria-expanded', 'false');
    }

    if (input) {
      input.addEventListener('input', function(e) {
        const q = this.value.trim();
        if (q.length === 0) {
          hideResults();
          return;
        }

        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
          fetch(`${BASE_URL}/producto/autocomplete?q=${encodeURIComponent(q)}`)
            .then(resp => {
              if (!resp.ok) throw new Error('Network error');
              return resp.json();
            })
            .then(data => {
              resultsContainer.innerHTML = '';
              if (!Array.isArray(data) || data.length === 0) {
                hideResults();
                return;
              }

              data.forEach(item => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                const imgSrc = item.imagen ? `${BASE_URL}/uploads/${item.imagen}` : `${BASE_URL}/uploads/default-product.png`;
                div.innerHTML = `
                  <img src="${escapeHtml(imgSrc)}" class="autocomplete-img" alt="${escapeHtml(item.nombre)}">
                  <div class="autocomplete-info">
                    <div class="autocomplete-name">${escapeHtml(item.nombre)}</div>
                    <div class="autocomplete-price">S/ ${Number(item.precio || 0).toFixed(2)}</div>
                  </div>
                `;
                div.addEventListener('click', () => {
                  // Redirigir a la ficha del producto
                  window.location.href = `${BASE_URL}/producto/ver/${encodeURIComponent(item.id)}`;
                });
                resultsContainer.appendChild(div);
              });

              resultsContainer.style.display = 'block';
              resultsContainer.setAttribute('aria-expanded', 'true');
            })
            .catch(err => {
              console.error('Autocomplete error', err);
              hideResults();
            });
        }, 250); // debounce
      });

      document.addEventListener('click', (e) => {
        if (!resultsContainer.contains(e.target) && e.target !== input) {
          hideResults();
        }
      });
    }

    /* ---------- Profile dropdown open/close with small delay ---------- */
    const profileContainer = document.getElementById('userProfileContainer');
    const profileDropdown = document.getElementById('profileDropdown');
    let profileCloseTimeout = null;

    if (profileContainer) {
      // show immediately on enter
      profileContainer.addEventListener('mouseenter', () => {
        clearTimeout(profileCloseTimeout);
        profileContainer.classList.add('open');
        profileDropdown.setAttribute('aria-hidden', 'false');
        document.getElementById('userProfileButton').setAttribute('aria-expanded', 'true');
      });

      // start a small timeout on leave to allow cursor to reach dropdown
      profileContainer.addEventListener('mouseleave', () => {
        profileCloseTimeout = setTimeout(() => {
          profileContainer.classList.remove('open');
          profileDropdown.setAttribute('aria-hidden', 'true');
          document.getElementById('userProfileButton').setAttribute('aria-expanded', 'false');
        }, 200); // 200ms tolerancia
      });

      // keyboard accessibility: toggle on focus/blur
      profileContainer.addEventListener('focusin', () => {
        clearTimeout(profileCloseTimeout);
        profileContainer.classList.add('open');
        profileDropdown.setAttribute('aria-hidden', 'false');
        document.getElementById('userProfileButton').setAttribute('aria-expanded', 'true');
      });
      profileContainer.addEventListener('focusout', () => {
        profileCloseTimeout = setTimeout(() => {
          profileContainer.classList.remove('open');
          profileDropdown.setAttribute('aria-hidden', 'true');
          document.getElementById('userProfileButton').setAttribute('aria-expanded', 'false');
        }, 200);
      });
    }

    /* ---------- Categories dropdown open/close with small delay ---------- */
    const allCategoriesButton = document.getElementById('allCategoriesButton');
    const categoriesDropdown = document.getElementById('categoriesDropdown');
    let categoriesCloseTimeout = null;

    if (allCategoriesButton && categoriesDropdown) {
      allCategoriesButton.addEventListener('mouseenter', () => {
        clearTimeout(categoriesCloseTimeout);
        categoriesDropdown.classList.add('open');
        allCategoriesButton.setAttribute('aria-expanded', 'true');
        categoriesDropdown.setAttribute('aria-hidden', 'false');
      });

      allCategoriesButton.addEventListener('mouseleave', () => {
        categoriesCloseTimeout = setTimeout(() => {
          categoriesDropdown.classList.remove('open');
          allCategoriesButton.setAttribute('aria-expanded', 'false');
          categoriesDropdown.setAttribute('aria-hidden', 'true');
        }, 200);
      });

      categoriesDropdown.addEventListener('mouseenter', () => {
        clearTimeout(categoriesCloseTimeout);
        categoriesDropdown.classList.add('open');
        allCategoriesButton.setAttribute('aria-expanded', 'true');
        categoriesDropdown.setAttribute('aria-hidden', 'false');
      });

      categoriesDropdown.addEventListener('mouseleave', () => {
        categoriesCloseTimeout = setTimeout(() => {
          categoriesDropdown.classList.remove('open');
          allCategoriesButton.setAttribute('aria-expanded', 'false');
          categoriesDropdown.setAttribute('aria-hidden', 'true');
        }, 200);
      });
    }

  });
</script>