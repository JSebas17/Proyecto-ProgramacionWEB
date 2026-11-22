// ===== Vista previa en vivo de la tarjeta en "Mi perfil" =====
// El guardado lo hace Laravel (PerfilController); este archivo solo pinta la tarjeta.

const por = id => document.getElementById(id);

/** Texto visible de la opción elegida en un <select>. */
function textoDe(id) {
  const s = por(id);
  return s && s.selectedIndex >= 0 ? s.options[s.selectedIndex].text : "";
}

/** Iniciales a partir del nombre y el último apellido ("Rosa Quispe" -> "RQ"). */
function iniciales() {
  const nombre = (por("nombre").value.trim().split(/\s+/)[0] || "");
  const partes = por("apellidos").value.trim().split(/\s+/).filter(Boolean);
  const apellido = partes.length ? partes[partes.length - 1] : "";
  return ((nombre[0] || "") + (apellido[0] || "")).toUpperCase() || "?";
}

function actualizar() {
  por("v-nombre").textContent = (por("nombre").value + " " + por("apellidos").value).trim() || "Tu nombre";

  const oficio = por("oficio_id") ? textoDe("oficio_id") : "Cliente";
  por("v-sub").textContent = oficio + " · " + textoDe("distrito_id");

  const precio = por("v-precio");
  if (precio) {
    const t = por("tarifa").value;
    precio.textContent = t ? "desde S/ " + t : "a convenir";
  }

  const estado = por("v-estado");
  if (estado) estado.hidden = por("disponible").checked;

  // Si no hay foto, las iniciales se actualizan al escribir
  const avatar = document.querySelector("[data-preview-avatar]");
  if (avatar && !avatar.querySelector("img")) {
    avatar.dataset.iniciales = iniciales();
    avatar.firstElementChild.textContent = iniciales();
  }
  const vistaForm = document.querySelector("[data-foto-vista]");
  if (vistaForm) vistaForm.dataset.iniciales = iniciales();
}

// La foto elegida en el formulario también se ve en la tarjeta
document.addEventListener("foto:cambio", e => {
  const avatar = document.querySelector("[data-preview-avatar]");
  if (!avatar) return;
  if (e.detail.url) {
    const img = document.createElement("img");
    img.alt = ""; img.src = e.detail.url;
    avatar.replaceChildren(img);
  } else {
    const span = document.createElement("span");
    span.setAttribute("aria-hidden", "true");
    span.textContent = iniciales();
    avatar.replaceChildren(span);
  }
});

document.querySelectorAll("#form-perfil input, #form-perfil select, #form-perfil textarea")
  .forEach(el => el.addEventListener("input", actualizar));
actualizar();
