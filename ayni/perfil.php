<?php
require_once 'includes/funciones.php';
require_once 'includes/datos.php';

$id = (int)($_GET['id'] ?? 0);
$t = buscar_trabajador($id);
if (!$t) {
    http_response_code(404);
    $titulo = 'No encontrado';
    require 'includes/header.php';
    echo '<main class="container py-5"><h1 class="h3">Trabajador no encontrado</h1><a href="index.php">Volver a resultados</a></main>';
    require 'includes/footer.php';
    exit;
}

$resenas = resenas_de($id);
$fotos = $t['fotos'] ?? [];
$titulo = $t['nombre'];
require 'includes/header.php';
?>

  <main class="container py-4 py-lg-5">
    <a href="index.php" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Volver a resultados</a>

    <div class="row g-4 mt-1">
      <div class="col-lg-8">
        <header class="d-flex align-items-center gap-3 mb-4">
          <div class="avatar avatar-lg"><?= e(iniciales($t['nombre'])) ?></div>
          <div>
            <h1 class="h2 mb-1"><?= e($t['nombre']) ?></h1>
            <p class="text-secondary mb-2"><?= e($t['oficio']) ?> · <?= e($t['distrito']) ?>, Cusco</p>
            <?php if ($t['verificado']): ?>
              <span class="badge badge-verified rounded-pill"><i class="bi bi-patch-check-fill"></i> Identidad verificada</span>
            <?php endif; ?>
          </div>
        </header>

        <div class="card-ayni shadow-sm p-3 mb-4">
          <div class="row text-center g-2">
            <div class="col-6">
              <?php if ($t['resenas'] > 0): ?>
                <div class="h4 mb-0"><?= e($t['nota']) ?> <i class="bi bi-star-fill stars"></i></div><small class="text-secondary"><?= e($t['resenas']) ?> reseñas</small>
              <?php else: ?>
                <div class="h4 mb-0">Nuevo</div><small class="text-secondary">sin reseñas aún</small>
              <?php endif; ?>
            </div>
            <div class="col-6"><div class="h4 mb-0"><?= $t['precio'] ? 'S/ ' . e($t['precio']) : 'A convenir' ?></div><small class="text-secondary">por visita</small></div>
          </div>
        </div>

        <?php if ($t['descripcion']): ?>
        <section class="card-ayni shadow-sm p-4 mb-4">
          <h2 class="h5">Sobre mi trabajo</h2>
          <p class="mb-0"><?= nl2br(e($t['descripcion'])) ?></p>
        </section>
        <?php endif; ?>

        <section class="card-ayni shadow-sm p-4 mb-4">
          <h2 class="h5 mb-3">Trabajos recientes</h2>
          <div class="row g-2 row-cols-2 row-cols-md-4">
            <?php if ($fotos): foreach ($fotos as $f): ?>
              <div class="col"><img src="uploads/<?= e($f) ?>" alt="Trabajo de <?= e($t['nombre']) ?>" class="img-fluid rounded"></div>
            <?php endforeach; else: for ($i = 0; $i < 4; $i++): ?>
              <div class="col"><div class="photo-slot"><i class="bi bi-image"></i></div></div>
            <?php endfor; endif; ?>
          </div>
        </section>

        <section class="card-ayni shadow-sm p-4">
          <h2 class="h5 mb-3">Reseñas</h2>
          <?php foreach ($resenas as $r): ?>
            <article class="pb-3 mb-3 border-bottom">
              <div><span class="stars"><?= str_repeat('★', $r['estrellas']) . str_repeat('☆', 5 - $r['estrellas']) ?></span>
                <strong><?= e($r['autor']) ?></strong> <span class="text-secondary small">· <?= e($r['lugar']) ?></span></div>
              <p class="mb-0"><?= e($r['texto']) ?></p>
            </article>
          <?php endforeach; ?>
          <?php if (!$resenas): ?><p class="text-secondary mb-0">Todavía no tiene reseñas.</p><?php endif; ?>
        </section>
      </div>

      <aside class="col-lg-4">
        <div class="card-ayni shadow-sm p-4 sticky-side">
          <div class="h3 mb-0"><?= $t['precio'] ? 'S/ ' . e($t['precio']) : 'A convenir' ?> <small class="fs-6 fw-normal text-secondary">por visita</small></div>
          <p class="text-secondary mt-2">Tarifa publicada por el trabajador.</p>
          <?php $saludo = rawurlencode('Hola ' . explode(' ', $t['nombre'])[0] . ', te vi en Ayni'); ?>
          <a class="btn btn-whatsapp w-100 py-2 fw-semibold" href="https://wa.me/<?= e($t['tel']) ?>?text=<?= $saludo ?>" target="_blank" rel="noopener">
            <i class="bi bi-whatsapp"></i> Contactar por WhatsApp
          </a>
          <a class="btn btn-outline-secondary w-100 mt-2" href="calificar.php?id=<?= (int)$t['id'] ?>"><i class="bi bi-star"></i> Calificar un trabajo</a>
          <p class="note mt-3 mb-0">Antes de contratar, revisa su identidad verificada y sus reseñas.</p>
        </div>
      </aside>
    </div>
  </main>

<?php require 'includes/footer.php'; ?>
