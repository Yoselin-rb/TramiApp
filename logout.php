<?php
// Iniciamos la sesión para poder destruirla
session_start();

require_once 'conexion.php';
require_once 'funciones_recordar.php';

// Borramos el token de "recordar usuario" (BD + cookie) para que este
// dispositivo deje de iniciar sesión automáticamente
borrarTokenRecordar($conexion);

// Limpiamos todas las variables de sesión
$_SESSION = array();

// Destruimos la sesión
session_destroy();

// Redirigimos al usuario al login
header("Location: index.html");
exit();
?>