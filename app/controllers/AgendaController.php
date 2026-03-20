<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/Agenda.php';
require_once __DIR__ . '/../helpers/Notifier.php';

final class AgendaController
{

  public static function handle(array $get, array $post): array {
  require_auth();
  $idUser = (int) ($_SESSION['user']['id'] ?? 0);

 
  $viewMode = trim((string)($get['view'] ?? 'day'));

$date = trim((string)($get['date'] ?? ($post['fecha'] ?? '')));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
  $date = date('Y-m-d');
}


  if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string)($post['action'] ?? '');

    try {
      if ($action === 'create') {
        $idEvento = Agenda::createEvent($idUser, $post);

Notifier::notifyShared(
  'agenda_create',
  'Agenda',
  'Nuevo evento agendado',
  'Se creó el evento "' . ($post['titulo'] ?? '') . '".',
  'agenda_eventos',
  $idEvento,
  [
    'fecha' => $post['fecha'] ?? '',
    'hora' => $post['hora'] ?? '',
    'modulo' => $post['modulo'] ?? 'General'
  ]
);
        $_SESSION['flash_success'] = 'Evento guardado';
        header('Location: index.php?page=agenda&date=' . urlencode($date));
        exit;
      }

      if ($action === 'update') {
        Agenda::updateEvent($idUser, (int)($post['id_evento'] ?? 0), $post);
        Notifier::notifyShared(
  'agenda_update',
  'Agenda',
  'Evento actualizado',
  'Se actualizó el evento "' . ($post['titulo'] ?? '') . '".',
  'agenda_eventos',
  (int)($post['id_evento'] ?? 0)
);
        $_SESSION['flash_success'] = 'Evento actualizado';
        header('Location: index.php?page=agenda&date=' . urlencode($date));
        exit;
      }

      if ($action === 'done') {
        Agenda::setEstado($idUser, (int)($post['id_evento'] ?? 0), 'HECHO');
        Notifier::notifyShared(
  'agenda_done',
  'Agenda',
  'Evento marcado como hecho',
  'El evento de agenda fue marcado como completado.',
  'agenda_eventos',
  (int)($post['id_evento'] ?? 0)
);
        $_SESSION['flash_success'] = 'Marcado como HECHO';
        header('Location: index.php?page=agenda&date=' . urlencode($date));
        exit;
      }

      if ($action === 'delete') {
        Agenda::deleteEvent($idUser, (int)($post['id_evento'] ?? 0));
        Notifier::notifyShared(
  'agenda_delete',
  'Agenda',
  'Evento eliminado',
  'Un evento fue eliminado de la agenda del sistema.',
  'agenda_eventos',
  (int)($post['id_evento'] ?? 0)
);
        $_SESSION['flash_success'] = 'Evento eliminado 🗑️';
        header('Location: index.php?page=agenda&date=' . urlencode($date));
        exit;
      }
    } catch (\Throwable $e) {
      $_SESSION['flash_error'] = $e->getMessage();

    }
  }

  $sel = new DateTime($date);
  $year  = (int)$sel->format('Y');
  $month = (int)$sel->format('m');

  $allEvents = [];
$dayEvents = [];

if ($viewMode === 'all') {
  $allEvents = Agenda::allEvents($idUser);
} else {
  $dayEvents = Agenda::eventsByDate($idUser, $date);
}

return [
  'selectedDate' => $date,
  'viewMode'     => $viewMode,
  'monthTitle'   => $sel->format('F Y'),
  'monthEvents'  => Agenda::monthEvents($idUser, $year, $month),
  'dayEvents'    => $dayEvents,
  'allEvents'    => $allEvents,
  'idUser'       => $idUser,
];
}
}