<?php
// admin-eliminar-tramite.php
// Borra un trámite y su contenido detallado (tramite_contenido).
//
// IMPORTANTE: no borramos las filas de favoritos/finalizados que lo
// referencian, para no perder el historial de otros usuarios. Si alguien
// tenía este trámite como favorito o finalizado, mi-actividad.php ya
// maneja el caso de un tramite_id que no existe más (ver datosTramite()
// en mi-actividad.php), mostrando el ID como nombre.

session_start();
require_once 'conexion.php';
require_once 'auto_login.php';
require_once 'verificar_editor.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin-tramites.php");
    exit();
}

$id = trim($_POST['id'] ?? '');

if ($id === '') {
    header("Location: admin-tramites.php?error=faltan_datos");
    exit();
}

try {
    $conexion->beginTransaction();

    $stmt = $conexion->prepare("DELETE FROM tramite_contenido WHERE tramite_id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $stmt = $conexion->prepare("DELETE FROM tramites WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $conexion->commit();

    header("Location: admin-tramites.php?eliminado=1");
    exit();
} catch (PDOException $e) {
    $conexion->rollBack();
    header("Location: admin-tramites.php?error=error_servidor");
    exit();
}