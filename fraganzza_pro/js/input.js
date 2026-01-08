//evento del ojo

document.querySelector(".ojo img").addEventListener("click", ver);
document.querySelector("form").addEventListener("submit", evaluar);

function evaluar(event) {
    event.preventDefault();
    //const usuario = document.querySelector("#usuario").value.trim();
    const email = document.querySelector("#email").value.trim().toLowerCase();
    const password = document.querySelector("#password").value.trim();
    const errores = [];

    // if(usuario.length<3){
    //     errores.push("El nombre de usuario tiene que ser mayor de dos digitos")
    // } 
    //expresion regular para el email
    const expr1 = /^\S+@\S+\.\S+$/
    if (!expr1.test(email)) {
        errores.push("El email es incorrecto");
    }
    //evaluar el password
    /* 5 caracteres | 1 letra mayuscula | 1letra miniuscula | numero*/
    if (password.length < 6 || !/[a-z]/.test(password) || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
        errores.push("El password es incorrecto. Mínimo 5 caracteres, 1 may, 1 min y 1 num")
    }
    if (errores.length > 0) {
        //mostrar errores
        document.querySelector(".errores").innerHTML = "";
        errores.map(error => document.querySelector(".errores").innerHTML += `<div class="error">${error}</div>`);
    } else {
        //enviar valores
        document.querySelector("form").submit();
    }
}

function ver() {
    let estado = document.querySelector("#password").getAttribute("type");
    if (estado == "password") {
        document.querySelector("#password").setAttribute("type", "text");
        document.querySelector(".ojo img").setAttribute("src", "img/ojoAbierto.png")
    } else {
        document.querySelector("#password").setAttribute("type", "password");
        document.querySelector(".ojo img").setAttribute("src", "img/ojoCerrado.png")

    }
}
