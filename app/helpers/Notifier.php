<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Notification.php';
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
    array $meta = []
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
        $html
      );

      if ($sent) {
        Notification::markEmailed($idNotif);
      }
    } catch (\Throwable $e) {
      error_log('Notifier error: ' . $e->getMessage());
    }
  }

  private static function buildEmailHtml(string $modulo, string $titulo, string $mensaje, array $meta = []): string
  {
    $extra = '';

    foreach ($meta as $k => $v) {
      $extra .= '<li><strong>' . htmlspecialchars((string)$k) . ':</strong> ' .
                htmlspecialchars((string)$v) . '</li>';
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