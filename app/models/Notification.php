<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Notification
{
  public static function create(
    ?int $idUsuario,
    string $tipo,
    string $modulo,
    string $titulo,
    string $mensaje,
    string $refTabla = '',
    int $refId = 0,
    array $meta = []
  ): int {
    $st = db()->prepare("
      INSERT INTO notificaciones
      (id_usuario, tipo, modulo, titulo, mensaje, ref_tabla, ref_id, meta_json, leida, enviada_email)
      VALUES
      (:u, :tipo, :modulo, :titulo, :mensaje, :tabla, :ref_id, :meta, 0, 0)
    ");

    $st->execute([
      ':u' => $idUsuario,
      ':tipo' => $tipo,
      ':modulo' => $modulo,
      ':titulo' => $titulo,
      ':mensaje' => $mensaje,
      ':tabla' => $refTabla,
      ':ref_id' => $refId,
      ':meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
    ]);

    return (int) db()->lastInsertId();
  }

  public static function unreadCount(?int $idUsuario = null): int
  {
    if ($idUsuario && $idUsuario > 0) {
      $st = db()->prepare("
        SELECT COUNT(*) n
        FROM notificaciones
        WHERE leida = 0 AND (id_usuario IS NULL OR id_usuario = :u)
      ");
      $st->execute([':u' => $idUsuario]);
      $r = $st->fetch();
      return (int)($r['n'] ?? 0);
    }

    $st = db()->query("SELECT COUNT(*) n FROM notificaciones WHERE leida = 0");
    $r = $st->fetch();
    return (int)($r['n'] ?? 0);
  }

  public static function latest(?int $idUsuario = null, int $limit = 8): array
  {
    if ($idUsuario && $idUsuario > 0) {
      $st = db()->prepare("
        SELECT *
        FROM notificaciones
        WHERE id_usuario IS NULL OR id_usuario = :u
        ORDER BY created_at DESC, id_notificacion DESC
        LIMIT " . (int)$limit
      );
      $st->execute([':u' => $idUsuario]);
      return $st->fetchAll() ?: [];
    }

    $st = db()->query("
      SELECT *
      FROM notificaciones
      ORDER BY created_at DESC, id_notificacion DESC
      LIMIT " . (int)$limit
    );
    return $st->fetchAll() ?: [];
  }

  public static function markAllRead(?int $idUsuario = null): void
  {
    if ($idUsuario && $idUsuario > 0) {
      $st = db()->prepare("
        UPDATE notificaciones
        SET leida = 1
        WHERE id_usuario IS NULL OR id_usuario = :u
      ");
      $st->execute([':u' => $idUsuario]);
      return;
    }

    db()->exec("UPDATE notificaciones SET leida = 1");
  }

  public static function markEmailed(int $idNotificacion): void
  {
    $st = db()->prepare("UPDATE notificaciones SET enviada_email = 1 WHERE id_notificacion = :id");
    $st->execute([':id' => $idNotificacion]);
  }
  public static function delete(int $id): void
{
  $st = db()->prepare("DELETE FROM notificaciones WHERE id_notificacion=:id");
  $st->execute([':id'=>$id]);
}
}