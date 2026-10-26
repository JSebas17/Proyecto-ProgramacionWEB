<?php
require_once 'includes/funciones.php';
require_once 'includes/datos.php';

// Filtros que llegan por la URL (GET). "Solo verificados" viene activo la primera vez.
$q        = mb_strtolower(trim($_GET['q'] ?? ''));
$distrito = $_GET['distrito'] ?? '';
$oficio   = $_GET['oficio'] ?? '';
$verif    = $_GET ? isset($_GET['verif']) : true;
$cuatro   = isset($_GET['cuatro']);

$lista = array_filter(todos_los_trabajadores(), function ($t) use ($q, $distrito, $oficio, $verif, $cuatro) {
    return (!$oficio   || $t['oficio'] === $oficio)
        && (!$distrito || $t['distrito'] === $distrito)
        && (!$verif    || $t['verificado'])
        && (!$cuatro   || $t['nota'] >= 4)
        && ($q === '' || str_contains(mb_strtolower($t['oficio'] . ' ' . $t['nombre']), $q));
});

$iconos = ['Electricista' => 'lightning-charge-fill', 'Pintura' => 'palette-fill', 'Gasfitería' => 'droplet-fill',
           'Carpintería' => 'hammer', 'Hotelería' => 'building', 'Limpieza' => 'stars'];

$titulo = 'Buscar trabajo';
$activo = 'buscar';
require 'includes/header.php';
?>

  <!-- Franja de confianza -->
  <div class="trust-strip">
    <div class="container py-2 d-flex flex-wrap justify-content-center justify-content-md-end gap-3 small">
      <span><i class="bi bi-graph-up-arrow"></i> 80% informalidad laboral en Cusco</span>
      <span><i class="bi bi-patch-check-fill"></i> Identidad verificada</span>
      <span><i class="bi bi-star-fill"></i> Calificación mutua</span>
    </div>
  </div>

  <main class="container py-4 py-lg-5">
    <header class="mb-4">
      <h1 class="display-title fw-bold">¿Qué trabajo necesitas en tu casa o negocio?</h1>
      <p class="text-secondary mb-0">Elige un oficio, ve quién está verificado y contáctalo directo.</p>
    </header>

    <!-- Un solo formulario GET: buscador, categorías y filtros -->
    <form method="get" action="index.php" id="filtros">
      <div class="card-ayni search-box shadow-sm p-2 mb-4" role="search">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-md-6">
            <label class="visually-hidden" for="q">Oficio</label>
            <input id="q" name="q" class="form-control" type="search" placeholder="Ej. arreglar un tablero" value="<?= e($_GET['q'] ?? '') ?>">
          </div>
          <div class="col-7 col-md-4">
            <label class="visually-hidden" for="distrito">Distrito</label>
            <select id="distrito" name="distrito" class="form-select">
              <option value="">Todos los distritos</option>
              <?php foreach (DISTRITOS as $d): ?>
                <option <?= $d === $distrito ? 'selected' : '' ?>><?= e($d) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-5 col-md-2 d-grid"><button class="btn btn-ayni" type="submit">Buscar</button></div>
        </div>
      </div>

      <!-- Categorías: clic = filtra; clic otra vez = quita el filtro -->
      <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-lg-6 mb-4">
        <?php foreach (OFICIOS as $o): $activa = ($o === $oficio); ?>
          <div class="col">
            <button type="submit" name="oficio" value="<?= $activa ? '' : e($o) ?>" class="cat-btn <?= $activa ? 'active' : '' ?>">
              <i class="bi bi-<?= $iconos[$o] ?>"></i><?= e($o) ?>
            </button>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Filtros rápidos -->
      <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <label class="chip <?= $verif ? 'active' : '' ?>">
          <input type="checkbox" name="verif" value="1" hidden <?= $verif ? 'checked' : '' ?> onchange="this.form.submit()">
          <i class="bi bi-check2"></i> Solo verificados
        </label>
        <label class="chip <?= $cuatro ? 'active' : '' ?>">
          <input type="checkbox" name="cuatro" value="1" hidden <?= $cuatro ? 'checked' : '' ?> onchange="this.form.submit()">
          <i class="bi bi-star-fill stars"></i> 4 o más
        </label>
        <span class="ms-auto small text-secondary"><?= count($lista) ?> resultados</span>
      </div>
    </form>

    <!-- Tarjetas -->
    <section class="row g-3 row-cols-1 row-cols-sm-2 row-cols-xl-4" aria-live="polite">
      <?php foreach ($lista as $t): ?>
        <div class="col">
          <article class="card-ayni shadow-sm p-3 h-100 d-flex flex-column">
            <div class="d-flex gap-3 align-items-center">
              <div class="avatar"><?= e(iniciales($t['nombre'])) ?></div>
              <div>
                <h2 class="h6 mb-0"><?= e($t['nombre']) ?></h2>
                <small class="text-secondary"><?= e($t['oficio']) ?> · <?= e($t['distrito']) ?></small>
              </div>
            </div>
            <?php if ($t['verificado']): ?>
              <span class="badge badge-verified rounded-pill align-self-start mt-2"><i class="bi bi-patch-check-fill"></i> Identidad verificada</span>
            <?php endif; ?>
            <div class="d-flex justify-content-between my-3">
              <?php if ($t['resenas'] > 0): ?>
                <span><i class="bi bi-star-fill stars"></i> <strong><?= e($t['nota']) ?></strong> <small class="text-secondary">(<?= e($t['resenas']) ?>)</small></span>
              <?php else: ?>
                <span class="text-secondary">Nuevo</span>
              <?php endif; ?>
              <strong class="text-dark"><?= $t['precio'] ? 'desde S/ ' . e($t['precio']) : 'a convenir' ?></strong>
            </div>
            <div class="d-flex gap-2 mt-auto">
              <a href="perfil.php?id=<?= (int)$t['id'] ?>" class="btn btn-outline-secondary btn-sm flex-fill">Ver perfil</a>
              <a href="https://wa.me/<?= e($t['tel']) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm flex-fill"><i class="bi bi-whatsapp"></i> WhatsApp</a>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
      <?php if (!$lista): ?>
        <p class="text-secondary">No hay resultados. Prueba quitando un filtro.</p>
      <?php endif; ?>
    </section>

    <!-- Proceso en 3 pasos -->
    <section class="card-ayni shadow-sm p-4 mt-5">
      <div class="row g-4">
        <div class="col-md-4 d-flex gap-3"><span class="step-num">1</span><div><strong>Busca</strong><div class="small text-secondary">Por oficio y distrito</div></div></div>
        <div class="col-md-4 d-flex gap-3"><span class="step-num">2</span><div><strong>Revisa</strong><div class="small text-secondary">Identidad verificada y reseñas</div></div></div>
        <div class="col-md-4 d-flex gap-3"><span class="step-num">3</span><div><strong>Contacta y califica</strong><div class="small text-secondary">Por WhatsApp, y ambos se califican</div></div></div>
      </div>
    </section>
  </main>

<?php require 'includes/footer.php'; ?>
