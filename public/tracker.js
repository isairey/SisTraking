/* Mini Analytics tracker (Fixed) */
(function () {
  try {
    var scr = document.currentScript;
    var API_TRACK = scr.getAttribute("data-endpoint") || "/api/track.php";
    var API_EVENT = scr.getAttribute("data-event-endpoint") || "/api/event.php";
    var SITE = scr.getAttribute("data-site") || "default";
    var LS_KEY = "ma_session_key";
    var UTM_KEY = "ma_utm";

    // Guardamos la hora de inicio para calcular la duración
    var startTime = Date.now();

    function getUTM() {
      try {
        var utm = localStorage.getItem(UTM_KEY);
        // Si ya existen en storage, devolverlos, PERO si hay nuevos en URL, sobrescribir
        var p = new URLSearchParams(location.search);
        var obj = {
          utm_source: p.get("utm_source"),
          utm_medium: p.get("utm_medium"),
          utm_campaign: p.get("utm_campaign"),
        };

        if (obj.utm_source || obj.utm_medium || obj.utm_campaign) {
          // Limpiar valores nulos antes de guardar
          Object.keys(obj).forEach(
            (key) => obj[key] === null && delete obj[key]
          );
          localStorage.setItem(UTM_KEY, JSON.stringify(obj));
          return obj;
        }

        return utm ? JSON.parse(utm) : {};
      } catch (e) {
        return {};
      }
    }

    function sid() {
      try {
        var s = localStorage.getItem(LS_KEY);
        if (!s) {
          s =
            Date.now().toString(36) + "-" + Math.random().toString(36).slice(2);
          localStorage.setItem(LS_KEY, s);
        }
        return s;
      } catch (e) {
        return (window.__ma_s ||=
          Date.now().toString(36) + "-" + Math.random().toString(36).slice(2));
      }
    }

    function send(url, payload) {
      var data = JSON.stringify(payload);
      if (navigator.sendBeacon) {
        var blob = new Blob([data], { type: "application/json" });
        navigator.sendBeacon(url, blob);
      } else {
        fetch(url, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: data,
          keepalive: true,
        }).catch(() => {});
      }
    }

    function basePayload() {
      var utm = getUTM();
      return {
        site: SITE,
        session_key: sid(),
        url: location.href,
        title: document.title,
        referrer: document.referrer || "",
        tz: Intl.DateTimeFormat().resolvedOptions().timeZone || "",
        utm_source: utm.utm_source || "",
        utm_medium: utm.utm_medium || "",
        utm_campaign: utm.utm_campaign || "",
        width: window.innerWidth || 0,
        height: window.innerHeight || 0,
      };
    }

    // Pageview on load
    function trackPageview() {
      var p = basePayload();
      p.type = "pageview";
      send(API_TRACK, p);
    }

    // Heartbeat cada 10s (5s es muy agresivo para el servidor)
    var hbInterval;
    function startHeartbeat() {
      hbInterval = setInterval(function () {
        var p = basePayload();
        p.type = "heartbeat"; // El PHP actualizará el last_seen
        send(API_TRACK, p);
      }, 10000);

      document.addEventListener("visibilitychange", function () {
        if (document.hidden && hbInterval) {
          clearInterval(hbInterval);
          hbInterval = null;
        } else if (!document.hidden && !hbInterval) {
          startHeartbeat();
        }
      });
    }

    // Detectar desconexión (CORREGIDO)
    window.addEventListener("pagehide", function () {
      var p = basePayload();
      p.type = "unload"; // Corregido: antes decía 'disconnect'
      p.duration_ms = Date.now() - startTime; // Agregado: duración real

      // Usar sendBeacon para asegurar que se envíe al cerrar la pestaña
      var blob = new Blob([JSON.stringify(p)], { type: "application/json" });
      navigator.sendBeacon(API_TRACK, blob);
    });

    // Public API
    window.MiniAnalytics = {
      event: function (name, data) {
        var p = basePayload();
        p.name = name;
        p.data = data || null;
        send(API_EVENT, p);
      },
    };

    trackPageview();
    startHeartbeat();
  } catch (e) {
    console.error(e);
  }
})();
