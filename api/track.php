<?php

require_once __DIR__ . '/../config.php';

cors();

$mysqli = db();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Leer JSON

$input = json_decode(file_get_contents('php://input'), true);

if (!$input)
    json_out(['ok' => false, 'error' => 'no json']);



// Datos b��sicos

$site = substr($input['site'] ?? 'default', 0, 100);

$session_key = substr($input['session_key'] ?? '', 0, 64);

$url = substr($input['url'] ?? '', 0, 2000);

$title = substr($input['title'] ?? '', 0, 1000);

$referrer = substr($input['referrer'] ?? '', 0, 2000);

$tz = substr($input['tz'] ?? '', 0, 64);

$width = intval($input['width'] ?? 0);

$height = intval($input['height'] ?? 0);

$type = $input['type'] ?? 'pageview';

$duration_ms = intval($input['duration_ms'] ?? 0);



// UTM

$utm_source = substr($input['utm_source'] ?? '', 0, 100);

$utm_medium = substr($input['utm_medium'] ?? '', 0, 100);

$utm_campaign = substr($input['utm_campaign'] ?? '', 0, 100);



// IP / UA / Device

$ip = get_ip();

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

$device = ua_device($ua);

$now = date('Y-m-d H:i:s');



// GEO helper

function geo_lookup($ip)
{
    // Configuración: Si no responde en 2 segundos, cancelamos para no trabar la web
    $ctx = stream_context_create([
        'http' => ['timeout' => 2]
    ]);

    // Usamos esa configuración aquí
    $resp = @file_get_contents(
        "http://ip-api.com/json/" . urlencode($ip) . "?fields=status,country,regionName,city,lat,lon",
        false,
        $ctx
    );

    if (!$resp)
        return [null, null, null, null, null];
    $j = json_decode($resp, true);
    if (($j['status'] ?? '') !== 'success')
        return [null, null, null, null, null];
    return [$j['country'] ?? null, $j['regionName'] ?? null, $j['city'] ?? null, $j['lat'] ?? null, $j['lon'] ?? null];
}



// Revisar si existe sesi��n

$exists = $mysqli->prepare("SELECT lat FROM sessions WHERE site=? AND session_key=? LIMIT 1");

$exists->bind_param('ss', $site, $session_key);

$exists->execute();

$ex = $exists->get_result()->fetch_assoc();



if (!$ex) {

    // Nueva sesi��n

    [$country, $region, $city, $lat, $lon] = geo_lookup($ip);



    $stmt = $mysqli->prepare("INSERT INTO sessions

        (site, session_key, ip, user_agent, device, referrer, first_seen, last_seen, pageviews,

         utm_source, utm_medium, utm_campaign, tz, country, region, city, lat, lon, disconnected)

        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");



    $pageviews = 0;
    $disconnected = 0;

    $stmt->bind_param(
        'sssssssssissssssddi',

        $site,
        $session_key,
        $ip,
        $ua,
        $device,
        $referrer,

        $now,
        $now,
        $pageviews,

        $utm_source,
        $utm_medium,
        $utm_campaign,
        $tz,

        $country,
        $region,
        $city,
        $lat,
        $lon,
        $disconnected

    );

    $stmt->execute();



} else {

    // Sesi��n existente: actualizar last_seen si no est�� desconectado

    if ($type !== 'unload') {

        $stmt = $mysqli->prepare("UPDATE sessions SET last_seen=?, utm_source=COALESCE(NULLIF(utm_source,''),?), utm_medium=COALESCE(NULLIF(utm_medium,''),?), utm_campaign=COALESCE(NULLIF(utm_campaign,''),?), tz=COALESCE(NULLIF(tz,''),?), disconnected=0 WHERE site=? AND session_key=?");

        $stmt->bind_param('sssssss', $now, $utm_source, $utm_medium, $utm_campaign, $tz, $site, $session_key);

        $stmt->execute();

    }

}



// Registrar pageview / unload

if ($type === 'pageview') {

    $stmt2 = $mysqli->prepare("INSERT INTO pageviews

        (site, session_key, url, title, referrer, ts, width, height, utm_source, utm_medium, utm_campaign, tz)

        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt2->bind_param(
        'ssssssiiisss',

        $site,
        $session_key,
        $url,
        $title,
        $referrer,
        $now,
        $width,
        $height,
        $utm_source,
        $utm_medium,
        $utm_campaign,
        $tz

    );

    $stmt2->execute();



    $mysqli->query("UPDATE sessions SET pageviews = pageviews + 1 WHERE site='{$mysqli->real_escape_string($site)}' AND session_key='{$mysqli->real_escape_string($session_key)}'");



} elseif ($type === 'unload' && $duration_ms > 0) {

    // Actualizar duraci��n del ��ltimo pageview

    $dur = intval($duration_ms / 1000);

    $stmt3 = $mysqli->prepare("UPDATE pageviews SET duration_sec=? WHERE site=? AND session_key=? ORDER BY id DESC LIMIT 1");

    $stmt3->bind_param('iss', $dur, $site, $session_key);

    $stmt3->execute();



    // Marcar sesi��n como desconectada

    $stmt4 = $mysqli->prepare("UPDATE sessions SET disconnected=1, last_seen=? WHERE site=? AND session_key=?");

    $stmt4->bind_param('sss', $now, $site, $session_key);

    $stmt4->execute();

}



json_out(['ok' => true]);

