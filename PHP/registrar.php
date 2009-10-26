<?php
function CONTENIDO_REGISTRAR()
{
    if (S_iniciado())
    {
        header("location: ./");
        return;
    }
    if (isset($_POST['registrar_proceder']))
    {
        $flag_registroExitoso=true;
        if (!empty($_POST['registrar_campo_email']))
        {
            if (!validEmail($_POST['registrar_campo_email']))
            {
                echo mensaje ("Este correo electrónico no es válido, por favor revise que este escrito correctamente o escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
            if (_F_usuario_existe($_POST['registrar_campo_email'],"email"))
            {
                echo mensaje ("Este correo electrónico ya existe en el sistema, por favor escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
                $datos['email'] = $_POST['registrar_campo_email'];
        }
        else
        {
            echo mensaje ("Por favor ingrese su email e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if (!empty($_POST['registrar_campo_usuario']))
        {
            if (_F_usuario_existe($_POST['registrar_campo_usuario']))
            {
                echo mensaje ("Este nombre de usuario ya existe en el sistema, por favor escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
            if (strpos(trim($_POST['registrar_campo_usuario'])," "))
            {
                echo mensaje ("Este nombre de usuario no es válido (contiene espacios), por favor escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
            $datos['usuario'] = trim($_POST['registrar_campo_usuario']);
        }
        else
        {
            echo mensaje ("Por favor ingrese su usuario e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if (!empty($_POST['registrar_campo_clave']) && !empty($_POST['registrar_campo_clave_2']))
        {
            //Contraseñas iguales?
            if (trim($_POST['registrar_campo_clave']) == trim($_POST['registrar_campo_clave_2']))
            {
                //Tamaño adecuado?
                if(strlen($_POST['registrar_campo_clave']) >= 6 && strlen($_POST['registrar_campo_clave']) <= 100)
                {
                    $datos['clave'] = sha1(strtolower($datos['usuario']).trim($_POST['registrar_campo_clave']));
                }
                else
                {
                    echo mensaje ("La contraseña debe tener mas de 6 caracteres",_M_ERROR);
                    $flag_registroExitoso=false;
                }
            }
            else
            {
                echo mensaje ("Las contraseñas no coinciden, por favor ingrese su contraseña e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
        }
        else
        {
            echo mensaje ("Por favor ingrese su contraseña e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if (!empty($_POST['registrar_campo_telefono']))
        {
            if (_F_usuario_existe($_POST['registrar_campo_telefono'], "telefono1"))
            {
                echo mensaje ("Este teléfono ya existe en el sistema, por favor escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }

            $datos['telefono1'] = $_POST['registrar_campo_telefono'];
        }
        else
        {
            echo mensaje ("Por favor ingrese su número telefonico e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if ($flag_registroExitoso)
        {
            $datos["estado"] = _N_esp_activacion;
            $datos["nivel"] = _N_vendedor;
            $datos["ultimo_acceso"] = mysql_datetime();
            $datos["registro"]= mysql_datetime();
            db_agregar_datos("ventas_usuarios",$datos);
            echo "¡Su solicitud de registro ha sido procesada!<br />Sin embargo su cuenta estará activa cuando un Administrador apruebe su nueva cuenta.<br />Un mensaje será enviado su correo electrónico en el que se le confirmará que su cuenta esta activa.<br />Este proceso puede tardar entre 10 minutos y 2 horas en llevarse a cabo, gracias por su espera.<br />";
            echo "Le invitamos a seguir navegando en nuestro sitio mientras su cuenta es activada. ". ui_href("registrar_continuar","./", "Continuar") ."<br />";
            email($datos['email'],"Su registro en ".PROY_NOMBRE." ha sido exitoso","Su registro de usuario  en ".PROY_NOMBRE." ha sido exitoso, sin embargo los Administradores deberán activar manualmente su cuenta para que Ud. puede acceder.<br />\nSe le notificará por esta vía cuando la activación sea realizada.<br />\n\n<hr><br />\n<h1>Datos registrados</h1><br />\nCorreo electrónico: <strong>".$datos['email']."</strong><br />\nUsuario: <strong>".$datos['usuario']."</strong><br />\n<br /><br />Gracias por su amable espera.<br />".PROY_NOMBRE."<br />".PROY_URL);
            email_x_nivel(_N_administrador,"aprobación de nuevo usuario pendiente",PROY_URL."admin_usuarios_activacion");
            return;
        }
    }
echo "¡Bienvenido!, ¿deseas formar parte del comercio electrónico?<br />Si ya posees una cuenta puedes ". ui_href("registrar_iniciar_sesion","./iniciar","iniciar sesión") . ".<br />Todos los campos son requeridos<br />";
echo "<form action=\"registrar\" method=\"POST\">";
echo "<table>";
echo ui_tr(ui_td("<acronym title='Ud. ingresará a nuestro sistema usando esta dirección de correo electronico. Asegurese que la dirección exista, puesto que será necesaria en caso de que desee recuperar su contraseña.'>Correo electronico (e-mail)</acronym>") . ui_td(ui_input("registrar_campo_email",_F_form_cache("registrar_campo_email"))) . ui_td('<span id="registrar_respuesta_email"></span>'));
echo ui_tr(ui_td("<acronym title='Este es el nombre que se le mostrará a los usuarios del sitio. Puede utilizar su código de vendedor o el apodo que Ud. prefiera'>Nombre de Usuario</acronym>")  . ui_td(ui_input("registrar_campo_usuario",_F_form_cache("registrar_campo_usuario"))) . ui_td('<span id="registrar_respuesta_usuario"></span>'));
echo ui_tr(ui_td("<acronym title='Le permitirá validar su identidad en nuestro sistema. Deberá ser mayor a 6 carácteres'>Contraseña</acronym>")      . ui_td(ui_input("registrar_campo_clave","","password")));
echo ui_tr(ui_td("<acronym title='Por favor ingrese nuevamente su contraseña (verificación)'>Contraseña (verificación)</acronym>")      . ui_td(ui_input("registrar_campo_clave_2","","password")));
echo ui_tr(ui_td("<acronym title='Número de contacto principal. Le llamaremos a este número si es necesario esclarecer datos sobre una venta'>Teléfono de contacto</acronym>")  . ui_td(ui_input("registrar_campo_telefono",_F_form_cache("registrar_campo_telefono"))));
echo "</table>";
echo ui_input("registrar_proceder", "Proceder", "submit")."<br />";
echo "</form>";
echo "<strong>Su correo electrónico y teléfono no serán revelados al público ni vendidos a terceras personas.</strong>";
echo JS_onload('
$("#registrar_campo_email").blur(function(){$("#registrar_respuesta_email").load("./registro_correo_existe:"+$("#registrar_campo_email").val());});
$("#registrar_campo_usuario").blur(function(){$("#registrar_respuesta_usuario").load("./registro_usuario_existe:"+$("#registrar_campo_usuario").val());});
');
}
?>
