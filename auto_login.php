<?php
// auto_login.php
// IMPORTANTE: incluir con require_once DESPUÉS de session_start() y de conexion.php
// (por ejemplo, al principio de inicio.php, configuracion.php, bienvenida.php, favorito.php)
//
// Si el usuario no tiene sesión activa pero sí una cookie válida de
// "recordar usuario", restauramos su sesión automáticamente.

if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['recordar_token'])) {

    // La cookie tiene el formato "idDelRegistro:tokenOriginal"
    $partes = explode(':', $_COOKIE['recordar_token'], 2);

    if (count($partes) === 2) {
        list($tokenId, $tokenOriginal) = $partes;

        $stmt = $conexion->prepare(
            "SELECT tr.usuario_id, tr.token_hash, tr.fecha_expiracion, u.nombre
             FROM tokens_recordar tr
             INNER JOIN usuarios u ON u.id = tr.usuario_id
             WHERE tr.id = :id"
        );
        $stmt->bindParam(':id', $tokenId);
        $stmt->execute();
        $registro = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($registro && strtotime($registro['fecha_expiracion']) > time() && password_verify($tokenOriginal, $registro['token_hash'])) {
            // Token válido: restauramos la sesión sin pedir contraseña
            $_SESSION['usuario_id'] = $registro['usuario_id'];
            $_SESSION['usuario_nombre'] = $registro['nombre'];
        } else {
            // Token inválido, vencido o inexistente: limpiamos la cookie por seguridad
            setcookie('recordar_token', '', time() - 3600, '/');
        }
    }
}
