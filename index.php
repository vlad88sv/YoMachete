<?php
require_once ("PHP/vital.php");
// Inclusiones JS
$arrJS[] = 'jquery-1.3.2.min';

// Inclusiones CSS
$arrCSS[] = 'estilo';

// Auxiliar para HEAD
$arrHEAD = array();

$HEAD_titulo = PROY_NOMBRE . ' - compra y venta de artículos en El Salvador';
$HEAD_descripcion = 'Clasificados de El Salvador.';
?>
<?php
/* CAPTURAR <body> */
ob_start();
?>
<body>
<div id="wrapper">
<div id="header"><?php GENERAR_CABEZA(); ?></div>
<div id="secc_general">
<?php require_once('PHP/traductor.php'); ?>
</div> <!-- secc_general !-->
</div> <!-- wrapper !-->
<div style="clear:both"></div>
<div id="footer"><?php echo GENERAR_PIE(); ?></div>
<div id="GA"><?php echo GENERAR_GOOGLE_ANALYTICS(); ?></div>
</body>
</html>
<?php $BODY = ob_get_clean(); ?>
<?php
/* CAPTURAR <head> */
ob_start();
echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta http-equiv="Content-Style-type" content="text/css" />
    <meta http-equiv="Content-Script-type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="es" />
    <title><?php echo $HEAD_titulo; ?></title>
    <meta name="description" content="<?php echo $HEAD_descripcion; ?>" />
    <meta name="keywords" content="Clasificados, El Salvador, Comprar, Vender" />
    <meta name="robots" content="index, follow" />
    <link href="favicon.ico" rel="icon" type="image/x-icon" />
<?php HEAD_CSS(); ?>
<?php HEAD_JS(); ?>
<?php HEAD_EXTRA(); ?>
</head>
<?php $HEAD = ob_get_clean(); ?>
<?php
/* MOSTRAR TODO */
echo $HEAD,$BODY;
?>
<?php
/* ---------------------------------------------------------------------------*/
/* Funciones adicionales */
function GENERAR_CABEZA()
{
    $usuarios = db_contar("ventas_usuarios");
    $publicaciones = db_contar("ventas_publicaciones","tipo IN("._A_aceptado.","._A_promocionado.")");

    // Cargamos el logo.
    echo '<table>';
    echo '<tr>';
    echo '<td id="logotipo">';
        echo ui_href("","./",ui_img("cabecera_logo","IMG/cabecera_logo.jpg","Logotipo YoMachete.com"));
    echo '</td>';
    echo '<td id="menu">';
    echo '<div style="clear:both;float:right">';
    echo '<table id="menu_der">';
    echo '<tr>';
    echo '<td>'.ui_href("","./","Comprar","boton").'</td>';
    echo '<td>'.ui_href("","vender","Vender","boton").'</td>';
    if (!S_iniciado())
    {
        echo '<td>'.ui_href("","iniciar","Ingresar","boton").'</td>';
        echo '<td>'.ui_href("","registrar","Registrarse","boton").'</td>';
        echo '<td>'.ui_href("","buscar","Búscar","boton").'</td>';
        echo '<td>'.ui_href("","ayuda","Ayuda","boton").'</td>';
    }
    else
    {
        if(_F_usuario_cache('nivel') == _N_administrador)
            echo '<td>'.ui_href("cabecera_link_admin","admin","Administración","boton").'</td>';
        echo '<td>'.ui_href("","perfil",_F_usuario_cache("usuario"),"boton").'</td>';
        echo '<td>'.ui_href("","buscar","Búscar","boton").'</td>';
        echo '<td>'.ui_href("","ayuda","Ayuda","boton").'</td>';
        echo '<td>'.ui_href("","finalizar","Salir","boton").'</td>';
    }
    echo '</tr>';
    echo '</table>';
    echo sprintf('¡%s publicaciones! | ¡%s usuarios!', $publicaciones,$usuarios);
    echo '</div>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
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
    global $db_contador,$arrJS,$arrCSS,$arrHEAD;
    $data = '';
    $js = '';
    $data .= "<p>El uso de este Sitio Web constituye una aceptación de los Términos y Condiciones y de las Políticas de Privacidad.<br />Copyright © 2009 ENLACE WEB S.A. de C.V. Todos los derechos reservados. [$db_contador]</p>";
    
    if (S_iniciado())
    {
        /// NOTIFICACION DE PUBLICACIONES PENDIENTES O USUARIOS PENDIENTES
        $mensaje='';
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
        $_EST_CANT_MP_NUEVOS = ObtenerEstadisticasUsuario(_F_usuario_cache('id_usuario'),_EST_CANT_MP_NUEVOS);
        if ($_EST_CANT_MP_NUEVOS > 0)
        {
            $mensaje.= 'Tiene ' . $_EST_CANT_MP_NUEVOS . ' nuevos Mensajes Privados sin leer. <a href="'.PROY_URL.'perfil?op=mp">Clic aquí para leerlos</a>.';            
        }
        
        if (!empty($mensaje))
        {
            $arrCSS[] = 'CSS/jquery.jgrowl';
            $arrJS[] = 'jquery.jgrowl';
            $arrHEAD[] = JS_onload('$.jGrowl.defaults.position = "bottom-right";'.JS_growl($mensaje));
            echo '<script>',JS_growl($mensaje),'</script>';
        }
    }
    
    return $data;
}
function GENERAR_GOOGLE_ANALYTICS()
{
    return '
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-611766-3");
pageTracker._trackPageview();
} catch(err) {}</script>
';
}
?>
