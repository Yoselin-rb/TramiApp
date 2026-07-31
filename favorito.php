<?php
// favorito.php
// Endpoint que recibe peticiones desde script.js (fetch) para:
//  - alternar (agregar/quitar) un trámite como favorito
//  - consultar si un trámite ya es favorito del usuario logueado
//
// Devuelve siempre JSON.

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'conexion.php';
require_once 'auto_login.php';

// El usuario debe estar logueado para usar favoritos
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'no_sesion']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Aceptamos tanto GET (para consultar estado) como POST (para alternar)
$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $tramite_id = isset($_GET['tramite_id']) ? trim($_GET['tramite_id']) : '';

    if ($tramite_id === '') {
        http_response_code(400);
        echo json_encode(['error' => 'falta_tramite_id']);
        exit();
    }

    $stmt = $conexion->prepare("SELECT COUNT(*) FROM favoritos WHERE usuario_id = :usuario_id AND tramite_id = :tramite_id");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':tramite_id', $tramite_id);
    $stmt->execute();
    $esFavorito = $stmt->fetchColumn() > 0;

    echo json_encode(['favorito' => $esFavorito]);
    exit();
}

if ($metodo === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $tramite_id = isset($datos['tramite_id']) ? trim($datos['tramite_id']) : '';

    if ($tramite_id === '') {
        http_response_code(400);
        echo json_encode(['error' => 'falta_tramite_id']);
        exit();
    }

    try {
        // Verificamos si ya es favorito
        $stmt = $conexion->prepare("SELECT id FROM favoritos WHERE usuario_id = :usuario_id AND tramite_id = :tramite_id");
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':tramite_id', $tramite_id);
        $stmt->execute();
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            // Ya era favorito -> lo quitamos
            $stmt = $conexion->prepare("DELETE FROM favoritos WHERE id = :id");
            $stmt->bindParam(':id', $existente['id']);
            $stmt->execute();
            $esFavorito = false;
        } else {
            // No era favorito -> lo agregamos
            $stmt = $conexion->prepare("INSERT INTO favoritos (usuario_id, tramite_id) VALUES (:usuario_id, :tramite_id)");
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':tramite_id', $tramite_id);
            $stmt->execute();
            $esFavorito = true;
        }

        // Contamos el total de favoritos del usuario para actualizar el contador de inicio.php si hace falta
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM favoritos WHERE usuario_id = :usuario_id");
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $total = $stmt->fetchColumn();

        echo json_encode(['favorito' => $esFavorito, 'total' => (int)$total]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'error_servidor']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'metodo_no_permitido']);