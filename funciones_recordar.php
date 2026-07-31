<?php
// funciones_recordar.php
// Funciones compartidas para la función "recordar usuario".
// Incluir con require_once donde se necesite (login.php, registro.php, logout.php).

/**
 * Crea un token de "recordar usuario", lo guarda hasheado en la BD
 * y setea la cookie en el navegador del usuario.
 */
function crearTokenRecordar($conexion, $usuario_id) {
    // Token aleatorio y seguro
    $tokenOriginal = bin2hex(random_bytes(32));
    $tokenHash = password_hash($tokenOriginal, PASSWORD_BCRYPT);

    // Vence en 30 días
    $expiracion = date('Y-m-d H:i:s', strtotime('+30 days'));

    $stmt = $conexion->prepare("INSERT INTO tokens_recordar (usuario_id, token_hash, fecha_expiracion) VALUES (:usuario_id, :token_hash, :fecha_expiracion)");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':token_hash', $tokenHash);
    $stmt->bindParam(':fecha_expiracion', $expiracion);
    $stmt->execute();

    $tokenId = $conexion->lastInsertId();

    // La cookie guarda "idDelRegistro:tokenOriginal" (el hash nunca sale de la BD)
    $valorCookie = $tokenId . ':' . $tokenOriginal;

    // httponly=true evita que JavaScript pueda leer la cookie (más seguro)
    // secure=true solo si tu sitio corre con HTTPS (cambialo a true en producción)
    setcookie('recordar_token', $valorCookie, time() + (30 * 24 * 60 * 60), '/', '', false, true);
}

/**
 * Borra el token de "recordar usuario" tanto de la BD como del navegador.
 * Se usa al cerrar sesión.
 */
function borrarTokenRecordar($conexion) {
    if (isset($_COOKIE['recordar_token'])) {
        $partes = explode(':', $_COOKIE['recordar_token'], 2);
        if (count($partes) === 2) {
            $tokenId = $partes[0];
            $stmt = $conexion->prepare("DELETE FROM tokens_recordar WHERE id = :id");
            $stmt->bindParam(':id', $tokenId);
            $stmt->execute();
        }
        // Expiramos la cookie en el navegador
        setcookie('recordar_token', '', time() - 3600, '/');
    }
}
