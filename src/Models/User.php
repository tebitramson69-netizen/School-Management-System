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

    public function find(int $id): array|false
    {
        $sql = "SELECT id, email, role, is_active, must_change_password
                FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Activate or deactivate an account. An inactive user is
     * blocked at login (see AuthController), so this is a safe,
     * reversible "remove" that preserves all their records.
     */
    public function setActive(int $id, bool $active): void
    {
        $sql = "UPDATE users SET is_active = :active WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'active' => $active ? 1 : 0,
            'id' => $id
        ]);
    }

    /**
     * Permanently delete a user. Foreign keys cascade to the role
     * profile and its data, so callers MUST guard this against
     * accounts that still have records worth keeping.
     */
    public function delete(int $id): void
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
    }
}