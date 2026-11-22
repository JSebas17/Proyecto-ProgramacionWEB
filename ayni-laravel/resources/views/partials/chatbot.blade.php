{{-- Chatbot de respuestas fijas. Contenido: App\Support\Chatbot · Lógica: public/js/chatbot.js
     Los datos van en un bloque JSON (no se ejecuta, así que la CSP lo permite). --}}
<div class="chatbot" id="chatbot">
  <button type="button" class="chatbot-boton" id="chatbot-abrir" aria-expanded="false" aria-controls="chatbot-panel">
    <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
    <span>Ayuda</span>
  </button>

  <section class="chatbot-panel" id="chatbot-panel" role="dialog" aria-labelledby="chatbot-titulo" hidden>
    <header class="chatbot-cabecera">
      <h2 class="h6 mb-0" id="chatbot-titulo"><i class="bi bi-chat-dots" aria-hidden="true"></i> Asistente de Ayni</h2>
      <button type="button" class="chatbot-cerrar" id="chatbot-cerrar" aria-label="Cerrar la ayuda">
        <i class="bi bi-x-lg" aria-hidden="true"></i>
      </button>
    </header>

    <div class="chatbot-mensajes" id="chatbot-mensajes" aria-live="polite"></div>
    <div class="chatbot-opciones" id="chatbot-opciones"></div>

    <form class="chatbot-form" id="chatbot-form" autocomplete="off">
      <label class="visually-hidden" for="chatbot-texto">Escribe tu pregunta</label>
      <input id="chatbot-texto" type="text" maxlength="120" placeholder="Escribe tu pregunta…">
      <button type="submit" aria-label="Enviar pregunta"><i class="bi bi-send-fill" aria-hidden="true"></i></button>
    </form>
  </section>

  <script type="application/json" id="chatbot-datos">@json(\App\Support\Chatbot::datos(auth()->check()))</script>
</div>
