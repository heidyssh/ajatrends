<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class Mailer
{
  public static function send(string $subject, string $html, array $to = []): bool
  {
    $cfg = require __DIR__ . '/../config/mail.php';

    if (empty($cfg['enabled'])) {
      return false;
    }

    $destinos = $to ?: ($cfg['notify_to'] ?? []);
    if (!$destinos) {
      return false;
    }

    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host       = (string)$cfg['host'];
      $mail->SMTPAuth   = true;
      $mail->Username   = (string)$cfg['username'];
      $mail->Password   = (string)$cfg['password'];
      $mail->SMTPSecure = (string)$cfg['secure'];
      $mail->Port       = (int)$cfg['port'];
      $mail->CharSet    = 'UTF-8';

      $mail->setFrom((string)$cfg['from_email'], (string)$cfg['from_name']);

      foreach ($destinos as $correo) {
        $mail->addAddress((string)$correo);
      }

      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $html;
      $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html)));

      return $mail->send();
    } catch (Exception $e) {
      error_log('Mailer error: ' . $e->getMessage());
      return false;
    }
  }
}