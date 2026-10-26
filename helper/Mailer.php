<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';

class Mailer
{
    public static function enviarVerificacion($destino, $nombre, $token)
    {
        $config = parse_ini_file(__DIR__ . '/../config/config.ini');
        $link = $config['base_url'] . 'index.php?controller=login&method=verificar&token=' . $token;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $config['mail_user'];
            $mail->Password = $config['mail_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($config['mail_user'], 'Preguntados');
            $mail->addAddress($destino, $nombre);
            $mail->isHTML(true);
            $mail->Subject = 'Activá tu cuenta de Preguntados';
            $mail->Body = "<h2>¡Hola $nombre!</h2>
                           <p>Gracias por registrarte. Hacé clic en el botón para activar tu cuenta:</p>
                           <p><a href='$link' style='background:#ffe600;color:#000;padding:10px 20px;text-decoration:none;font-weight:bold'>ACTIVAR CUENTA</a></p>
                           <p>Si el botón no funciona, copiá este link: $link</p>";
            $mail->AltBody = "Activá tu cuenta entrando a: $link";

            $mail->send();
            return true;
        } catch (Exception $e) {
            Log::info('Error de mail: ' . $mail->ErrorInfo);
            return false;
        }
    }
}