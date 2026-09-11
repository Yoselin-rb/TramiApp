<?php
// Asegúrate de iniciar la sesión al principio de tu archivo para acceder a los datos del usuario
session_start();

require_once 'conexion.php';
require_once 'auto_login.php';   // <-- nuevo: restaura la sesión (y el rol) si venía por cookie "recordar usuario"

// Obtenemos el nombre desde la sesión. Si no existe, podemos mostrar un texto por defecto como "Invitado".
$esInvitado = !isset($_SESSION['usuario_id']);
$nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Invitado';

// Si el usuario logueado es editor de contenido, le mostramos el acceso rápido al panel
$esEditor = isset($_SESSION['rol']) && $_SESSION['rol'] === 'editor';

// Finalizados: cantidad de trámites DISTINTOS que el usuario completó
// aprobando el test (si repitió un test varias veces, cuenta una sola vez)
$finalizados = 0;
if (isset($_SESSION['usuario_id'])) {
    $stmt = $conexion->prepare("SELECT COUNT(DISTINCT tramite_id) FROM finalizados WHERE usuario_id = :usuario_id");
    $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt->execute();
    $finalizados = (int) $stmt->fetchColumn();
}

// Favoritos: total real de trámites que el usuario marcó con la estrella ⭐
$favoritos = 0;
if (isset($_SESSION['usuario_id'])) {
    $stmt = $conexion->prepare("SELECT COUNT(*) FROM favoritos WHERE usuario_id = :usuario_id");
    $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmt->execute();
    $favoritos = (int) $stmt->fetchColumn();
}

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
            <?php if ($esInvitado): ?>
                <a href="login.php" class="btn-iniciar-sesion">Iniciar sesión</a>
            <?php else: ?>
                Bienvenido, <strong><?php echo htmlspecialchars($nombreUsuario); ?></strong>
            <?php endif; ?>
        </div>
    </header>

    <div class="main-container" style="margin-bottom: 70px;">
        
        <div class="stats-container">
            <a href="mi-actividad.php" class="stat-box">
                <span class="number"><?php echo $finalizados; ?></span>
                <span class="label">Finalizados</span>
            </a>
            <a href="mi-actividad.php?tab=favoritos" class="stat-box">
                <span class="number"><?php echo $favoritos; ?></span>
                <span class="label">Favoritos</span>
            </a>
        </div>

        <div class="section-title-container">
            <h3 class="section-title">Trámites frecuentes</h3>
            <div class="acciones-titulo">
                <?php if ($esEditor): ?>
                    <a href="admin-tramites.php" class="btn-editar-tramites" title="Administrar trámites">✏️</a>
                <?php endif; ?>
                <a href="tramites-guichon.php" class="btn-ver-mas">Ver más</a>
            </div>
        </div>

        <div class="grid-tramites">
            <a href="<?php echo $esInvitado ? 'registro.php' : '#'; ?>" class="card-tramite">
                <img src="./img/Tramites/Cedula.jpg" alt="Cédula" style="width: 100px; height: 100px;">
                <span>Renovación de Cédula</span>
            </a>
            <a href="<?php echo $esInvitado ? 'registro.php' : 'ute-presencial.html'; ?>" class="card-tramite">
                <img src="./img/Tramites/factura-ute.webp" alt="UTE" style="width: 100px; height: 100px;">
                <span>Pago de factura de UTE</span>
            </a>
            <a href="<?php echo $esInvitado ? 'registro.php' : '#'; ?>" class="card-tramite">                
                <img src="./img/Tramites/BUTIA.jpeg" alt="Butiá" style="width: 100px; height: 100px;">
                <span>Inscripción para beca Butiá</span>
            </a>
            <a href="<?php echo $esInvitado ? 'registro.php' : '#'; ?>" class="card-tramite">
                <img src="./img/Tramites/gub.png" alt="gub.uy" style="width: 70px; height: 70px;">
                <span>Registro en gub.uy</span>
            </a>
            <a href="<?php echo $esInvitado ? 'registro.php' : '#'; ?>" class="card-tramite">
                <img src="./img/Tramites/licenciaConducir.png" alt="Licencia" style="width: 100px; height: 100px;">
                <span>Licencia de Conducir</span>
            </a>
        </div>

        <div class="section-title-container">
            <h3 class="section-title">Tips y consejos</h3>
            <a href="tips.html" class="btn-ver-mas">Ver más</a>
        </div>

        <div class="grid-tips">
            <div class="card-tip">
                <img src="img/Tips/fondo_solidaridad.png" alt="Beca" style="width: 70px; height: 100px;">
                <div class="card-tip-content">
                    <h4>Apoyá tus estudios con una beca</h4>
                    <p>El Fondo de Solidaridad brinda ayuda económica para tu carrera.</p>
                </div>
            </div>
            <div class="card-tip">
                <img src="img/Tips/estafasTelefonicas.png" alt="Estafas" style="width: 70px; height: 100px;">
                <div class="card-tip-content">
                    <h4>Cuidado con las estafas telefónicas</h4>
                    <p>Ninguna entidad pública te va a pedir contraseñas por llamada.</p>
                </div>
            </div>
        </div>

    </div>

    <nav class="bottom-nav">
        <a href="inicio.php" class="nav-item active">🏠</a>
        <a href="asistente.html" class="nav-item">💬</a>
        <a href="configuracion.php" class="nav-item">⚙️</a>
    </nav>

    <script src="script.js"></script>
    
</body>
</html>