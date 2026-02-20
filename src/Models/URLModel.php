<?php

namespace Src\Models;

use Src\Config\Database;
use PDO;

class URLModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create($original, $code, $expiration, $maxUses, $ip)
    {
        $stmt = $this->db->prepare("
            INSERT INTO short_urls 
            (original_url, short_code, expiration_date, max_uses, creator_ip) 
            VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([$original, $code, $expiration, $maxUses, $ip]);
    }

    public function findByCode($code)
    {
        $stmt = $this->db->prepare("SELECT * FROM short_urls WHERE short_code = ?");
        $stmt->execute([$code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function increaseVisit($code)
    {
        $stmt = $this->db->prepare("UPDATE short_urls SET visits = visits + 1 WHERE short_code = ?");
        $stmt->execute([$code]);
    }

    public function logVisit($code, $ip, $agent)
    {
        $stmt = $this->db->prepare("
            INSERT INTO url_visits (short_code, ip_address, user_agent)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$code, $ip, $agent]);
    }

    public function getStats($code)
    {
        $stmt = $this->db->prepare("
            SELECT visits, created_at 
            FROM short_urls 
            WHERE short_code = ?
        ");
        $stmt->execute([$code]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
            SELECT DATE(visit_date) as day, COUNT(*) as total
            FROM url_visits
            WHERE short_code = ?
            GROUP BY day
        ");
        $stmt->execute([$code]);
        $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "general" => $data,
            "daily" => $daily
        ];
    }
}