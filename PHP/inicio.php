<?php
function CONTENIDO_INICIAR_SESION()
{

if (isset($_POST['iniciar_proceder']))
{
    ob_start();
    $ret = _F_usuario_acceder($_POST['iniciar_campo_correo'],$_POST['iniciar_campo_clave']);
    $buffer = ob_get_clean();
    if ($ret != 1)
    {
        echo mensaje ("Datos de acceso erroneos, por favor intente de nuevo",_M_ERROR);
        echo mensaje ($buffer,_M_INFO);
    }
}

if (S_iniciado())
{
    if (!empty($_POST['iniciar_retornar']))
    {
        header("location: ".$_POST['iniciar_retornar']);
    }
    else
    {
        header("location: ./");
    }
    return;
}

echo "¿Deseas comprar y vender pero aún no tienes una cuenta? ". ui_href("iniciar_sesion_crear_cuenta","./registrar","¡entonces registrate ahora!") . ", es gratis, fácil y rápido.<br />";
echo "<span class=\"explicacion\">¡Puedes utilizar tu usuario (o el correo electronico) y contraseña de <b><a target=\"_blank\" href=\"http://svcommunity.org/constancias/constancia-yomachete.com.html\">SVCommunity.org</a></b>!</span>";
$retorno = empty($_POST['iniciar_retornar']) ? "http://".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : $_POST['iniciar_retornar'];
echo "<form action=\"iniciar\" method=\"POST\">";
echo ui_input("iniciar_retornar", $retorno, "hidden");
echo "<table>";
echo ui_tr(ui_td("Correo electronico (e-mail) / Usuario") . ui_td(ui_input("iniciar_campo_correo")));
echo ui_tr(ui_td("Constraseña") . ui_td(ui_input("iniciar_campo_clave","","password")));
echo "</table>";
echo ui_input("iniciar_proceder", "Iniciar sesión", "submit")."<br />";
echo "</form>";
}
?>
