<?php
require_once __DIR__ . '/../config.php';
cors();
$mysqli = db();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) json_out(['ok' => false, 'error' => 'no json']);

$site = substr($input['site'] ?? 'default', 0, 100);
$session_key = substr($input['session_key'] ?? '', 0, 64);
$url = substr($input['url'] ?? '', 0, 2000);
$name = substr($input['name'] ?? '', 0, 100);
$data = $input['data'] ?? null;
$now = date('Y-m-d H:i:s');

$stmt = $mysqli->prepare("INSERT INTO events (site, session_key, url, name, data, ts) VALUES (?, ?, ?, ?, ?, ?)");
$json = $data ? json_encode($data) : null;
$stmt->bind_param('ssssss', $site, $session_key, $url, $name, $json, $now);
$stmt->execute();

json_out(['ok' => true]);
