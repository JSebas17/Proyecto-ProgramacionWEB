// ===== calificar.php: estrellas y chips (el guardado lo hace PHP) =====
const campoPuntaje = document.getElementById("puntaje");
const estrellas = document.querySelectorAll(".star-btn");

// Pinta de la 1 hasta la elegida y guarda el valor en el campo oculto
function pintar(puntaje) {
  estrellas.forEach(e => e.classList.toggle("on", Number(e.dataset.v) <= puntaje));
  if (puntaje) document.getElementById("estrellas-txt").textContent = puntaje + " de 5";
}

estrellas.forEach(estrella => estrella.addEventListener("click", () => {
  campoPuntaje.value = estrella.dataset.v;
  pintar(Number(estrella.dataset.v));
}));
pintar(Number(campoPuntaje.value));

// Chips: el checkbox oculto cambia solo; aquí solo se pinta el estado
document.querySelectorAll("#chips .chip input").forEach(input =>
  input.addEventListener("change", () => input.parentElement.classList.toggle("active", input.checked)));
