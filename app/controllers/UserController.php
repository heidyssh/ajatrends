<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/User.php';

final class UserController
{
  public static function handle(array $post): array
  {
    require_auth();
    require_admin('Usuarios');

    if (!empty($post['action']) && $post['action'] === 'toggle_status') {
      $id = (int) ($post['id_usuario'] ?? 0);
      $estado = (int) ($post['estado'] ?? -1);

      if ($id > 0 && in_array($estado, [0, 1], true)) {
        User::setStatus($id, $estado);

        return [
          'success' => $estado === 1
            ? 'Usuario activado correctamente.'
            : 'Usuario desactivado correctamente.',
          'users' => User::all()
        ];
      }
    }

    if (!empty($post['action']) && $post['action'] === 'delete') {
      $id = (int) ($post['id_usuario'] ?? 0);

      if ($id > 0) {
        User::deleteById($id);
        return [
          'success' => 'Usuario desactivado.',
          'users' => User::all()
        ];
      }
    }

    return [
      'users' => User::all()
    ];
  }
}