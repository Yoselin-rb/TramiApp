<?php
// admin-cambiar-estado.php
// Alterna el campo "activo" de un trámite (1 <-> 0). Se usa para ocultar
// de tramites-guichon.php un trámite que todavía no está listo o que era
// solo de prueba, sin necesidad de borrarlo.

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
    $stmt = $conexion->prepare("UPDATE tramites SET activo = NOT activo WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header("Location: admin-tramites.php?estado_cambiado=1");
    exit();
} catch (PDOException $e) {
    header("Location: admin-tramites.php?error=error_servidor");
    exit();
}