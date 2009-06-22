<?php
require_once ("PHP/vital.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>Compra y Venta de articulos en El Salvador</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta http-equiv="Content-Style-type" content="text/css" />
    <meta http-equiv="Content-Script-type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="es" />
    <meta name="description" content="Servicio de compra y venta en línea." />
    <meta name="keywords" content="El Salvador, Comprar, Vender, Clasificados" />
    <meta name="robots" content="index, follow" />
    <link rel="stylesheet" type="text/css" href="estilo.css" />
    <link rel="stylesheet" type="text/css" href="CSS/jquery.lightbox-0.5.css" media="screen" />
    <script src="JS/jquery-1.3.1.min.js" type="text/javascript"></script>
    <script src="JS/jquery.lightbox-0.5.pack.js" type="text/javascript"></script>
</head>

<body>
<div id="wrapper">
<div id="header"><?php GENERAR_CABEZA(); ?><div style="clear:both"></div></div>
<?php
if (!isset($_GET['peticion']))
{
    echo '
		<div id="secc_categorias">'. GENERAR_CATEGORIAS() .' </div>
		<div id="secc_articulos">'. GENERAR_ARTICULOS() .' </div>
        ';
}
else
{
    echo '<div id="secc_general">';
    require_once('PHP/traductor.php');
    echo '</div>';
}
?>
</div>
<div id="footer"><?php echo GENERAR_PIE(); ?></div>
<?php echo JS('$("#ver_categorias").click(function() {$("#contenedor_categorias").toggle("slow");});'); ?>
</body>
</html>

<?php
function GENERAR_CABEZA()
{
    /*
     * Elementos en la cabecera:
     * 0. Será "una" linea.
     * 1. A la Izq. el Logo.
     * 2. resto el menú.
     */
    // Cargamos el logo.
    echo "<div id='logotipo'>";
    echo ui_href("logotipo","./",ui_img("cabecera_logo","IMG/cabecera_logo.png"));
    echo "</div>";
    /*
     * Mostramos los controles.
     * Los controles a mostrar consisten en:
     * 1. Link para Iniciar sesión -Si inicia sesión> Se convierte en Link para Finalizar Sesión
     * 2. Link para Registrarse    -Si inicia sesión> nick+link hacia su perfil
     * 3. Vender
     * 4. Búsqueda
     * 5. Ayuda.
    */
    echo "<div id='menu'>";
        echo ui_href("cabecera_link_Categorias","./","Categorías","izq");
    if (!S_iniciado())
    {
        echo ui_href("cabecera_link_sesion","iniciar","Ingresar","");
        echo ui_href("cabecera_link_cuenta","registrar","Registrarse","");
        echo ui_href("cabecera_link_vender","vender","Vender","");
        echo ui_href("cabecera_link_busqueda","buscar","Búscar","");
        echo ui_href("cabecera_link_ayuda","ayuda","Ayuda","");
    }
    else
    {
        if(_F_usuario_cache('nivel') == _N_administrador) echo ui_href("cabecera_link_admin","admin","Administración","");
        echo ui_href("cabecera_link_cuenta","perfil",_F_usuario_cache("usuario"),"");
        echo ui_href("cabecera_link_vender","vender","Vender","");
        echo ui_href("cabecera_link_busqueda","buscar","Búscar","");
        echo ui_href("cabecera_link_ayuda","ayuda","Ayuda","");
        echo ui_href("cabecera_link_sesion","finalizar","Salir","");
    }

    echo "</div>";
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
            $data .= "<h1>Mostrando artículos de la sub-categoria <span style='color:#00F'>" . db_resultado($resultado, 'nombre') . "</span></h1>";
            $data .= "Ubicación: " . join(" > ", get_path($categoria)) . "<br />";
            $data .= "<hr />";
            $data .= "Deseo publicar una <a href=\"./vender?op=$categoria\">venta</a> en esta categoría<br />";
            $data .= "<hr />";
            $c = "SELECT id_categoria, id_articulo, (SELECT id_img FROM ventas_imagenes as b WHERE b.id_articulo = a.id_articulo LIMIT 1) as imagen, titulo, descripcion_corta, id_usuario, precio FROM ventas_articulos AS a WHERE id_categoria='$categoria' AND tipo IN ("._A_aceptado . ","._A_promocionado.") ORDER by fecha_fin ASC LIMIT 10";
        }
        else
        {
            $data .= "<h1>Mostrando artículos recientes de la categoria <span style='color:#00F'>" . db_resultado($resultado, 'nombre') . "</span></h1>";
            $c = "SELECT id_categoria, id_articulo, (SELECT id_img FROM ventas_imagenes as b WHERE b.id_articulo = a.id_articulo LIMIT 1) as imagen, titulo, descripcion_corta, id_usuario, precio FROM ventas_articulos AS a WHERE (SELECT padre FROM ventas_categorias AS b where b.id_categoria=a.id_categoria)='$categoria' AND tipo IN ("._A_aceptado . ","._A_promocionado.") ORDER by fecha_fin ASC LIMIT 10";
        }
    }
    else
    {
        $data .= "<h1>Artículos mas recientes</h1>";
        // Mostrar todos los articulos en la categoría
        $c = "SELECT id_categoria, id_articulo, (SELECT id_img FROM ventas_imagenes as b WHERE b.id_articulo = a.id_articulo LIMIT 1) as imagen, titulo, descripcion_corta, id_usuario, precio FROM ventas_articulos AS a WHERE tipo IN ("._A_aceptado . ","._A_promocionado.")  ORDER by fecha_fin ASC LIMIT 10";
    }
    $r = db_consultar($c);
    while ($f = mysql_fetch_array($r))
    {
        $data .= VISTA_ArticuloEnLista($f['titulo'],"publicacion_".$f['id_articulo'],$f['precio'],substr($f['descripcion_corta'],0,200),"<a href=\"./imagen_".$f['imagen']."\" target=\"_blank\" rel=\"lightbox\" title=\"VISTA DE ARTÍCULO\"><img src=\"./imagen_".$f['imagen']."m\" /></a>",join(" > ", get_path($f['id_categoria'])));
    }

    $data .= JS_onload('$("a[rel=\'lightbox\']").lightBox();');
    return $data;
}

