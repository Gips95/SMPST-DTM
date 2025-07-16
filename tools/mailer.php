<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
include_once('../PHPmailer/vendor/autoload.php');


function server_settings(&$mail):void{
    $mail= new PHPMailer(true);
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'cdiwaos@gmail.com';                     //SMTP username
    $mail->Password   = 'omeg ehgq dvqw qlmv';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;
}

function recovery_pass_email($email, $subject='Codigo de verificacion', $token, $name_user='User'){    
    $mail;
    server_settings($mail);
    
    $mail->setFrom('cdiwaos@gmail.com', 'CDIWaos'); //direccion y nombre a mostrar del emisor
    $mail->addAddress($email, $name_user); //direccion y nombre del receptor
    
    
    $mail->isHTML(true);
    $mail->Subject=$subject;

    //solo puede enviar html en texto plano y css en linea
    $mail->Body='<body style="background-color: #00000000; text-align: center; font-family: system-ui;">
  <div style="padding-top:20px; padding-bottom:20px;">
    <header>
      <h4>Tu Codigo de Verificacion:</h3>
      <h1 style="font-size: 55px; padding: 10px; border: 2px black solid; border-radius: 4px; width: fit-content;margin: auto;">'.$token.'</h1>
    </header>
    <section>
      <p>
        No compartas este codigo con nadie
      </p>
    </section>
  </div>
</body>';

    $mail->AltBody = 'Codigo de Recuperacion: '.$token;
    if($mail->send()){
        return true;
      }else{
        return 'Error al enviar el email: '.$mail->ErrorInfo;
      }  
    }

    function notify_registration($email, $subject='Has sido registrado en el sistema', $name, $username){
        server_settings($mail);
        
        $mail->setFrom('cdiwaos@gmail.com', 'CDIWaos'); //direccion y nombre a mostrar del emisor
        $mail->addAddress($email, $name); //direccion y nombre del receptor

        $mail->isHTML(true);
        $mail->Subject=$subject;

        $mail->Body='<body style="background-color: #00000000; text-align: center; font-family: system-ui;">
                          <div>
                              <h1>Has sido registrado en el sistema por un administrador</h1>
                          </div>
                          <div style="padding: 10px; border: 2px black solid; border-radius: 4px; width: fit-content;margin: auto;">
                              <div style="font-size: 20px; font-weight: bold">Tu nombre de usuario: </div>
                              <p>'.$username.'</p>
                          </div>
                    </body>';
        $mail->Altbody = 'Registrado en el sistema, tu nombre de usuario es: '.$username;
        if(!$mail->send()) throw new Exception('Error al enviar el email: '.$mail->ErrorInfo);
    }
?>