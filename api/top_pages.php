<?php
require_once __DIR__ . '/../config.php';
cors();
$mysqli = db();

$site = $_GET['site'] ?? 'default';
$limit = intval($_GET['limit'] ?? 10);
$q = $mysqli->query("SELECT url, COUNT(*) c FROM pageviews WHERE site='{$mysqli->real_escape_string($site)}' GROUP BY url ORDER BY c DESC LIMIT $limit");
$out = [];
while ($row = $q->fetch_assoc()) $out[] = ['url' => $row['url'], 'count' => intval($row['c'])];
json_out($out);
