// ===== Publicar / editar servicio: vista previa en vivo y casillas de fotos =====
// El guardado lo hace Laravel (ServicioController); aquí solo se maneja lo visual.

const MAX_FOTOS = 4;
const TIPOS     = ["image/jpeg", "image/png", "image/webp"];
const MAX_BYTES = 3 * 1024 * 1024;

/** Atajo para obtener un elemento por su id. */
const campo = id => document.getElementById(id);

/** Devuelve el texto visible de la opción elegida en un <select> (no su id). */
function textoSeleccionado(id) {
  const select = campo(id);
  return select.options[select.selectedIndex]?.text ?? "";
}

// ---------- 1) Tarjeta de vista previa ----------
function actualizarVistaPrevia() {
  campo("p-sub").textContent    = textoSeleccionado("oficio") + " · " + textoSeleccionado("distrito");
  campo("p-precio").textContent = "desde S/ " + (campo("tarifa").value || "—");
  campo("p-desc").textContent   = campo("descripcion").value.slice(0, 90);
}
["oficio", "distrito", "tarifa", "descripcion"].forEach(id =>
  campo(id).addEventListener("input", actualizarVistaPrevia));
actualizarVistaPrevia();

// ---------- 2) Casillas de fotos de trabajos (hasta 4) ----------
const input    = campo("fotos");
const rejilla  = campo("rejilla-fotos");
const contador = campo("contador-fotos");
const aviso    = campo("fotos-aviso");
const lista    = new DataTransfer(); // acumula lo elegido: un <input file> normal borraría la elección anterior
let nuevas = [];                     // [{ archivo, url }]

const decir = texto => { aviso.textContent = texto; aviso.hidden = !texto; };

/** Fotos que ya tenía el servicio y NO están marcadas para quitar. */
const existentesActivas = () => rejilla.querySelectorAll("[data-existente]:not(.marcada)").length;

/** Redibuja las casillas nuevas y el contador, y sincroniza el <input> con lo elegido. */
function dibujar() {
  rejilla.querySelectorAll("[data-js]").forEach(n => n.remove());

  nuevas.forEach((n, i) => {
    const casilla = document.createElement("div");
    casilla.className = "upload-slot llena";
    casilla.dataset.js = "";

    const img = document.createElement("img");
    img.src = n.url; img.alt = "Foto nueva " + (i + 1);

    const x = document.createElement("button");
    x.type = "button"; x.className = "quitar"; x.setAttribute("aria-label", "Quitar esta foto");
    x.innerHTML = '<i class="bi bi-x-lg"></i>';
    x.addEventListener("click", () => { URL.revokeObjectURL(n.url); nuevas.splice(i, 1); sincronizar(); dibujar(); });

    casilla.append(img, x);
    rejilla.append(casilla);
  });

  // Casillas vacías "+" hasta completar 4
  const libres = Math.max(0, MAX_FOTOS - existentesActivas() - nuevas.length);
  for (let i = 0; i < libres; i++) {
    const mas = document.createElement("label");
    mas.className = "upload-slot"; mas.htmlFor = "fotos"; mas.dataset.js = "";
    mas.innerHTML = '<i class="bi bi-plus-lg" aria-hidden="true"></i><span class="visually-hidden">Agregar foto</span>';
    rejilla.append(mas);
  }

  contador.textContent = (existentesActivas() + nuevas.length) + " de " + MAX_FOTOS + " fotos";
}

/** Copia la lista de archivos elegidos al <input> real (lo que se envía al servidor). */
function sincronizar() {
  lista.items.clear();
  nuevas.forEach(n => lista.items.add(n.archivo));
  input.files = lista.files;
}

input.addEventListener("change", () => {
  decir("");
  for (const archivo of input.files) {
    if (!TIPOS.includes(archivo.type))   { decir("Solo JPG, PNG o WEBP."); continue; }
    if (archivo.size > MAX_BYTES)        { decir("Cada foto pesa máximo 3 MB."); continue; }
    if (existentesActivas() + nuevas.length >= MAX_FOTOS) { decir("Máximo 4 fotos en total."); break; }
    nuevas.push({ archivo, url: URL.createObjectURL(archivo) });
  }
  sincronizar();
  dibujar();
});

// Fotos que ya existían (al editar): la "x" las marca o desmarca para quitarlas al guardar
rejilla.querySelectorAll("[data-existente]").forEach(casilla => {
  const marca = casilla.querySelector("input[type=checkbox]");
  casilla.querySelector(".quitar").addEventListener("click", () => {
    marca.checked = !marca.checked;
    casilla.classList.toggle("marcada", marca.checked);
    decir(""); dibujar();
  });
});

dibujar();
