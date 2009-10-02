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
    <script src="JS/jquery-1.3.2.min.js" type="text/javascript"></script>
    <script src="JS/jquery.cookie.js" type="text/javascript"></script>
    <script src="JS/fancybox/jquery.easing.1.3.js" type="text/javascript"></script>
    <script src="JS/fancybox/jquery.fancybox-1.2.1.pack.js" type="text/javascript"></script>
    <script src="JS/jquery.jgrowl.js" type="text/javascript"></script>
    <style type="text/css">
    div.jGrowl div.aviso {z-index:50;background-color: #F1AF35;color: #FFF;-moz-border-radius:10px;-webkit-border-radius:10px;width:600px;overflow:hidden;opacity:1;filter:alpha(opacity = 100);border:2px solid #000000;}
    </style>
    <?php
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
    ?>
</head>
<body>
<div id="wrapper">
<div id="header"><?php GENERAR_CABEZA(); ?></div>
<?php
if (!isset($_GET['peticion']))
{
?>
<div id="buscador" class="principal">
    <form action="buscar" method="get">
    <div>
        <input id="busqueda" name="b" type="text" value="<?php echo @$_GET["b"]; ?>" />
        <?php echo ui_combobox("c",'<option value="">Todas las categorias</option>'.join("",ver_hijos("","")),@$_GET["c"]); ?>
        <input type="submit" value="Buscar" />
    </div>
    </form>
</div>
<div id="columnas">
<div id="col2">
<div id="secc_articulos"><?php echo GENERAR_ARTICULOS(); echo GENERAR_TAG_CLOUD(); ?></div>
</div>
<div id="col1">
<div id="secc_categorias"><?php echo GENERAR_CATEGORIAS() ?></div>
</div>
<div style="clear:both"></div>
</div>
<?php
}
else
{
    echo '<div id="secc_general">';
    require_once('PHP/traductor.php');
    echo '</div>';
}
?>
<div style="clear:both"></div>
</div>
<div id="footer"><?php echo GENERAR_PIE(); ?></div>
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
}

// Columna central
function GENERAR_ARTICULOS()
{
    $data = '';
    $categoria = isset($_GET['categoria']) ? db_codex($_GET['categoria']) : 0;
    if ($categoria)
    {
        $c = "SELECT * FROM ventas_categorias WHERE id_categoria='$categoria'";
        $resultado = db_consultar($c);

        if (db_resultado($resultado, 'padre') > 0)
        {
            $data .= "<h1>Mostrando publicaciones de la sub-categoria <span style='color:#00F'>" . db_resultado($resultado, 'nombre') . "</span></h1>";
            $data .= "Ubicación: " . get_path($categoria) . "<br />";
            $data .= "<hr />";
            $data .= "Deseo publicar una <a href=\"./vender?op=$categoria\">venta</a> en esta categoría<br />";
            $data .= "<hr />";
            $WHERE = "z.id_categoria='$categoria'";
        }
        else
        {
            $data .= "<h1>Mostrando publicaciones recientes de la categoria <span style='color:#00F'>" . db_resultado($resultado, 'nombre') . "</span></h1>";
            $WHERE = "(SELECT padre FROM ventas_categorias AS b where b.id_categoria=z.id_categoria)='$categoria'";
        }
    }
    else
    {
        $data .= "<h1>Publicaciones mas recientes</h1>";
        // Mostrar todos los articulos recientes
        $WHERE = "1";
    }

    $WHERE .= "  AND z.tipo IN ("._A_aceptado . ","._A_promocionado.") AND fecha_fin >= CURDATE()";
    $data .= VISTA_ListaPubs($WHERE,"ORDER by promocionado DESC,fecha_fin ASC","indice");
    return $data;
}

// Columna Izq.
function GENERAR_CATEGORIAS()
{
    $data = '';
    $data .= (isset($_GET['categoria'])) ? '<div class="item_cat item_cat_todos"><a href="./">Mostrar categorías</a><div style="clear:both"></div></div>' : "<h1>Categorías</h1>";
    $nivel = (isset($_GET['categoria'])) ? $_GET['categoria'] : 0;
    $c = "SELECT id_categoria, nombre FROM ventas_categorias WHERE padre=$nivel ORDER BY nombre";
    $resultado = db_consultar($c);
    $n_campos = mysql_num_rows($resultado);
    $data .= "<div id=\"contenedor_categorias\">";
    for ($i = 0; $i < $n_campos; $i++) {
        $r = mysql_fetch_row($resultado);
        $data .= "<div class=\"item_cat\">".('<a title="'.$r[1].'" href="categoria-'.$r[0].'-'.SEO($r[1]).'">'. $r[1].'</a>')."</div> "; //Importante!, no quitar el espacio despues del </div>!!!
    }
    $data .= "</div>";
    return $data;
}
function GENERAR_PIE()
{
    global $db_contador;
    $data = '';
    $data .= "<p>El uso de este Sitio Web constituye una aceptación de los Términos y Condiciones y de las Políticas de Privacidad.<br />Copyright © 2009 ENLACE WEB S.A. de C.V. Todos los derechos reservados. [$db_contador]</p>";
    return $data;
}
function GENERAR_TAG_CLOUD()
{
$c = "SELECT (SELECT tag FROM ventas_tag AS b WHERE b.id = a.id_tag) as tag, count(id_tag) AS hits FROM (SELECT * FROM ventas_tag_uso AS b WHERE b.id_publicacion IN (SELECT c.id_publicacion FROM ventas_publicaciones AS c WHERE tipo IN ("._A_aceptado . ","._A_promocionado.") AND fecha_fin >= CURDATE())) AS a GROUP BY id_tag ORDER BY hits DESC LIMIT 20";
$r = db_consultar($c);
return '<h1>Nube de etiquetas</h1><div id="nube_etiquetas">'.tag_cloud($r).'</div>';
}
?>
