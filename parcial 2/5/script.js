const boton = document.querySelector("#boton");
const parrafo = document.querySelector("#parrafo");

const btn_contar = document.querySelector('#btn-contar');
var cuenta = 0;
const mensaje = document.querySelector('#mensaje');
const contador = document.querySelector('#contador');

const btn_rosa = document.querySelector('#btn-rosa');
const tarjetas = document.querySelectorAll('.target');

const input_nombre = document.querySelector('#input-nombre');
const mensaje_saludo = document.querySelector('#mensaje-saludo');

const btn_mostrar = document.querySelector('#btn-mostrar');
const btn_ocultar = document.querySelector('#btn-ocultar');
const card_contenido = document.querySelector('#card-contenido');
const caja = document.querySelector('#caja');

const input_precio = document.querySelector('#input-precio');
const btn_descuento = document.querySelector('#btn-descuento');
const resultado_precio = document.querySelector('#resultado-precio');


const campo_texto = document.querySelector('#campo-texto')
const btn_validar = document.querySelector('#btn-validar')
const mensaje_validacion = document.querySelector('#mensaje-validacion')

boton.addEventListener("click", () => {
    parrafo.textContent = "El boton fue presionado";
    parrafo.classList.add("text-danger")
    parrafo.classList.remove("text-success");
});

boton.addEventListener("mouseover", () => {
    parrafo.textContent = "El mouse esta sobre el boton";
    parrafo.classList.add("text-warning");
    parrafo.classList.remove("text-success");
});

boton.addEventListener("mouseout", () => {
    parrafo.textContent = "El mouse salio del boton";
    parrafo.classList.add("text-success");
    parrafo.classList.remove("text-warning");
});

btn_contar.addEventListener("click", () => {
    cuenta += 1;
    contador.textContent = cuenta;
    if (cuenta == 10) {
        mensaje.textContent = "Empezamos de nuevo";
        cuenta = 0;
    } else {
        mensaje.textContent = "";
    }
});
btn_rosa.addEventListener("click", () => {
    if (document.body.style.backgroundColor === "rgb(255, 192, 203)" || document.body.style.backgroundColor === "#ffc0cb") {

        document.body.style.backgroundColor = "";
        document.body.classList.add("bg-light");

        tarjetas.forEach(tarjeta => {
            tarjeta.style.backgroundColor = "#ffffff";
        });

    } else {
        document.body.classList.remove("bg-light");
        document.body.style.backgroundColor = "#ffc0cb";

        tarjetas.forEach(tarjeta => {
            tarjeta.style.backgroundColor = "rgba(255, 255, 255, 0.8)";
        });
    }
});

input_nombre.addEventListener("input", () => {
    mensaje_saludo.textContent = "Hola " + input_nombre.value;
});

btn_mostrar.addEventListener("click", () => {
    caja.append(card_contenido)
});
btn_ocultar.addEventListener("click", () => {
    card_contenido.remove()
});

btn_descuento.addEventListener("click", () => {
    const precio = parseFloat(input_precio.value);
    const descuento = precio * 0.15
    const precio_final = precio - descuento
    resultado_precio.textContent = precio_final.toFixed(2)
});

btn_validar.addEventListener("click", () => {
    const texto = campo_texto.value
    if (texto.length > 7) {
        mensaje_validacion.textContent = "Aceptado"
    }else{
        mensaje_validacion.textContent = "Denegado"
    }
});