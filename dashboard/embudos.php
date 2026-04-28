<?php
session_start(); if (empty($_SESSION['admin'])) { header("Location: /analytics/dashboard/login.php"); exit; }
?><!doctype html><html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Embudo – Mini Analytics</title>
<link rel="stylesheet" href="assets/style.css">
<script src="assets/chart.min.js"></script>
</head><body>
<div class="header"><h1>Embudo</h1><a href="/analytics/dashboard/">← Dashboard</a></div>
<div class="container">
  <div class="card">
    <div class="row">
      <input type="datetime-local" id="from">
      <input type="datetime-local" id="to">
    </div>
    <div class="row">
      <input id="steps" placeholder='Definición de pasos (ej: {"type":"page","match":"/producto"},{"type":"event","match":"checkout"})'>
    </div>
    <button id="run">Calcular</button>
  </div>
  <div class="card">
    <canvas id="funnel"></canvas>
  </div>
</div>
<script>
async function run(){
  const from = document.getElementById('from').value.replace('T',' ') || '';
  const to = document.getElementById('to').value.replace('T',' ') || '';
  let steps;
  try{ steps = JSON.parse('['+document.getElementById('steps').value+']'); }catch(e){ alert('JSON inválido'); return; }
  let url = `../api/funnel.php?site=default&steps=${encodeURIComponent(JSON.stringify(steps))}`;
  if (from) url += `&from=${encodeURIComponent(from)}`;
  if (to)   url += `&to=${encodeURIComponent(to)}`;
  const r = await fetch(url); const data = await r.json();
  const labels = steps.map((s,i)=>`${i+1}. ${s.type}:${s.match}`);
  const values = data.counts || [];
  new Chart(document.getElementById('funnel'), {type:'bar', data:{labels, datasets:[{data:values}]}}); 
}
document.getElementById('run').onclick = run;
</script>
</body></html>
