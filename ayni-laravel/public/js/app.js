// ===== Comportamientos compartidos por todas las pantallas =====

// 1) Confirmación antes de enviar cualquier formulario marcado con data-confirmar="¿Seguro?"
document.querySelectorAll("form[data-confirmar]").forEach(form =>
  form.addEventListener("submit", e => {
    if (!confirm(form.dataset.confirmar)) e.preventDefault();
  }));

// 2) Evita el doble envío en formularios con data-bloquear (útil al subir fotos con internet lento):
//    después de enviar, el botón queda desactivado y dice "Guardando…".
document.querySelectorAll("form[data-bloquear]").forEach(form =>
  form.addEventListener("submit", e => {
    if (e.defaultPrevented) return;
    const boton = form.querySelector("[type=submit]");
    if (!boton) return;
    // setTimeout: se desactiva DESPUÉS de que el navegador empezó a enviar el formulario
    setTimeout(() => {
      boton.dataset.texto = boton.textContent;
      boton.textContent = "Guardando…";
      boton.disabled = true;
    }, 0);
  }));

// Si el usuario vuelve con el botón "atrás", se restauran los botones
window.addEventListener("pageshow", e => {
  if (!e.persisted) return;
  document.querySelectorAll("form[data-bloquear] [type=submit][data-texto]").forEach(b => {
    b.textContent = b.dataset.texto;
    b.disabled = false;
  });
});

// 3) Filtros del buscador: los controles con data-autoenviar envían el formulario al cambiar
document.querySelectorAll("[data-autoenviar]").forEach(control =>
  control.addEventListener("change", () => control.form.submit()));

// 4) Botón "volver arriba": aparece al bajar más de una pantalla y sube suavemente
const botonSubir = document.getElementById("subir");
if (botonSubir) {
  const mostrar = () => { botonSubir.hidden = window.scrollY < window.innerHeight; };
  window.addEventListener("scroll", mostrar, { passive: true });
  mostrar();
}
