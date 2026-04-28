async function fetchJSON(url){
  const r = await fetch(url, {credentials:'include'});
  return await r.json();
}

window.loadAll = async function(site, from, to){
  site = site || 'default';
  const range = (from||to) ? (`${from?`&from=${encodeURIComponent(from)}`:''}${to?`&to=${encodeURIComponent(to)}`:''}`) : '';

  try {
    // Stats generales
    const stats = await fetchJSON(`../api/stats.php?site=${encodeURIComponent(site)}${range}`);
    const totals = stats.totals || {};
    const rangeStats = stats.range || {};

    document.getElementById('kpi-sessions').textContent = (totals.sessions || 0).toLocaleString();
    document.getElementById('kpi-pageviews').textContent = (totals.pageviews || 0).toLocaleString();
    document.getElementById('kpi-events').textContent = (totals.events || 0).toLocaleString();

    if(document.getElementById('kpi-sessions-range')) 
      document.getElementById('kpi-sessions-range').textContent = (rangeStats.sessions || 0).toLocaleString();
    if(document.getElementById('kpi-pageviews-range'))
      document.getElementById('kpi-pageviews-range').textContent = (rangeStats.pageviews || 0).toLocaleString();
    if(document.getElementById('kpi-events-range'))
      document.getElementById('kpi-events-range').textContent = (rangeStats.events || 0).toLocaleString();

    // Usuarios en línea
    const online = await fetchJSON(`../api/online.php?site=${encodeURIComponent(site)}`);
    document.getElementById('kpi-online').textContent = (online.online || 0).toLocaleString();

    // Gráfica de tráfico
    const ts = await fetchJSON(`../api/timeseries.php?site=${encodeURIComponent(site)}&days=7${range}`);
    const labels = ts.map(x => (x.hour || '').slice(5,16).replace(' ','\n'));
    const values = ts.map(x => x.count || 0);
    new Chart(document.getElementById('chart-traffic'), {type:'line', data:{labels, datasets:[{data: values}]}});

    // Top páginas
    const top = await fetchJSON(`../api/top_pages.php?site=${encodeURIComponent(site)}&limit=10${range}`);
    const tbodyTop = document.querySelector('#tbl-top tbody'); 
    tbodyTop.innerHTML='';
    top.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${r.url || '-'}</td><td>${r.count || 0}</td>`;
      tbodyTop.appendChild(tr);
    });

    // Referers
    const ref = await fetchJSON(`../api/referrers.php?site=${encodeURIComponent(site)}&limit=10${range}`);
    const tbodyRef = document.querySelector('#tbl-ref tbody'); 
    tbodyRef.innerHTML='';
    ref.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${r.referrer || '-'}</td><td>${r.count || 0}</td>`;
      tbodyRef.appendChild(tr);
    });

    // Dispositivos
    const dev = await fetchJSON(`../api/devices.php?site=${encodeURIComponent(site)}${range}`);
    const tbodyDev = document.querySelector('#tbl-dev tbody'); 
    tbodyDev.innerHTML='';
    dev.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${r.device || '-'}</td><td>${r.count || 0}</td>`;
      tbodyDev.appendChild(tr);
    });

  } catch(err){
    console.error('Error cargando el dashboard:', err);
  }
}

// Inicializa al cargar la página y refresca cada 5 segundos
document.addEventListener('DOMContentLoaded', function(){ 
  window.loadAll(); 
  setInterval(()=>window.loadAll(), 5000); // 5s en lugar de 30s
});
