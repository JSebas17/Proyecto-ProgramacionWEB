<?php
require_once 'includes/funciones.php';

// Hay que haber ingresado para publicar
$usuario = usuario_actual();
if (!$usuario) {
    header('Location: login.php');
    exit;
}

$errores = [];
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oficio      = post('oficio');
    $distrito    = post('distrito');
    $tarifa      = post('tarifa');
    $descripcion = post('descripcion');

    if (!in_array($oficio, OFICIOS, true))     $errores['oficio']   = 'Elige un oficio.';
    if (!in_array($distrito, DISTRITOS, true)) $errores['distrito'] = 'Elige un distrito.';
    if (!ctype_digit($tarifa) || (int)$tarifa < 1) $errores['tarifa'] = 'Ingresa una tarifa mayor a 0.';

    // Fotos (opcionales): solo imágenes, máx. 4
    $fotos = [];
    if (!$errores && !empty($_FILES['fotos']['name'][0])) {
        $permitidos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        foreach (array_slice($_FILES['fotos']['tmp_name'], 0, 4) as $i => $tmp) {
            if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK || $_FILES['fotos']['size'][$i] > 3 * 1024 * 1024) continue;
            $tipo = @exif_imagetype($tmp);
            if (!isset($permitidos[$tipo])) continue;
            $nombre = bin2hex(random_bytes(8)) . '.' . $permitidos[$tipo];
            if (move_uploaded_file($tmp, __DIR__ . '/uploads/' . $nombre)) $fotos[] = $nombre;
        }
    }

    if (!$errores) {
        $servicios = leer_json('servicios');
        $servicios[] = [
            'id'          => 100 + count($servicios) + 1,
            'nombre'      => $usuario['nombre'] . ' ' . mb_substr($usuario['apellidos'], 0, 1) . '.',
            'oficio'      => $oficio,
            'distrito'    => $distrito,
            'precio'      => (int)$tarifa,
            'tel'         => '51' . $usuario['telefono'],
            'descripcion' => $descripcion,
            'fotos'       => $fotos,
        ];
        guardar_json('servicios', $servicios);
        $ok = true;
    }
}

function inv($c) { global $errores; return isset($errores[$c]) ? 'is-invalid' : ''; }
function msg($c) { global $errores; return e($errores[$c] ?? ''); }

$nombreCorto = $usuario['nombre'] . ' ' . mb_substr($usuario['apellidos'], 0, 1) . '.';
$titulo = 'Publicar servicio';
$activo = 'publicar';
$script = 'publicar.js';
require 'includes/header.php';
?>

  <main class="container py-4 py-lg-5">
    <div class="row g-4">
      <div class="col-lg-7">
        <h1 class="display-title fw-bold">Publica tu servicio</h1>
        <p class="text-secondary">Así te encuentran los clientes de tu distrito.</p>

        <form method="post" enctype="multipart/form-data" class="card-ayni shadow-sm p-4" novalidate>
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="oficio">Oficio</label>
              <select id="oficio" name="oficio" class="form-select">
                <?php foreach (OFICIOS as $o): ?>
                  <option <?= post('oficio') === $o ? 'selected' : '' ?>><?= e($o) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="distrito">Distrito donde trabajo</label>
              <select id="distrito" name="distrito" class="form-select">
                <?php foreach (DISTRITOS as $d): ?>
                  <option <?= (post('distrito') ?: $usuario['distrito']) === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" for="tarifa">Tarifa referencial (S/)</label>
              <input id="tarifa" name="tarifa" class="form-control <?= inv('tarifa') ?>" type="number" min="1" placeholder="60 por visita" value="<?= e(post('tarifa')) ?>">
              <div class="invalid-feedback"><?= msg('tarifa') ?></div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" for="descripcion">Describe tu trabajo</label>
              <textarea id="descripcion" name="descripcion" class="form-control" rows="4" placeholder="Instalaciones, tableros y reparación de cortes…"><?= e(post('descripcion')) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" for="fotos">Fotos de tus trabajos (hasta 4, JPG/PNG/WEBP, máx. 3 MB)</label>
              <input id="fotos" name="fotos[]" class="form-control" type="file" accept="image/jpeg,image/png,image/webp" multiple>
            </div>
            <div class="col-12"><button class="btn btn-ayni px-4" type="submit">Publicar servicio</button></div>
          </div>
          <?php if ($ok): ?>
            <div class="alert alert-success mt-3 mb-0">¡Servicio publicado! <a href="index.php">Ver el catálogo</a></div>
          <?php endif; ?>
        </form>
      </div>

      <!-- Vista previa en vivo (JS) -->
      <aside class="col-lg-5">
        <div class="card-ayni shadow-sm p-4 sticky-side">
          <h2 class="h5 mb-3">Así se verá tu tarjeta</h2>
          <div class="card-ayni p-3">
            <div class="d-flex gap-3 align-items-center">
              <div class="avatar"><?= e(iniciales($usuario['nombre'] . ' ' . $usuario['apellidos'])) ?></div>
              <div>
                <div class="fw-bold text-dark"><?= e($nombreCorto) ?></div>
                <small class="text-secondary" id="p-sub"></small>
              </div>
            </div>
            <p class="small text-secondary mt-2 mb-2" id="p-desc"></p>
            <div class="d-flex justify-content-between border-top pt-2">
              <span class="text-secondary">Nuevo</span>
              <strong class="text-dark" id="p-precio"></strong>
            </div>
          </div>
          <p class="note mt-3 mb-0">Tu tarifa es visible para todos, sin sorpresas.</p>
        </div>
      </aside>
    </div>
  </main>

<?php require 'includes/footer.php'; ?>
