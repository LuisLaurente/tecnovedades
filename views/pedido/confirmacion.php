
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Confirmada - Bytebox</title>
    <link rel="stylesheet" href="<?= url('css/confirmacion.css') ?>">
</head>
<body>
    <div class="confirmation-container">
        <div class="celebration">🎉</div>
        
        <div class="success-icon"></div>
        
        <h1 class="confirmation-title">¡Compra Exitosa!</h1>
        
        <p class="confirmation-message">
            Tu pedido ha sido registrado correctamente.
            <br><br>
            Nuestro equipo procesará tu pedido en las próximas horas y te mantendremos informado sobre el estado de tu envío.
        </p>
        
        <div class="order-info">
            <h3>Información del Pedido</h3>
            <div class="order-details">
                <div class="order-detail">
                    <span class="detail-label">Total:</span>
                    <span class="detail-value">S/ <?= number_format($pedido['monto_total'] ?? 0, 2) ?></span>
                </div>
                <div class="order-detail">
                    <span class="detail-label">Fecha:</span>
                    <span class="detail-value"><?= date('d/m/Y H:i', strtotime($pedido['creado_en'] ?? 'now')) ?></span>
                </div>
                <?php if (isset($direccion_pedido) && $direccion_pedido): ?>
                <div class="order-detail">
                    <span class="detail-label">Dirección de Envío:</span>
                    <span class="detail-value"><?= htmlspecialchars($direccion_pedido) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="<?= url('home/index') ?>" class="btn btn-primary">
                Seguir comprando
            </a>
             <a href="<?= url('/usuario/pedidos') ?>" class="btn btn-secondary">
                Ver mi pedido
            </a>
        </div>
    </div>
    
    <script>
        // Agregar confeti al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Crear elementos de confeti
            for (let i = 0; i < 30; i++) {
                createConfetti();
            }
        });
        
        function createConfetti() {
            const confetti = document.createElement('div');
            confetti.style.cssText = `
                position: fixed;
                width: 10px;
                height: 10px;
                background: ${getRandomColor()};
                top: -10px;
                left: ${Math.random() * 100}vw;
                border-radius: 50%;
                pointer-events: none;
                z-index: 1000;
                animation: confettiFall ${3 + Math.random() * 3}s linear forwards;
            `;
            
            document.body.appendChild(confetti);
            
            setTimeout(() => {
                confetti.remove();
            }, 6000);
        }
        
        function getRandomColor() {
            const colors = ['#3498db', '#e74c3c', '#f39c12', '#2ecc71', '#9b59b6', '#1abc9c'];
            return colors[Math.floor(Math.random() * colors.length)];
        }
        
        // Agregar animación CSS para el confeti
        const style = document.createElement('style');
        style.textContent = `
            @keyframes confettiFall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
