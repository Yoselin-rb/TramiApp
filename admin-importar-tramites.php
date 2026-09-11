<?php
// admin-importar-tramites.php
// Recibe un archivo .csv desde admin-tramites.php y carga (o actualiza)
// varios trámites de una sola vez.
//
// Formato esperado del CSV (primera fila = nombres de columna, en este orden):
//   id,nombre,descripcion,modalidad,icono
//
// La columna "icono" debe tener solo el NOMBRE del archivo (ej: "cedula.jpg"),
// no la ruta completa. Ese archivo de imagen tiene que subirse por separado
// (por FTP, o a mano) a la carpeta img/Tramites/ del servidor.

session_start();
require_once 'conexion.php';
require_once 'auto_login.php';
require_once 'verificar_editor.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin-tramites.php");
    exit();
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    header("Location: admin-tramites.php?error=archivo_invalido");
    exit();
}

// Leemos el archivo entero como texto y lo separamos en líneas. Usamos
// fopen/fgetcsv (en vez de leer todo con file()) porque interpreta bien
// las comas que puedan venir DENTRO de un campo entre comillas, algo que
// pasa seguido en la columna "descripcion" si el editor escribe texto largo.
$manejador = fopen($_FILES['archivo']['tmp_name'], 'r');
if ($manejador === false) {
    header("Location: admin-tramites.php?error=archivo_invalido");
    exit();
}

// Primera fila: nombres de columna (ej: ['id','nombre','descripcion','modalidad','icono'])
$encabezados = fgetcsv($manejador);
$encabezados = array_map('trim', $encabezados);

$columnasRequeridas = ['id', 'nombre', 'descripcion', 'modalidad', 'icono'];
$faltanColumnas = array_diff($columnasRequeridas, $encabezados);

if (!empty($faltanColumnas)) {
    fclose($manejador);
    header("Location: admin-tramites.php?error=columnas_faltantes");
    exit();
}

$stmt = $conexion->prepare(
    "INSERT INTO tramites (id, nombre, descripcion, modalidad, icono)
     VALUES (:id, :nombre, :descripcion, :modalidad, :icono)
     ON DUPLICATE KEY UPDATE
        nombre = VALUES(nombre),
        descripcion = VALUES(descripcion),
        modalidad = VALUES(modalidad),
        icono = VALUES(icono)"
);

$modalidadesValidas = ['online', 'presencial', 'ambas'];

$insertados = 0;
$omitidos = 0;

while (($fila = fgetcsv($manejador)) !== false) {
    // Fila vacía (puede pasar en la última línea del archivo) -> la salteamos
    if (count($fila) === 1 && trim($fila[0]) === '') {
        continue;
    }

    // Combinamos encabezados con la fila para poder acceder por nombre,
    // ej: $datos['nombre'] en vez de $fila[1]
    $datos = array_combine($encabezados, $fila);

    $id        = trim($datos['id'] ?? '');
    $nombre    = trim($datos['nombre'] ?? '');
    $descripcion = trim($datos['descripcion'] ?? '');
    $modalidad = trim($datos['modalidad'] ?? 'ambas');
    $icono     = trim($datos['icono'] ?? '');

    // Validamos cada fila individualmente: si una fila viene mal, la
    // saltamos y seguimos con las demás en vez de cortar toda la importación
    if ($id === '' || $nombre === '' || mb_strlen($nombre) > 50 || !preg_match('/^[a-z0-9\-]+$/', $id)) {
    $omitidos++;
    continue;
    }

    if (!in_array($modalidad, $modalidadesValidas, true)) {
        $modalidad = 'ambas';
    }

    $rutaIcono = $icono !== '' ? 'img/Tramites/' . $icono : null;

    try {
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':modalidad', $modalidad);
        $stmt->bindParam(':icono', $rutaIcono);
        $stmt->execute();
        $insertados++;
    } catch (PDOException $e) {
        $omitidos++;
    }
}

fclose($manejador);

header("Location: admin-tramites.php?importados=$insertados" . ($omitidos > 0 ? "&omitidos=$omitidos" : ""));
exit();
