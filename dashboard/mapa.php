<?php
session_start(); if (empty($_SESSION['admin'])) { header("Location: /analytics/dashboard/login.php"); exit; }
?><!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mapa – Mini Analytics</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head><body>
<div class="header"><h1>Mapa</h1><a href="/analytics/dashboard/">← Dashboard</a></div>
<div class="container">
  <div class="card" style="margin-bottom:12px">
    <label class="label">Rango</label>
    <div class="row">
      <input type="datetime-local" id="from">
      <input type="datetime-local" id="to">
      <button id="apply">Aplicar</button>
    </div>
  </div>
  <div class="card"><div id="map" style="height:70vh;border-radius:16px"></div></div>
</div>
<script>
let map = L.map('map').setView([20,-100], 4);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:18}).addTo(map);

async function load(){
  const from = document.getElementById('from').value.replace('T',' ') || '';
  const to = document.getElementById('to').value.replace('T',' ') || '';
  let url = `../api/geo_sessions.php?site=default`;
  if (from) url += `&from=${encodeURIComponent(from)}`;
  if (to)   url += `&to=${encodeURIComponent(to)}`;
  const r = await fetch(url); const data = await r.json();
  let layer = L.layerGroup().addTo(map);
  data.forEach(s=>{
    if(!s.lat || !s.lon) return;
    L.marker([parseFloat(s.lat), parseFloat(s.lon)])
      .bindPopup(`<b>${s.city||''}, ${s.region||''}</b><br>${s.country||''}<br><small>${s.last_seen}</small>`)
      .addTo(layer);
  });
}
document.getElementById('apply').onclick = load;
load();
</script>
</body></html>
