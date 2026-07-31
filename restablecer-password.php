<?php
session_start();
require_once 'conexion.php';

// Solo se puede llegar acá si ya se verificó el código correctamente
if (!isset($_SESSION['recuperacion_verificado']) || $_SESSION['recuperacion_verificado'] !== true) {
    header("Location: olvide-password.php");
    exit();
}

$mensaje = "";
$usuario_id = $_SESSION['recuperacion_usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $password_confirmar = $_POST['password_confirmar'];

    if (empty($password) || empty($password_confirmar)) {
        $mensaje = "<div class='mensaje error'>Por favor, completa todos los campos.</div>";
    } elseif ($password !== $password_confirmar) {
        $mensaje = "<div class='mensaje error'>Las contraseñas no coinciden.</div>";
    } else {
        // Mismos requisitos que en registro.php
        $largo = strlen($password);
        $tiene_mayuscula = preg_match('/[A-Z]/', $password);
        $tiene_numero = preg_match('/[0-9]/', $password);

        if ($largo < 8 || $largo > 16 || !$tiene_mayuscula || !$tiene_numero) {
            $mensaje = "<div class='mensaje error'>La contraseña debe tener entre 8 y 16 caracteres, al menos una mayúscula y al menos un número.</div>";
        } else {
            $password_cifrada = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conexion->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
            $stmt->bindParam(':password', $password_cifrada);
            $stmt->bindParam(':id', $usuario_id);
            $stmt->execute();

            // Por seguridad, invalidamos cualquier "recordar usuario" de otros dispositivos
            $stmt = $conexion->prepare("DELETE FROM tokens_recordar WHERE usuario_id = :id");
            $stmt->bindParam(':id', $usuario_id);
            $stmt->execute();

            // Limpiamos los datos temporales de recuperación
            unset($_SESSION['recuperacion_usuario_id']);
            unset($_SESSION['recuperacion_verificado']);

            header("Location: login.php?password_restablecida=1");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - TramiApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Crear Nueva Contraseña</h2>

        <?php echo $mensaje; ?>

        <form action="restablecer-password.php" method="POST">
            <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required
                           pattern="(?=.*\d)(?=.*[A-Z]).{8,16}"
                           title="Debe tener entre 8 y 16 caracteres, incluir al menos una mayúscula y un número.">
                    <span class="toggle-password" onclick="togglePassword('password', this)">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12C1 12 5 5 12 5C19 5 23 12 23 12C23 12 19 19 12 19C5 19 1 12 1 12Z" stroke="#666666" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="#666666" stroke-width="1.6"/>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label for="password_confirmar">Confirmar Nueva Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="password_confirmar" name="password_confirmar" required>
                    <span class="toggle-password" onclick="togglePassword('password_confirmar', this)">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12C1 12 5 5 12 5C19 5 23 12 23 12C23 12 19 19 12 19C5 19 1 12 1 12Z" stroke="#666666" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="#666666" stroke-width="1.6"/>
                        </svg>
                    </span>
                </div>
            </div>
            <button type="submit">Guardar nueva contraseña</button>
        </form>
    </div>
    <script src="script.js"></script>
</body>
</html>
