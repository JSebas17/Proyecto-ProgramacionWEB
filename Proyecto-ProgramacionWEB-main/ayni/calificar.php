<?php
require_once 'includes/funciones.php';
require_once 'includes/datos.php';

$t = buscar_trabajador((int)($_GET['id'] ?? 0));
$destino = $t ? $t['nombre'] : 'el trabajador';

$msg = '';
$exito = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $puntaje = (int)post('puntaje');
    if ($puntaje < 1 || $puntaje > 5) {
        $msg = 'Elige de 1 a 5 estrellas antes de enviar.';
    } else {
        $permitidos = ['Puntual', 'Pagó lo acordado', 'Buen trato'];
        $chips = array_values(array_intersect($_POST['chips'] ?? [], $permitidos));
        $cal = leer_json('calificaciones');
        $cal[] = [
            'trabajador' => $t['id'] ?? null,
            'autor'      => usuario_actual()['email'] ?? 'anónimo',
            'puntaje'    => $puntaje,
            'chips'      => $chips,
            'comentario' => mb_substr(post('comentario'), 0, 500),
            'fecha'      => date('Y-m-d H:i'),
        ];
        guardar_json('calificaciones', $cal);
        $exito = true;
        $msg = 'Calificación enviada. Aparecerá en el perfil cuando ambos califiquen.';
    }
}

$titulo = 'Calificar trabajo';
$script = 'calificar.js';
require 'includes/header.php';
?>

  <main class="container py-4 py-lg-5">
    <div class="row g-4">
      <div class="col-lg-8">
        <h1 class="display-title fw-bold">Califica el trabajo</h1>
        <p class="text-secondary">Trabajo realizado por <?= e($destino) ?></p>

        <form method="post" class="card-ayni shadow-sm p-4">
          <fieldset class="mb-4">
            <legend class="h6">Tu calificación</legend>
            <input type="hidden" name="puntaje" id="puntaje" value="<?= e(post('puntaje')) ?>">
            <div id="estrellas" role="radiogroup" aria-label="Calificación de 1 a 5 estrellas">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" class="star-btn" data-v="<?= $i ?>" aria-label="<?= $i ?> estrella<?= $i > 1 ? 's' : '' ?>">★</button>
              <?php endfor; ?>
            </div>
            <small class="text-secondary" id="estrellas-txt">Elige de 1 a 5 estrellas.</small>
          </fieldset>

          <fieldset class="mb-4">
            <legend class="h6">¿Qué tal fue?</legend>
            <div class="d-flex flex-wrap gap-2" id="chips">
              <?php foreach (['Puntual', 'Pagó lo acordado', 'Buen trato'] as $c): $on = in_array($c, $_POST['chips'] ?? [], true); ?>
                <label class="chip <?= $on ? 'active' : '' ?>">
                  <input type="checkbox" name="chips[]" value="<?= e($c) ?>" hidden <?= $on ? 'checked' : '' ?>><?= e($c) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </fieldset>

          <label class="form-label fw-semibold" for="comentario">Comentario (opcional)</label>
          <textarea id="comentario" name="comentario" class="form-control mb-3" rows="4" placeholder="Excelente acabado, dejó todo limpio…"><?= e(post('comentario')) ?></textarea>

          <button class="btn btn-ayni px-4" type="submit">Enviar calificación</button>
          <?php if ($msg): ?>
            <div class="alert mt-3 mb-0 <?= $exito ? 'alert-success' : 'alert-danger' ?>" role="status"><?= e($msg) ?></div>
          <?php endif; ?>
        </form>
      </div>

      <aside class="col-lg-4">
        <div class="card-ayni shadow-sm p-4">
          <h2 class="h5">Calificación mutua</h2>
          <p class="text-secondary">Trabajador y cliente se califican entre sí. Así los dos construyen un historial confiable.</p>
          <p class="note mb-0">Tu reseña aparece en el perfil una vez que ambos califican.</p>
        </div>
      </aside>
    </div>
  </main>

<?php require 'includes/footer.php'; ?>
