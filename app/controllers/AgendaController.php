<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Agenda.php';

final class AgendaController
{

  public static function handle(array $get, array $post): array {
  require_auth();
  $idUser = (int) ($_SESSION['user']['id'] ?? 0);

  // Fecha seleccionada
  $date = trim((string)($get['date'] ?? ($post['fecha'] ?? '')));
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

  // ACCIONES (POST)
  if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string)($post['action'] ?? '');

    try {
      if ($action === 'create') {
        Agenda::createEvent($idUser, $post);
        $_SESSION['flash_success'] = 'Evento guardado ✅';
        header('Location: index.php?page=agenda&date=' . urlencode($date));
        exit;
      }

      if ($action === 'update') {
        Agenda::updateEvent($idUser, (int)($post['id_evento'] ?? 0), $post);
        $_SESSION['flash_success'] = 'Evento actualizado ✅';
        header('Location: index.php?page=agenda&date=' . urlencode($date));
        exit;
      }

      if ($action === 'done') {
        Agenda::setEstado($idUser, (int)($post['id_evento'] ?? 0), 'HECHO');
        $_SESSION['flash_success'] = 'Marcado como HECHO ✅';
        header('Location: index.php?page=agenda&date=' . urlencode($date));
        exit;
      }

      if ($action === 'delete') {
        Agenda::deleteEvent($idUser, (int)($post['id_evento'] ?? 0));
        $_SESSION['flash_success'] = 'Evento eliminado 🗑️';
        header('Location: index.php?page=agenda&date=' . urlencode($date));
        exit;
      }
    } catch (\Throwable $e) {
      $_SESSION['flash_error'] = $e->getMessage();
      // sigue para renderizar normal
    }
  }

  $sel = new DateTime($date);
  $year  = (int)$sel->format('Y');
  $month = (int)$sel->format('m');

  return [
    'selectedDate' => $date,
    'monthTitle'   => $sel->format('F Y'),
    'monthEvents'  => Agenda::monthEvents($idUser, $year, $month),
    'dayEvents'    => Agenda::eventsByDate($idUser, $date),
    'idUser'       => $idUser,
  ];
}
}