<?php
namespace Core;

use App\Models\authUsers;

class authMiddleware
{
    public static function check(): void
    {
        session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: /auth/loginForm');
            exit;
        }
    }

    public static function userId(): ?int
    {
        return $_SESSION['auth_user'] ?? null;
    }

    public static function role(): ?string
    {
        $id = self::userId();
        if (!$id) return null;

        $user = new authUsers();
        $dados = $user->find($id);
        return $dados['role'] ?? null;
    }

    public static function requireRole(string $required): void
    {
        self::check();

        $current = self::role();
        if ($current !== $required) {
            http_response_code(403);
            echo "Acesso negado: permissão insuficiente.";
            exit;
        }
    }

    public static function logout(): void
    {
        session_destroy();
    }
}
