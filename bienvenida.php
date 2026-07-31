<?php
// Reanudamos la sesión
session_start();

require_once 'conexion.php';
require_once 'auto_login.php';

// Si el usuario no ha iniciado sesión, lo redirigimos al login de inmediato
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - TramiApp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Mostramos de forma personalizada el nombre del usuario logueado -->
        <h2>¡Bienvenido/a, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!</h2>
        <p>Has iniciado sesión con éxito en TramiApp.</p>

        <!-- Botón para cerrar la sesión actual -->
        <a href="logout.php" style="display: block; text-align: center; margin-top: 20px; color: red; text-decoration: none; font-weight: bold;">Cerrar Sesión</a>
    </div>
</body>
</html>
