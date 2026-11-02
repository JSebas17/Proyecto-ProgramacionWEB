```php
<?php
require_once __DIR__ . '/../sistema/app/models/trabajador.php';

function e($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

$id = (int)($_GET['id'] ?? 0);
$t = buscar_trabajador($id);

if (!$t) {
    http_response_code(404);
    die('Trabajador no encontrado. <a href="index.php">Volver</a>');
}

$resenas = [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= e($t['nombre']) ?> | AYNI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            background: #f5f5f5;
            color: #111;
        }

        .perfil-contenedor {
            max-width: 900px;
            margin: 40px auto;
        }

        .perfil-tarjeta {
            background: white;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 20px;
        }

        .perfil-nombre {
            font-size: 28px;
            font-weight: 700;
        }

        .perfil-oficio {
            color: #666;
            margin-bottom: 15px;
        }

        .perfil-precio {
            font-size: 25px;
            font-weight: bold;
        }

        .boton-negro {
            display: inline-block;
            background: #111;
            color: white;
            padding: 12px 20px;
            border-radius: 7px;
            text-decoration: none;
            text-align: center;
        }

        .boton-negro:hover {
            background: #333;
            color: white;
        }

        .boton-claro {
            display: inline-block;
            border: 1px solid #111;
            color: #111;
            padding: 11px 20px;
            border-radius: 7px;
            text-decoration: none;
        }

        .boton-claro:hover {
            background: #eee;
            color: #111;
        }

        .seccion-titulo {
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .dato {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body>

<div class="container perfil-contenedor">

    <a href="index.php" class="text-dark text-decoration-none">
        ← Volver a resultados
    </a>

    <!-- INFORMACIÓN DEL TRABAJADOR -->
    <div class="perfil-tarjeta mt-3">

        <div class="perfil-nombre"><?= e($t['nombre']) ?></div>

        <div class="perfil-oficio">
            <?= e($t['oficio']) ?> · <?= e($t['distrito']) ?>
        </div>

        <span class="badge bg-dark">
            <?= !empty($t['verificado']) ? 'Verificado' : 'Nuevo' ?>
        </span>

        <hr>

        <p class="mb-1">
            <strong><?= number_format((float)$t['nota'], 1) ?></strong>
            · <?= (int)$t['resenas'] ?> reseñas
        </p>

        <div class="perfil-precio">
            S/ <?= number_format((float)$t['precio'], 2) ?>
        </div>
        <div class="dato">Por visita</div>

    </div>

    <!-- DESCRIPCIÓN -->
    <div class="perfil-tarjeta">
        <h2 class="seccion-titulo">Sobre el trabajador</h2>

        <p class="mb-0">
            <?= e($t['descripcion'] ?? 'Este trabajador todavía no ha agregado una descripción.') ?>
        </p>
    </div>

    <!-- TRABAJOS -->
    <div class="perfil-tarjeta">
        <h2 class="seccion-titulo">Trabajos recientes</h2>
        <p class="text-secondary mb-0">
            Todavía no hay trabajos publicados.
        </p>
    </div>

    <!-- RESEÑAS -->
    <div class="perfil-tarjeta">
        <h2 class="seccion-titulo">Reseñas de clientes</h2>
        <p class="text-secondary mb-0">
            Todavía no tiene reseñas.
        </p>
    </div>

    <!-- CONTACTO -->
    <div class="perfil-tarjeta">
        <h2 class="seccion-titulo">Contactar al trabajador</h2>

        <p class="dato">Tarifa publicada por el trabajador.</p>

        <?php
        $telefono = preg_replace('/\D/', '', $t['tel'] ?? '');
        ?>

        <?php if ($telefono): ?>
            <a class="boton-negro mb-2"
               href="https://wa.me/<?= e($telefono) ?>"
               target="_blank" rel="noopener">
                Contactar por WhatsApp
            </a>
        <?php endif; ?>

        <a href="calificar.php?id=<?= (int)$t['id'] ?>"
           class="boton-claro mb-2">
            Calificar un trabajo
        </a>
    </div>

    <p class="text-center text-secondary small">
        AYNI · Conectando trabajadores con oportunidades.
    </p>

</div>

</body>
</html>
```