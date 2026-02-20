<?php

namespace Src\Controllers;

use Src\Services\URLService;
use Exception;

class URLController
{
    private $service;

    public function __construct()
    {
        $this->service = new URLService();
    }

    public function shorten($data)
    {
        header("Content-Type: application/json");

        try {

            if (!$data || !isset($data['url'])) {
                throw new Exception("URL requerida", 400);
            }

            $code = $this->service->create($data);

            echo json_encode([
                "short_url" => "http://localhost:8000/$code"
            ]);

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function redirect($code)
    {
        try {
            $url = $this->service->redirect($code);
            header("Location: $url", true, 302);
            exit;
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            header("Content-Type: application/json");
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    public function stats($code)
    {
        header("Content-Type: application/json");

        try {
            echo json_encode($this->service->stats($code));
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}