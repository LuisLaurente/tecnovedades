
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Confirmada - TecnoVedades</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .confirmation-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .confirmation-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #27ae60, #2ecc71, #3498db);
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            position: relative;
            animation: successPulse 2s infinite;
        }
        
        .success-icon::before {
            content: "✓";
            color: white;
            font-size: 4rem;
            font-weight: bold;
        }
        
        .success-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            opacity: 0.3;
            animation: ripple 2s infinite;
        }
        
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes ripple {
            0% { transform: scale(1); opacity: 0.3; }
            100% { transform: scale(1.3); opacity: 0; }
        }
        
        .confirmation-title {
            color: #2c3e50;
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .confirmation-message {
            color: #7f8c8d;
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        
        .order-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 40px;
            border-left: 5px solid #27ae60;
        }
        
        .order-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .order-info h3::before {
            content: "📋";
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            text-align: left;
        }
        
        .order-detail {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .order-detail:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 500;
            color: #34495e;
        }
        
        .detail-value {
            color: #27ae60;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            min-width: 180px;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .btn-secondary {
            background: transparent;
            color: #3498db;
            border: 2px solid #3498db;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #1f5fb8);
        }
        
        .btn-secondary:hover {
            background: #3498db;
            color: white;
        }
        
        .btn::before {
            margin-right: 8px;
            font-size: 1.1rem;
        }
        
        .btn-primary::before {
            content: "🛍️";
        }
        
        .btn-secondary::before {
            content: "📱";
        }
        
        .celebration {
            position: absolute;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 2rem;
            animation: celebration 3s ease-in-out infinite;
        }
        
        @keyframes celebration {
            0%, 100% { transform: translateX(-50%) translateY(0) rotate(0deg); opacity: 0; }
            50% { transform: translateX(-50%) translateY(-20px) rotate(360deg); opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .confirmation-container {
                padding: 40px 20px;
                margin: 20px;
            }
            
            .confirmation-title {
                font-size: 2rem;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 280px;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="celebration">🎉</div>
        
        <div class="success-icon"></div>
        
        <h1 class="confirmation-title">¡Compra Exitosa!</h1>
        
        <p class="confirmation-message">
            Tu pedido ha sido registrado correctamente y pronto recibirás la confirmación por correo electrónico.
            <br><br>
            Nuestro equipo procesará tu pedido en las próximas horas y te mantendremos informado sobre el estado de tu envío.
        </p>
        
        <div class="order-info">
            <h3>Información del Pedido</h3>
            <div class="order-details">
                <div class="order-detail">
                    <span class="detail-label">Número de Pedido:</span>
                    <span class="detail-value">#<?= $pedido['id'] ?? 'N/A' ?></span>
                </div>
                <div class="order-detail">
                    <span class="detail-label">Estado:</span>
                    <span class="detail-value"><?= ucfirst($pedido['estado'] ?? 'Pendiente') ?></span>
                </div>
                <div class="order-detail">
                    <span class="detail-label">Total:</span>
                    <span class="detail-value">S/ <?= number_format($pedido['monto_total'] ?? 0, 2) ?></span>
                </div>
                <div class="order-detail">
                    <span class="detail-label">Fecha:</span>
                    <span class="detail-value"><?= date('d/m/Y H:i', strtotime($pedido['creado_en'] ?? 'now')) ?></span>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="<?= url('producto/index') ?>" class="btn btn-primary">
                Seguir comprando
            </a>
            <a href="<?= url('pedido/ver/' . ($pedido['id'] ?? '')) ?>" class="btn btn-secondary">
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
