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
        despachar_notificaciones_sms("nuevo registro en foro de ventas");
    }
echo "¡Bienvenido!, ¿deseas formar parte del comercio electrónico?<br />Si ya posees una cuenta puedes ". ui_href("registrar_iniciar_sesion","./iniciar","iniciar sesión") . ".<br />";
echo "<form action=\"registrar\" method=\"POST\">";
echo "<table>";
echo ui_tr(ui_td("<acronym title='Ud. ingresará a nuestro sistema usando esta dirección de correo electronico. Asegurese que la dirección exista, puesto que será necesaria en caso de que desee recuperar su contraseña.'>Correo electronico (e-mail)</acronym>") . ui_td(ui_input("registrar_campo_email")) . ui_td('<span id="registrar_respuesta_email"></span>'));
echo ui_tr(ui_td("<acronym title='Este es el nombre que se le mostrará a los usuarios del sitio. Puede utilizar su código de vendedor o el apodo que Ud. prefiera'>Nombre de Usuario</acronym>")  . ui_td(ui_input("registrar_campo_usuario")) . ui_td('<span id="registrar_respuesta_usuario"></span>'));
echo ui_tr(ui_td("<acronym title='Le permitirá validar su identidad en nuestro sistema. Deberá ser mayor a 6 carácteres'>Contraseña</acronym>")      . ui_td(ui_input("registrar_campo_clave","","password")));
echo ui_tr(ui_td("<acronym title='Por favor ingrese nuevamente su contraseña (verificación)'>Contraseña (verificación)</acronym>")      . ui_td(ui_input("registrar_campo_clave_2","","password")));
echo ui_tr(ui_td("<acronym title='Número de contacto principal. Le llamaremos a este número si es necesario esclarecer datos sobre una venta'>Teléfono fijo principal</acronym>")  . ui_td(ui_input("registrar_campo_telefono")));
echo "</table>";
echo ui_input("registrar_proceder", "Proceder", "submit")."<br />";
echo "</form>";
echo ui_href("registrar_cancelar","./","Cancelar registro")."<br />";
echo JS_onload('
$("#registrar_campo_email").keyup(function(){$("#registrar_respuesta_email").load("./registro_correo_existe:"+$("#registrar_campo_email").val());});
$("#registrar_campo_usuario").keyup(function(){$("#registrar_respuesta_usuario").load("./registro_usuario_existe:"+$("#registrar_campo_usuario").val());});
');
}
?>
