<?php
require_once 'includes/funciones.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(post('email'));
    $clave = $_POST['clave'] ?? '';

    foreach (leer_json('usuarios') as $u) {
        if ($u['email'] === $email && password_verify($clave, $u['clave'])) {
            session_regenerate_id(true);
            $_SESSION['usuario'] = ['nombre' => $u['nombre'], 'apellidos' => $u['apellidos'], 'email' => $u['email'],
                                    'rol' => $u['rol'], 'telefono' => $u['telefono'], 'distrito' => $u['distrito']];
            header('Location: index.php');
            exit;
        }
    }
    $error = 'Correo o contraseña incorrectos.';
}

$titulo = 'Ingresar';
require 'includes/header.php';
?>

  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-5">
        <h1 class="h2 fw-bold">Ingresar</h1>
        <p class="text-secondary">Entra con tu correo para contactar o publicar.</p>

        <form method="post" class="card-ayni shadow-sm p-4" novalidate>
          <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="email">Correo</label>
            <input id="email" name="email" class="form-control" type="email" placeholder="tucorreo@ejemplo.com" value="<?= e($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="clave">Contraseña</label>
            <input id="clave" name="clave" class="form-control" type="password" required>
          </div>
          <button class="btn btn-ayni w-100" type="submit">Ingresar</button>
        </form>

        <p class="text-center mt-3 mb-0">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
      </div>
    </div>
  </main>

<?php require 'includes/footer.php'; ?>
