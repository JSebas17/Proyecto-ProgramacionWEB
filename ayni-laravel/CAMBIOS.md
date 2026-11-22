# Ayni · Mejoras al proyecto Laravel

## Cómo aplicarlas (3 comandos)
Copia el contenido de este zip **encima** de tu carpeta `ayni-laravel` (tu `.env` y `vendor/` no se tocan) y ejecuta:

```bash
php artisan migrate          # agrega la columna "foto" a users y los índices de la fase 7
php artisan storage:link     # IMPRESCINDIBLE: sin esto las fotos subidas no se ven
php artisan test             # 37 pruebas (ver también CAMBIOS_FASES_6_9.md)
```
Si usas XAMPP: activa `extension=gd` (y `exif`) en php.ini para que las fotos se recorten y comprimen. Sin GD la app sigue funcionando y guarda la foto original.

## 1. Fotos de perfil
- Se pueden subir al **registrarse** y en la nueva pantalla **Mi perfil** (menú de la cuenta > "Mi perfil y foto").
- Se recortan en cuadrado (400 px), se corrigen si salieron giradas del celular y se comprimen (de ~4 MB a ~60 KB).
- Se muestran en: tarjetas del buscador, perfil público, reseñas, menú de cuenta, vista previa de publicar, panel admin.
- Sin foto: círculo con iniciales y color propio.

## 2. Diseño según los wireframes
Misma estructura: franja de cabecera con las 3 cifras, barra de menú blanca, buscador con pin de distrito, categorías, chips "Solo verificados / 4 o más / Mejor calificados", tarjetas, 3 pasos, y las pantallas Publicar (4 casillas de fotos + vista previa), Calificar y Perfil. Identidad: tejido andino (chakana + franja escalonada), indigo y magenta. Tipografías propias dentro del proyecto (`public/fonts`), sin depender de Google.

## 3. Pantallas conectadas
- "Ofrecer mi servicio" (`/ofrecer`): visitante -> Registro (ya marcado "Trabajador"); trabajador -> Publicar.
- Login/Registro devuelven a la página que se quería abrir (antes siempre iban al inicio).
- Perfil público <-> Calificar <-> Mi perfil <-> Mis servicios <-> Publicar/Editar, con enlaces de ida y vuelta.
- Pie de página en todas las vistas, páginas 403/404/419/429 con salida al inicio.
- El panel admin enlaza al perfil público de cada trabajador.

## 4. Errores corregidos
| Problema | Efecto |
|---|---|
| `fotos` faltaba en `$fillable` de `Servicio` | Las fotos de trabajos **nunca se guardaban** |
| Filtro "4 o más" usaba `HAVING` sin `GROUP BY` | Error 500 (en SQLite) |
| "Solo verificados" se apagaba al paginar | Resultados distintos en la página 2 |
| El botón "Buscar" olvidaba la categoría elegida | Había que volver a elegirla |
| Login ignoraba la página de origen | Se perdía el flujo "calificar" |
| Trabajadores sin perfil aparecían en el buscador | Tarjetas vacías |
| Scripts de confirmación duplicados | Doble ventana de "¿seguro?" |

## 5. Funciones nuevas
Mi perfil (datos, oficio, tarifa, años de experiencia, descripción, disponibilidad), editar/quitar fotos de cada servicio (máx. 4), orden del buscador, desactivar la propia cuenta, consentimiento de datos en el registro (sección 5.5 del informe), límite de intentos de login, anti doble envío en formularios.

## Pendiente (decisión de equipo)
- **Calificación mutua real**: hoy solo el cliente califica al trabajador. Para que el trabajador califique al cliente hace falta una tabla de "contrataciones" (quién contrató a quién).
- Recuperación de contraseña (figura en el módulo Autenticación del informe).
- Verificación de identidad: hoy el admin marca el check; falta subir/revisar el DNI.
- Datos de ejemplo: los teléfonos del seeder tienen 11 dígitos (51…); los reales se piden de 9.
