// ===== index.html: catálogo con filtros =====
// Datos de ejemplo (luego vendrán de la base de datos)
const TRABAJADORES = [
  { nombre: "Rosa Quispe H.", ini: "RQ", oficio: "Pintura",      distrito: "San Sebastián", nota: 4.9, resenas: 18, precio: 50,   verificado: true,  tel: "51900000001" },
  { nombre: "Juan C. Mamani", ini: "JM", oficio: "Electricista", distrito: "San Sebastián", nota: 4.8, resenas: 23, precio: 60,   verificado: true,  tel: "51900000002" },
  { nombre: "Marco Paredes",  ini: "MP", oficio: "Hotelería",    distrito: "Santiago",      nota: 4.7, resenas: 9,  precio: null, verificado: true,  tel: "51900000003" },
  { nombre: "Luis Condori",   ini: "LC", oficio: "Gasfitería",   distrito: "San Sebastián", nota: 4.6, resenas: 14, precio: 40,   verificado: true,  tel: "51900000004" },
  { nombre: "Elena Huamán",   ini: "EH", oficio: "Limpieza",     distrito: "San Jerónimo",  nota: 4.5, resenas: 7,  precio: 45,   verificado: true,  tel: "51900000005" },
  { nombre: "Walter Ayma",    ini: "WA", oficio: "Electricista", distrito: "San Sebastián", nota: 4.4, resenas: 11, precio: 55,   verificado: true,  tel: "51900000006" },
  { nombre: "Pedro Callo",    ini: "PC", oficio: "Carpintería",  distrito: "Wanchaq",       nota: 3.9, resenas: 5,  precio: 70,   verificado: false, tel: "51900000007" },
];

// Filtros activos
const filtros = { oficio: "", texto: "", distrito: "", verificados: true, cuatroMas: false };

// Dibuja las tarjetas que cumplen los filtros
function mostrar() {
  const lista = TRABAJADORES.filter(t =>
    (!filtros.oficio    || t.oficio === filtros.oficio) &&
    (!filtros.distrito  || t.distrito === filtros.distrito) &&
    (!filtros.verificados || t.verificado) &&
    (!filtros.cuatroMas || t.nota >= 4) &&
    (`${t.oficio} ${t.nombre}`.toLowerCase().includes(filtros.texto))
  );

  document.getElementById("contador").textContent = lista.length + " resultados";

  document.getElementById("resultados").innerHTML = lista.map(t => `
    <div class="col">
      <article class="card-ayni shadow-sm p-3 h-100 d-flex flex-column">
        <div class="d-flex gap-3 align-items-center">
          <div class="avatar">${t.ini}</div>
          <div>
            <h2 class="h6 mb-0">${t.nombre}</h2>
            <small class="text-secondary">${t.oficio} · ${t.distrito}</small>
          </div>
        </div>
        ${t.verificado ? '<span class="badge badge-verified rounded-pill align-self-start mt-2"><i class="bi bi-patch-check-fill"></i> Identidad verificada</span>' : ""}
        <div class="d-flex justify-content-between my-3">
          <span><i class="bi bi-star-fill stars"></i> <strong>${t.nota}</strong> <small class="text-secondary">(${t.resenas})</small></span>
          <strong class="text-dark">${t.precio ? "desde S/ " + t.precio : "a convenir"}</strong>
        </div>
        <div class="d-flex gap-2 mt-auto">
          <a href="perfil.html" class="btn btn-outline-secondary btn-sm flex-fill">Ver perfil</a>
          <a href="https://wa.me/${t.tel}" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm flex-fill"><i class="bi bi-whatsapp"></i> WhatsApp</a>
        </div>
      </article>
    </div>`).join("") || '<p class="text-secondary">No hay resultados. Prueba quitando un filtro.</p>';
}

// Categoría: un clic la activa, otro clic la quita
document.getElementById("categorias").addEventListener("click", (e) => {
  const btn = e.target.closest(".cat-btn");
  if (!btn) return;
  const estabaActiva = btn.classList.contains("active");
  document.querySelectorAll(".cat-btn").forEach(b => b.classList.remove("active"));
  btn.classList.toggle("active", !estabaActiva);
  filtros.oficio = estabaActiva ? "" : btn.dataset.oficio;
  mostrar();
});

// Chips de filtro rápido
document.getElementById("f-verif").addEventListener("click", (e) => {
  filtros.verificados = e.currentTarget.classList.toggle("active");
  mostrar();
});
document.getElementById("f-stars").addEventListener("click", (e) => {
  filtros.cuatroMas = e.currentTarget.classList.toggle("active");
  mostrar();
});

// Distrito y buscador
document.getElementById("distrito").addEventListener("change", (e) => { filtros.distrito = e.target.value; mostrar(); });
document.getElementById("search-form").addEventListener("submit", (e) => {
  e.preventDefault();
  filtros.texto = document.getElementById("q").value.trim().toLowerCase();
  mostrar();
});

mostrar();
