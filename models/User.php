<?php

namespace Models;

use PDO;
use PDOStatement;

class User
{
    private PDO $conn;
    private string $table_name = "users";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function readAll(): PDOStatement
    {
        $query = "SELECT * FROM {$this->table_name}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    public function readOne(int $id): ?array
    {
        $query = "SELECT * FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $name, string $email, string $password): bool
    {
        $query = "INSERT INTO {$this->table_name} VALUES (:name, :email, :password)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password);
        return $stmt->execute();
    }

    public function update(int $id, string $name, string $email, string $password): bool
    {
        $query = "UPDATE {$this->table_name} SET name = :name, email = :email, password = :password WHERE id = :id;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
    public function delete(int $id): bool
    {
        $query = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
