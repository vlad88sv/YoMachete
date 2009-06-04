<?php
function CONTENIDO_VENDER()
{
    if (!S_iniciado())
    {
        echo "Necesitas iniciar sesión para poder <b>publicar</b> y <b>vender</b>.<br />";
        require_once("PHP/inicio.php");
        CONTENIDO_INICIAR_SESION();
        return;
    }

    /*
     * Primero necesitamos conocer que es lo que necesita, se le presentarán las siguientes opciones:
     * 1. Articulo o Producto
     * 2. Servicios
    */
    // Determinamos si ya escogió la opción o no...
    if (!isset($_GET['op']))
    {
        // Le mostramos las opciones
        echo "Por favor especifique a continuación que tipo de venta desea publicar:<br/>";
        echo "Deseo publicar un: " . ui_href("vender_ir_inmueble","vender_inmueble", "inmueble") . " / " . ui_href("vender_ir_inmueble","vender_automotor", "automotor") . " / " . ui_href("vender_ir_servicio","vender_servicio", "servicio") . " / " . ui_href("vender_ir_articulo","vender_articulo", "artículo");
        return;
    }

    $flag_categoriaDirecta=false;
    // Ya escogió
    switch($_GET['op'])
    {
        case "servicio":
            $tipoVenta="servicio";
        break;
        case "articulo":
            $tipoVenta="articulo";
        break;
        case "inmueble":
            $tipoVenta="inmueble";
        break;
        case "automotor":
            $tipoVenta="automotor";
        break;
        case "categoria":
            if (isset($_GET['cat']))
            {
                $c = "SELECT rubro,nombre FROM ventas_categorias WHERE id_categoria='".db_codex(isset($_GET['cat']))."' LIMIT 1";
                $r = db_consultar($c);
                $f = mysql_fetch_row($r);
                if (isset($f[0]))
                {
                    $flag_categoriaDirecta=true;
                    $tipoVenta = $f[0];
                    $nombreCategoria = $f[1];
                    break;
                }
            }
        default:
            $tipoVenta="articulo";
    }
    echo "<form action=\"vender\" method=\"POST\">";
    echo "<b>Nota:</b> Esta utilizando una cuenta gratuita, actualicese a una cuenta de ".ui_href("vender_vip","vip","Vendedor Distinguido","",'target="_blank"')." y disfrute de las ventajas!<br />";
    echo "<b>Nota:</b> Si desea regresar a la pantalla de selección de opciones de venta ".ui_href("vender_regresar","vender","presione aquí").". Perderá cualquier información ingresada.";
    echo "<ol class=\"ventas\">";
    if (!$flag_categoriaDirecta || !isset($nombreCategoria))
    {
    echo "<li>Selección de categoría</li>";
    echo "<span class='explicacion'>Ubique su árticulo en la categoría que consideres apropiada.</span><br />";
    echo "Mi árticulo corresponde a la siguiente categoría<br />".ui_combobox("vender_categoria",join("",ver_hijos("",$tipoVenta)))."<br />";
    }
    else
    {
    echo "<li>Categoría seleccionada</li>";
    echo "Ud. ha pre-seleccionado la categoría <b>$nombreCategoria</b>";
    echo ui_input("vender_categoria",$nombreCategoria,"hidden");
    }
    echo "<li>Título de la publicación</li>";
    echo "<span class='explicacion'>Utilice un título corto, descriptivo y llamativo, máximo 30 carácteres. No se admite código HTML.</span><br />";
    echo "Titulo " . ui_input("vender_titulo","","","","width:30ex","MAXLENGTH='30'")."<br />";
    echo "<li>Descripción corta de la publicación</li>";
    echo "<span class='explicacion'>Describa brevemente su venta (o prestación de servicio), solo los detalles más importantes, máximo 100 carácteres. No se admite código HTML.</span><br />";
    echo "Descripción corta<br />" . ui_textarea("vender_descripcion_corta","","","width:50em;height:4em;") . "<br />";
    echo "<li>Descripción del artículo</li>";
    echo "<span class='explicacion'>Describa en detalle tu artículo, incluye todos los datos relevantes que desees, máximo 5000 carácteres.<br />Se admite código HTML (".ui_href("vender_ayuda_limitacionesHMTL","ayuda#limitacionesHTML","con algunas limitantes","",'target="_blank"').").</span><br />";
    echo "Descripción larga<br />" . ui_textarea("vender_descripcion_larga","","","width:50em;height:20em;")."<br />";
    if (in_array($tipoVenta, array("articulo","automotor")))
    {
    echo "<li>Características del artículo</li>";
    echo "<span class='explicacion'>Seleccione solo las opciones que ayuden a describir de forma precisa tu producto.</span><br />";
    echo db_ui_checkboxes("vender_chkFlags[]", "ventas_flags_ventas", "nombre", "nombrep", "descripcion");
    }
    echo "<li>Precio</li>";
    echo "<span class='explicacion'>Précio en dólares de Estados Unidos de America ($ USA).</span><br />";
    echo "Précio " . ui_input("vender_precio","","","","width:30ex","MAXLENGTH='30'")."<br />";
    echo "<li>Formas de pago admitidas</li>";
    echo "<span class='explicacion'>Selecione solo las opciones de pago que admitirá.</span><br />";
    echo db_ui_checkboxes("vender_opcionespago_chkFlags[]", "ventas_flags_pago", "nombre", "nombrep", "descripcion");
    if (in_array($tipoVenta, array("articulo")))
    {
    echo "<li>Formas de entrega admitidas</li>";
    echo "<span class='explicacion'>Selecione solo las opciones de tipos de entrega que admitirá.</span><br />";
    echo db_ui_checkboxes("vender_opcionesentrega_chkFlags[]", "ventas_flags_entrega", "nombre", "nombrep", "descripcion");
    }
    switch($tipoVenta)
    {
        case "articulo":
            echo "<li>Fotografías del artículo</li>";
        break;
        case "automotor":
            echo "<li>Fotografías del automotor</li>";
        break;
        case "inmuble":
            echo "<li>Fotografías del inmueble</li>";
        break;
        case "servicio":
            echo "<li>Imagen relacionada con su servicio (logotipo, etc.)</li>";
        break;
    }
    echo "<span class='explicacion'>Cargue las fotografías reales de su artículo, se necesita al menos una para ser aprobado y publicado.<br />Imagenes tomadas de la página del fabricante o similires son permitidas con un máximo de dos imagenes.<br />En total se admiten cinco imagenes</span><br />";
    echo "Imagen 1: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    if (in_array($tipoVenta, array("articulo","automotor","inmueble")))
    {
    echo "Imagen 2: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    echo "Imagen 3: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    echo "Imagen 4: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    echo "Imagen 5: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    }
    echo "<li>Previsualizar y Publicar</li>";
    echo "</li>";
    echo "<span class='explicacion'>Puede observar como quedaría su publicación utilizando el botón 'Previsualizar'.<br />Cuando este satisfecho con el resultado presione el botón 'Publicar'.</span><br />";
    echo "<br />";
    echo "<center>" . ui_input("vender_proceder", "Previsualizar", "submit") . "</center>";
    echo "</form>";
}
?>
