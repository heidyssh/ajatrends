<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../helpers/auth.php';

final class NotificationController
{
  public static function delete(array $post): void
  {
    require_auth();

    $id = (int)($post['id'] ?? 0);
    $idUsuario = (int)($_SESSION['user']['id'] ?? 0);

    header('Content-Type: application/json; charset=utf-8');

    if ($id <= 0) {
      echo json_encode(['ok' => false]);
      exit;
    }

    Notification::delete($id, $idUsuario);

    echo json_encode(['ok' => true]);
    exit;
  }

  public static function clearAll(): void
  {
    require_auth();

    $idUsuario = (int)($_SESSION['user']['id'] ?? 0);

    header('Content-Type: application/json; charset=utf-8');

    Notification::deleteAll($idUsuario);

    echo json_encode(['ok' => true]);
    exit;
  }
}
