# Ayni · Correcciones de las fases 6 a 9

## Cómo aplicarlas
Copia el contenido de este zip encima de tu carpeta `ayni-laravel` y ejecuta:

```bash
php artisan migrate        # crea 3 índices nuevos (fase 7)
php artisan test           # 37 pruebas; saca la captura de esta pantalla (fase 8)
```
No hace falta `composer install` otra vez ni tocar tu `.env`.

**Importante:** copia los archivos completos del zip (la lista está en `ARCHIVOS_CAMBIADOS.txt`) y no los edites a mano mezclando versiones. Un error como `<opcion value={{$d-->nombre` viene de un tipeo al copiar: lo correcto es `<option value="{{ $d->nombre }}">`. Si aparece una pantalla de error de Laravel, limpia las vistas guardadas con `php artisan view:clear`.

---

## Fase 6 · Calificaciones y botón de WhatsApp

| Problema encontrado | Corrección |
|---|---|
| Si el trabajador no tenía teléfono válido, el botón abría `https://wa.me/` vacío | Nuevo accesor `User::whatsapp_url`. Devuelve `null` sin teléfono válido; el perfil muestra "Sin WhatsApp disponible" y la tarjeta del buscador no muestra botón |
| El enlace de WhatsApp estaba armado a mano en 2 vistas (código repetido) | Ahora ambas vistas usan `whatsapp_url` (un solo lugar) |
| La pantalla Calificar decía "Trabajador y cliente se califican entre sí", pero solo califica el cliente | Texto corregido para decir lo que realmente hace la app |
| El selector de servicios mostraba servicios que el usuario ya había calificado (y fallaba al enviar) | Solo se ofrecen los no calificados; si no queda ninguno, vuelve al perfil con un aviso |
| Una consulta extra por cada opción del selector (distrito) | `with('distrito')` en la consulta (eager loading) |
| Se podían enviar reseñas sin límite | `throttle:10,1` en la ruta de guardado |

## Fase 7 · Optimización

- **Eager loading:** el buscador, el perfil y el formulario de calificar cargan sus relaciones en bloque (sin consulta por trabajador). Hay una prueba que lo comprueba: con 5 trabajadores más, el número de consultas no cambia.
- **Caché de catálogos:** `Distrito::catalogo()` y `Oficio::catalogo()` guardan el listado 1 hora. Se limpia solo cuando el admin agrega o elimina un elemento (eventos `saved`/`deleted` del modelo).
- **Caché del panel admin:** ya existía (10 min), pero solo se borraba al moderar. Ahora también se borra cuando cambian usuarios, servicios o calificaciones, así que nunca muestra cifras viejas.
- **Índices** (migración `2026_11_16_000001_add_indices_de_optimizacion`):
  - `users (rol, activo, verificado)`: filtro base del buscador.
  - `servicios (trabajador_id, estado)`: servicios activos de un trabajador.
  - `calificaciones (autor_id, servicio_id)`: "¿ya calificó este servicio?".

## Fase 8 · Pruebas

Se agregaron 23 pruebas nuevas (de 14 a 37 en total):
- `CalificacionesWhatsappTest` (11): enlace de WhatsApp, teléfono ausente, validación de puntaje/chips/comentario, servicio ajeno, admin y autocalificación bloqueados, no repetir calificación, límite de envíos, caché del panel.
- `OptimizacionTest` (5): sin N+1 en el buscador, caché de catálogos y su limpieza, caché del panel, existencia de los índices.
- `GraficasNavegacionTest` (7): gráfica de estrellas, panel con 3 gráficas, migas de pan, accesos del perfil, cache viejo sin romper el panel y paleta 100 % en grises.

Para la captura: `php artisan test` y toma la pantalla completa con el resumen final.

## Fase 9 · Texto para actualizar el informe

### Framework y herramientas
Laravel 13 sobre PHP 8.3, con plantillas Blade, Bootstrap 5 (paginación y componentes), Eloquent ORM y migraciones versionadas. Base de datos SQLite en desarrollo (compatible con MySQL). Pruebas con PHPUnit 12 mediante `php artisan test`.

