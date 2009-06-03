<?php
function CONTENIDO_INICIAR_SESION()
{
if (S_iniciado())
{
    header("location: ./");
    return;
}
echo "¿Deseas comprar y vender pero aún no tienes una cuenta? ". ui_href("iniciar_sesion_crear_cuenta","./registrar","¡entonces registrate ahora!") . ", es gratis, fácil y rápido.<br />";
/* Si ya intentó iniciar... */
if (isset($_POST['iniciar_proceder']))
{
    $ret = _F_usuario_acceder($_POST['iniciar_campo_correo'],$_POST['iniciar_campo_clave']);
    // Si fue exitoso
    if ($ret == 1)
    {
        header("location: ./");
        return;
    }
    else
    {
        echo mensaje ("Datos de acceso erroneos, por favor intente de nuevo",_M_ERROR);
    }
}
echo "<form action=\"iniciar\" method=\"POST\">";
echo "<table>";
echo "<span class=\"explicacion\">Utilice el mismo correo y contraseña que utilizó al registrarse en el sistema</span>";
echo ui_tr(ui_td("Correo electronico (e-mail)") . ui_td(ui_input("iniciar_campo_correo")));
echo ui_tr(ui_td("Constraseña") . ui_td(ui_input("iniciar_campo_clave","","password")));
echo "</table>";
echo ui_input("iniciar_proceder", "Iniciar sesión", "submit")."<br />";
echo "</form>";
echo ui_href("iniciar_cancelar","./","Cancelar inicio");
}
?>
