<?php
session_start(); if (empty($_SESSION['admin'])) { header("Location: /analytics/dashboard/login.php"); exit; }
?><!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>UTM – Mini Analytics</title>
<link rel="stylesheet" href="assets/style.css">
<script src="assets/chart.min.js"></script>
</head><body>
<div class="header"><h1>UTM Breakdown</h1><a href="/analytics/dashboard/">← Dashboard</a></div>
<div class="container">
  <div class="card">
    <div class="row">
      <input type="datetime-local" id="from">
      <input type="datetime-local" id="to">
      <button id="apply">Aplicar</button>
    </div>
  </div>
  <div class="card" style="overflow:auto">
    <table class="table" id="tbl"><thead>
      <tr><th>Source</th><th>Medium</th><th>Campaign</th><th>Pageviews</th></tr></thead><tbody></tbody>
    </table>
  </div>
  <div class="card"><canvas id="chart"></canvas></div>
</div>
<script>
async function load(){
  const from = document.getElementById('from').value.replace('T',' ') || '';
  const to = document.getElementById('to').value.replace('T',' ') || '';
  let url = `../api/utm.php?site=default`;
  if (from) url += `&from=${encodeURIComponent(from)}`;
  if (to)   url += `&to=${encodeURIComponent(to)}`;
  const r = await fetch(url); const data = await r.json();
  const tbody = document.querySelector('#tbl tbody'); tbody.innerHTML = '';
  data.forEach(d=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${d.utm_source}</td><td>${d.utm_medium}</td><td>${d.utm_campaign}</td><td>${d.c}</td>`;
    tbody.appendChild(tr);
  });
  // gráfico por source
  const bySource = {};
  data.forEach(d=>bySource[d.utm_source]=(bySource[d.utm_source]||0)+parseInt(d.c,10));
  new Chart(document.getElementById('chart'), {type:'bar', data:{labels:Object.keys(bySource), datasets:[{data:Object.values(bySource)}]}});
}
document.getElementById('apply').onclick = load; load();
</script>
</body></html>
