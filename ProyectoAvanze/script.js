// Elementos del HTML
const botonBuscar = document.getElementById("btnBuscar");
const buscador = document.getElementById("buscarOficio");
const distrito = document.getElementById("distrito");

const tarjetas = document.querySelectorAll(".tarjeta");

const cantidadResultados =
    document.getElementById("cantidadResultados");


// Buscar trabajadores
botonBuscar.addEventListener("click", function () {

    const texto = buscador.value.toLowerCase();
    const distritoSeleccionado = distrito.value;

    let cantidad = 0;

    tarjetas.forEach(function (tarjeta) {

        const oficio = tarjeta.dataset.oficio.toLowerCase();
        const distritoTrabajador = tarjeta.dataset.distrito;

        const coincideOficio =
            oficio.includes(texto) || texto === "";

        const coincideDistrito =
            distritoSeleccionado === "Todos" ||
            distritoTrabajador === distritoSeleccionado;

        if (coincideOficio && coincideDistrito) {

            tarjeta.style.display = "block";
            cantidad++;

        } else {

            tarjeta.style.display = "none";

        }

    });

    cantidadResultados.textContent =
        cantidad + " resultados";

    // Guardamos la búsqueda usando LocalStorage API
    localStorage.setItem(
        "ultimaBusqueda",
        buscador.value
    );

    localStorage.setItem(
        "ultimoDistrito",
        distrito.value
    );

});


// Categorías
const botonesCategoria =
    document.querySelectorAll(".categorias button");

botonesCategoria.forEach(function (boton) {

    boton.addEventListener("click", function () {

        buscador.value = boton.dataset.oficio;

        botonBuscar.click();

    });

});


// Filtro de trabajadores verificados
const botonVerificados =
    document.getElementById("soloVerificados");

botonVerificados.addEventListener("click", function () {

    tarjetas.forEach(function (tarjeta) {

        if (tarjeta.dataset.verificado === "true") {
            tarjeta.style.display = "block";
        }

    });

});


// Contactar trabajador
function contactar(nombre) {

    alert(
        "Puedes contactar a " +
        nombre +
        " mediante WhatsApp."
    );

}


// Recuperar la última búsqueda
window.addEventListener("load", function () {

    const ultimaBusqueda =
        localStorage.getItem("ultimaBusqueda");

    const ultimoDistrito =
        localStorage.getItem("ultimoDistrito");

    if (ultimaBusqueda) {
        buscador.value = ultimaBusqueda;
    }

    if (ultimoDistrito) {
        distrito.value = ultimoDistrito;
    }

});