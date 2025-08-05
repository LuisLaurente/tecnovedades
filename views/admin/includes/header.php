<link rel="stylesheet" href="<?= url('css/header.css') ?>">
<!-- Header moderno -->
<header class="main-header">
    <div class="header-content">
        <!-- Lado izquierdo: Título dinámico y breadcrumb -->
        <div class="header-left">
            <!-- Botón hamburguesa para móviles -->
            <button id="mobile-header-menu" class="mobile-menu-button">
                <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Información de la página actual -->
            <div class="page-info">
                <div class="time-status">
                    <svg class="clock-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="current-time" class="current-time"></span>
                    <span class="separator">•</span>
                    <span class="online-status">En línea</span>
                </div>
            </div>
        </div>

                <!-- Menú de navegación -->
        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="<?= url('info/nosotros') ?>">Nosotros</a></li>
                <li><a href="<?= url('info/contacto') ?>">Contacto</a></li>
                <li><a href="<?= url('info/terminos') ?>">Términos</a></li>
                <li><a href="<?= url('info/privacidad') ?>">Privacidad</a></li>
            </ul>
        </nav>

        <!-- Lado derecho: Notificaciones y perfil de usuario -->
        <div class="header-right">
            <!-- Separador visual -->
            <div class="visual-separator"></div>

            <!-- Perfil de usuario -->
            <div class="user-profile-container">
                <button class="user-profile-button">
                    <!-- Avatar con gradiente -->
                    <div class="avatar-container">
                        <div class="avatar-wrapper">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name'] ?? 'Usuario') ?>&background=f8fafc&color=374151&size=128"
                                alt="Avatar" class="avatar-image">
                        </div>
                        <!-- Indicador de estado en línea -->
                        <div class="online-indicator"></div>
                    </div>

                    <!-- Información del usuario -->
                    <div class="user-info">
                        <p class="user-name">
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Invitado') ?>
                        </p>
                        <p class="user-role">
                            <?= htmlspecialchars($_SESSION['user_role_name'] ?? 'Sin rol') ?>
                        </p>
                    </div>

                    <!-- Icono de dropdown -->
                    <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown del perfil -->
                <div class="profile-dropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-user-info">
                            <div class="dropdown-user-details">
                                <p class="dropdown-user-name">
                                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'Invitado') ?>
                                </p>
                                <p class="dropdown-user-email">
                                    <?= htmlspecialchars($_SESSION['user_email'] ?? 'Sin email') ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown-options">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Si está logueado -->
                            <a href="<?= url('/auth/profile') ?>" class="dropdown-item">
                                <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="dropdown-item-text">Mi Perfil</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= url('/auth/logout') ?>" class="dropdown-item logout-item">
                                <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span class="dropdown-item-text">Cerrar Sesión</span>
                            </a>
                        <?php else: ?>
                            <!-- Si NO está logueado -->
                            <a href="<?= url('/auth/login') ?>" class="dropdown-item login-item">
                                <svg class="dropdown-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path>
                                </svg>
                                <span class="dropdown-item-text">Iniciar Sesión</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</header>

<!-- Script para funcionalidades del header -->
<script>
    // Actualizar la hora en tiempo real
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
        const dateString = now.toLocaleDateString('es-ES', {
            day: 'numeric',
            month: 'short'
        });

        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = `${dateString}, ${timeString}`;
        }
    }

    // Actualizar la hora cada segundo
    updateTime();
    setInterval(updateTime, 1000);

    // Animación de los badges de notificaciones
    function animateNotificationBadge() {
        const badges = document.querySelectorAll('.notification-badge');
        badges.forEach(badge => {
            if (badge.textContent && parseInt(badge.textContent) > 0) {
                badge.classList.add('pulse-animation');
            }
        });
    }

    // Inicializar animaciones
    animateNotificationBadge();

    // Toggle del menú móvil
    document.getElementById('mobile-header-menu')?.addEventListener('click', function() {
        // Agregar lógica para mostrar/ocultar menú móvil
        console.log('Toggle mobile menu');
    });
</script>