### Arquitectura
Patrón MVC. Las rutas (`routes/web.php`) se agrupan por permiso: públicas, solo visitantes (`guest`), con sesión (`auth`), solo trabajador (`rol:trabajador`) y solo administrador (`rol:admin`). Los controladores validan y coordinan; la lógica de datos vive en los modelos (relaciones, scopes y accesores como `whatsapp_url` y `foto_url`); `App\Support\Imagenes` concentra el recorte y la compresión de fotos. Los catálogos (oficios y distritos) son tablas configurables desde el panel del administrador, sin tocar código.

### Seguridad
- **Contraseñas:** cifradas con hash (cast `hashed`); el ID de sesión se regenera al iniciar sesión.
- **Fuerza bruta y spam:** `throttle` de 6 intentos por minuto en el login y de 10 por minuto al calificar.
- **CSRF:** todos los formularios llevan `@csrf`.
- **XSS:** la salida usa `{{ }}`, que escapa el HTML (hay una prueba con un comentario que incluye `<b>`).
- **Inyección SQL:** Eloquent y parámetros enlazados en todas las búsquedas.
- **Validación con listas cerradas:** rol de registro (nadie puede registrarse como admin), etiquetas de reseña, orden del buscador.
- **Autorización:** solo el dueño edita su servicio (403 para otros); el admin no califica; nadie se califica a sí mismo; un usuario califica una sola vez cada servicio.
- **Archivos:** las fotos se validan (tipo y 3 MB) y se recomprimen.
- **Privacidad:** consentimiento explícito en el registro; las cuentas se desactivan (borrado lógico) en lugar de borrarse.

### Pruebas
37 pruebas automáticas (`php artisan test`) sobre base de datos en memoria: páginas públicas, filtros del buscador, registro y login, perfil y fotos, CRUD de servicios, permisos por rol, calificaciones, botón de WhatsApp, caché, índices y ausencia de consultas N+1.

---

## Entrega final · Diseño y navegación (toda la app)

**Paleta: solo blanco, negro y plomo.** Se cambiaron los colores en `public/css/styles.css`, el favicon y el `theme-color`. La estructura, el espaciado y las tipografías no cambian: es el mismo diseño con otra paleta. Lo que antes se distinguía por color ahora también se distingue por forma:
- Identidad verificada: gris oscuro con ícono de check.
- Mensaje de error: fondo negro con texto blanco e ícono de alerta.
- Campo con error: borde negro grueso.
- Categoría o filtro activo: relleno negro.
- Estrellas: negras las marcadas, plomo claro las vacías.
- Botón de WhatsApp: gris oscuro con su ícono (ya no verde).

**Gráficas (barras en grises, accesibles con `aria-label`):**
- Perfil público: "Distribución de calificaciones" (cuántas reseñas de 5 a 1 estrella).
- Panel admin: verificación de trabajadores, calificaciones por estrella y trabajadores por oficio. Los datos viajan en el caché del panel (una consulta con `GROUP BY` cada una).
- Componente reutilizable: `<x-grafica-barras :datos="[...]" />`.

**Navegación:**
- Migas de pan (`<x-migas>`) en perfil público, calificar, mi perfil, mis servicios y todo el panel admin.
- Accesos dentro del perfil: Sobre mi trabajo · Trabajos · Reseñas.
- Pestañas del panel admin con la página actual marcada (`aria-current`).
- Botón "volver arriba" que aparece al bajar.

---

## Pendiente / límites conocidos
- **No pude ver la app funcionando** (sin `vendor/` no arranca Laravel), así que el recoloreo y las gráficas no se vieron en pantalla. Revisa `/`, un perfil y `/admin` en el navegador.
- **No pude ejecutar `php artisan test` aquí** (el zip no incluye `vendor/` y no tengo red para instalarlo). El código pasa la revisión de sintaxis (`php -l`), pero las pruebas nuevas no se han corrido: si alguna falla, avísame con el mensaje de error.
- El informe no venía en el zip, por eso la fase 9 está como texto para pegar.
- La regla "una calificación por usuario y servicio" se aplica en el controlador, no con un índice único: los datos de ejemplo del seeder repiten autor y romperían la migración. Si quieres el índice único, hay que cambiar primero el seeder.
- Los métodos `down()` de las migraciones de `perfiles_trabajador` y `calificaciones` borran un nombre de tabla equivocado (`perfil_trabajadors`, `calificacions`); solo afecta a `migrate:rollback`.
