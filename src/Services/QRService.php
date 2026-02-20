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
        if (empty($data['type'])) {
            throw new Exception("Tipo requerido", 400);
        }

        $type = strtolower(trim($data['type']));
        $size = isset($data['size']) ? (int)$data['size'] : 300;
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

                return $this->generator->generateText(
                    trim($data['content']),
                    $size,
                    $errorLevel
                );

            case 'url':

                if (empty($data['content'])) {
                    throw new Exception("URL requerida", 400);
                }

                $url = trim($data['content']);

                // Si no tiene http o https lo agrega automáticamente
                if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                    $url = "https://" . $url;
                }

                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    throw new Exception("URL inválida", 400);
                }

                return $this->generator->generateUrl(
                    $url,
                    $size,
                    $errorLevel
                );

            case 'wifi':

                if (empty($data['ssid'])) {
                    throw new Exception("SSID requerido", 400);
                }

                if (empty($data['encryption'])) {
                    throw new Exception("Tipo de encriptación requerido", 400);
                }

                $allowedEncryptions = ['WPA', 'WEP', 'nopass'];

                if (!in_array($data['encryption'], $allowedEncryptions)) {
                    throw new Exception("Encriptación inválida (WPA, WEP o nopass)", 400);
                }

                return $this->generator->generateWifi(
                    trim($data['ssid']),
                    $data['password'] ?? '',
                    $data['encryption'],
                    $size,
                    $errorLevel
                );

            case 'geo':

                if (!isset($data['lat']) || !isset($data['lng'])) {
                    throw new Exception("Latitud y longitud requeridas", 400);
                }

                $lat = (float)$data['lat'];
                $lng = (float)$data['lng'];

                if ($lat < -90 || $lat > 90) {
                    throw new Exception("Latitud inválida", 400);
                }

                if ($lng < -180 || $lng > 180) {
                    throw new Exception("Longitud inválida", 400);
                }

                return $this->generator->generateGeo(
                    $lat,
                    $lng,
                    $size,
                    $errorLevel
                );

            default:
                throw new Exception("Tipo no soportado", 415);
        }
    }

    public function getTypes()
    {
        return ['text', 'url', 'wifi', 'geo'];
    }
}