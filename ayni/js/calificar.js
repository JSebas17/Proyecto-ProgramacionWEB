// ===== calificar.html: estrellas, chips y envío =====
let puntaje = 0;

// Estrella: pinta de la 1 hasta la elegida
document.querySelectorAll(".star-btn").forEach(estrella => {
  estrella.addEventListener("click", () => {
    puntaje = Number(estrella.dataset.v);
    document.querySelectorAll(".star-btn").forEach(e =>
      e.classList.toggle("on", Number(e.dataset.v) <= puntaje));
    document.getElementById("estrellas-txt").textContent = puntaje + " de 5";
  });
});

// Chips: activar / desactivar
document.querySelectorAll("#chips .chip").forEach(chip =>
  chip.addEventListener("click", () => chip.classList.toggle("active")));

// Enviar (simulado): exige al menos 1 estrella
document.getElementById("form-calificar").addEventListener("submit", (e) => {
  e.preventDefault();
  const msg = document.getElementById("msg");
  msg.className = "alert mt-3 mb-0 " + (puntaje ? "alert-success" : "alert-danger");
  msg.textContent = puntaje
    ? "Calificación enviada. Aparecerá en el perfil cuando ambos califiquen."
    : "Elige de 1 a 5 estrellas antes de enviar.";
});
