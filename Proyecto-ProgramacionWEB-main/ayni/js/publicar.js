// ===== publicar.html: vista previa en vivo =====
const campo = (id) => document.getElementById(id);

// Copia lo que escribe el usuario a la tarjeta de vista previa
function actualizarVistaPrevia() {
  campo("p-sub").textContent    = campo("oficio").value + " · " + campo("distrito").value;
  campo("p-precio").textContent = "desde S/ " + (campo("tarifa").value || "—");
  campo("p-desc").textContent   = campo("descripcion").value.slice(0, 90);
}

["oficio", "distrito", "tarifa", "descripcion"].forEach(id =>
  campo(id).addEventListener("input", actualizarVistaPrevia));

// Publicar (simulado): solo valida la tarifa y muestra el mensaje
campo("form-publicar").addEventListener("submit", (e) => {
  e.preventDefault();
  const valida = campo("tarifa").value > 0;
  campo("tarifa").classList.toggle("is-invalid", !valida);
  campo("ok").classList.toggle("d-none", !valida);
});

actualizarVistaPrevia();
