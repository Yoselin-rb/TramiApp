<?php
// 1. Iniciar la sesión al principio del archivo
session_start();

require_once 'conexion.php';
require_once 'auto_login.php';

// 2. Obtener el nombre del usuario desde la sesión (o asignarle 'Invitado' por defecto)
$nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Invitado';
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Configuración</title>
        <link rel="stylesheet" href="estilos.css">
        <link rel="shortcut icon" href= "./img/LogoICO.ico" type="image/x-icon">

</head>
<body>

    <div class="sidebar-overlay">
        <div class="sidebar">
            
            <div class="profile-section">
                <div class="profile-img">
                    <img src="img/Logo TramiApp sin fondo.png" alt="Logo TramiApp">
                </div>
                <div>
                    <h2 style="font-size: 20px;">¡Hola <?php echo htmlspecialchars($nombreUsuario); ?>!</h2>
                    <p style="font-size: 13px; opacity: 0.9;">Personaliza tu experiencia en TramiApp.</p>
                </div>
            </div>

            <div class="menu-list">
                <a href="mi-actividad.php" class="menu-item">
                    <span>📋 &nbsp; Mi actividad</span>
                    <span>›</span>
                </a>
                <a href="tamano-letra.html" class="menu-item">
                    <span>Aa &nbsp; Tamaño de letra</span>
                    <span>›</span>
                </a>
                <a href="#" class="menu-item">
                    <span>❓ &nbsp; Ayuda y soporte</span>
                    <span>›</span>
                </a>
                <div class="menu-item" style="border:none;">
                    <span>🔔 &nbsp; Notificaciones</span>
                    <input type="checkbox" checked style="cursor:pointer;">
                </div>
            </div>

            <a href="logout.php" class="btn-logout">Cerrar sesión</a>
        </div>
    </div>

    <nav class="bottom-nav">
        <a href="inicio.php" class="nav-item">🏠</a>
        <a href="asistente.html" class="nav-item">💬</a>
        <a href="configuracion.php" class="nav-item active">⚙️</a>
    </nav>

    <script src="script.js"></script>
    
</body>
</html>