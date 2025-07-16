<?php
class Encrypt{

    public static function EncryptData($data, $key, $cipher_method = "aes-256-cbc"){
        $iv = random_bytes(openssl_cipher_iv_length($cipher_method));//genera un string de caracteres aleatorios ilegibles de un tamaño en bytes proporcionado por el largo del vector de inicializacion del metodo de cifrado 
        $encrypted = openssl_encrypt($data, $cipher_method, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted); //unimos el vector de inicializacion y el dato encriptado en un mismo string poder usar el primero en la desencryptacion mas tarde
        //tambien se codifica a base64 para que pueda entrar en la base de datos como una cadena de caracteres normal
    }

    public static function DecryptData($encryptedData, $key, $cipher_method = "aes-256-cbc"){
        $data = base64_decode($encryptedData); //decodificamos la cadena encriptada
        $ivLength = openssl_cipher_iv_length($cipher_method); //se obtiene el largo del vector de inicializacion del metodo de cifrado que usamos anteriormente
        
        $iv = substr($data, 0, $ivLength); //se extrae el vector de inicializacion de la cadena encriptada
        $encrypted = substr($data, $ivLength); //se extrae el dato encriptado
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv); //se desencripta el dato con la misma key que usamos para encriptarlo
    }
}
?>