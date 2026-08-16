<?php
// Incluimos el archivo de conexión
require_once 'conexion.php';
require_once 'funciones_recordar.php';

// Iniciamos la sesión para poder loguear automáticamente al usuario
session_start();

$mensaje = "";

// Verificamos si el formulario fue enviado mediante el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturamos los datos del formulario y eliminamos espacios en blanco innecesarios
    $nombre           = trim($_POST['nombre']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $correo           = trim($_POST['correo']);
    $password         = $_POST['password'];
    $password_confirmar = $_POST['password_confirmar'];
    $recordar         = isset($_POST['recordar']); // checkbox marcado por defecto

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
                    // Obtenemos el id recién generado por el INSERT
                    $nuevoId = $conexion->lastInsertId();

                    // Guardamos los datos de sesión, igual que hace login.php
                    $_SESSION['usuario_id'] = $nuevoId;
                    $_SESSION['usuario_nombre'] = $nombre;

                    // Tamaño de letra por defecto (Mediano) para la cuenta recién creada
                    $_SESSION['tamano_letra'] = 2;
                    setcookie('tamano_letra', '2', time() + (365 * 24 * 60 * 60), '/');

                    // Si dejó tildado "recordar usuario", creamos el token persistente
                    if ($recordar) {
                        crearTokenRecordar($conexion, $nuevoId);
                    }

                    // Redirigimos directo a inicio.php, sin pasar por login
                    header("Location: inicio.php");
                    exit();
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
                <label for="password_confirmar">Confirmar Contraseña</label>
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

            <div class="form-group" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" id="recordar" name="recordar" checked style="width:auto; cursor:pointer;">
                <label for="recordar" style="margin:0; cursor:pointer;">Recordar mi usuario en este dispositivo</label>
            </div>

            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        <p><a href="invitado.php">Entrar como invitado</a></p>
    </div>
    <script src="script.js"></script>
</body>
</html>