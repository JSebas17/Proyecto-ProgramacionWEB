// ===== registro.html: validar y decidir el siguiente paso según el rol =====
const form = document.getElementById("form-registro");

form.addEventListener("submit", (e) => {
  e.preventDefault();

  // Las dos contraseñas deben ser iguales
  const clave2 = document.getElementById("clave2");
  clave2.setCustomValidity(clave2.value === document.getElementById("clave").value ? "" : "no coinciden");

  form.classList.add("was-validated");
  if (!form.checkValidity()) return;

  // Trabajador → completa su servicio; cliente → va al catálogo
  const esTrabajador = form.rol.value === "trabajador";
  const enlace = document.getElementById("continuar");
  enlace.href = esTrabajador ? "publicar.html" : "index.html";
  enlace.textContent = esTrabajador ? "Completar mi servicio" : "Buscar trabajo";
  document.getElementById("ok").classList.remove("d-none");
});