// Columna Izq.
function GENERAR_CATEGORIAS()
{
    $data = '';
    $data .= "<div id=\"ver_categorias\">[<a>ocultar</a>]</div>";
    $data .= (isset($_GET['categoria'])) ? '<div class="item_cat item_cat_todos"><a href="./">Ver todas las categorías</a><div style="clear:both"></div></div>' : "<h1>Categorías</h1>";
    $nivel = (isset($_GET['categoria'])) ? $_GET['categoria'] : 0;
    $c = "SELECT id_categoria, nombre FROM ventas_categorias WHERE padre=$nivel ORDER BY nombre";
    $resultado = db_consultar($c);
    $n_campos = mysql_num_rows($resultado);
    $data .= "<div id=\"contenedor_categorias\">";
    for ($i = 0; $i < $n_campos; $i++) {
        $r = mysql_fetch_row($resultado);
        $data .= "<div class=\"item_cat\">".('<a title="'.$r[1].'" href="categoria-'.$r[0].'-'.urlencode($r[1]).'">'. $r[1].'</a>')."</div> "; //Importante!, no quitar el espacio despues del </div>!!!
    }
    $data .= "</div>";
    return $data;
}
function GENERAR_PIE()
{
    $data = '';
    $data .= "<p>El uso de este Sitio Web constituye una aceptación de los Términos y Condiciones y de las Políticas de Privacidad.<br />Copyright © 2009 CEPASA de C.V. Todos los derechos reservados.</p>";
    return $data;
}
?>
