// ===== Selector de foto de perfil (registro y "Mi perfil") =====
// Muestra la foto elegida al instante y avisa si el archivo no sirve, ANTES de subirlo.
// El servidor vuelve a validar y además recorta y comprime la imagen (App\Support\Imagenes).

const TIPOS_FOTO = ["image/jpeg", "image/png", "image/webp"];
const MAX_BYTES  = 3 * 1024 * 1024; // 3 MB, igual que en PHP

document.querySelectorAll("[data-foto]").forEach(raiz => {
  const input  = raiz.querySelector("[data-foto-input]");
  const vista  = raiz.querySelector("[data-foto-vista]");   // círculo del avatar
  const aviso  = raiz.querySelector("[data-foto-aviso]");   // mensaje de error
  const quitar = raiz.querySelector("[data-foto-quitar]");  // casilla "Quitar foto" (solo Mi perfil)
  const original = vista.innerHTML;                          // lo que había al cargar (foto actual o iniciales)

  const decir = texto => { aviso.textContent = texto; aviso.hidden = !texto; };

  /** Dibuja las iniciales (cuando no hay foto). */
  const mostrarIniciales = () => {
    const span = document.createElement("span");
    span.setAttribute("aria-hidden", "true");
    span.textContent = vista.dataset.iniciales || "?";
    vista.replaceChildren(span);
  };

  /** Avisa a otras partes de la página (p. ej. la tarjeta de vista previa) que cambió la foto. */
  const avisarCambio = url =>
    document.dispatchEvent(new CustomEvent("foto:cambio", { detail: { url } }));

  input.addEventListener("change", () => {
    const archivo = input.files[0];
    decir("");

    if (!archivo) { vista.innerHTML = original; avisarCambio(null); return; }

    if (!TIPOS_FOTO.includes(archivo.type)) {
      decir("Solo JPG, PNG o WEBP.");
      input.value = "";
      return;
    }
    if (archivo.size > MAX_BYTES) {
      decir("La foto pesa más de 3 MB. Elige una más liviana.");
      input.value = "";
      return;
    }

    const img = document.createElement("img");
    img.alt = "";
    img.src = URL.createObjectURL(archivo);
    vista.replaceChildren(img);
    if (quitar) quitar.checked = false;
    avisarCambio(img.src);
  });

  quitar?.addEventListener("change", () => {
    if (quitar.checked) {
      input.value = "";
      decir("");
      mostrarIniciales();
      avisarCambio(null);
    } else {
      vista.innerHTML = original;
    }
  });
});
