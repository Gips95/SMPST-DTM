import fetchDataJson from '../js/fetching.js'
import {ValidateWithRegex, validateForm, inputSuccess, inputError, createSpan} from '../js/validator.js'

const validationRules = {
    'email': {
        required: {
            value:true,
            msg:'Ingrese un email'
        },
        Regex: {
            value: '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}',
            msg: 'Formato de email invalido'
        }
    },
    'code': {
        required: {
            value: true,
            msg: 'Ingrese el codigo de recuperación'
        },
        stringLength: {
            min: 5,
            max: 5,
            minmsg: 'El codigo no puede ser inferior a los 5 digitos',
            maxmsg: 'El codigo no puede ser mayor a los 5 digitos'
        }
    }
}



//Verificar email ----------------

const emailInput = document.getElementById('email')
const emailForm = document.querySelector('.email-reset-form')

async function HandleSubmitEmail(e){
    e.preventDefault()
    const modal = document.querySelector('.modal')

    if(validateForm(e.target, validationRules)){ 
        const btn = e.target.querySelector('button')
        try {
            btn.setAttribute('disabled', "")
            btn.innerHTML = '<div class="spinner-border m-0 p-0" role="status"></div>'

            const formdata = new FormData(e.target)

            const checkEmailResponse = await fetchDataJson('../endpoints/check_email.php', formdata, 'POST')
            if(checkEmailResponse.status == 400) throw new Error(checkEmailResponse.msg)
            localStorage.setItem('user_id', checkEmailResponse.id)
            modal.classList.add('open')

        } catch(e) {
            const emailSpan = document.querySelector('#email-span')
            inputError(emailInput, emailSpan, e.message)
            return false
        }finally{
            btn.innerHTML = ''
            btn.innerText = 'Confirmar'
            btn.removeAttribute('disabled')
        }

}else{
    modal.classList.remove('open')
  }
}


emailForm.addEventListener('submit', HandleSubmitEmail)
emailForm.addEventListener('input', function(e){
    const field = e.target;
    const name = field.name || field.id;
    if (validationRules[name]) {
        validateForm(emailForm, { [name]: validationRules[name] }, validationRules);
    }           
})

//Verificar codigo de recuperacion----------------------------------------
const codeInput = document.getElementById('code')
const codeForm = document.querySelector('.code-form')

async function HandleSubmitCode(e){
    e.preventDefault()

    if(!validateForm(e.target, validationRules)) return false
    const btn = e.target.querySelector('button')
    try{
        btn.setAttribute('disabled', "")
        btn.innerHTML = '<div class="spinner-border m-0 p-0" role="status"></div>'

        const codigoForm = new FormData(e.target) 
        codigoForm.append('id', localStorage.getItem('user_id'))
        const checkCodeResponse = await fetchDataJson('../endpoints/check_rpwcode.php', codigoForm, 'POST')

        if(checkCodeResponse.status == 400) throw new Error(checkCodeResponse.msg)
        
        localStorage.removeItem('user_id')
        window.location.href = './reset_password.php'
        
    }catch(e){
         const codeSpan = document.querySelector('#code-span')
         inputError(codeInput, codeSpan, e.message)
         return false
    }finally{
        btn.innerHTML = ''
        btn.innerText = 'Confirmar'
        btn.removeAttribute('disabled')
    }
}

codeForm.addEventListener('submit', HandleSubmitCode)
codeForm.addEventListener('input', function(e){
    const field = e.target;
    const name = field.name || field.id;
    if (validationRules[name]) {
        validateForm(codeForm, { [name]: validationRules[name] }, validationRules);
    }           
})
//Listener para el modal-----------

const closeBtnModal = document.querySelector('.close-modal')

closeBtnModal.addEventListener('click', (e) => {
    e.preventDefault()
    localStorage.removeItem('user_id')
    const modal = document.querySelector('.modal')
    modal.classList.remove('open')
    emailInput.value = ''
})


