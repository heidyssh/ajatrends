<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/Notification.php';

final class NotificationController
{
  public static function delete(array $post): void
  {
    $id = (int)($post['id'] ?? 0);

    if ($id <= 0) {
      echo json_encode(['ok'=>false]);
      exit;
    }

    Notification::delete($id);

    echo json_encode(['ok'=>true]);
    exit;
  }
}