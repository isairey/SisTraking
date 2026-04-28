<?php
require_once __DIR__ . '/../config.php';
cors();
$mysqli = db();

$site = $_GET['site'] ?? 'default';
$from = $_GET['from'] ?? null;
$to   = $_GET['to']   ?? null;
$steps = json_decode($_GET['steps'] ?? '[]', true); // [{type:'page'|'event', match:'...'}]

if (!is_array($steps) || count($steps)<2) { http_response_code(400); echo json_encode(['error'=>'steps invalid']); exit; }

$timeFilterPV = "site='{$mysqli->real_escape_string($site)}'";
$timeFilterEV = $timeFilterPV;
if ($from) { $from = $mysqli->real_escape_string($from); $timeFilterPV .= " AND ts>='$from'"; $timeFilterEV .= " AND ts>='$from'"; }
if ($to)   { $to   = $mysqli->real_escape_string($to);   $timeFilterPV .= " AND ts<='$to'";   $timeFilterEV .= " AND ts<='$to'"; }

$sets = []; // array de arrays de session_key
foreach ($steps as $s) {
  $match = $mysqli->real_escape_string($s['match'] ?? '');
  if (($s['type'] ?? '') === 'event') {
    $sql = "SELECT DISTINCT session_key FROM events WHERE $timeFilterEV AND site='{$mysqli->real_escape_string($site)}' AND name LIKE '%$match%'";
  } else {
    $sql = "SELECT DISTINCT session_key FROM pageviews WHERE $timeFilterPV AND url LIKE '%$match%'";
  }
  $res = $mysqli->query($sql);
  $set = [];
  while($row = $res->fetch_assoc()) $set[$row['session_key']] = true;
  $sets[] = $set;
}

$counts = [];
$carry = null;
for ($i=0; $i<count($sets); $i++){
  if ($i===0) { $carry = $sets[0]; }
  else {
    $tmp = [];
    foreach($carry as $k=>$_) if (isset($sets[$i][$k])) $tmp[$k] = true;
    $carry = $tmp;
  }
  $counts[] = count($carry);
}
echo json_encode(['counts'=>$counts]);
