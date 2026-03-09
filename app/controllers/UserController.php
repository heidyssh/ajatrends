<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../models/User.php';

final class UserController
{
  public static function handle(array $post): array
  {
    require_admin();

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