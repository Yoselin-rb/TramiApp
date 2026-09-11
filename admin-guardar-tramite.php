<?php
// admin-guardar-tramite.php
// Recibe el POST del formulario de admin-tramites.php, valida los datos,
// guarda la imagen (si se subió una) en img/Tramites/ y guarda el trámite
// en la tabla "tramites".
//
// Si el ID ya existía, ACTUALIZA ese trámite en vez de crear uno nuevo
// (así esta misma pantalla sirve tanto para cargar como para editar).

session_start();
require_once 'conexion.php';
require_once 'auto_login.php';
require_once 'verificar_editor.php';

// Solo aceptamos que este archivo se use enviando el formulario (POST).
// Si alguien intenta entrar a esta URL directo desde el navegador (GET),
// lo mandamos de vuelta al panel.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin-tramites.php");
    exit();
}

// Limpiamos espacios en blanco de los campos de texto
$id          = trim($_POST['id'] ?? '');
$nombre      = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$modalidad   = trim($_POST['modalidad'] ?? 'ambas');

// Validación básica: el ID y el nombre son obligatorios
if ($id === '' || $nombre === '') {
    header("Location: admin-tramites.php?error=faltan_datos");
    exit();
}
// Nuevo: el nombre no puede ser eterno, rompe la tabla y las tarjetas
if (mb_strlen($nombre) > 50) {
    header("Location: admin-tramites.php?error=nombre_muy_largo");
    exit();
}

// El ID solo puede tener minúsculas, números y guiones (ej: "ute-factura").
// Esto es importante porque el mismo ID se usa como clave en favoritos y
// finalizados, y también para armar el nombre del archivo de imagen.
if (!preg_match('/^[a-z0-9\-]+$/', $id)) {
    header("Location: admin-tramites.php?error=id_invalido");
    exit();
}

// La modalidad solo puede ser una de estas tres (por si alguien manipula
// el formulario y manda un valor distinto)
if (!in_array($modalidad, ['online', 'presencial', 'ambas'], true)) {
    $modalidad = 'ambas';
}

// ---------- Imagen ----------
// Si el editor subió un archivo, lo validamos y lo guardamos en
// img/Tramites/ usando el ID del trámite como nombre de archivo (así
// queda prolijo y si se vuelve a cargar el mismo trámite, se reemplaza
// la imagen anterior en vez de acumular archivos sueltos).
$rutaIcono = null;

if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
    $tipoMime = mime_content_type($_FILES['icono']['tmp_name']);

    $extensionesPermitidas = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensionesPermitidas[$tipoMime])) {
        header("Location: admin-tramites.php?error=imagen_invalida");
        exit();
    }

    $extension = $extensionesPermitidas[$tipoMime];
    $nombreArchivo = $id . '.' . $extension;
    $carpetaDestino = __DIR__ . '/img/Tramites/';

    // Por si la carpeta todavía no existe en este servidor
    if (!is_dir($carpetaDestino)) {
        mkdir($carpetaDestino, 0755, true);
    }

    if (!move_uploaded_file($_FILES['icono']['tmp_name'], $carpetaDestino . $nombreArchivo)) {
        header("Location: admin-tramites.php?error=error_servidor");
        exit();
    }

    $rutaIcono = 'img/Tramites/' . $nombreArchivo;
}

// ---------- Guardar en la base de datos ----------
try {
    if ($rutaIcono !== null) {
        // Se subió una imagen nueva: actualizamos también el ícono
        $stmt = $conexion->prepare(
            "INSERT INTO tramites (id, nombre, descripcion, modalidad, icono)
             VALUES (:id, :nombre, :descripcion, :modalidad, :icono)
             ON DUPLICATE KEY UPDATE
                nombre = VALUES(nombre),
                descripcion = VALUES(descripcion),
                modalidad = VALUES(modalidad),
                icono = VALUES(icono)"
        );
        $stmt->bindParam(':icono', $rutaIcono);
    } else {
        // No se subió imagen nueva: si el trámite ya existía, dejamos su
        // ícono anterior tal cual (no lo pisamos con un valor vacío)
        $stmt = $conexion->prepare(
            "INSERT INTO tramites (id, nombre, descripcion, modalidad)
             VALUES (:id, :nombre, :descripcion, :modalidad)
             ON DUPLICATE KEY UPDATE
                nombre = VALUES(nombre),
                descripcion = VALUES(descripcion),
                modalidad = VALUES(modalidad)"
        );
    }

    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':modalidad', $modalidad);
    $stmt->execute();

    // ---------- Contenido detallado por modalidad ----------
    // Guardamos el bloque "Presencial" si el trámite admite esa modalidad
    // (o "ambas") y si el editor completó algo; lo mismo para "Online".
    // Si el editor dejó todo el bloque vacío, no guardamos una fila vacía.
    if ($modalidad === 'presencial' || $modalidad === 'ambas') {
        guardarContenidoModalidad($conexion, $id, 'presencial', $_POST, 'presencial');
    }
    if ($modalidad === 'online' || $modalidad === 'ambas') {
        guardarContenidoModalidad($conexion, $id, 'online', $_POST, 'online');
    }

    header("Location: admin-tramites.php?guardado=1");
    exit();
} catch (PDOException $e) {
    header("Location: admin-tramites.php?error=error_servidor");
    exit();
}

/**
 * Guarda (o actualiza) el contenido detallado de una modalidad puntual de
 * un trámite, leyendo los campos "descripcion_$sufijo", "requisitos_$sufijo",
 * "donde_$sufijo" y "video_$sufijo" del formulario.
 *
 * Si el editor no completó ninguno de esos 4 campos, no se guarda nada
 * (para no llenar la base con filas vacías).
 */
function guardarContenidoModalidad(PDO $conexion, string $tramiteId, string $modalidad, array $post, string $sufijo): void
{
    $descripcion = trim($post["descripcion_$sufijo"] ?? '');
    $requisitos  = trim($post["requisitos_$sufijo"] ?? '');
    $donde       = trim($post["donde_$sufijo"] ?? '');
    $video       = trim($post["video_$sufijo"] ?? '');

    // Si los 4 campos están vacíos, no hay nada que guardar para esta modalidad
    if ($descripcion === '' && $requisitos === '' && $donde === '' && $video === '') {
        return;
    }

    $stmt = $conexion->prepare(
        "INSERT INTO tramite_contenido (tramite_id, modalidad, descripcion, requisitos, donde_texto, video_url)
         VALUES (:tramite_id, :modalidad, :descripcion, :requisitos, :donde_texto, :video_url)
         ON DUPLICATE KEY UPDATE
            descripcion = VALUES(descripcion),
            requisitos = VALUES(requisitos),
            donde_texto = VALUES(donde_texto),
            video_url = VALUES(video_url)"
    );
    $stmt->bindParam(':tramite_id', $tramiteId);
    $stmt->bindParam(':modalidad', $modalidad);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':requisitos', $requisitos);
    $stmt->bindParam(':donde_texto', $donde);
    $stmt->bindParam(':video_url', $video);
    $stmt->execute();
}