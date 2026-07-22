<?php
// Incluimos el archivo de conexión
require_once 'conexion.php';

$mensaje = "";

// Verificamos si el formulario fue enviado mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturamos los datos del formulario y eliminamos espacios en blanco innecesarios
    $nombre           = trim($_POST['nombre']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $correo           = trim($_POST['correo']);
    $password         = $_POST['password'];
    $password_confirmar = $_POST['password_confirmar'];

    // Validación básica en el servidor
    if (empty($nombre) || empty($fecha_nacimiento) || empty($correo) || empty($password) || empty($password_confirmar)) {

        $mensaje = "<div class='mensaje error'>Por favor, completa todos los campos.</div>";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "<div class='mensaje error'>El correo ingresado no es válido.</div>";

    } elseif ($password !== $password_confirmar) {

        $mensaje = "<div class='mensaje error'>Las contraseñas no coinciden.</div>";

    } else {

        // Validamos requisitos de la contraseña (entre 8 y 16 caracteres, una mayúscula, un número)
        $largo = strlen($password);
        $tiene_mayuscula = preg_match('/[A-Z]/', $password);
        $tiene_numero = preg_match('/[0-9]/', $password);

        if ($largo < 8 || $largo > 16 || !$tiene_mayuscula || !$tiene_numero) {

            $mensaje = "<div class='mensaje error'>La contraseña debe tener entre 8 y 16 caracteres, al menos una mayúscula y al menos un número.</div>";

        } else {

            // Ciframos la contraseña usando la función segura password_hash
            $password_cifrada = password_hash($password, PASSWORD_BCRYPT);

            try {
                // Preparamos la consulta SQL para evitar inyecciones SQL
                $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, fecha_nacimiento, correo, password) VALUES (:nombre, :fecha_nacimiento, :correo, :password)");

                // Vinculamos los parámetros con las variables de PHP
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':password', $password_cifrada);

                // Ejecutamos la consulta
                if ($stmt->execute()) {
                    $mensaje = "<div class='mensaje exito'>¡Usuario registrado con éxito! <a href='login.php'>Inicia sesión aquí</a></div>";
                }
            } catch (PDOException $e) {
                // Manejamos el caso de que el correo ya esté registrado (error por duplicado)
                if ($e->getCode() == 23000) {
                    $mensaje = "<div class='mensaje error'>El correo ya se encuentra registrado.</div>";
                } else {
                    $mensaje = "<div class='mensaje error'>Error al registrar el usuario.</div>";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - TramiApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Crear Cuenta</h2>

        <!-- Mostrar mensajes si existen -->
        <?php echo $mensaje; ?>

        <!-- Formulario que envía los datos a sí mismo (registro.php) -->
        <form action="registro.php" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
            </div>
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required
                       pattern="(?=.*\d)(?=.*[A-Z]).{8,16}"
                       title="Debe tener entre 8 y 16 caracteres, incluir al menos una mayúscula y un número.">
            </div>
            <div class="form-group">
                <label for="password_confirmar">Confirmar Contraseña</label>
                <input type="password" id="password_confirmar" name="password_confirmar" required>
            </div>
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>
</body>
</html>
