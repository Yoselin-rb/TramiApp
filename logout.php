<?php
// Iniciamos la sesión para poder destruirla
session_start();

// Limpiamos todas las variables de sesión
$_SESSION = array();

// Destruimos la sesión
session_destroy();

// Redirigimos al usuario al login
header("Location: index.html");
exit();
?>
