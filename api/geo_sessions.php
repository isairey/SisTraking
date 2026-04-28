<?php
require_once __DIR__ . '/../config.php';
cors();
$mysqli = db();

$site = $_GET['site'] ?? 'default';
$from = $_GET['from'] ?? null; // 'YYYY-MM-DD HH:MM:SS'
$to   = $_GET['to']   ?? null;

$where = "WHERE site='{$mysqli->real_escape_string($site)}' AND lat IS NOT NULL AND lon IS NOT NULL";
if ($from) $where .= " AND last_seen >= '{$mysqli->real_escape_string($from)}'";
if ($to)   $where .= " AND last_seen <= '{$mysqli->real_escape_string($to)}'";

$q = $mysqli->query("SELECT session_key, country, region, city, lat, lon, last_seen FROM sessions $where ORDER BY last_seen DESC LIMIT 1000");

$out = [];
while($r=$q->fetch_assoc()) $out[] = $r;
header('Content-Type: application/json'); echo json_encode($out);
