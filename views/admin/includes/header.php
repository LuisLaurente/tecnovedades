<?php
// header.php (completo)
// Asegurarnos session + $cantidadEnCarrito disponible si no viene definido
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

?>
<link rel="stylesheet" href="<?= url('css/header.css') ?>">

<header class="main-header">
  <div class="header-content">

    <!-- Left: logo -->
    <div class="header-left">
      <a href="<?= url('home/index') ?>" class="logo-link" aria-label="Bytebox home">
        <img src="<?= url('images/logoBitebox.png') ?>" alt="Bytebox" class="logo-image">
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
          value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
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

    <!-- Right: tienda, perfil, cart -->
    <div class="header-right">
      <!-- Botón Ir a Tienda (solo para clientes) -->
      <?php if (isClienteHeader()): ?>
        <div class="shop-button-container">
          <a href="<?= url('/producto/index') ?>" class="shop-button" aria-label="Ir a la tienda">
            <svg class="shop-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <span class="shop-text">Tienda</span>
          </a>
        </div>
      <?php endif; ?>

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
                <span class="dropdown-item-text">Mi Perfil</span>
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
  </div>
</header>

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

  });
</script>