export function ValidateWithRegex(str, options){

    let isPassword = false
    let textSpan = ''
    str = typeof str == 'number' ? str.toString() : str

    //if (options.hasOwnProperty('required')) {
      const isRequired = typeof options.required === 'boolean'
      ? options.required
      : options.required?.value ?? false;


      if (!isRequired && str === '') return;

      if (options.hasOwnProperty('required')) {
        textSpan = options.required?.msg || 'Este campo es requerido';
        if (isRequired && str === '') throw new Error(textSpan);
      }

    if(options.hasOwnProperty('Regex')){
      const nRegex = typeof options.Regex == 'string' ? options.Regex : options.Regex.value

      options.Regex.hasOwnProperty('msg') 
      ? textSpan = options.Regex.msg 
      : textSpan = 'No cumple con el formato solicitado'

      if(!new RegExp(nRegex).test(str)) throw new Error(textSpan)

    }

    
    if(options.hasOwnProperty('isPassword')){
      if(options.isPassword){
        isPassword = true
      }
    }
  
    if(isPassword){
        const allowedCharacters = new RegExp("^[a-zA-Z0-9!@#$%&?._]*$");
        const atl_oneLowerLetter = new RegExp("^.*[a-z].*$");
        const atl_oneCapitalLetter = new RegExp("^.*[A-Z].*$");
        const atl_oneNumber = /^.*\d.*$/;
        const atl_oneSpecialCharacter = new RegExp("^.*[@#$!%?&].*$");
  
        if (!allowedCharacters.test(str)) throw new Error("Solo se permiten letras, numeros y caracteres especiales");
        if (!atl_oneLowerLetter.test(str)) throw new Error("Se necesita al menos una letra minuscula");
        if (!atl_oneCapitalLetter.test(str)) throw new Error("Se necesita al menos una letra mayuscula");
        if (!atl_oneNumber.test(str)) throw new Error("Se necesita al menos un numero");
        if (!atl_oneSpecialCharacter.test(str)) throw new Error("Se necesita al menos un caracter especial")
    }
  
    if(options.hasOwnProperty('stringLength')){
      
      if(options.stringLength.hasOwnProperty('min')){
  
        options.stringLength.hasOwnProperty('minmsg') 
        ? textSpan = options.stringLength.minmsg 
        : textSpan = 'El campo es demasiado corto'
  
        const lenA = options.stringLength.min
      
        if (str.length < lenA) throw new Error(textSpan)
      }
      if(options.stringLength.hasOwnProperty('max')){
  
        options.stringLength.hasOwnProperty('maxmsg') 
        ? textSpan = options.stringLength.maxmsg 
        : textSpan = 'El campo es demasiado largo'
  
        const lenB = options.stringLength.max
  
        if (str.length > lenB) throw new Error(textSpan);
  
      }
    }
  }

  //funciones auxiliares

  export function inputSuccess(input, span){
    span.style.display = 'none'
    span.classList.remove('error')

    input.classList.remove('input-error')
    input.classList.add('input-success')
}

export function inputError(input, span, msg){
  span.style.display = 'inline-block';
  span.innerText = msg;
  span.classList.remove('success')
  span.classList.add('error')

  input.classList.remove('input-success')
  input.classList.add('input-error')
}

export function createSpan(field) {
    const span = document.createElement("span");
    const sp = field.name || field.id
    span.id = sp + "-span";
    span.classList.add("message");
    field.parentNode.insertBefore(span, field.nextSibling);
    return span;
  }
//---------------

export function validateForm(formElement, rules, generalRules = null, alreadyChecked = false) {
    const elements = formElement.querySelectorAll("input, textarea, select");
    let isValid = true;
    const gRules = generalRules != null ? generalRules : rules
  
    for (const field of elements) {
      const name = field.name || field.id;
      if (!rules.hasOwnProperty(name)) continue;
  
      const options = rules[name];
      const span = formElement.querySelector(`#${name}-span`) || createSpan(field);
      let value = field.value.trim();
  
      try {
        // Validación normal
        ValidateWithRegex(value, options);
  
        // Validación cruzada
        if(options.hasOwnProperty('matchField')){
          const matchF = typeof options.matchField == 'string' ? options.matchField : options.matchField.value
  
          if (matchF) {
            const matchInput = formElement.querySelector(`[name="${matchF}"], #${matchF}`);
            let textSpan = ''
  
            options.matchField.hasOwnProperty('msg') 
            ? textSpan = options.matchField.msg
            : textSpan = 'Los campos no coinciden'

            const m =  matchInput.id || matchInput.name

            if (value !== matchInput.value.trim()){
              const errorSpan = formElement.querySelector(`#${m}-span`) ?? createSpan(matchInput)
              inputError(matchInput, errorSpan, textSpan)
              throw new Error(textSpan);
            }
            
            if(!alreadyChecked){
              validateForm(formElement, { [m]: gRules[m] }, true)
            }
          }
        }    
        inputSuccess(field, span);
      } catch (error) {
        inputError(field, span, error.message);
        isValid = false;
      }
    }
  
    return isValid;
  }
