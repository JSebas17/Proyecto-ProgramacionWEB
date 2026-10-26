// ===== login.html: validar y simular el ingreso =====
const form = document.getElementById("form-login");

form.addEventListener("submit", (e) => {
  e.preventDefault();
  form.classList.add("was-validated");                       // muestra los errores de Bootstrap
  document.getElementById("ok").classList.toggle("d-none", !form.checkValidity());
});
