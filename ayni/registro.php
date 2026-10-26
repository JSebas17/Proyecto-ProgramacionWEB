<?php
require_once 'includes/funciones.php';

$errores = [];
$creado  = false;
$rol     = 'cliente';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rol       = post('rol') === 'trabajador' ? 'trabajador' : 'cliente';
    $nombre    = post('nombre');
    $apellidos = post('apellidos');
    $dni       = post('dni');
    $telefono  = post('telefono');
    $email     = strtolower(post('email'));
    $distrito  = post('distrito');
    $clave     = $_POST['clave'] ?? '';
    $clave2    = $_POST['clave2'] ?? '';

    if ($nombre === '')                              $errores['nombre']    = 'Escribe tu nombre.';
    if ($apellidos === '')                           $errores['apellidos'] = 'Escribe tus apellidos.';
    if (!preg_match('/^[0-9]{8}$/', $dni))           $errores['dni']       = 'El DNI tiene 8 números.';
    if (!preg_match('/^9[0-9]{8}$/', $telefono))     $errores['telefono']  = 'Escribe 9 números, empezando con 9.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errores['email']     = 'Escribe un correo válido.';
    if (!in_array($distrito, DISTRITOS, true))       $errores['distrito']  = 'Elige tu distrito.';
    if (strlen($clave) < 8)                          $errores['clave']     = 'Mínimo 8 caracteres.';
    if ($clave !== $clave2)                          $errores['clave2']    = 'Las contraseñas no coinciden.';

    $usuarios = leer_json('usuarios');
    if (!isset($errores['email'])) {
        foreach ($usuarios as $u) {
            if ($u['email'] === $email) $errores['email'] = 'Ese correo ya está registrado.';
        }
    }

    if (!$errores) {
        $usuarios[] = [
            'rol' => $rol, 'nombre' => $nombre, 'apellidos' => $apellidos, 'dni' => $dni,
            'telefono' => $telefono, 'email' => $email, 'distrito' => $distrito,
            'clave' => password_hash($clave, PASSWORD_DEFAULT),
        ];
        guardar_json('usuarios', $usuarios);
        $_SESSION['usuario'] = ['nombre' => $nombre, 'apellidos' => $apellidos, 'email' => $email,
                                'rol' => $rol, 'telefono' => $telefono, 'distrito' => $distrito];
        $creado = true;
    }
}

// Ayuda para recordar lo escrito y marcar errores
function val($c) { return e($_POST[$c] ?? ''); }
function inv($c) { global $errores; return isset($errores[$c]) ? 'is-invalid' : ''; }
function msg($c) { global $errores; return e($errores[$c] ?? ''); }

$titulo = 'Registrarme';
require 'includes/header.php';
?>

  <main class="container py-4 py-lg-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 class="display-title fw-bold">Crea tu cuenta</h1>
        <p class="text-secondary">Primero tus datos; luego completas tu perfil según lo que necesites.</p>

        <?php if ($creado): ?>
          <div class="alert alert-success">
            ¡Cuenta creada!
            <?php if ($rol === 'trabajador'): ?>
              <a href="publicar.php" class="fw-semibold">Completar mi servicio</a>
            <?php else: ?>
              <a href="index.php" class="fw-semibold">Buscar trabajo</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
        <form method="post" class="card-ayni shadow-sm p-4" novalidate>
          <fieldset class="mb-4">
            <legend class="h6">¿Qué quieres hacer en Ayni?</legend>
            <div class="row g-2">
              <div class="col-sm-6">
                <input type="radio" class="btn-check" name="rol" id="rol-cliente" value="cliente" <?= $rol === 'cliente' ? 'checked' : '' ?>>
                <label class="btn btn-rol w-100 text-start p-3" for="rol-cliente">
                  <i class="bi bi-search"></i> <strong>Busco trabajadores</strong><br><small class="text-secondary">Soy cliente</small>
                </label>
              </div>
              <div class="col-sm-6">
                <input type="radio" class="btn-check" name="rol" id="rol-trabajador" value="trabajador" <?= $rol === 'trabajador' ? 'checked' : '' ?>>
                <label class="btn btn-rol w-100 text-start p-3" for="rol-trabajador">
                  <i class="bi bi-tools"></i> <strong>Ofrezco mi servicio</strong><br><small class="text-secondary">Soy trabajador</small>
                </label>
              </div>
            </div>
          </fieldset>

          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="nombre">Nombre</label>
              <input id="nombre" name="nombre" class="form-control <?= inv('nombre') ?>" value="<?= val('nombre') ?>">
              <div class="invalid-feedback"><?= msg('nombre') ?></div>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="apellidos">Apellidos</label>
              <input id="apellidos" name="apellidos" class="form-control <?= inv('apellidos') ?>" value="<?= val('apellidos') ?>">
              <div class="invalid-feedback"><?= msg('apellidos') ?></div>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="dni">DNI</label>
              <input id="dni" name="dni" class="form-control <?= inv('dni') ?>" inputmode="numeric" maxlength="8" value="<?= val('dni') ?>">
              <div class="invalid-feedback"><?= msg('dni') ?></div>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="telefono">Teléfono (WhatsApp)</label>
              <input id="telefono" name="telefono" class="form-control <?= inv('telefono') ?>" inputmode="numeric" maxlength="9" placeholder="9XXXXXXXX" value="<?= val('telefono') ?>">
              <div class="invalid-feedback"><?= msg('telefono') ?></div>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="email">Correo</label>
              <input id="email" name="email" class="form-control <?= inv('email') ?>" type="email" value="<?= val('email') ?>">
              <div class="invalid-feedback"><?= msg('email') ?></div>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="distrito">Distrito</label>
              <select id="distrito" name="distrito" class="form-select <?= inv('distrito') ?>">
                <option value="">Elige un distrito</option>
                <?php foreach (DISTRITOS as $d): ?>
                  <option <?= post('distrito') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback"><?= msg('distrito') ?></div>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="clave">Contraseña</label>
              <input id="clave" name="clave" class="form-control <?= inv('clave') ?>" type="password">
              <div class="invalid-feedback"><?= msg('clave') ?></div>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold" for="clave2">Repite la contraseña</label>
              <input id="clave2" name="clave2" class="form-control <?= inv('clave2') ?>" type="password">
              <div class="invalid-feedback"><?= msg('clave2') ?></div>
            </div>
            <div class="col-12"><button class="btn btn-ayni px-4" type="submit">Crear cuenta</button></div>
          </div>
        </form>
        <?php endif; ?>

        <p class="text-center mt-3 mb-0">¿Ya tienes cuenta? <a href="login.php">Ingresar</a></p>
      </div>
    </div>
  </main>

<?php require 'includes/footer.php'; ?>
