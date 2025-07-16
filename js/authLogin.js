// Valida que la cédula no esté vacía
function HandleCedula(){
    const cedula = document.querySelector('#Cedula').value;

    if (cedula.trim() === '') {
        const msg = 'La cédula es obligatoria';
        const span = document.querySelector(".login-form .user-span");
        span.removeAttribute("hidden");
        span.innerText = msg;
        return false;
    } else {
        const span = document.querySelector(".login-form .user-span");
        span.setAttribute('hidden', "");
        return true;
    }
}

// Valida que la contraseña no esté vacía
function HandlePassword(){
    const password = document.querySelector('#password').value;

    if (password.trim() === '') {
        const msg = 'La contraseña es obligatoria';
        const span = document.querySelector(".login-form .pass-span");
        span.removeAttribute("hidden");
        span.innerText = msg;
        return false;
    } else {
        const span = document.querySelector(".login-form .pass-span");
        span.setAttribute('hidden', "");
        return true;
    }
}

// Evita el envío si alguno de los dos campos no es válido
function formLoginValidate(e){
    if (!HandleCedula()) {
        e.preventDefault();
    }
    if (!HandlePassword()) {
        e.preventDefault();
    }
}

const cedulaBox   = document.querySelector('#Cedula');
const passwordBox = document.querySelector('#password');
const loginForm   = document.querySelector('.login-form');

// Listeners
loginForm.addEventListener('submit', formLoginValidate);
cedulaBox.addEventListener('input', HandleCedula);
passwordBox.addEventListener('input', HandlePassword);
