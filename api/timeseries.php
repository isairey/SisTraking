<?php
require_once __DIR__ . '/../config.php';
cors();
$mysqli = db();

$site = $_GET['site'] ?? 'default';
$days = intval($_GET['days'] ?? 7);
if ($days < 1) $days = 1; if ($days > 60) $days = 60;

$start = date('Y-m-d H:i:s', time() - $days*86400);
$sql = "SELECT DATE_FORMAT(ts, '%Y-%m-%d %H:00:00') h, COUNT(*) c
        FROM pageviews WHERE site='{$mysqli->real_escape_string($site)}' AND ts >= '{$mysqli->real_escape_string($start)}'
        GROUP BY h ORDER BY h";
$q = $mysqli->query($sql);
$out = [];
while ($row = $q->fetch_assoc()) $out[] = ['hour' => $row['h'], 'count' => intval($row['c'])];
json_out($out);
