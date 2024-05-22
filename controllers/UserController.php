<?php

namespace Controllers;

use Models\User;
use PDO;

class UserController
{
    private PDO $db;
    private string $requestMehod;
    private ?int $userId;

    public function __construct(PDO $db, string $requestMehod, ?int $userId = null)
    {
        $this->db = $db;
        $this->requestMehod = $requestMehod;
        $this->userId = $userId;
    }

    public function processReq()
    {
        $res = match ($this->requestMehod) {
            "GET" => $this->userId ? $this->getUser($this->userId) : $this->getAllUsers(),
            "POST" => $this->createUser(),
            "PUT" => $this->updateUser($this->userId),
            "DELETE" => $this->deleteUser($this->userId),
            default => $this->notFoundResponse()
        };

        header($res['status_code_header']);
        if (isset($res["body"])) {
            echo $res["body"];
        }
    }

    private function getAllUsers(): array
    {
        $user = new User($this->db);
        $result = $user->readAll();
        $users = $result->fetchAll(PDO::FETCH_ASSOC);

        return [
            "status_code_header" => "HTTP/1.1 200 OK",
            "body" => json_encode($users)
        ];
    }

    private function getUser(int $id): array
    {
        $user = new User($this->db);
        $result = $user->readOne($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        return [
            "status_code_header" => "HTTP/1.1 200 OK",
            "body" => json_encode($result)
        ];
    }

    private function createUser(): array
    {
        $input = (array) json_decode(file_get_contents("php://input"), TRUE);

        if (!$this->validateUser($input)) {
            return $this->unprocessableEntityResponse();
        }

        $user = new User($this->db);

        if ($user->create($input["name"], $input["email"], $input["password"])) {
            return [
                'status_code_header' => 'HTTP/1.1 201 Created',
                'body' => json_encode(["message" => "User created"])
            ];
        }
        return [
            'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
            'body' => json_encode(["message" => "Internal Server Error"])
        ];
    }
    private function updateUser(int $id): array
    {

        if (!$id) {
            return $this->notFoundResponse();
        }

        $input = (array) json_decode(file_get_contents("php://input"), TRUE);

        if (!$this->validateUser($input)) {
            return $this->unprocessableEntityResponse();
        }

        $user = new User($this->db);

        if ($user->update($id, $input["name"], $input["email"], $input["password"])) {
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode(["message" => "User updated"])
            ];
        }
        return [
            'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
            'body' => json_encode(["message" => "Internal Server Error"])
        ];
    }

    private function deleteUser(int $id)
    {
        if (!$id) {
            return $this->notFoundResponse();
        }

        $user = new User($this->db);
        if ($user->delete($id)) {
            return [
                'status_code_header' => 'HTTP/1.1 200 OK',
                'body' => json_encode(["message" => "User deleted"])
            ];
        }
        return [
            'status_code_header' => 'HTTP/1.1 500 Internal Server Error',
            'body' => json_encode(["message" => "Internal Server Error"])
        ];
    }

    private function validateUser(array $input): bool
    {
        return isset($input["name"]) && $input["email"] && $input["password"];
    }

    private function unprocessableEntityResponse(): array
    {
        return [
            'status_code_header' => 'HTTP/1.1 422 Unprocessable Entity',
            'body' => json_encode(['message' => 'Invalid input'])
        ];
    }
    private function notFoundResponse(): array
    {
        return [
            'status_code_header' => 'HTTP/1.1 404 Not Found',
            'body' => json_encode(['message' => 'Not Found'])
        ];
    }
}
