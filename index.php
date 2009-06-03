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
    <script src="JS/jquery-1.3.1.min.js" type="text/javascript"></script>
</head>

<body>
<div id="header"><?php GENERAR_CABEZA(); ?></div>
<div class="colmask">
<?php
if (!isset($_GET['peticion']))
{
    echo '
        <div class="colmask threecol"><div class="colmid"><div class="colleft">
		<div class="col1">'. GENERAR_COLUMNA1() .' </div>
		<div class="col2">'. GENERAR_COLUMNA2() .' </div>
		<div class="col3">'. GENERAR_COLUMNA3() .' </div>
        </div></div></div>
        ';

    return;
}
else
{
    require_once('PHP/traductor.php');
}
?>
</div>
<div id="footer"><?php GENERAR_PIE(); ?></div>
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
    echo "<div id='logotipo' style='width:115px;float:left;'>";
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
        echo ui_href("cabecera_link_sesion","iniciar","Cuenta","");
        echo ui_href("cabecera_link_cuenta","registrar","Registrarse","");
    }
    else
    {
        echo ui_href("cabecera_link_sesion","finalizar","Salir","");
        echo ui_href("cabecera_link_cuenta","perfil","Perfil","");
    }
        echo ui_href("cabecera_link_vender","vender","Vender","");
        echo ui_href("cabecera_link_busqueda","buscar","Búscar","");
        echo ui_href("cabecera_link_ayuda","ayuda","Ayuda","");

    echo "</div>";
}

// Columna central
function GENERAR_COLUMNA1()
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
            $data .= "Deseo publicar una <a href=\"./vender:$categoria\">venta</a> / <a href=\"./comprar:$categoria\">compra</a> en esta categoría<br />";
            $data .= "<hr />";
            // Mostrar todos los articulos en la categoría
            $c = "SELECT (SELECT ubicacion FROM ventas_imagenes as b WHERE b.id_articulo = a.id_articulo) as imagen, titulo, descripcion_corta, id_usuario, precio FROM ventas_articulos AS a WHERE id_categoria='$categoria'";
            $resultado = db_consultar($c);
            $data .= db_ui_tabla($resultado,"",false,"No hay articulos");
        }
        else
        {
            $data .= "<h1>Mostrando artículos recientes de la categoria <span style='color:#00F'>" . db_resultado($resultado, 'nombre') . "</span></h1>";
            $data .= "";
        }
    }
    else
    {
        $data .= "<h1>Artículos mas recientes</h1>";

    }
    return $data;
}

// Columna Izq.
function GENERAR_COLUMNA2()
{
    $data = '';
    $data .= "<h1>Categorías</h1>";
    $nivel = (isset($_GET['categoria'])) ? $_GET['categoria'] : 0;
    $c = "SELECT CONCAT('<a href=\"categoria_' , id_categoria, '_', nombre , '\">', nombre, '</a>') AS link FROM ventas_categorias WHERE padre=$nivel ORDER BY nombre";
    $resultado = db_consultar($c);
    $data .= '<span id="categorias">';
    $data .= db_ui_lista($resultado);
    $data .= '</span>';
    $data .= (isset($_GET['categoria'])) ? '<br /><a href="./">Ver todas las categorías</a>' : '';
    return $data;
}

// Columna Der.
function GENERAR_COLUMNA3()
{
    $data = '';
    $data .= "<h1>Destacados</h1>";
    $data .= "<center>".ui_href("columna3_productos_promocionados","","¡Promocionate aquí!")."</center>";
    return $data;
}
function GENERAR_PIE()
{
    echo "<p>El uso de este Sitio Web constituye una aceptación de los Términos y Condiciones y de las Políticas de Privacidad.<br />Copyright © 2009 CEPASA de C.V. Todos los derechos reservados.</p>";
}
?>
