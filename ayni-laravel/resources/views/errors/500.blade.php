{{-- Página de error autónoma: NO usa el layout (que consulta sesión y base de datos) para no fallar
     una segunda vez cuando el problema es justamente la base de datos. Sin detalles técnicos (A02/A10). --}}
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Algo salió mal de nuestro lado · Ayni</title>
  <style>
    body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#F3F0E7;color:#222;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;text-align:center;padding:1.5rem}
    .codigo{font-size:5rem;font-weight:800;color:#9A0000;line-height:1}
    h1{font-size:1.6rem;margin:.5rem 0}
    p{color:#555;max-width:28rem;margin:0 auto 1.25rem}
    a{display:inline-block;background:#9A0000;color:#fff;text-decoration:none;padding:.65rem 1.4rem;border-radius:.5rem;font-weight:600}
  </style>
</head>
<body>
  <main>
    <div class="codigo" aria-hidden="true">500</div>
    <h1>Algo salió mal de nuestro lado</h1>
    <p>Ya quedó registrado y lo vamos a revisar. Intenta de nuevo en unos minutos.</p>
    <a href="{{ url('/') }}">Volver al inicio</a>
  </main>
</body>
</html>
