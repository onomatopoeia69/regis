<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Users extends Model
{
    public $tableName = 'rgr_users';
    public $primaryKey = 'id';

    protected function findUserByUsername($username)
    {
        $sql = "SELECT
                    u.id,
                    u.first_name,
                    u.middle_name,
                    u.last_name,
                    u.email,
                    u.username,
                    u.password,
                    u.role_id,
                    u.status,
                    r.role_name,
                    r.description AS role_description
                FROM {$this->tableName} u
                INNER JOIN rgr_roles r
                    ON u.role_id = r.role_id
                WHERE u.username = :username
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':username' => $username]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function authenticate($username, $password)
    {
        $user = $this->findUserByUsername($username);

        if (!$user || $user['status'] !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        $this->updateLastLogin($user['id']);

        unset($user['password']);

        return $user;
    }

    protected function updateLastLogin($userId)
    {
        $sql = "UPDATE {$this->tableName}
                SET last_login = NOW()
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([':id' => $userId]);
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = new self();

        return $instance->$name(...$arguments);
    }
}
