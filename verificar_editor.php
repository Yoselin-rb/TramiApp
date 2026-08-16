<?php
// verificar_editor.php
// IMPORTANTE: incluir con require_once DESPUÉS de session_start(), conexion.php
// y auto_login.php, al principio de CUALQUIER página que solo el editor de
// contenido pueda usar (ej: admin-tramites.php, admin-guardar-tramite.php,
// admin-importar-tramites.php).
//
// Si quien está mirando la página no tiene sesión iniciada, o la tiene pero
// su rol no es 'editor', lo mandamos para otro lado y cortamos la ejecución
// con exit() para que ni una línea del resto del archivo llegue a mostrarse.

if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'editor') {
    // Si no tiene sesión, lo mandamos a loguearse; si tiene sesión pero no
    // es editor, lo mandamos a inicio (no tiene sentido devolverlo al login,
    // ya que su usuario y contraseña son correctos, solo no tiene permiso).
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
    } else {
        header("Location: inicio.php?sin_permiso=1");
    }
    exit();
}
