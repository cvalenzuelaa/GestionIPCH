<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>404 — Página no encontrada</title>
  <style>
    :root{
      --bg1: #0f1724;
      --bg2: #0b1220;
      --accent: #7dd3fc;
      --accent-2: #60a5fa;
      --text: #e6eef8;
      --muted: #9fb3c9;
      --glass: rgba(255,255,255,0.03);
      --shadow: 0 10px 30px rgba(2,6,23,0.6);
      --radius: 14px;
    }

    html,body{
      height:100%;
      margin:0;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: radial-gradient(1200px 600px at 10% 10%, rgba(96,165,250,0.08), transparent 8%),
                  radial-gradient(1000px 500px at 90% 90%, rgba(125,211,252,0.06), transparent 10%),
                  linear-gradient(180deg,var(--bg1),var(--bg2));
      color:var(--text);
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    .center {
      min-height:100%;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:32px;
      box-sizing:border-box;
    }

    .card {
      width:100%;
      max-width:980px;
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      padding:36px;
      display:grid;
      grid-template-columns: 1fr 420px;
      gap:28px;
      align-items:center;
      border: 1px solid rgba(255,255,255,0.03);
    }

    .left {
      padding:18px 8px;
    }

    h1{
      margin:0;
      font-size:86px;
      letter-spacing: -4px;
      line-height:0.9;
      color:var(--text);
    }

    p.lead{
      margin:12px 0 20px 0;
      color:var(--muted);
      font-size:18px;
      max-width:56ch;
    }

    .actions{
      display:flex;
      gap:12px;
      align-items:center;
      flex-wrap:wrap;
      margin-top:18px;
    }

    .btn {
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding:12px 18px;
      border-radius:10px;
      border: none;
      cursor:pointer;
      font-weight:600;
      text-decoration:none;
      background: linear-gradient(90deg,var(--accent),var(--accent-2));
      color: #04263b;
      box-shadow: 0 6px 20px rgba(59,130,246,0.14);
      transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease;
    }

    .btn:active{ transform: translateY(1px) scale(.997); }
    .btn.ghost{
      background: transparent;
      color:var(--text);
      border: 1px solid rgba(255,255,255,0.06);
      box-shadow:none;
    }

    .meta {
      margin-top:16px;
      color:var(--muted);
      font-size:13px;
    }

    .right {
      background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.00));
      border-radius:12px;
      padding:20px;
      text-align:center;
      border: 1px solid rgba(255,255,255,0.03);
      box-shadow: 0 6px 20px rgba(2,6,23,0.5);
    }

    .illustr {
      width:100%;
      border-radius:10px;
      display:flex;
      align-items:center;
      justify-content:center;
      flex-direction:column;
      color:var(--muted);
      padding:10px;
      box-sizing:border-box;
    }

    .illustr img{
      width:100%;
      max-width:350px;
      border-radius:10px;
      margin-bottom:10px;
      box-shadow:0 4px 20px rgba(0,0,0,0.35);
    }

    .small-info{
      margin-top:16px;
      color:var(--muted);
      font-size:14px;
      line-height:1.4;
    }

    code.path {
      display:inline-block;
      margin-top:10px;
      padding:8px 10px;
      background:var(--glass);
      color:var(--text);
      border-radius:8px;
      font-size:13px;
      overflow:auto;
      max-width:100%;
    }

    footer {
      margin-top:18px;
      font-size:13px;
      color:var(--muted);
    }

    @media (max-width:880px){
      .card{
        grid-template-columns: 1fr;
      }
      h1{ font-size:54px; }
    }
  </style>
</head>
<body>
  <div class="center">
    <main class="card" role="main" aria-labelledby="title">
      <section class="left">
        <h1 id="title">404</h1>
        <p class="lead">Lo sentimos — la página que buscas no se encuentra o fue movida. Puede que la URL esté escrita incorrectamente o el recurso ya no exista.</p>

        <div class="actions">
          <button class="btn" onclick="goBack()">← Volver</button>
          <a class="btn ghost" href="/">Ir al inicio</a>
        </div>

        <div class="meta">
          Si llegaste aquí por un enlace roto, intenta volver atrás. Si el problema persiste, contacta al administrador.
        </div>

        <footer>
          <span class="meta">Código: <strong>NOT_FOUND</strong> · <span id="ts"></span></span>
        </footer>
      </section>

      <!-- DERECHO CON IMAGEN REFERENCIAL -->
      <aside class="right">
  <div class="illustr">
    <img 
  src="assets/img/url_error_example.png"
  alt="Ejemplo de URL mal escrita y cómo corregirla"
  style="width:100%; max-width:350px; border-radius:10px; margin-bottom:10px;"
>
    <span style="font-size:13px; color:var(--muted);">
      Ejemplo: La URL está mal escrita. Comprueba mayúsculas, minúsculas, símbolos y la ruta completa.
    </span>
  </div>

  <div class="small-info">
    <strong>Consejo:</strong> revisa la URL o vuelve a la página correcta usando el botón “Volver”.
    <div class="path" id="pathContainer">Ruta: <em>desconocida</em></div>
  </div>
</aside>
    </main>
  </div>

  <script>
    function goBack(){
      if (window.history.length > 1) window.history.back();
      else window.location.href = '/';
    }

    document.getElementById('ts').textContent = new Date().toLocaleString();

    (function(){
      var path = location.pathname + location.search + location.hash;
      var container = document.getElementById('pathContainer');
      if (path && path !== '/')
        container.innerHTML = 'Ruta solicitada: <code class="path">' + path + '</code>';
    })();
  </script>
</body>
</html>
