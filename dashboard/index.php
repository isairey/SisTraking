<?php
require_once __DIR__ . '/../config.php';
session_start();
if (empty($_SESSION['admin'])) {
  header("Location: /analytics/dashboard/login.php"); // Asegúrate de que esta ruta sea correcta según tu servidor
  exit;
}
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

?><!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mini Analytics – Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <div class="header">
    <h1>Mini Analytics</h1>
    <div>
      <a href="logout.php">Salir</a>
    </div>
  </div>

  <div class="container">
    <div class="card" style="grid-column: span 12;">
      <div class="row">
        <input type="datetime-local" id="from">
        <input type="datetime-local" id="to">
        <button id="apply">Aplicar</button>
        <a href="mapa.php" style="margin-left:auto">🗺️ Mapa</a>
        <a href="embudos.php" style="margin-left:12px">🔀 Embudos</a>
        <a href="utm.php" style="margin-left:12px">📊 UTM</a>
      </div>
    </div>

    <div class="grid">
      <div class="card" style="grid-column: span 3;">
        <div class="label">Sesiones totales</div>
        <div id="kpi-sessions" class="kpi">0</div>
      </div>
      <div class="card" style="grid-column: span 3;">
        <div class="label">Pageviews totales</div>
        <div id="kpi-pageviews" class="kpi">0</div>
      </div>
      <div class="card" style="grid-column: span 3;">
        <div class="label">Eventos</div>
        <div id="kpi-events" class="kpi">0</div>
      </div>
      <div class="card" style="grid-column: span 3;">
        <div class="label">Usuarios en línea</div>
        <div id="kpi-online" class="kpi">0</div>
      </div>

      <div class="card" style="grid-column: span 12;">
        <div class="label">Tráfico (últimos 7 días)</div>
        <canvas id="chart-traffic"></canvas>
      </div>

      <div class="card" style="grid-column: span 6;">
        <div class="label">Top páginas</div>
        <table class="table" id="tbl-top">
          <thead>
            <tr>
              <th>URL</th>
              <th>Pageviews</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="card" style="grid-column: span 6;">
        <div class="label">Referers</div>
        <table class="table" id="tbl-ref">
          <thead>
            <tr>
              <th>Origen</th>
              <th>Pageviews</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <div class="card" style="grid-column: span 12;">
        <div class="label">Dispositivos</div>
        <table class="table" id="tbl-dev">
          <thead>
            <tr>
              <th>Dispositivo</th>
              <th>Sesiones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
    <div class="footer">
      Hecho con ❤️ – Mini Analytics (PHP)
    </div>
  </div>

  <script src="assets/chart.min.js"></script>
  <script src="assets/dashboard.js"></script>
  <script>
    document.getElementById('apply').addEventListener('click', function () {
      const from = document.getElementById('from').value.replace('T', ' ');
      const to = document.getElementById('to').value.replace('T', ' ');
      window.loadAll && window.loadAll('default', from, to);
    });
  </script>
</body>

</html>