<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class Mailer
{
  public static function send(string $subject, string $html, array $to = []): bool
  {
    $cfg = require __DIR__ . '/../config/mail.php';

    if (empty($cfg['enabled'])) {
      error_log('Mailer: envio deshabilitado en mail.php');
      return false;
    }

    $destinos = $to ?: ($cfg['notify_to'] ?? []);
    if (!$destinos) {
      error_log('Mailer: no hay destinatarios');
      return false;
    }

    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host       = (string)$cfg['host'];
      $mail->SMTPAuth   = true;
      $mail->Username   = (string)$cfg['username'];
      $mail->Password   = (string)$cfg['password'];
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = (int)$cfg['port'];
      $mail->CharSet    = 'UTF-8';

      $mail->SMTPOptions = [
        'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true,
        ],
      ];

      $mail->setFrom((string)$cfg['from_email'], (string)$cfg['from_name']);

      foreach ($destinos as $correo) {
        $correo = trim((string)$correo);
        if ($correo !== '') {
          $mail->addAddress($correo);
        }
      }

      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $html;
      $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)));

      $ok = $mail->send();
      error_log('Mailer OK: enviado a ' . implode(', ', $destinos));
      return $ok;

    } catch (Exception $e) {
      error_log('Mailer error: ' . $mail->ErrorInfo);
      error_log('Mailer exception: ' . $e->getMessage());
      return false;
    }
  }
}