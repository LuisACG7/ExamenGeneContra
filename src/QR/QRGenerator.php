<?php

namespace Src\QR;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

class QRGenerator
{
    private function build(string $data, int $size, string $errorLevel)
    {
        $errorCorrection = match ($errorLevel) {
            'L' => ErrorCorrectionLevel::Low,
            'M' => ErrorCorrectionLevel::Medium,
            'Q' => ErrorCorrectionLevel::Quartile,
            'H' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };

        $qrCode = new QrCode(
            data: $data,
            size: $size,
            margin: 10,
            errorCorrectionLevel: $errorCorrection
        );

        $writer = new PngWriter();

        return $writer->write($qrCode);
    }

    public function generateText(string $text, int $size = 300, string $errorLevel = 'M')
    {
        return $this->build($text, $size, $errorLevel);
    }

    public function generateUrl(string $url, int $size = 300, string $errorLevel = 'M')
    {
        return $this->build($url, $size, $errorLevel);
    }

    public function generateWifi(string $ssid, string $password, string $encryption, int $size = 300, string $errorLevel = 'M')
    {
        $data = "WIFI:T:$encryption;S:$ssid;P:$password;;";
        return $this->build($data, $size, $errorLevel);
    }

    public function generateGeo(float $lat, float $lng, int $size = 300, string $errorLevel = 'M')
    {
        $data = "geo:$lat,$lng";
        return $this->build($data, $size, $errorLevel);
    }
}