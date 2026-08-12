<?php

require_once __DIR__ . '/../../config/database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(string $email, string $password, string $role): int
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (email, password_hash, role) VALUES (:email, :password_hash, :role)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email' => $email,
            'password_hash' => $hashedPassword,
            'role' => $role
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email): array|false
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch();
    }

    public function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== false;
    }

    public function updatePassword(int $userId, string $newPassword): void
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password_hash = :password_hash, must_change_password = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'password_hash' => $hashedPassword,
            'id' => $userId
        ]);
    }
}