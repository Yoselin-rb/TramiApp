<?php
// tramite-detalle.php
// Página genérica de "dentro del trámite" (ver maquetación, páginas 8 y 9).
// Reemplaza a los archivos individuales como ute-presencial.html: en vez
// de una página por trámite, esta lee el id de la URL (?id=ute-factura)
// y arma todo con los datos que el editor de contenido cargó desde
// admin-tramites.php.

session_start();
require_once 'conexion.php';
require_once 'auto_login.php';

$id = trim($_GET['id'] ?? '');

if ($id === '') {
    header("Location: tramites-guichon.php");
    exit();
}

// Traemos los datos generales del trámite
$stmt = $conexion->prepare("SELECT * FROM tramites WHERE id = :id AND activo = 1");
$stmt->bindParam(':id', $id);
$stmt->execute();
$tramite = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el id no existe (o el trámite está inactivo), no hay nada que mostrar
if (!$tramite) {
    header("Location: tramites-guichon.php?error=no_encontrado");
    exit();
}

// Traemos el contenido detallado (puede haber hasta 2 filas: presencial y online)
$stmt = $conexion->prepare("SELECT * FROM tramite_contenido WHERE tramite_id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$filasContenido = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Las indexamos por modalidad para acceder fácil: $contenido['presencial'], $contenido['online']
$contenido = [];
foreach ($filasContenido as $fila) {
    $contenido[$fila['modalidad']] = $fila;
}

// Qué modalidades mostrar según lo que el trámite admite
$modalidadesDisponibles = [];
if ($tramite['modalidad'] === 'presencial' || $tramite['modalidad'] === 'ambas') {
    $modalidadesDisponibles[] = 'presencial';
}
if ($tramite['modalidad'] === 'online' || $tramite['modalidad'] === 'ambas') {
    $modalidadesDisponibles[] = 'online';
}
// Por si el trámite quedó sin ninguna modalidad válida cargada (no debería pasar)
if (empty($modalidadesDisponibles)) {
    $modalidadesDisponibles = ['presencial'];
}
$modalidadInicial = $modalidadesDisponibles[0];

// Función chica para no repetir el mismo bloque de HTML dos veces (una
// por cada modalidad). Recibe la modalidad y devuelve el texto ya
// preparado para mostrar, resolviendo los "no cargado todavía".
function textoOAviso(?string $texto, string $aviso): string
{
    $texto = trim((string) $texto);
    if ($texto === '') {
        return "<p style='color:var(--texto-mutado); font-style:italic;'>$aviso</p>";
    }
    // nl2br para respetar los saltos de línea que el editor escribió en el textarea
    return nl2br(htmlspecialchars($texto));
}

function listaRequisitos(?string $texto): string
{
    $texto = trim((string) $texto);
    if ($texto === '') {
        return "<p style='color:var(--texto-mutado); font-style:italic;'>Sin requisitos cargados todavía.</p>";
    }
    $lineas = array_filter(array_map('trim', explode("\n", $texto)));
    $html = "<ul style='padding-left:18px;'>";
    foreach ($lineas as $linea) {
        $html .= "<li>" . htmlspecialchars($linea) . "</li>";
    }
    $html .= "</ul>";
    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tramite['nombre']); ?> - TramiApp</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="shortcut icon" href="img/LogoICO.ico" type="image/x-icon">
    <style>
        .video-link-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--fondo-gris);
            border-radius: 10px;
            padding: 12px 15px;
            text-decoration: none;
            color: var(--texto-oscuro);
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }
        .video-link-box:hover {
            background: #e9e9e9;
        }
        .btn-realizar-test {
            display: block;
            text-align: center;
            background-color: var(--verde-principal);
            color: var(--blanco);
            padding: 14px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }
        .btn-realizar-test:hover {
            background-color: var(--verde-oscuro);
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="tramites-guichon.php" class="btn-volver">‹</a>
        <h1>TramiApp</h1>
        <button type="button" id="btn-favorito" class="btn-favorito"
                data-tramite-id="<?php echo htmlspecialchars($tramite['id']); ?>"
                onclick="alternarFavorito()">☆</button>
    </header>

    <div class="main-container" style="margin-bottom: 70px;">

        <div class="hero-section">
            <div class="tramite-header-block">
                <?php if (!empty($tramite['icono'])): ?>
                    <img src="<?php echo htmlspecialchars($tramite['icono']); ?>" alt="<?php echo htmlspecialchars($tramite['nombre']); ?>">
                <?php endif; ?>
                <div class="tramite-header-text">
                    <h2><?php echo htmlspecialchars($tramite['nombre']); ?></h2>
                    <?php
                        // Descripción: preferimos la específica de la modalidad inicial;
                        // si no hay, usamos la descripción general del trámite.
                        $descripcionInicial = $contenido[$modalidadInicial]['descripcion'] ?? '';
                        if (trim($descripcionInicial) === '') {
                            $descripcionInicial = $tramite['descripcion'] ?? '';
                        }
                    ?>
                    <p><?php echo nl2br(htmlspecialchars($descripcionInicial)); ?></p>
                </div>
            </div>
        </div>

        <?php if (count($modalidadesDisponibles) > 1): ?>
            <div class="info-box" style="margin-bottom:20px;">
                <h3>Modalidad</h3>
                <p style="font-size:13px; color:var(--texto-mutado); margin-bottom:10px;">¿Cómo querés realizar tu trámite?</p>
                <div class="modalidad-selector" data-modalidad-inicial="<?php echo $modalidadInicial; ?>">
                    <button type="button" id="btn-modalidad-presencial"
                            class="btn-modalidad<?php echo $modalidadInicial === 'presencial' ? ' active' : ''; ?>"
                            onclick="mostrarModalidadTramite('presencial')">Presencial 📍</button>
                    <button type="button" id="btn-modalidad-online"
                            class="btn-modalidad<?php echo $modalidadInicial === 'online' ? ' active' : ''; ?>"
                            onclick="mostrarModalidadTramite('online')">En línea 💻</button>
                </div>
            </div>
        <?php endif; ?>

        <div class="info-grid">
            <div class="info-box">
                <h3>Requisitos</h3>
                <?php foreach ($modalidadesDisponibles as $mod): ?>
                    <div class="contenido-<?php echo $mod; ?><?php echo $mod !== $modalidadInicial ? ' oculto' : ''; ?>">
                        <?php echo listaRequisitos($contenido[$mod]['requisitos'] ?? ''); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="info-box">
                <h3>¿Dónde se realiza?</h3>
                <?php foreach ($modalidadesDisponibles as $mod): ?>
                    <div class="contenido-<?php echo $mod; ?><?php echo $mod !== $modalidadInicial ? ' oculto' : ''; ?>">
                        <?php echo textoOAviso($contenido[$mod]['donde_texto'] ?? '', 'Sin información cargada todavía.'); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="info-box" style="grid-column: 1 / -1;">
                <h3>¿Cómo se hace?</h3>
                <?php foreach ($modalidadesDisponibles as $mod): ?>
                    <div class="contenido-<?php echo $mod; ?><?php echo $mod !== $modalidadInicial ? ' oculto' : ''; ?>">
                        <?php $video = trim($contenido[$mod]['video_url'] ?? ''); ?>
                        <?php if ($video !== ''): ?>
                            <a href="<?php echo htmlspecialchars($video); ?>" target="_blank" rel="noopener" class="video-link-box">
                                ▶️ Ver video paso a paso
                            </a>
                        <?php else: ?>
                            <p style="color:var(--texto-mutado); font-style:italic;">Todavía no hay un video cargado para este trámite.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="info-box" style="text-align:center; margin-top:20px;">
            <h3>¿Ya hiciste el trámite?</h3>
            <p style="font-size:13px; margin-bottom:15px;">Comprobá lo aprendido con un breve test y marcá este trámite como finalizado.</p>
            <a href="test.php?id=<?php echo urlencode($tramite['id']); ?>" class="btn-comenzar-test" style="text-decoration:none;">Comenzar test ›</a>
        </div>
        
    </div>

    <nav class="bottom-nav">
        <a href="inicio.php" class="nav-item">🏠</a>
        <a href="chat.html" class="nav-item">💬</a>
        <a href="configuracion.php" class="nav-item">⚙️</a>
    </nav>

    <script src="script.js"></script>
</body>
</html>
