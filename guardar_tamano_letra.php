<?php
// guardar_tamano_letra.php
// Recibe (POST, JSON) el nivel de tamaño de letra elegido por el usuario:
//   1 = Pequeño, 2 = Mediano, 3 = Grande
//
// - Si hay sesión iniciada, lo guarda también en la base de datos (columna
//   usuarios.tamano_letra) para que quede asociado a la cuenta y se recupere
//   en cualquier dispositivo la próxima vez que inicie sesión.
// - Siempre se guarda además en una cookie, para que las páginas .html
//   (que no son PHP) también puedan leerlo con JavaScript.

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'conexion.php';
require_once 'auto_login.php';

$datos = json_decode(file_get_contents('php://input'), true);
$tamano = isset($datos['tamano']) ? (int) $datos['tamano'] : 0;

// Solo aceptamos 1, 2 o 3
if ($tamano < 1 || $tamano > 3) {
    http_response_code(400);
    echo json_encode(['error' => 'valor_invalido']);
    exit();
}

// Si el usuario tiene sesión iniciada, guardamos la preferencia en su cuenta
if (isset($_SESSION['usuario_id'])) {
    try {
        $stmt = $conexion->prepare("UPDATE usuarios SET tamano_letra = :tamano WHERE id = :id");
        $stmt->bindParam(':tamano', $tamano, PDO::PARAM_INT);
        $stmt->bindParam(':id', $_SESSION['usuario_id']);
        $stmt->execute();

        $_SESSION['tamano_letra'] = $tamano;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'error_servidor']);
        exit();
    }
}

// Cookie por 1 año, legible por JavaScript (sin httponly) para que también
// funcione en las páginas .html estáticas del sitio.
setcookie('tamano_letra', (string) $tamano, time() + (365 * 24 * 60 * 60), '/');

echo json_encode(['ok' => true, 'tamano' => $tamano]);
