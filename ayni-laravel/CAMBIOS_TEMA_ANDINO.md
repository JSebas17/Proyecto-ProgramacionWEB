# Ayni · Tema andino, logo grande, carruseles y dashboard

Nada de lo que ya funcionaba fue reescrito: rutas, modelos, migraciones, controladores existentes, formularios y pruebas siguen igual.
Todo lo nuevo se **suma** encima.

## Paleta (los 4 colores de la imagen)
| Nombre | Color | Uso |
|---|---|---|
| Dye Red | `#9A0000` | cabecera, botones, títulos activos |
| Maize Yellow | `#E4BC40` | destellos, WhatsApp, estrellas, acentos |
| Inca Stone | `#787878` | pie de página, detalles, tablero oscuro (en tono oscuro) |
| Alpaca White | `#F3F0E7` | fondo general |

## Archivos nuevos
- `public/css/tema-andino.css` — se carga DESPUÉS de `styles.css` y solo sobrescribe colores y adornos (el original queda intacto).
- `public/js/graficas.js` — gráficas SVG propias (dona, pastel, barras, líneas, medidor). Sin CDN ni librerías.
- `public/js/inicio.js` — carruseles de la página principal (JS puro).
- `resources/views/components/logo-ayni.blade.php` — logo grande (chakana dorada con aros giratorios).
- `resources/views/partials/hero-inicio.blade.php` — héroe con logo, destellos y carrusel.

## Archivos editados (cambios pequeños y aditivos)
- `resources/views/layouts/app.blade.php` — +1 línea para cargar `tema-andino.css` y color del navegador.
- `resources/views/home.blade.php` — héroe arriba, carrusel «Trabajadores destacados» y `@push` de `inicio.js`.
  El héroe y el carrusel solo se muestran en la portada (sin filtros ni paginación); al buscar, la lista queda como estaba.
- `resources/views/admin/panel.blade.php` — dashboard nuevo. Conserva los títulos que verifican las pruebas
  («Verificación de trabajadores», «Calificaciones por estrella», «Trabajadores por oficio», «Aún no hay calificaciones»)
  y deja las barras originales dentro de `<noscript>` como respaldo.
- `app/Http/Controllers/Admin/PanelController.php` — +2 métodos privados (`actividadMensual`, `ranking`) y 2 claves nuevas
  en el arreglo cacheado. La vista usa `?? []`, así que un caché viejo no la rompe.

## Cómo verlo
No hace falta ningún comando nuevo: `php artisan serve` y entra a `/` y `/admin`
(admin@ayni.test / password123 si ya corriste los seeders). Si ves el panel sin datos nuevos, `php artisan cache:clear`.

## Nota sobre pruebas
`GraficasNavegacionTest::test_la_paleta_de_la_app_es_solo_blanco_negro_y_plomo` lee solo `public/css/styles.css`, que no se modificó,
así que sigue pasando. Si quieres que esa prueba refleje la nueva paleta, apúntala a `tema-andino.css` con los 4 colores.
