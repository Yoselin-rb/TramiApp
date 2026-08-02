<?php
// enviar_correo.php
//
// Requiere PHPMailer instalado con Composer. En la carpeta de tu proyecto:
//   composer require phpmailer/phpmailer
//
// Eso crea la carpeta /vendor con el autoload que usamos abajo.

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config_correo.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía el código de verificación de 6 dígitos al correo del usuario.
 * Devuelve true si se envió correctamente, false si hubo un error.
 */
function enviarCodigoRecuperacion($correoDestino, $nombreDestino, $codigo) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP (ver config_correo.php)
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Importante: el remitente (From) tiene que ser una dirección con
        // formato de correo válido, NO el usuario de autenticación SMTP.
        $mail->setFrom(FROM_EMAIL, 'TramiApp');
        $mail->addAddress($correoDestino, $nombreDestino);

        $mail->isHTML(true);
        $mail->Subject = 'Tu código de verificación - TramiApp';
        $mail->Body    = "
            <p>Hola <strong>" . htmlspecialchars($nombreDestino) . "</strong>,</p>
            <p>Recibimos una solicitud para restablecer tu contraseña en TramiApp.</p>
            <p>Tu código de verificación es:</p>
            <h2 style='letter-spacing:6px; text-align:center;'>" . htmlspecialchars($codigo) . "</h2>
            <p>Este código vence en 10 minutos. Si no fuiste vos quien lo solicitó, podés ignorar este correo.</p>
        ";
        $mail->AltBody = "Tu código de verificación de TramiApp es: $codigo (vence en 10 minutos)";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Registramos el error en el log del servidor, sin mostrárselo al usuario
        error_log("Error enviando correo de recuperación: " . $mail->ErrorInfo);
        return false;
    }
}