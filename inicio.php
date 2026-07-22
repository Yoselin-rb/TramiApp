<?php
// Asegúrate de iniciar la sesión al principio de tu archivo para acceder a los datos del usuario
session_start();

// Obtenemos el nombre desde la sesión. Si no existe, podemos mostrar un texto por defecto como "Invitado".
$nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Invitado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TramiApp - Inicio</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="shortcut icon" href="img/LogoICO.ico" type="image/x-icon">
</head>
<body>

    <header class="header">
        <div class="header-left">
            
            <img src="img/Logo TramiApp sin fondo.png" alt="Logo TramiApp" class="logo-header" width="50" height="50">
        </div>
        <h1>Trami<span>App</span></h1>
        <div class="user-welcome">
            Bienvenido, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong>
        </div>
    </header>

    <div class="main-container" style="margin-bottom: 70px;">
        
        <div class="stats-container">
            <div class="stat-box">
                <span class="number">33</span>
                <span class="label">Finalizados</span>
            </div>
            <div class="stat-box">
                <span class="number">2</span>
                <span class="label">En curso</span>
            </div>
            <div class="stat-box">
                <span class="number">8</span>
                <span class="label">Favoritos</span>
            </div>
        </div>

        <div class="section-title-container">
            <h3 class="section-title">Trámites frecuentes</h3>
            <a href="tramites-guichon.html" class="btn-ver-mas">Ver más</a>
        </div>

        <div class="grid-tramites">
            <a href="#" class="card-tramite">
                <img src="https://via.placeholder.com/50" alt="Cédula">
                <span>Renovación de Cédula</span>
            </a>
            <a href="ute-presencial.html" class="card-tramite">
                <img src="https://via.placeholder.com/50" alt="UTE">
                <span>Pago de factura de UTE</span>
            </a>
            <a href="#" class="card-tramite">
                <img src="https://via.placeholder.com/50" alt="Butiá">
                <span>Inscripción para beca Butiá</span>
            </a>
            <a href="#" class="card-tramite">
                <img src="https://via.placeholder.com/50" alt="gub.uy">
                <span>Registro en gub.uy</span>
            </a>
            <a href="#" class="card-tramite">
                <img src="https://via.placeholder.com/50" alt="Licencia">
                <span>Licencia de Conducir</span>
            </a>
        </div>

        <div class="section-title-container">
            <h3 class="section-title">Tips y consejos</h3>
            <a href="tips.html" class="btn-ver-mas">Ver más</a>
        </div>

        <div class="grid-tips">
            <div class="card-tip">
                <img src="https://via.placeholder.com/70" alt="Beca">
                <div class="card-tip-content">
                    <h4>Apoyá tus estudios con una beca</h4>
                    <p>El Fondo de Solidaridad brinda ayuda económica para tu carrera.</p>
                </div>
            </div>
            <div class="card-tip">
                <img src="https://via.placeholder.com/70" alt="Estafas">
                <div class="card-tip-content">
                    <h4>Cuidado con las estafas telefónicas</h4>
                    <p>Ninguna entidad pública te va a pedir contraseñas por llamada.</p>
                </div>
            </div>
        </div>

    </div>

    <nav class="bottom-nav">
        <a href="inicio.html" class="nav-item active">🏠</a>
        <a href="chat.html" class="nav-item">💬</a>
        <a href="configuracion.html" class="nav-item">⚙️</a>
    </nav>

    <script src="script.js"></script>
    
</body>
</html>