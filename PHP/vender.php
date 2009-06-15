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
    if ( !empty($_POST['op']) ) $_GET['op'] = $_POST['op'];
    // Determinamos si ya escogió la opción o no...
    if (!isset($_GET['op']))
    {
        // Le mostramos las opciones
        echo "Por favor especifique a continuación que tipo de venta desea publicar:<br/>";
        echo "Deseo publicar un: " . ui_href("vender_ir_inmueble","vender_inmueble", "inmueble") . " / " . ui_href("vender_ir_inmueble","vender_automotor", "automotor") . " / " . ui_href("vender_ir_servicio","vender_servicio", "servicio") . " / " . ui_href("vender_ir_articulo","vender_articulo", "artículo");
        return;
    }

    if(isset($_POST['vender_cancelar']))
    {
        header("location: ./");
        if (!empty($_POST['ticket']))
        {
            if (ComprobarTicketTMP(_F_usuario_cache('id_usuario'),$_POST['ticket']))
            {
                DestruirTicketTMP(_F_usuario_cache('id_usuario'),$_POST['ticket']);
            }
        }
        echo "Cancelando venta...";
        return;
    }

    // Creamos el Ticket Temporal si no lo tenemos, o rechazamos crear una nueva venta.
    if (empty($_POST['ticket']))
    {
        $ticket = ObtenerTicketTMP(_F_usuario_cache('id_usuario'));
    }
    else
    {
        $ticket = $_POST['ticket'];
        if (!ComprobarTicketTMP(_F_usuario_cache('id_usuario'),$ticket))
        {
            echo "La validación de su Ticket ha fallado.<br />";
            echo "Esto podría ser una falla del sistema o un error en su navegador<br />";
            echo "Lo sentimos, por seguridad su venta se ha descartado";
            return;
        }
    }

    echo "<b>Ticket:</b> $ticket<br />";

    $flag_habilitar_publicar = false;

    if(isset($_POST['vender_previsualizar']))
    {
        DescargarArchivos("vender_deshabilitar",$ticket,_F_usuario_cache('id_usuario'));
        CargarArchivos("vender_imagenes",$ticket,_F_usuario_cache('id_usuario'));
        $imagenes = ObtenerImagenesArr($ticket,"");
        $flag_habilitar_publicar = true;
        echo mensaje("esta es una previsualización. Sus información no será ingresada al sistema hasta que presione el botón \"Publicar\"",_M_INFO);
        echo "<hr style=\"margin-top:50px\" />";
        echo "Ud. ha escogido la siguiente categoría: <b>" . join(" > ", get_path(db_codex($_POST['vender_categoria']),false))."</b><br/><br/>";
        echo "Su publicación (una vez aprobada) se verá de la siguiente forma en la lista de publicaciones de la categoria seleccionada:<br /><br />";
        echo VISTA_ArticuloEnLista(ui_href("titulo","#",$_POST['vender_titulo']),$_POST['vender_precio'],substr($_POST['vender_descripcion_corta'],0,200),"<img src=\"./imagen_".@$imagenes[0]."m\" />");
        echo "<br /><br />Su publicación (una vez aprobada) se verá de la siguiente forma al ser accedida:<br /><br />";
        echo "<hr style=\"margin-bottom:50px\" />";
    }
    if (isset($_POST['vender_publicar']))
    {
        MANEJAR_VENTA();
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
            if (!empty($_GET['cat']))
            {
                $c = "SELECT rubro,nombre,id_categoria FROM ventas_categorias WHERE id_categoria='".db_codex($_GET['cat'])."' LIMIT 1";
                $r = db_consultar($c);
                $f = mysql_fetch_row($r);
                if (!empty($f[0]))
                {
                    $flag_categoriaDirecta=true;
                    $tipoVenta = $f[0];
                    $nombreCategoria = $f[1];
                    $idCategoria = $f[2];
                    break;
                }
            }
        default:
            $tipoVenta="articulo";
    }
    echo "<form action=\"vender\" method=\"POST\" enctype=\"multipart/form-data\">";
    echo ui_input("op",$tipoVenta,"hidden");
    echo ui_input("ticket",$ticket,"hidden");
    echo "<b>Nota:</b> Esta utilizando una cuenta gratuita, actualicese a una cuenta de ".ui_href("vender_vip","vip","Vendedor Distinguido","",'target="_blank"')." y disfrute de las ventajas!<br />";
    echo "<b>Nota:</b> Si desea regresar a la pantalla de selección de opciones de venta ".ui_href("vender_regresar","vender","presione aquí").". Perderá cualquier información ingresada.";
    echo "<ol class=\"ventas\">";
    if (!$flag_categoriaDirecta || !isset($nombreCategoria))
    {
    echo "<li>Selección de categoría</li>";
    echo "<span class='explicacion'>Ubique su árticulo en la categoría que consideres apropiada.</span><br />";
    echo "Mi árticulo corresponde a la siguiente categoría<br />".ui_combobox("vender_categoria",join("",ver_hijos("",$tipoVenta)), _F_form_cache("vender_categoria"))."<br />";
    }
    else
    {
    echo "<li>Categoría seleccionada</li>";
    echo "Ud. ha pre-seleccionado la categoría <b>$nombreCategoria</b>";
    echo ui_input("vender_categoria",$idCategoria,"hidden");
    }
    echo "<li>Título de la publicación</li>";
    echo "<span class='explicacion'>Utilice un título corto, descriptivo y llamativo, máximo 50 carácteres. No se admite código HTML.</span><br />";
    echo "Titulo " . ui_input("vender_titulo",_F_form_cache("vender_titulo"),"","","width:50ex","MAXLENGTH='50'")."<br />";
    echo "<li>Descripción corta de la publicación</li>";
    echo "<span class='explicacion'>Describa brevemente su venta (o prestación de servicio), solo los detalles más importantes, máximo 200 carácteres. No se admite código HTML.</span><br />";
    echo "Descripción corta<br />" . ui_textarea("vender_descripcion_corta",_F_form_cache("vender_descripcion_corta"),"","width:50em;height:4em;") . "<br />";
    echo "<li>Descripción del artículo</li>";
    echo "<span class='explicacion'>Describa en detalle tu artículo, incluye todos los datos relevantes que desees, máximo 5000 carácteres.<br />Se admite código HTML (".ui_href("vender_ayuda_limitacionesHMTL","ayuda#limitacionesHTML","con algunas limitantes","",'target="_blank"').").</span><br />";
    echo "Descripción larga<br />" . ui_textarea("vender_descripcion_larga",_F_form_cache("vender_descripcion_larga"),"","width:50em;height:20em;")."<br />";
    if (in_array($tipoVenta, array("articulo","automotor")))
    {
    echo "<li>Características del artículo</li>";
    echo "<span class='explicacion'>Seleccione solo las opciones que ayuden a describir de forma precisa tu producto.</span><br />";
    echo db_ui_checkboxes("vender_chkFlags[]", "ventas_flags_ventas", "nombre", "nombrep", "descripcion",$_POST["vender_chkFlags"]);
    }
    echo "<li>Precio</li>";
    echo "<span class='explicacion'>Précio en dólares de Estados Unidos de America ($ USA).</span><br />";
    echo "Précio " . ui_input("vender_precio",_F_form_cache("vender_precio"),"","","width:30ex","MAXLENGTH='30'")."<br />";
    echo "<li>Formas de pago admitidas</li>";
    echo "<span class='explicacion'>Selecione solo las opciones de pago que admitirá.</span><br />";
    echo db_ui_checkboxes("vender_opcionespago_chkFlags[]", "ventas_flags_pago", "nombre", "nombrep", "descripcion",$_POST["vender_opcionespago_chkFlags"]);
    if (in_array($tipoVenta, array("articulo")))
    {
    echo "<li>Formas de entrega admitidas</li>";
    echo "<span class='explicacion'>Selecione solo las opciones de tipos de entrega que admitirá.</span><br />";
    echo db_ui_checkboxes("vender_opcionesentrega_chkFlags[]", "ventas_flags_entrega", "nombre", "nombrep", "descripcion",$_POST["vender_opcionesentrega_chkFlags"]);
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

    echo "<br />";

    if (isset($imagenes) && is_array($imagenes))
    {
    foreach($imagenes as $archivo)
    {
        echo "<img src=\"./imagen_$archivo\" /><br />";
        echo ui_input("vender_deshabilitar[]",$archivo,"checkbox")." Eliminar esta imagen<br />";
    }
    }


    $NoMaxImg = (in_array($tipoVenta, array("servicio"))) ? 1 : 5;
    $inicio = isset($imagenes) ? count($imagenes) : 0;
    for ($i = $inicio; $i < $NoMaxImg; $i++)
    {
        echo "Imagen ".($i+1).": Cargar ". ui_input("vender_imagenes[]","","file") . "<br />";
    }
    echo "<li>Previsualizar y Publicar</li>";
    echo "</li>";
    echo "<span class='explicacion'>Puede observar como quedaría su publicación utilizando el botón 'Previsualizar'.<br />Cuando este satisfecho con el resultado presione el botón 'Publicar'.</span><br />";
    echo "<br />";
    echo "<center>";
    echo ui_input("vender_previsualizar", "Previsualizar", "submit");
    if ($flag_habilitar_publicar) echo ui_input("vender_publicar", "Publicar", "submit");
    echo ui_input("vender_cancelar", "Cancelar", "submit");
    echo "</center>";
    echo "</form>";
}

function MANEJAR_VENTA()
{
    echo "Su publicación se ha enviado, sin embargo tiene que ser aprobada manualmente por un administrador para ser pública.<br />Dicha aprobación puede tardar entre 10 minutos y 2 horas; un correo de confirmación le será enviado cuando su publicación sea aceptada.<br />";
    echo "Le invitamos a seguir navegando en nuestro sitio mientras su publicación es aceptada. ". ui_href("vender_continuar","./", "Continuar") ."<br />";
    return;
}
?>
