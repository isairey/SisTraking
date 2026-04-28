<?php
require_once __DIR__ . '/../config.php';
cors();
$mysqli = db();

$site = $_GET['site'] ?? 'default';
$since = date('Y-m-d H:i:s', time() - ONLINE_WINDOW); // ONLINE_WINDOW en segundos

$stmt = $mysqli->prepare("
    SELECT COUNT(*) AS c
    FROM sessions
    WHERE site = ? 
      AND last_seen >= ? 
      AND disconnected = 0
");
$stmt->bind_param('ss',$site,$since);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

json_out(['online'=>intval($res['c'])]);

