const campoPuntaje = document.getElementById("puntaje");
const estrellas = document.querySelectorAll(".star-btn");

function pintar(puntaje) {
  estrellas.forEach(e => e.classList.toggle("on", Number(e.dataset.v) <= puntaje));
  if (puntaje) document.getElementById("estrellas-txt").textContent = puntaje + " de 5";
}

estrellas.forEach(estrella => estrella.addEventListener("click", () => {
  campoPuntaje.value = estrella.dataset.v;
  pintar(Number(estrella.dataset.v));
}));
pintar(Number(campoPuntaje.value));

document.querySelectorAll("#chips .chip input").forEach(input =>
  input.addEventListener("change", () => input.parentElement.classList.toggle("active", input.checked)));
