<?php
// CONFIGURACIÓN BÁSICA
define('DB_HOST', 'localhost');
define('DB_USER', 'puntoce6_admin');
define('DB_PASS', '8nw3Xqq9FCS.k#n');
define('DB_NAME', 'puntoce6_punto_cero_digital');

// Clave para acceder al dashboard
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', '1234');
date_default_timezone_set('America/Mexico_City');

$ALLOWED_ORIGINS = [
    'https://puntocerodigital.com.mx',
    'http://localhost:3000', // Si usas entorno local
    'http://localhost'
];

// Tiempo en segundos para considerar un usuario como "online"
define('ONLINE_WINDOW', 30); // 15 segundos

function db()
{
    static $mysqli = null;
    if ($mysqli === null) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($mysqli->connect_errno) {
            http_response_code(500);
            die(json_encode(['error' => 'DB connection failed']));
        }
        $mysqli->set_charset('utf8mb4');
    }
    return $mysqli;
}

function cors()
{
    global $ALLOWED_ORIGINS;
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    if (in_array('*', $ALLOWED_ORIGINS) || in_array($origin, $ALLOWED_ORIGINS)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Vary: Origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
    }
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function json_out($data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function get_ip()
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = $_SERVER[$k];
            if (strpos($ip, ',') !== false)
                $ip = trim(explode(',', $ip)[0]);
            return $ip;
        }
    }
    return '0.0.0.0';
}

function ua_device($ua)
{
    $ua = strtolower($ua);

    // 1. Primero buscamos Tablets (iPad, Playbook, etc.)
    if (strpos($ua, 'tablet') !== false || strpos($ua, 'ipad') !== false || strpos($ua, 'playbook') !== false) {
        return 'tablet';
    }

    // 2. Luego buscamos Móviles (iPhone, Android, etc.)
    if (strpos($ua, 'mobile') !== false || strpos($ua, 'android') !== false || strpos($ua, 'iphone') !== false || strpos($ua, 'ipod') !== false) {
        return 'mobile';
    }

    // 3. Detectamos Bots/Robots (Googlebot, Bingbot, etc.) para que sepas si es tráfico real
    if (strpos($ua, 'bot') !== false || strpos($ua, 'crawl') !== false || strpos($ua, 'slurp') !== false || strpos($ua, 'spider') !== false) {
        return 'bot';
    }

    // 4. Si no es nada de lo anterior, asumimos que es PC de escritorio
    return 'desktop';
}