<?php require_once ("PHP/vital.php"); ?>
<?php echo '<?xml version="1.0" encoding="utf-8"?>'."\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta http-equiv="Content-Style-type" content="text/css" />
    <meta http-equiv="Content-Script-type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="es" />
    <title>Compra y Venta de articulos en El Salvador</title>
    <meta name="description" content="Servicio de compra y venta en línea." />
    <meta name="keywords" content="El Salvador, Comprar, Vender, Clasificados" />
    <meta name="robots" content="index, follow" />
    <link href="favicon.ico" rel="icon" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="estilo.css" />
    <link rel="stylesheet" type="text/css" href="JS/fancybox/jquery.fancybox.css" />
    <link rel="stylesheet" type="text/css" href="CSS/jquery.jgrowl.css" />
    <link rel="stylesheet" type="text/css" href="JS/jquery.bookmark.css"  />
    <script src="JS/jquery-1.3.2.min.js" type="text/javascript"></script>
    <script src="JS/jquery.cookie.js" type="text/javascript"></script>
    <script src="JS/fancybox/jquery.easing.1.3.js" type="text/javascript"></script>
    <script src="JS/fancybox/jquery.fancybox-1.2.1.pack.js" type="text/javascript"></script>
    <script src="JS/jquery.jgrowl.js" type="text/javascript"></script>
    <script src="JS/jquery.fav-1.0.js" type="text/javascript"></script>
    <script src="JS/jquery.bookmark.pack.js" type="text/javascript"></script>
    <style type="text/css">
    div.jGrowl div.aviso {z-index:255;background-color:#FFF;color:#000;-moz-border-radius:10px;-webkit-border-radius:10px;width:600px;overflow:hidden;border:3px solid #F1AF35;}
    </style>
</head>
<body>
<div id="wrapper">
<div id="header"><?php GENERAR_CABEZA(); ?></div>
<?php
echo '<div id="secc_general">';
require_once('PHP/traductor.php');
echo '</div>';
echo '<div style="clear:both"></div>';
echo '</div>';

/// NOTIFICACION DE PUBLICACIONES PENDIENTES O USUARIOS PENDIENTES
$mensaje="";
if (_F_usuario_cache('nivel') == _N_administrador)
{
    $PPA = db_contar("ventas_publicaciones","tipo='"._A_esp_activacion."'");
    $UPA = db_contar("ventas_usuarios","estado='"._N_esp_activacion."'");
    if ($PPA || $UPA)
    {
        $mensaje.= "$PPA publicaciones por aprobar (".ui_href("","admin_publicaciones_activacion","ver").").<br />";
        $mensaje.= "$UPA usuarios por aprobar (".ui_href("","admin_usuarios_activacion","ver").").<br />";
    }
}
echo JS_onload('
$("a.fancybox").fancybox();
$.jGrowl.defaults.position = "bottom-right";
'.($mensaje ? JS_growl($mensaje) : "").'
');
// -----------------------------------------------------------------
?>
<div id="footer"><?php echo GENERAR_PIE(); ?></div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-611766-3");
pageTracker._trackPageview();
} catch(err) {}</script>
<script type="text/javascript">
$(document).ready(function(){
$("#bookmark").jFav();
$("#bookmarks").bookmark({title: 'YoMachete.com - Ventas en línea en El Salvador',url: 'http://yomachete.com',sites: ['delicious', 'twitter','digg', 'facebook', 'stumbleupon','google','yahoo','windows']});
});
</script>
</body>
</html>

<?php
function GENERAR_CABEZA()
{
    $usuarios = db_contar("ventas_usuarios");
    $publicaciones = db_contar("ventas_publicaciones","tipo IN("._A_aceptado.","._A_promocionado.")");

    // Cargamos el logo.
    echo "<table>";
    echo "<tr>";
    echo "<td id=\"logotipo\">";
        echo ui_href("","./",ui_img("cabecera_logo","IMG/cabecera_logo.jpg","Logotipo YoMachete.com"));
    echo "</td>";
    echo "<td id=\"menu\">";
    echo "<table id=\"menu_der\">";
    echo "<tr>";
    echo "<td>";
    echo ui_href("","./","Comprar","boton");
    echo ui_href("","vender","Vender","boton");
    if (!S_iniciado())
    {
        echo ui_href("","iniciar","Ingresar","boton");
        echo ui_href("","registrar","Registrarse","boton");
        echo ui_href("","buscar","Búscar","boton");
        echo ui_href("","ayuda","Ayuda","boton");
    }
    else
    {
        if(_F_usuario_cache('nivel') == _N_administrador) echo ui_href("cabecera_link_admin","admin","Administración","boton");
        echo ui_href("","perfil",_F_usuario_cache("usuario"),"boton");
        echo ui_href("","buscar","Búscar","boton");
        echo ui_href("","ayuda","Ayuda","boton");
        echo ui_href("","finalizar","Salir","boton");
    }
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>";
    echo sprintf('¡%s publicaciones! | ¡%s usuarios! | <a>Contáctenos</a> | <a>Mapa del sitio</a>', $publicaciones,$usuarios);
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    if (@$_GET['peticion'] != 'buscar')
    {
        echo '
        <div id="buscador" class="principal">
            <form action="buscar" method="get">
                <input id="busqueda" name="b" type="text" value="" />
                '.ui_combobox("c",'<option value="">Todas las categorias</option>'.join("",ver_hijos("","")),@$_GET["c"]).'
                <input type="submit" value="Buscar" />
            </form>
        </div> <!-- wrapper !-->
    ';
    }
}
function GENERAR_PIE()
{
    global $db_contador;
    $data = '';
    $data .= "<p>El uso de este Sitio Web constituye una aceptación de los Términos y Condiciones y de las Políticas de Privacidad.<br />Copyright © 2009 ENLACE WEB S.A. de C.V. Todos los derechos reservados. [$db_contador]</p>";
    return $data;
}
?>
