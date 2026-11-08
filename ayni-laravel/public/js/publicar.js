// ===== Vista previa en vivo de la tarjeta (servicios/form.blade.php) =====
// El guardado lo hace Laravel (ServicioController); este archivo solo pinta la vista previa.

/** Atajo para obtener un elemento por su id. */
const campo = (id) => document.getElementById(id);

/** Devuelve el texto visible de la opción elegida en un <select> (no su id). */
function textoSeleccionado(id) {
  const select = campo(id);
  return select.options[select.selectedIndex]?.text ?? "";
}

/** Copia oficio, distrito, tarifa y descripción a la tarjeta de vista previa. */
function actualizarVistaPrevia() {
  campo("p-sub").textContent    = textoSeleccionado("oficio") + " · " + textoSeleccionado("distrito");
  campo("p-precio").textContent = "desde S/ " + (campo("tarifa").value || "—");
  campo("p-desc").textContent   = campo("descripcion").value.slice(0, 90);
}

// Actualiza al escribir o cambiar cualquier campo, y una vez al cargar
["oficio", "distrito", "tarifa", "descripcion"].forEach(id =>
  campo(id).addEventListener("input", actualizarVistaPrevia));
actualizarVistaPrevia();