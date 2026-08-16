<?php
// invitado.php
// Permite entrar a la app sin iniciar sesión, en modo "solo visualización".
// Si había una sesión (o cookie de "recordar usuario") activa, la cerramos,
// para que la persona navegue realmente como invitado.

session_start();
require_once 'conexion.php';
require_once 'funciones_recordar.php';

borrarTokenRecordar($conexion);

$_SESSION = array();
session_destroy();

header("Location: inicio.php");
exit();