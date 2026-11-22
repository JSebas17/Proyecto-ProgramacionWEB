// ===== Calificar: estrellas accesibles y etiquetas (el guardado lo hace PHP) =====
const campoPuntaje = document.getElementById("puntaje");
const estrellas    = [...document.querySelectorAll(".star-btn")];
const textoEstrellas = document.getElementById("estrellas-txt");
const NOMBRES = ["", "Malo", "Regular", "Bien", "Muy bien", "Excelente"];

/** Pinta de la 1 hasta la elegida y deja el estado listo para lectores de pantalla. */
function pintar(puntaje) {
  estrellas.forEach(e => {
    const v = Number(e.dataset.v);
    e.classList.toggle("on", v <= puntaje);
    e.setAttribute("role", "radio");
    e.setAttribute("aria-checked", String(v === puntaje));
    e.tabIndex = (v === puntaje || (!puntaje && v === 1)) ? 0 : -1; // una sola parada con Tab
  });
  if (puntaje) textoEstrellas.textContent = puntaje + " de 5 · " + NOMBRES[puntaje];
}

function elegir(valor, enfocar = false) {
  campoPuntaje.value = valor;
  pintar(valor);
  textoEstrellas.classList.remove("text-danger");
  if (enfocar) estrellas[valor - 1].focus();
}

estrellas.forEach(estrella => {
  estrella.addEventListener("click", () => elegir(Number(estrella.dataset.v)));
  // Flechas del teclado: cambian la calificación (como un grupo de radios)
  estrella.addEventListener("keydown", e => {
    const actual = Number(campoPuntaje.value) || 0;
    if (e.key === "ArrowRight" || e.key === "ArrowUp")   { e.preventDefault(); elegir(Math.min(5, actual + 1), true); }
    if (e.key === "ArrowLeft"  || e.key === "ArrowDown") { e.preventDefault(); elegir(Math.max(1, actual - 1), true); }
  });
});
pintar(Number(campoPuntaje.value));

// Aviso inmediato si intenta enviar sin elegir estrellas
document.querySelector("form[data-calificar]")?.addEventListener("submit", e => {
  if (!campoPuntaje.value) {
    e.preventDefault();
    textoEstrellas.textContent = "Elige de 1 a 5 estrellas antes de enviar.";
    textoEstrellas.classList.add("text-danger");
    estrellas[0].focus();
  }
});

// Etiquetas: el checkbox oculto cambia solo; aquí solo se pinta el estado
document.querySelectorAll("#chips .chip input").forEach(input =>
  input.addEventListener("change", () => input.parentElement.classList.toggle("active", input.checked)));
