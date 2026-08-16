<?php
// tramites-guichon.php
// Antes era tramites-guichon.html con las tarjetas de trámites escritas a
// mano. Ahora arma la lista consultando la tabla "tramites", así que
// cualquier trámite que el editor de contenido cargue desde
// admin-tramites.php (uno por uno o por planilla) aparece acá solo,
// sin que nadie tenga que tocar este archivo.

session_start();
require_once 'conexion.php';

// Solo mostramos los trámites marcados como activos
$stmt = $conexion->query("SELECT id, nombre, icono FROM tramites WHERE activo = 1 ORDER BY nombre ASC");
$tramites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trámites en Guichón</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="shortcut icon" href="img/LogoICO.ico" type="image/x-icon">
</head>
<body>

    <header class="header">
        <a href="inicio.php" class="btn-volver">‹</a>
        <h1>Trámites en Guichón</h1>
        <div style="width:24px;"></div>
    </header>

    <div class="main-container" style="margin-bottom: 70px;">

        <input type="text" id="buscador" class="search-bar" placeholder="Buscar trámites en Guichón..." onkeyup="filtrarTramites()">

        <?php if (empty($tramites)): ?>
            <p style="text-align:center; color:var(--texto-mutado); padding: 40px 10px;">
                Todavía no hay trámites cargados.
            </p>
        <?php else: ?>
            <div class="grid-tramites" id="lista-tramites">
                <?php foreach ($tramites as $t): ?>
                    <a href="tramite-detalle.php?id=<?php echo urlencode($t['id']); ?>" class="card-tramite">
                        <img src="<?php echo htmlspecialchars($t['icono'] ?? 'https://via.placeholder.com/50'); ?>"
                             alt="<?php echo htmlspecialchars($t['nombre']); ?>"
                             style="width: 100px; height: 100px;">
                        <span><?php echo htmlspecialchars($t['nombre']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <nav class="bottom-nav">
        <a href="inicio.php" class="nav-item active">🏠</a>
        <a href="asistente.html" class="nav-item">💬</a>
        <a href="configuracion.php" class="nav-item">⚙️</a>
    </nav>

    <script src="script.js"></script>

</body>
</html>
