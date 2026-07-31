<?php
session_start();
require_once 'conexion.php';

// Si no vino desde olvide-password.php, lo mandamos de nuevo ahí
if (!isset($_SESSION['recuperacion_usuario_id'])) {
    header("Location: olvide-password.php");
    exit();
}

$mensaje = "";
$usuario_id = $_SESSION['recuperacion_usuario_id'];
$maximo_intentos = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoIngresado = trim($_POST['codigo']);

    // Tomamos el código más reciente sin usar de este usuario
    $stmt = $conexion->prepare("SELECT * FROM codigos_recuperacion WHERE usuario_id = :usuario_id AND usado = 0 ORDER BY fecha_creado DESC LIMIT 1");
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registro || strtotime($registro['fecha_expiracion']) < time()) {
        $mensaje = "<div class='mensaje error'>El código venció. Solicitá uno nuevo.</div>";
    } elseif ($registro['intentos'] >= $maximo_intentos) {
        $mensaje = "<div class='mensaje error'>Superaste el número de intentos permitidos. Solicitá un código nuevo.</div>";
    } elseif (password_verify($codigoIngresado, $registro['codigo_hash'])) {
        // Código correcto: lo marcamos como usado y habilitamos el cambio de contraseña
        $stmt = $conexion->prepare("UPDATE codigos_recuperacion SET usado = 1 WHERE id = :id");
        $stmt->bindParam(':id', $registro['id']);
        $stmt->execute();

        $_SESSION['recuperacion_verificado'] = true;
        header("Location: restablecer-password.php");
        exit();
    } else {
        $stmt = $conexion->prepare("UPDATE codigos_recuperacion SET intentos = intentos + 1 WHERE id = :id");
        $stmt->bindParam(':id', $registro['id']);
        $stmt->execute();
        $mensaje = "<div class='mensaje error'>El código es incorrecto. Intentá de nuevo.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - TramiApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Verificar Código</h2>
        <p style="text-align:center; font-size:14px; color:#666; margin-bottom:15px;">
            Ingresá el código de 6 dígitos que te enviamos por correo. Vence en 10 minutos.
        </p>

        <?php echo $mensaje; ?>

        <form action="verificar-codigo.php" method="POST">
            <div class="form-group">
                <label for="codigo">Código de verificación</label>
                <input type="text" id="codigo" name="codigo" inputmode="numeric" pattern="[0-9]{6}"
                       maxlength="6" required
                       style="text-align:center; font-size:22px; letter-spacing:8px;">
            </div>
            <button type="submit">Verificar</button>
        </form>
        <p><a href="olvide-password.php">Reenviar código</a></p>
    </div>
</body>
</html>
