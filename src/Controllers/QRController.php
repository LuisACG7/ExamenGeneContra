<?php

namespace Src\Controllers;

use Src\Services\QRService;
use Exception;

class QRController
{
    private QRService $service;

    public function __construct()
    {
        $this->service = new QRService();
    }

    public function generate()
    {
        try {
            $input = json_decode(file_get_contents("php://input"), true);

            $result = $this->service->generate($input);

            header('Content-Type: ' . $result->getMimeType());
            echo $result->getString();

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                "error" => $e->getMessage()
            ]);
        }
    }

    public function types()
    {
        echo json_encode([
            "types" => $this->service->getTypes()
        ]);
    }
}