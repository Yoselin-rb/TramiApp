<?php
// Iniciamos la sesión para poder guardar datos del usuario logueado
session_start();

// Incluimos la conexión a la base de datos
require_once 'conexion.php';

$mensaje = "";

// Verificamos si el formulario se envió por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    if (!empty($correo) && !empty($password)) {
        try {
            // Buscamos al usuario por su correo electrónico
            $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = :correo");
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();

            // Obtenemos los datos del usuario si existe
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificamos si encontramos al usuario y si la contraseña coincide
            if ($usuario && password_verify($password, $usuario['password'])) {
                // Guardamos los datos de sesión del usuario
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];

                // Redirigimos a una página de bienvenida o dashboard
                header("Location: inicio.php");
                exit();
            } else {
                $mensaje = "<div class='mensaje error'>El correo o la contraseña son incorrectos.</div>";
            }
        } catch (PDOException $e) {
            $mensaje = "<div class='mensaje error'>Ocurrió un error en el servidor.</div>";
        }
    } else {
        $mensaje = "<div class='mensaje error'>Por favor, rellena todos los campos.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TramiApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>

        <!-- Mostrar mensajes de error en caso de fallo -->
        <?php echo $mensaje; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required>
                    <span class="toggle-password" onclick="togglePassword('password', this)">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12C1 12 5 5 12 5C19 5 23 12 23 12C23 12 19 19 12 19C5 19 1 12 1 12Z" stroke="#666666" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="3" stroke="#666666" stroke-width="1.6"/>
                        </svg>
                    </span>
                </div>
            </div>
            <button type="submit">Entrar</button>
        </form>
        <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
    </div>
    <script src="script.js"></script>
</body>
</html>