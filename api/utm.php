<?php
require_once __DIR__ . '/../config.php';
cors();
$mysqli = db();
$site = $_GET['site'] ?? 'default';
$from = $_GET['from'] ?? null;
$to   = $_GET['to']   ?? null;

$where = "site='{$mysqli->real_escape_string($site)}'";
if ($from) $where .= " AND ts >= '{$mysqli->real_escape_string($from)}'";
if ($to)   $where .= " AND ts <= '{$mysqli->real_escape_string($to)}'";

$sql = "SELECT COALESCE(NULLIF(utm_source,''),'(none)') utm_source,
               COALESCE(NULLIF(utm_medium,''),'(none)') utm_medium,
               COALESCE(NULLIF(utm_campaign,''),'(none)') utm_campaign,
               COUNT(*) c
        FROM pageviews WHERE $where
        GROUP BY utm_source, utm_medium, utm_campaign
        ORDER BY c DESC LIMIT 200";
$r = $mysqli->query($sql);
$out = [];
while($row=$r->fetch_assoc()) $out[] = $row;
header('Content-Type: application/json'); echo json_encode($out);
