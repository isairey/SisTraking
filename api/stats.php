<?php
require_once __DIR__ . '/../config.php';
cors();
$mysqli = db();

// Site a consultar
$site = substr($_GET['site'] ?? 'default', 0, 100);

// 1️⃣ Usuarios en línea (últimos 5 min)
$timeout_seconds = 10;
$stmt = $mysqli->prepare("
    SELECT COUNT(*) AS online_users 
    FROM sessions 
    WHERE site = ? AND last_seen >= DATE_SUB(NOW(), INTERVAL ? SECOND)
");
$stmt->bind_param('si', $site, $timeout_seconds);
$stmt->execute();
$online_users = $stmt->get_result()->fetch_assoc()['online_users'] ?? 0;

// 2️⃣ Total sesiones y pageviews
$stmt = $mysqli->prepare("SELECT COUNT(*) AS total_sessions, SUM(pageviews) AS total_pageviews FROM sessions WHERE site = ?");
$stmt->bind_param('s', $site);
$stmt->execute();
$totals_row = $stmt->get_result()->fetch_assoc();
$total_sessions = intval($totals_row['total_sessions']);
$total_pageviews = intval($totals_row['total_pageviews']);

// 3️⃣ Top páginas
$stmt = $mysqli->prepare("
    SELECT url, COUNT(*) AS views 
    FROM pageviews 
    WHERE site = ? 
    GROUP BY url 
    ORDER BY views DESC 
    LIMIT 10
");
$stmt->bind_param('s', $site);
$stmt->execute();
$top_pages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 4️⃣ Top referers
$stmt = $mysqli->prepare("
    SELECT referrer, COUNT(*) AS count 
    FROM pageviews 
    WHERE site = ? AND referrer IS NOT NULL 
    GROUP BY referrer 
    ORDER BY count DESC 
    LIMIT 10
");
$stmt->bind_param('s', $site);
$stmt->execute();
$top_referrers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 5️⃣ Dispositivos
$stmt = $mysqli->prepare("
    SELECT device, COUNT(*) AS count 
    FROM sessions 
    WHERE site = ? 
    GROUP BY device 
    ORDER BY count DESC
");
$stmt->bind_param('s', $site);
$stmt->execute();
$devices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 6️⃣ Armar JSON final
$response = [
    'totals' => [
        'sessions' => $total_sessions,
        'pageviews' => $total_pageviews,
        'online_users' => $online_users
    ],
    'top_pages' => $top_pages,
    'top_referrers' => $top_referrers,
    'devices' => $devices
];

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
