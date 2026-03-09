<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/Mailer.php';

final class Notifier
{
  public static function notify(
    ?int $idUsuario,
    string $tipo,
    string $modulo,
    string $titulo,
    string $mensaje,
    string $refTabla = '',
    int $refId = 0,
    array $meta = [],
    array $emailTo = []
  ): void {
    try {
      $idNotif = Notification::create(
        $idUsuario,
        $tipo,
        $modulo,
        $titulo,
        $mensaje,
        $refTabla,
        $refId,
        $meta
      );

      $html = self::buildEmailHtml($modulo, $titulo, $mensaje, $meta);

      $sent = Mailer::send(
        '[AJA Trends] ' . $titulo,
        $html,
        $emailTo
      );

      if ($sent) {
        Notification::markEmailed($idNotif);
      }
    } catch (\Throwable $e) {
      error_log('Notifier error: ' . $e->getMessage());
    }
  }

  public static function notifyShared(
    string $tipo,
    string $modulo,
    string $titulo,
    string $mensaje,
    string $refTabla = '',
    int $refId = 0,
    array $meta = []
  ): void {
    self::notify(
      null,
      $tipo,
      $modulo,
      $titulo,
      $mensaje,
      $refTabla,
      $refId,
      $meta,
      User::activeEmails()
    );
  }

  public static function notifyAdmins(
    ?int $idUsuario,
    string $tipo,
    string $modulo,
    string $titulo,
    string $mensaje,
    string $refTabla = '',
    int $refId = 0,
    array $meta = []
  ): void {
    self::notify(
      $idUsuario,
      $tipo,
      $modulo,
      $titulo,
      $mensaje,
      $refTabla,
      $refId,
      $meta,
      User::adminEmails()
    );
  }

  public static function notifyUser(
    int $idUsuario,
    string $tipo,
    string $modulo,
    string $titulo,
    string $mensaje,
    string $refTabla = '',
    int $refId = 0,
    array $meta = []
  ): void {
    self::notify(
      $idUsuario,
      $tipo,
      $modulo,
      $titulo,
      $mensaje,
      $refTabla,
      $refId,
      $meta,
      User::emailById($idUsuario)
    );
  }

  private static function buildEmailHtml(string $modulo, string $titulo, string $mensaje, array $meta = []): string
  {
    $extra = '';

    foreach ($meta as $k => $v) {
  $label = ucwords(str_replace('_', ' ', $k));
  $html .= "<li><strong>{$label}:</strong> {$v}</li>";
}

    return '
      <div style="font-family:Arial,sans-serif;font-size:14px;color:#222">
        <h2 style="margin:0 0 12px 0;color:#6f42c1">AJA Trends</h2>
        <p><strong>Módulo:</strong> ' . htmlspecialchars($modulo) . '</p>
        <p><strong>Evento:</strong> ' . htmlspecialchars($titulo) . '</p>
        <p>' . htmlspecialchars($mensaje) . '</p>
        ' . ($extra !== '' ? '<ul>' . $extra . '</ul>' : '') . '
        <p style="margin-top:16px;color:#666">Notificación automática del sistema.</p>
      </div>
    ';
  }
}