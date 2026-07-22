<?php
// conexion.php
// Datos de configuración de la base de datos (ajusta según tu servidor local)
$host = "localhost";
$db_name = "tramiapp";
$username = "root"; // Usuario por defecto en XAMPP/WampServer
$password_bd = "";  // Contraseña por defecto (vacía en XAMPP)

try {
    // Creamos la conexión usando PDO
    $conexion = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password_bd);

    // Configuramos PDO para que lance excepciones en caso de error
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    // Si la conexión falla, se muestra este mensaje de error
    die("Error de conexión a la base de datos: " . $exception->getMessage());
}
?>
