// ===== Campo de CV (PDF) =====
// 1) Avisa si el archivo no sirve ANTES de subirlo (el servidor vuelve a validar).
// 2) En el registro, muestra el campo solo cuando se elige "Ofrezco mi servicio".

const MAX_CV = 5 * 1024 * 1024; // 5 MB, igual que en PHP

document.querySelectorAll("[data-cv]").forEach(raiz => {
  const input = raiz.querySelector("[data-cv-input]");
  const aviso = raiz.querySelector("[data-cv-aviso]");
  if (!input || !aviso) return;

  input.addEventListener("change", () => {
    const archivo = input.files[0];
    let mensaje = "";

    if (archivo) {
      const esPdf = archivo.type === "application/pdf" || archivo.name.toLowerCase().endsWith(".pdf");
      if (!esPdf) mensaje = "El CV debe ser un archivo PDF.";
      else if (archivo.size > MAX_CV) mensaje = "El CV pesa máximo 5 MB.";
    }

    aviso.textContent = mensaje;
    aviso.hidden = !mensaje;
    if (mensaje) input.value = ""; // descarta el archivo que no sirve
  });
});

const radiosRol  = document.querySelectorAll('input[name="rol"]');
const soloTrabajador = document.querySelectorAll("[data-solo-trabajador]");

if (radiosRol.length && soloTrabajador.length) {
  const actualizar = () => {
    const esTrabajador = document.querySelector('input[name="rol"]:checked')?.value === "trabajador";
    soloTrabajador.forEach(el => { el.hidden = !esTrabajador; });
  };
  radiosRol.forEach(r => r.addEventListener("change", actualizar));
  actualizar();
}
