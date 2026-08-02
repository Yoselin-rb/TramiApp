<?php
// config_correo.php
//
// Completá esto con los datos de tu propio servicio de correo.
// Ejemplo con Gmail (necesitás crear una "contraseña de aplicación",
// no la contraseña normal de tu cuenta de Gmail):
// https://myaccount.google.com/apppasswords
//
// También podés usar un servicio pensado para esto, como Brevo (ex Sendinblue)
// o Mailtrap (este último es ideal solo para probar en local, no envía
// correos reales, los "atrapa" para que los veas en un panel).

// SMTP_USER es el usuario con el que te AUTENTICÁS ante el servidor SMTP
// (en Mailtrap es ese código raro tipo "c8989218b0a986").
//
// FROM_EMAIL es la dirección que aparece como remitente (De:) del correo.
// Con Mailtrap Sandbox podés poner cualquier dirección inventada, ya que
// los correos nunca salen de verdad. Cuando pases a un servicio real
// (Gmail, Brevo, etc.) tiene que ser una casilla que exista de verdad.

define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_USER', 'c8989218b0a986');
define('SMTP_PASS', 'f441625559a693'); // la que está tapada como ****a693
define('SMTP_PORT', 2525);
define('FROM_EMAIL', 'noreply@tramiapp.com'); // <-- nueva línea, puede ser cualquier correo inventado