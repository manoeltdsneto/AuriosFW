<?php
namespace App\Models;

use Core\model;
use PDO;

class authUsers extends model
{
    protected string $table = 'auth_users';

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :u LIMIT 1");
        $stmt->bindValue(':u', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function register(string $username, string $password, string $role = 'user'): int
    {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        return $this->create([
            'username' => $username,
            'password' => $hashed,
            'role'     => $role
        ]);
    }
}
