<?php
// finalizar_tramite.php
// Endpoint que recibe peticiones desde script.js (fetch) para:
//  - marcar un trámite como finalizado cuando el usuario aprueba el test
//  - consultar si un trámite ya fue finalizado por el usuario logueado
//
// Es el equivalente de favorito.php pero para la sección "Finalizados".
// Devuelve siempre JSON.

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'conexion.php';
require_once 'auto_login.php';

// El usuario debe estar logueado para que su avance quede guardado en la cuenta
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'no_sesion']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $tramite_id = isset($_GET['tramite_id']) ? trim($_GET['tramite_id']) : '';

    if ($tramite_id === '') {
        http_response_code(400);
        echo json_encode(['error' => 'falta_tramite_id']);
        exit();
    }

    $stmt = $conexion->prepare("SELECT COUNT(*) FROM finalizados WHERE usuario_id = :usuario_id AND tramite_id = :tramite_id");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':tramite_id', $tramite_id);
    $stmt->execute();
    $estaFinalizado = $stmt->fetchColumn() > 0;

    echo json_encode(['finalizado' => $estaFinalizado]);
    exit();
}

if ($metodo === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $tramite_id = isset($datos['tramite_id']) ? trim($datos['tramite_id']) : '';
    $correctas = isset($datos['correctas']) ? (int) $datos['correctas'] : 0;
    $total = isset($datos['total']) ? (int) $datos['total'] : 0;

    if ($tramite_id === '' || $total <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'datos_invalidos']);
        exit();
    }

    // Solo se guarda como finalizado si aprobó el test (al menos 75% correcto,
    // igual que en la maqueta: 4 de 4 = "Aprobaste el test")
    $aprobo = $correctas >= ceil($total * 0.75);

    if (!$aprobo) {
        echo json_encode(['finalizado' => false, 'aprobo' => false]);
        exit();
    }

    try {
        // INSERT IGNORE porque el usuario puede repetir el test luego de haberlo
        // aprobado antes; no queremos duplicar el registro (unique key usuario+tramite)
        $stmt = $conexion->prepare(
            "INSERT IGNORE INTO finalizados (usuario_id, tramite_id) VALUES (:usuario_id, :tramite_id)"
        );
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':tramite_id', $tramite_id);
        $stmt->execute();

        // Contamos el total de trámites finalizados del usuario para actualizar
        // el contador de inicio.php si hace falta
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM finalizados WHERE usuario_id = :usuario_id");
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $total_finalizados = $stmt->fetchColumn();

        echo json_encode(['finalizado' => true, 'aprobo' => true, 'total' => (int) $total_finalizados]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'error_servidor']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'metodo_no_permitido']);
