<?php
namespace Controllers;

use Services\PasswordService;
use Core\Response;

class PasswordController {

    private PasswordService $service;

    public function __construct() {
        $this->service = new PasswordService();
    }

    public function generateSingle(array $params) {

        $password = $this->service->generate($params);

        Response::json([
            "success" => true,
            "password" => $password
        ]);
    }

    public function generateMultiple(array $data) {

        $passwords = $this->service->generateMultiple($data);

        Response::json([
            "success" => true,
            "count" => count($passwords),
            "passwords" => $passwords
        ]);
    }

    public function validatePassword(array $data) {

        if (!isset($data['password']) || !isset($data['requirements'])) {
            Response::json(["error" => "Datos incompletos"], 400);
        }

        $result = $this->service->validate($data['password'], $data['requirements']);

        Response::json($result);
    }

}