<?php

namespace Src\Services;

use Src\QR\QRGenerator;
use Exception;

class QRService
{
    private QRGenerator $generator;

    public function __construct()
    {
        $this->generator = new QRGenerator();
    }

    public function generate(array $data)
    {
        $type = $data['type'] ?? null;
        $size = $data['size'] ?? 300;
        $errorLevel = $data['error_level'] ?? 'M';

        if ($size < 100 || $size > 1000) {
            throw new Exception("El tamaño debe estar entre 100 y 1000", 400);
        }

        if (!in_array($errorLevel, ['L', 'M', 'Q', 'H'])) {
            throw new Exception("Nivel de corrección inválido", 400);
        }

        switch ($type) {

            case 'text':
                if (empty($data['content'])) {
                    throw new Exception("Contenido requerido", 400);
                }
                return $this->generator->generateText($data['content'], $size, $errorLevel);

            case 'url':
                if (!filter_var($data['content'], FILTER_VALIDATE_URL)) {
                    throw new Exception("URL inválida", 400);
                }
                return $this->generator->generateUrl($data['content'], $size, $errorLevel);

            case 'wifi':
                if (empty($data['ssid']) || empty($data['encryption'])) {
                    throw new Exception("Datos WiFi incompletos", 400);
                }
                return $this->generator->generateWifi(
                    $data['ssid'],
                    $data['password'] ?? '',
                    $data['encryption'],
                    $size,
                    $errorLevel
                );

            case 'geo':
                $lat = $data['lat'] ?? null;
                $lng = $data['lng'] ?? null;

                if (!is_numeric($lat) || $lat < -90 || $lat > 90) {
                    throw new Exception("Latitud inválida", 400);
                }

                if (!is_numeric($lng) || $lng < -180 || $lng > 180) {
                    throw new Exception("Longitud inválida", 400);
                }

                return $this->generator->generateGeo($lat, $lng, $size, $errorLevel);

            default:
                throw new Exception("Tipo no soportado", 415);
        }
    }

    public function getTypes()
    {
        return ['text', 'url', 'wifi', 'geo'];
    }
}