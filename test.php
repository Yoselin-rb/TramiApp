<?php
// test.php
// Pantalla de test dinámica para cualquier trámite. Reemplaza a los antiguos
// archivos estáticos (test-ute.html, etc.): lee el trámite y sus preguntas
// desde la base de datos (tabla tramite_preguntas).

session_start();
require_once 'conexion.php';
require_once 'auto_login.php';

$id = trim($_GET['id'] ?? '');

if ($id === '') {
    header("Location: tramites-guichon.php");
    exit();
}

$stmt = $conexion->prepare("SELECT * FROM tramites WHERE id = :id AND activo = 1");
$stmt->bindParam(':id', $id);
$stmt->execute();
$tramite = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tramite) {
    header("Location: tramites-guichon.php?error=no_encontrado");
    exit();
}

$stmt = $conexion->prepare("SELECT * FROM tramite_preguntas WHERE tramite_id = :id ORDER BY orden ASC, id ASC");
$stmt->bindParam(':id', $id);
$stmt->execute();
$preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mismo formato que ya espera MAPA_TESTS en script.js
$preguntasJs = array_map(function ($p) {
    return [
        'texto' => $p['texto'],
        'opciones' => [$p['opcion_a'], $p['opcion_b'], $p['opcion_c'], $p['opcion_d']],
        'correcta' => (int) $p['correcta'],
    ];
}, $preguntas);

$totalPreguntas = count($preguntasJs);
$minutosEstimados = max(1, (int) ceil($totalPreguntas * 0.5));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - <?php echo htmlspecialchars($tramite['nombre']); ?></title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="shortcut icon" href="img/LogoICO.ico" type="image/x-icon">
</head>
<body>

    <header class="header">
        <a href="tramite-detalle.php?id=<?php echo urlencode($id); ?>" class="btn-volver">‹</a>
        <h1>Trami<span>App</span></h1>
        <div style="width:24px;"></div>
    </header>

    <div class="main-container-test">

        <?php if ($totalPreguntas < 4): ?>

            <div class="mensaje error">
                Este trámite todavía no tiene un test disponible. Volvé a intentarlo más tarde.
            </div>
            <a href="tramite-detalle.php?id=<?php echo urlencode($id); ?>" class="link-volver-inicio-test">
                ‹ Volver al trámite
            </a>

        <?php else: ?>

            <!-- Pantalla 1: introducción -->
            <div id="test-intro">
                <p class="test-intro-texto">Para comprobar lo aprendido, responde este breve test.</p>

                <div class="test-card">
                    <div class="test-card-titulo"><?php echo htmlspecialchars($tramite['nombre']); ?></div>
                    <div class="test-card-cuerpo">
                        <div class="test-meta-fila">
                            <span>🕒</span>
                            <span>Tiempo estimado: <?php echo $minutosEstimados; ?> - <?php echo $minutosEstimados + 1; ?> minutos</span>
                        </div>
                        <div class="test-meta-fila">
                            <span>❓</span>
                            <span>Preguntas: <?php echo $totalPreguntas; ?></span>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn-comenzar-test" data-tramite-id="<?php echo htmlspecialchars($id); ?>" onclick="comenzarTest(this)">
                    Comenzar test <span>›</span>
                </button>
            </div>

            <!-- Pantalla 2: preguntas -->
            <div id="test-quiz" class="oculto">
                <div class="test-progress-header">
                    <span>Evaluación rápida</span>
                    <span id="test-contador">Pregunta 1 de <?php echo $totalPreguntas; ?></span>
                </div>
                <div id="test-barra-progreso" class="test-progress-bar"></div>

                <p id="test-texto-pregunta" class="test-pregunta"></p>

                <div id="test-opciones" class="test-opciones"></div>

                <div class="test-nav-botones">
                    <button type="button" id="btn-test-anterior" class="btn-test-anterior" onclick="irPreguntaAnteriorTest()">
                        ‹ Anterior
                    </button>
                    <button type="button" id="btn-test-siguiente" class="btn-test-siguiente" onclick="irSiguientePreguntaTest()">
                        Siguiente ›
                    </button>
                </div>
            </div>

            <!-- Pantalla 3: resultado -->
            <div id="test-resultado" class="test-resultado oculto">
                <div id="test-resultado-icono" class="test-resultado-icono">👍</div>
                <h2 id="test-resultado-titulo" class="test-resultado-titulo">¡Excelente!</h2>
                <span id="test-resultado-subtitulo" class="test-subtitulo-aprobado">Aprobaste el test</span>

                <div class="test-resultado-card">
                    <div class="test-resultado-fila">
                        <span>🏅</span>
                        <span id="test-resultado-correctas">Respuestas correctas: 0 de <?php echo $totalPreguntas; ?></span>
                    </div>
                    <div id="test-resultado-fila-estado" class="test-resultado-fila">
                        <span>✅</span>
                        <span>Trámite finalizado</span>
                    </div>
                </div>

                <p id="test-resultado-aviso-sesion" class="test-resultado-aviso oculto">
                    Iniciá sesión para que este avance quede guardado en tu cuenta.
                </p>

                <button type="button" id="btn-test-reintentar" class="btn-test-reintentar" style="display:none;" onclick="reintentarTest()">
                    Volver a intentar
                </button>

                <p class="test-resultado-ayuda">Si te quedaron dudas, consulta nuestro ChatBot.</p>

                <a href="asistente.html" class="btn-test-chatbot">ChatBot</a>
                <a href="tramite-detalle.php?id=<?php echo urlencode($id); ?>" class="link-volver-inicio-test">Volver al trámite</a>
            </div>

        <?php endif; ?>

    </div>

    <nav class="bottom-nav">
        <a href="inicio.php" class="nav-item">🏠</a>
        <a href="asistente.html" class="nav-item">💬</a>
        <a href="configuracion.php" class="nav-item">⚙️</a>
    </nav>

    <script src="script.js"></script>
    <?php if ($totalPreguntas >= 4): ?>
    <script>
        // Sumamos las preguntas de este trámite al banco que ya usa script.js
        // (MAPA_TESTS), así comenzarTest(), finalizarTest(), etc. funcionan
        // sin tocarles una línea — igual que con el test estático de UTE.
        MAPA_TESTS['<?php echo addslashes($id); ?>'] = <?php echo json_encode($preguntasJs, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <?php endif; ?>

</body>
</html>