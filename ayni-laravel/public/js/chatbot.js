// ===== Chatbot de respuestas fijas =====
// Los textos vienen del servidor (App\Support\Chatbot) en el bloque JSON #chatbot-datos.
// Todo se escribe con textContent (nunca innerHTML), así ningún texto puede inyectar HTML.

(() => {
  const raiz = document.getElementById("chatbot");
  const bloque = document.getElementById("chatbot-datos");
  if (!raiz || !bloque) return;

  let datos;
  try { datos = JSON.parse(bloque.textContent); } catch { return; }

  const boton    = document.getElementById("chatbot-abrir");
  const panel    = document.getElementById("chatbot-panel");
  const cerrar   = document.getElementById("chatbot-cerrar");
  const mensajes = document.getElementById("chatbot-mensajes");
  const opciones = document.getElementById("chatbot-opciones");
  const form     = document.getElementById("chatbot-form");
  const texto    = document.getElementById("chatbot-texto");

  let iniciado = false;

  /** Minúsculas y sin tildes: "Contraseña" y "contrasena" cuentan igual. */
  const normalizar = s => s.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

  const bajar = () => { mensajes.scrollTop = mensajes.scrollHeight; };

  const mensaje = (quien, contenido, enlaces = []) => {
    const burbuja = document.createElement("div");
    burbuja.className = "chatbot-msg " + quien;
    burbuja.textContent = contenido;

    enlaces.forEach(e => {
      let url;
      try { url = new URL(e.url, location.origin); } catch { return; }
      if (!["http:", "https:"].includes(url.protocol)) return; // nada de javascript: ni otros esquemas

      const a = document.createElement("a");
      a.className = "chatbot-enlace";
      a.href = url.href;
      a.textContent = e.texto + " →";
      if (url.origin !== location.origin) { a.target = "_blank"; a.rel = "noopener noreferrer"; }
      burbuja.append(document.createElement("br"), a);
    });

    mensajes.append(burbuja);
    bajar();
  };

  const botonOpcion = (titulo, alClic, extra = "") => {
    const b = document.createElement("button");
    b.type = "button";
    b.className = "chatbot-opcion " + extra;
    b.textContent = titulo;
    b.addEventListener("click", alClic);
    return b;
  };

  const mostrarOpciones = ids => {
    opciones.replaceChildren(...ids.filter(id => datos.temas[id]).map(id =>
      botonOpcion(datos.temas[id].titulo, () => responder(id, true))));
  };

  const mostrarMenu = () => mostrarOpciones(datos.menu);

  /** Muestra la respuesta de un tema y las preguntas relacionadas. */
  const responder = (id, escribirPregunta) => {
    const tema = datos.temas[id] || datos.temas.otro;
    if (!tema) return;
    if (escribirPregunta) mensaje("yo", tema.titulo);
    mensaje("bot", tema.respuesta, tema.enlaces || []);

    const sugeridas = (tema.siguientes && tema.siguientes.length) ? tema.siguientes : datos.menu.slice(0, 6);
    mostrarOpciones(sugeridas);
    if (id !== "inicio") {
      opciones.append(botonOpcion("Ver todas las preguntas", () => { mensaje("bot", "Estas son todas las preguntas:"); mostrarMenu(); }, "menu"));
    }
  };

  /** Busca el tema cuyas palabras clave (por raíz: "calific" entiende calificar/califico) pesan más en lo escrito. */
  const entender = escrito => {
    const t = normalizar(escrito);
    let mejor = null, puntos = 0;
    Object.entries(datos.temas).forEach(([id, tema]) => {
      // Cada palabra encontrada suma su largo: una frase específica pesa más que una palabra suelta
      const p = (tema.palabras || []).reduce((suma, w) => suma + (t.includes(normalizar(w)) ? w.length : 0), 0);
      if (p > puntos) { puntos = p; mejor = id; }
    });
    return mejor || "otro";
  };

  const esSaludo = escrito => /^(hola|holi|buenas|buenos|hey|ola|saludos)\b/.test(normalizar(escrito.trim()));

  const abrir = () => {
    panel.hidden = false;
    boton.setAttribute("aria-expanded", "true");
    if (!iniciado) {
      iniciado = true;
      mensaje("bot", datos.saludo);
      mostrarMenu();
    }
    texto.focus();
  };

  const cerrarPanel = () => {
    panel.hidden = true;
    boton.setAttribute("aria-expanded", "false");
    boton.focus();
  };

  boton.addEventListener("click", () => (panel.hidden ? abrir() : cerrarPanel()));
  cerrar.addEventListener("click", cerrarPanel);
  document.addEventListener("keydown", e => { if (e.key === "Escape" && !panel.hidden) cerrarPanel(); });

  form.addEventListener("submit", e => {
    e.preventDefault();
    const escrito = texto.value.trim();
    if (!escrito) return;
    mensaje("yo", escrito.slice(0, 120));
    texto.value = "";

    if (esSaludo(escrito)) {
      mensaje("bot", datos.saludo);
      mostrarMenu();
      return;
    }

    const id = entender(escrito);
    responder(id, false);
    if (id === "otro") mostrarMenu();
  });
})();
