<?php
// mi-actividad.php
// Pantalla "Mi actividad": muestra los trámites que el usuario finalizó
// (aprobó el test) y los que marcó como favoritos. Corresponde a las
// páginas 5 y 6 de la maquetación ("Finalizados" / "Favoritos").
//
// Para Finalizados, además del nombre y la imagen del trámite se muestra:
//   - cuántas veces lo hizo
//   - la fecha de la última vez que lo hizo
//   - el mejor puntaje que obtuvo
// Esos datos salen del historial de intentos guardado en la tabla
// "finalizados" (ver finalizar_tramite.php y migracion_finalizados_v2.sql).

session_start();
require_once 'conexion.php';
require_once 'auto_login.php';

// Favoritos y finalizados están atados a la cuenta, así que hace falta sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Mapeo tramite_id -> nombre / imagen
$tramites = require 'tramites_datos.php';

function datosTramite($tramites, $tramite_id) {
    if (isset($tramites[$tramite_id])) {
        return $tramites[$tramite_id];
    }
    // Trámite todavía no cargado en tramites_datos.php: mostramos algo
    // razonable en vez de romper la página
    return [
        'nombre' => $tramite_id,
        'imagen' => 'img/Tramites/generico.png',
    ];
}

// ---------- Finalizados ----------
// Traemos todo el historial de intentos aprobados del usuario (más reciente
// primero) y lo agrupamos en PHP por trámite: veces realizado, fecha de la
// última vez y mejor puntaje.
$stmt = $conexion->prepare(
    "SELECT tramite_id, puntaje, fecha_finalizado
     FROM finalizados
     WHERE usuario_id = :usuario_id
     ORDER BY fecha_finalizado DESC"
);
$stmt->bindParam(':usuario_id', $usuario_id);
$stmt->execute();
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

$finalizados = [];
foreach ($historial as $fila) {
    $id = $fila['tramite_id'];

    if (!isset($finalizados[$id])) {
        // El historial viene del más reciente al más viejo, así que la
        // primera fila de cada trámite que encontramos es la última vez
        $finalizados[$id] = [
            'veces'         => 0,
            'fecha_ultima'  => $fila['fecha_finalizado'],
            'mejor_puntaje' => 0,
        ];
    }

    $finalizados[$id]['veces']++;
    $finalizados[$id]['mejor_puntaje'] = max($finalizados[$id]['mejor_puntaje'], (int) $fila['puntaje']);
}

// ---------- Favoritos ----------
$stmt = $conexion->prepare("SELECT tramite_id FROM favoritos WHERE usuario_id = :usuario_id");
$stmt->bindParam(':usuario_id', $usuario_id);
$stmt->execute();
$favoritos = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi actividad - TramiApp</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="shortcut icon" href="img/LogoICO.ico" type="image/x-icon">
</head>
<body>

    <header class="header">
        <a href="inicio.php" class="btn-volver">‹</a>
        <h1>Mi actividad</h1>
        <div style="width:24px;"></div>
    </header>

    <div class="main-container" style="margin-bottom: 70px;">

        <div class="tabs-actividad">
            <div class="tab-actividad" id="tab-finalizados" onclick="mostrarTabActividad('finalizados')">✅ Finalizados</div>
            <div class="tab-actividad" id="tab-favoritos" onclick="mostrarTabActividad('favoritos')">★ Favoritos</div>
        </div>

        <!-- Finalizados -->
        <div class="panel-actividad" id="panel-finalizados">
            <?php if (empty($finalizados)): ?>
                <div class="estado-vacio-actividad">
                    <span>✅</span>
                    Todavía no finalizaste ningún trámite. ¡Animate a hacer tu primer test!
                </div>
            <?php else: ?>
                <div class="lista-actividad">
                    <?php foreach ($finalizados as $tramite_id => $datos):
                        $info = datosTramite($tramites, $tramite_id);
                    ?>
                        <div class="card-actividad">
                            <img src="<?php echo htmlspecialchars($info['imagen']); ?>" alt="<?php echo htmlspecialchars($info['nombre']); ?>">
                            <div class="card-actividad-info">
                                <h4><?php echo htmlspecialchars($info['nombre']); ?></h4>
                                <div class="card-actividad-meta">
                                    <span>🔁 <?php echo $datos['veces']; ?> <?php echo $datos['veces'] == 1 ? 'vez' : 'veces'; ?></span>
                                    <span>🗓️ Última vez: <?php echo date('d/m/Y', strtotime($datos['fecha_ultima'])); ?></span>
                                    <span>🏅 Mejor puntaje: <?php echo $datos['mejor_puntaje']; ?>%</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Favoritos -->
        <div class="panel-actividad" id="panel-favoritos">
            <?php if (empty($favoritos)): ?>
                <div class="estado-vacio-actividad">
                    <span>☆</span>
                    Todavía no marcaste trámites como favoritos.
                </div>
            <?php else: ?>
                <div class="lista-actividad">
                    <?php foreach ($favoritos as $tramite_id):
                        $info = datosTramite($tramites, $tramite_id);
                    ?>
                        <div class="card-actividad">
                            <img src="<?php echo htmlspecialchars($info['imagen']); ?>" alt="<?php echo htmlspecialchars($info['nombre']); ?>">
                            <div class="card-actividad-info">
                                <h4><?php echo htmlspecialchars($info['nombre']); ?></h4>
                            </div>
                            <button type="button" class="btn-quitar-favorito" title="Quitar de favoritos"
                                    onclick="quitarFavoritoActividad(this, '<?php echo htmlspecialchars($tramite_id, ENT_QUOTES); ?>')">★</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <nav class="bottom-nav">
        <a href="inicio.php" class="nav-item">🏠</a>
        <a href="asistente.html" class="nav-item">💬</a>
        <a href="configuracion.php" class="nav-item">⚙️</a>
    </nav>

    <script src="script.js"></script>
</body>
</html>
