<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class Agenda {

  public static function upcoming(int $idUsuario = 0, int $limit = 8): array {
    $pdo = db();

    
    $sql = "
      SELECT id_evento, id_usuario, titulo, descripcion, fecha, hora, modulo, estado
      FROM agenda_eventos
      WHERE fecha >= CURDATE()
        AND fecha <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        AND (id_usuario IS NULL OR id_usuario = :u)
        AND estado <> 'CANCELADO'
      ORDER BY fecha ASC, hora ASC
      LIMIT " . (int)$limit;

    $st = $pdo->prepare($sql);
    $st->execute([':u' => $idUsuario]);
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  public static function monthEvents(int $idUsuario, int $year, int $month): array {
    $pdo = db();
    $sql = "
      SELECT fecha, COUNT(*) AS c
      FROM agenda_eventos
      WHERE YEAR(fecha)=:y AND MONTH(fecha)=:m
        AND (id_usuario IS NULL OR id_usuario=:u)
        AND estado <> 'CANCELADO'
      GROUP BY fecha
    ";
    $st = $pdo->prepare($sql);
    $st->execute([':y'=>$year, ':m'=>$month, ':u'=>$idUsuario]);
    return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
  public static function eventsByDate(int $idUsuario, string $date): array {
  $pdo = db();
  $sql = "
    SELECT id_evento, id_usuario, titulo, descripcion, fecha, hora, modulo, estado
    FROM agenda_eventos
    WHERE fecha = :f
      AND (id_usuario IS NULL OR id_usuario = :u)
      AND estado <> 'CANCELADO'
    ORDER BY hora ASC, id_evento ASC
  ";
  $st = $pdo->prepare($sql);
  $st->execute([':f' => $date, ':u' => $idUsuario]);
  return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
public static function allEvents(int $idUsuario): array {
  $pdo = db();
  $sql = "
    SELECT id_evento, id_usuario, titulo, descripcion, fecha, hora, modulo, estado
    FROM agenda_eventos
    WHERE (id_usuario IS NULL OR id_usuario = :u)
      AND estado <> 'CANCELADO'
    ORDER BY fecha ASC, hora ASC, id_evento ASC
  ";
  $st = $pdo->prepare($sql);
  $st->execute([':u' => $idUsuario]);
  return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
public static function createEvent(int $idUsuario, array $data): int {
  $pdo = db();
  $titulo = trim((string)($data['titulo'] ?? ''));
  $descripcion = trim((string)($data['descripcion'] ?? ''));
  $fecha = trim((string)($data['fecha'] ?? ''));
  $hora = trim((string)($data['hora'] ?? ''));
  $modulo = (string)($data['modulo'] ?? 'General');

  if ($titulo === '') throw new RuntimeException('El título es obligatorio.');
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new RuntimeException('Fecha inválida.');

  
  $horaDb = ($hora === '') ? null : ($hora . ':00');

  $sql = "INSERT INTO agenda_eventos (id_usuario, titulo, descripcion, fecha, hora, modulo, estado)
          VALUES (:u,:t,:d,:f,:h,:m,'PENDIENTE')";
  $st = $pdo->prepare($sql);
  $st->execute([
    ':u'=>$idUsuario,
    ':t'=>$titulo,
    ':d'=>($descripcion === '' ? null : $descripcion),
    ':f'=>$fecha,
    ':h'=>$horaDb,
    ':m'=>$modulo,
  ]);
  return (int)$pdo->lastInsertId();
}

public static function updateEvent(int $idUsuario, int $idEvento, array $data): void {
  if ($idEvento <= 0) throw new RuntimeException('Evento inválido.');
  $pdo = db();

  $titulo = trim((string)($data['titulo'] ?? ''));
  $descripcion = trim((string)($data['descripcion'] ?? ''));
  $hora = trim((string)($data['hora'] ?? ''));
  $modulo = (string)($data['modulo'] ?? 'General');

  if ($titulo === '') throw new RuntimeException('El título es obligatorio.');
  $horaDb = ($hora === '') ? null : ($hora . ':00');

  $sql = "UPDATE agenda_eventos
          SET titulo=:t, descripcion=:d, hora=:h, modulo=:m
          WHERE id_evento=:id AND id_usuario=:u";
  $st = $pdo->prepare($sql);
  $st->execute([
    ':t'=>$titulo,
    ':d'=>($descripcion === '' ? null : $descripcion),
    ':h'=>$horaDb,
    ':m'=>$modulo,
    ':id'=>$idEvento,
    ':u'=>$idUsuario
  ]);
}

public static function setEstado(int $idUsuario, int $idEvento, string $estado): void {
  if ($idEvento <= 0) throw new RuntimeException('Evento inválido.');
  $pdo = db();
  $sql = "UPDATE agenda_eventos SET estado=:e
          WHERE id_evento=:id AND id_usuario=:u";
  $st = $pdo->prepare($sql);
  $st->execute([':e'=>$estado, ':id'=>$idEvento, ':u'=>$idUsuario]);
}

public static function deleteEvent(int $idUsuario, int $idEvento): void {
  if ($idEvento <= 0) throw new RuntimeException('Evento inválido.');
  $pdo = db();
  $sql = "DELETE FROM agenda_eventos WHERE id_evento=:id AND id_usuario=:u";
  $st = $pdo->prepare($sql);
  $st->execute([':id'=>$idEvento, ':u'=>$idUsuario]);
}
}