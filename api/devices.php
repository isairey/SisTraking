<?php
require_once __DIR__ . '/../config.php';
cors();
$mysqli = db();

$site = $_GET['site'] ?? 'default';
$q = $mysqli->query("SELECT device, COUNT(*) c FROM sessions WHERE site='{$mysqli->real_escape_string($site)}' GROUP BY device");
$out = [];
while ($row = $q->fetch_assoc()) $out[] = ['device' => $row['device'] ?: 'unknown', 'count' => intval($row['c'])];
json_out($out);
