<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = db()->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    public static function create(int $idRol, string $nombre, string $email, string $passHash, int $estado = 1): int
    {
        $stmt = db()->prepare("
        INSERT INTO usuarios (id_rol, nombre, email, pass_hash, estado)
        VALUES (?, ?, ?, ?, ?)
    ");
        $stmt->execute([$idRol, $nombre, $email, $passHash, $estado]);
        return (int) db()->lastInsertId();
    }
    public static function findById(int $id): ?array
    {
        $st = db()->prepare("SELECT * FROM usuarios WHERE id_usuario=?");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public static function updatePassword(int $id, string $hash): void
    {
        $st = db()->prepare("UPDATE usuarios SET pass_hash=? WHERE id_usuario=?");
        $st->execute([$hash, $id]);
    }
    public static function all(): array
    {
        $st = db()->query("
        SELECT u.id_usuario, u.nombre, u.email, u.estado, u.creado_en, r.nombre AS rol_nombre
        FROM usuarios u
        INNER JOIN roles r ON r.id_rol = u.id_rol
        ORDER BY u.id_usuario DESC
    ");
        return $st->fetchAll() ?: [];
    }

    public static function deleteById(int $id): void
    {
        $st = db()->prepare("UPDATE usuarios SET estado = 0 WHERE id_usuario = ?");
        $st->execute([$id]);
    }
    public static function setStatus(int $idUsuario, int $estado): bool
    {
        $st = db()->prepare("UPDATE usuarios SET estado = ? WHERE id_usuario = ?");
        return $st->execute([$estado, $idUsuario]);
    }
    public static function activeEmails(): array
    {
        $st = db()->query("
        SELECT email
        FROM usuarios
        WHERE estado = 1 AND email <> ''
        ORDER BY id_usuario ASC
    ");

        return array_values(array_filter(array_map(
            static fn($r) => trim((string) ($r['email'] ?? '')),
            $st->fetchAll() ?: []
        )));
    }

    public static function adminEmails(): array
    {
        $st = db()->query("
        SELECT email
        FROM usuarios
        WHERE estado = 1 AND id_rol = 1 AND email <> ''
        ORDER BY id_usuario ASC
    ");

        return array_values(array_filter(array_map(
            static fn($r) => trim((string) ($r['email'] ?? '')),
            $st->fetchAll() ?: []
        )));
    }

    public static function emailById(int $idUsuario): array
    {
        $st = db()->prepare("
        SELECT email
        FROM usuarios
        WHERE id_usuario = ? AND estado = 1 AND email <> ''
        LIMIT 1
    ");
        $st->execute([$idUsuario]);
        $row = $st->fetch();

        if (!$row) {
            return [];
        }

        return [trim((string) $row['email'])];
    }
}


