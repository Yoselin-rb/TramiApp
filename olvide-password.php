<?php
session_start();
require_once 'conexion.php';
require_once 'enviar_correo.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);

    if (empty($correo)) {
        $mensaje = "<div class='mensaje error'>Por favor, ingresá tu correo electrónico.</div>";
    } else {
        $stmt = $conexion->prepare("SELECT id, nombre FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Por seguridad, no revelamos si el correo existe o no en la base de datos
        if ($usuario) {
            // Generamos un código de 6 dígitos
            $codigo = strval(random_int(100000, 999999));
            $codigoHash = password_hash($codigo, PASSWORD_BCRYPT);
            $expiracion = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $stmt = $conexion->prepare("INSERT INTO codigos_recuperacion (usuario_id, codigo_hash, fecha_expiracion) VALUES (:usuario_id, :codigo_hash, :fecha_expiracion)");
            $stmt->bindParam(':usuario_id', $usuario['id']);
            $stmt->bindParam(':codigo_hash', $codigoHash);
            $stmt->bindParam(':fecha_expiracion', $expiracion);
            $stmt->execute();

            enviarCodigoRecuperacion($correo, $usuario['nombre'], $codigo);

            $_SESSION['recuperacion_usuario_id'] = $usuario['id'];
            $_SESSION['recuperacion_verificado'] = false;

            header("Location: verificar-codigo.php");
            exit();
        }

        // Mismo mensaje exista o no el correo, para no dar pistas a un atacante
        $mensaje = "<div class='mensaje exito'>Si el correo está registrado, te enviamos un código de verificación.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - TramiApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Recuperar Contraseña</h2>
        <p style="text-align:center; font-size:14px; color:#666; margin-bottom:15px;">
            Ingresá el correo con el que te registraste y te mandamos un código de verificación.
        </p>

        <?php echo $mensaje; ?>

        <form action="olvide-password.php" method="POST">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            <button type="submit">Enviar código</button>
        </form>
        <p><a href="login.php">Volver a iniciar sesión</a></p>
    </div>
</body>
</html>
