<?php
function CONTENIDO_VENDER()
{
    // Comprobamos que ya haya ingresado al sistema
    if (!S_iniciado())
    {
        echo "Necesitas iniciar sesión para poder <b>publicar</b> y <b>vender</b>.<br />";
        require_once("PHP/inicio.php");
        CONTENIDO_INICIAR_SESION();
        return;
    }

    // --------------------------VARIABLES----------------------------
    $flag_op_y_saltar = false;
    $flag_enviar = isset($_POST['vender_enviar']);
    $flag_publicar = isset($_POST['vender_publicar']);
    $flag_modo_previsualizacion = isset($_POST['vender_previsualizar']);
    $flag_modo_escritura = (isset($_POST['vender_publicar']) || isset($_POST['vender_previsualizar'])) && !isset($_POST['vender_editar']);

    // --------------------------CATEGORIA-------------------------------
    if ( !isset($_GET['op']) && !isset($_GET['ticket']))
    {
        // No ha escogido categoría, le mostramos las opciones.
        echo "Por favor especifique a continuación que tipo de venta desea publicar:<br/>";
        echo "Deseo publicar un: " . ui_href("vender_ir_inmueble","vender?op=inmueble", "inmueble") . " / " . ui_href("vender_ir_inmueble","vender?op=automotor", "automotor") . " / " . ui_href("vender_ir_servicio","vender?op=servicio", "servicio") . " / " . ui_href("vender_ir_articulo","vender?op=articulo", "artículo");

        // Mostrar las ventas incompletas:

        $c = "SELECT id_articulo, IF(titulo='','<sin título>', titulo) AS titulo2, id_categoria, IF((SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria) is NULL,'<sin categoría>',(SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria)) AS categoria, (SELECT rubro FROM ventas_categorias AS b WHERE b.id_categoria=a.id_categoria) AS rubro FROM ventas_articulos AS a WHERE id_usuario='"._F_usuario_cache('id_usuario')."' AND tipo='"._A_temporal."'";
        $r = db_consultar($c);
        if ( mysql_num_rows($r) > 0 )
        {
            echo "<hr />";
            echo "Se han encontrado los siguientes borradores de ventas que no ha enviado para publicación.<br />";
            echo "<ul>";
            while ($f = mysql_fetch_array($r))
            {
                echo "<li>[".ui_href("","vender?ticket=".$f['id_articulo'],"CONTINUAR") ."] / [" . ui_href("","vender?ticket=".$f['id_articulo']."&eliminar=proceder","ELIMINAR") . "] > ticket: <b>" . htmlentities($f['id_articulo'],ENT_QUOTES,'UTF-8') . "</b>, título: <b>" . htmlentities($f['titulo2'],ENT_QUOTES,'UTF-8') . "</b>, categoría: <b>" . htmlentities($f['categoria'],ENT_QUOTES,'UTF-8') . "</b>, tipo: <b>" . htmlentities($f['rubro'],ENT_QUOTES,'UTF-8') . "</b></li>";
            }
            echo "</ul>";
        }

        // Mostrar las ventas esperando aprobación

        $c = "SELECT id_articulo, IF(titulo='','<sin título>', titulo) AS titulo2, id_categoria, IF((SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria) is NULL,'<sin categoría>',(SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria)) AS categoria FROM ventas_articulos AS a WHERE id_usuario='"._F_usuario_cache('id_usuario')."' AND tipo='"._A_esp_activacion."'";
        $r = db_consultar($c);
        if ( mysql_num_rows($r) > 0 )
        {
            echo "<hr />";
            echo "Se han encontrado las siguientes ventas que estan esperando aprobación de un administrador:<br />";
            echo "<ul>";
            while ($f = mysql_fetch_array($r))
            {
                echo "<li>Ticket: <b>" . htmlentities($f['id_articulo'],ENT_QUOTES,'UTF-8') . "</b>, título: <b>" . htmlentities($f['titulo2'],ENT_QUOTES,'UTF-8') . "</b>, categoría: <b>" . htmlentities($f['categoria'],ENT_QUOTES,'UTF-8') . "</b></li>";
            }
            echo "</ul>";
        }

        // Mostrar las ventas caducadas

        $c = "SELECT id_articulo, IF(titulo='','<sin título>', titulo) AS titulo2, id_categoria, IF((SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria) is NULL,'<sin categoría>',(SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria)) AS categoria FROM ventas_articulos AS a WHERE id_usuario='"._F_usuario_cache('id_usuario')."' AND tipo = '"._A_aceptado."'AND fecha_fin <='".mysql_datetime()."'";
        $r = db_consultar($c);
        if ( mysql_num_rows($r) > 0 )
        {
            echo "<hr />";
            echo "Se han encontrado las siguientes ventas que han caducado:<br />";
            echo "<ul>";
            while ($f = mysql_fetch_array($r))
            {
                echo "<li>Ticket: <b>" . htmlentities($f['id_articulo'],ENT_QUOTES,'UTF-8') . "</b>, título: <b>" . htmlentities($f['titulo2'],ENT_QUOTES,'UTF-8') . "</b>, categoría: <b>" . htmlentities($f['categoria'],ENT_QUOTES,'UTF-8') . "</b> [". ui_href("","servicios?op=atp&pub=".$f['id_articulo'],"ampliar tiempo")."]</li>";
            }
            echo "</ul>";
        }
        return;
    }
    elseif (isset($_GET['op']) && !isset($_GET['ticket']))
    {
        $op = $_GET['op'];
        if (!is_numeric($op))
        {
            $c = "SELECT id_categoria FROM ventas_categorias WHERE rubro='".db_codex($op)."' LIMIT 1";
        }
        else
        {
            $c = "SELECT id_categoria FROM ventas_categorias WHERE id_categoria='".db_codex($op)."' LIMIT 1";
        }
        $r = db_consultar($c);
        $f = mysql_fetch_row($r);
        if (!empty($f[0]))
        {
            $_POST["id_categoria"] =  $f[0];
            $flag_modo_escritura=true;
            $flag_op_y_saltar=true;
        }
    }
    elseif (isset($_GET['op']) && isset($_GET['ticket']))
    {
        $flag_modo_escritura=false;
        $flag_op_y_saltar=true;
    }

    // --------------------------TICKET-------------------------------
    // Creamos el Ticket Temporal de venta si no lo tenemos o validamos el actual
    $ticket = empty($_GET['ticket']) ?  ObtenerTicketTMP(_F_usuario_cache('id_usuario')) : $_GET['ticket'];

    if (!ComprobarTicket($ticket))
    {
        echo "La validación de su Ticket ha fallado.<br />";
        echo "Esto podría bien ser una falla del sistema o un error en su navegador<br />";
        echo "Lo sentimos, por seguridad esta operación no continuará";
        return;
    }

    // ---Si el ticket es valido entoces rescatemos lo que lleva hecho---

    if(isset($_GET['eliminar']))
    {
        if (!empty($_GET['ticket']))
        {
            DestruirTicket($_GET['ticket']);
        }
        echo "La publicación ha sido cancelada y eliminada.<br />";
        echo ui_href("","./","Regresar a la página principal") . " / " . ui_href("","./vender", "Regresar a ventas") ;
        return;
    }

    if(isset($_POST['vender_cancelar']))
    {
        header("location: ./");
        if (!empty($_GET['ticket']))
        {
            DestruirTicket($_GET['ticket']);
        }
        echo "Cancelando venta...";
        return;
    }

    if ($flag_modo_escritura)
    {
        DescargarArchivos("vender_deshabilitar",$ticket,_F_usuario_cache('id_usuario'));
        CargarArchivos("vender_imagenes",$ticket,_F_usuario_cache('id_usuario'));
        CargarDatos($ticket,_F_usuario_cache('id_usuario'));
    }

    if ($flag_op_y_saltar)
    {
        header("location: ./vender?ticket=$ticket");
    }

    if ($flag_enviar)
    {
        // Al fin lo terminó de editar y lo esta enviando... Aleluya!
        //-
        // Si es Admin entonces aprobar automaticamente, si no pues mandarlo a esperar activacion
        $c = "UPDATE ventas_articulos SET tipo='"._A_esp_activacion."' WHERE id_articulo=$ticket LIMIT 1";
        $r = db_consultar($c);
        if ( db_afectados() == 1 )
        {
            if (_F_usuario_cache('nivel') == _N_administrador)
            {
                Publicacion_Aprobar($ticket);
                echo Mensaje ("Su venta ha sido publicada", _M_INFO);
            }
            else
            {
                echo Mensaje ("Su venta ha sido exitosamente enviada para aprobación", _M_INFO);
            }
        }
        else
        {
            echo Mensaje ("Su venta ha NO a sido enviada para aprobación, sucedió algún error", _M_ERROR);
        }
        echo "Continuar a: " . ui_href("","vender","publicar otra venta") . " / " . ui_href("","./","página principal")."<br />";
        return;
    }

    $Buffer = ObtenerDatos($ticket);
    $imagenes = ObtenerImagenesArr($ticket,"");

    echo "Ud. se encuentra utilizando una cuenta gratuita, ¡actualicese a una cuenta de ".ui_href("vender_vip","vip","Vendedor Distinguido","",'target="_blank"')." y disfrute de las ventajas!<br />";

    if($flag_modo_previsualizacion || $flag_publicar)
    {
        echo mensaje("esta es una previsualización. Sus información no será ingresada al sistema hasta que presione el botón \"Publicar\"",_M_INFO);
        echo "<hr style=\"margin-top:50px\" />";
        echo "Ud. ha escogido la siguiente categoría: <b>" . join(" > ", get_path(db_codex(@$Buffer['id_categoria']),false))."</b><br/><br/>";
        echo "Su publicación (una vez aprobada) se verá de la siguiente forma en la lista de publicaciones de la categoria seleccionada:<br /><br />";
        echo VISTA_ArticuloEnLista("id_articulo=$ticket","","previsualizacion","Woops!, ¡problemas intentando cargar la previsualización!");
        echo "<br /><br />Su publicación (una vez aprobada) se verá de la siguiente forma al ser accedida:<br /><br />";
        echo "<div id=\"prev_pub\">";
        require_once ("PHP/contenido.php");
        $_GET['publicacion'] = $ticket;
        CONTENIDO_PUBLICACION("previsualizacion");
        echo "</div>";
        echo "<hr style=\"margin-bottom:50px\" />";
    }

    // -----------------------------------------------------------------
    // Inicio de formulario

    echo "<form action=\"vender?ticket=$ticket\" method=\"POST\" enctype=\"multipart/form-data\">";
    if( $flag_publicar )
    {
        $Aprobacion = (_F_usuario_cache('nivel') == _N_administrador) ? "Ud. es administrador, su publicación será aprobada automaticamente" : "No podrá editar su publicación de nuevo hasta que esta sea esta sea revisada y aprobada.";
        echo "<span class='explicacion'>Esta a punto de enviar su publicación a revisión. Puede seguir editando su publicación presionando el botón <b>Editar</b> o finalizar presionando el botón <b>Enviar</b>.<br />$Aprobacion</span>";
        echo "<br />";
        echo "<center>";
        echo ui_input("vender_enviar","Enviar","submit");
        echo ui_input("vender_editar","Editar","submit");
        echo "</center>";
        return;
    }
    echo "<ol class=\"ventas\">";
    echo "<li>Selección de categoría</li>";
    echo "<span class='explicacion'>Ubique su árticulo en la categoría que consideres apropiada.</span><br />";
    echo "Mi árticulo corresponde a la siguiente categoría<br />".ui_combobox("id_categoria",join("",ver_hijos("",@$Buffer["rubro"])), @$Buffer["id_categoria"])."<br />";

    echo "<li>Título de la publicación</li>";
    echo "<span class='explicacion'>Utilice un título corto, descriptivo y llamativo, máximo 50 carácteres. No se admite código HTML.</span><br />";
    echo "Titulo " . ui_input("titulo",@$Buffer["titulo"],"","","width:50ex","MAXLENGTH='50'")."<br />";
    echo "<li>Descripción corta de la publicación</li>";
    echo "<span class='explicacion'>Describa brevemente su venta (o prestación de servicio), solo los detalles más importantes, máximo 300 carácteres. No se admite código HTML.</span><br />";
    echo "Descripción corta<br />" . ui_textarea("descripcion_corta",@$Buffer["descripcion_corta"],"","width:50em;height:4em;") . "<br />";
    echo "<li>Descripción del artículo</li>";
    echo "<span class='explicacion'>Describa en detalle tu artículo, incluye todos los datos relevantes que desees, máximo 5000 carácteres.<br />Se admite código HTML (".ui_href("vender_ayuda_limitacionesHMTL","ayuda#limitacionesHTML","con algunas limitantes","",'target="_blank"').").</span><br />";
    echo "Descripción larga<br />" . ui_textarea("descripcion",@$Buffer["descripcion"],"","width:50em;height:20em;")."<br />";
    if (in_array(@$Buffer["rubro"], array("articulo","automotor")))
    {
        echo "<li>Características del artículo</li>";
        echo "<span class='explicacion'>Seleccione solo las opciones que ayuden a describir de forma precisa tu producto.</span><br />";
        echo db_ui_checkboxes("flags_ventas[]", "ventas_flags_ventas", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"flags_ventas"));
    }
    echo "<li>Precio</li>";
    echo "<span class='explicacion'>Précio en dólares de Estados Unidos de America ($ USA).</span><br />";
    echo "Précio " . ui_input("precio",@$Buffer["precio"],"","","width:30ex","MAXLENGTH='30'")."<br />";
    echo "<li>Formas de pago admitidas</li>";
    echo "<span class='explicacion'>Selecione solo las opciones de pago que admitirá.</span><br />";
    echo db_ui_checkboxes("flags_pago[]", "ventas_flags_pago", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"flags_pago"));
    if (in_array(@$Buffer["rubro"], array("articulo")))
    {
        echo "<li>Formas de entrega admitidas</li>";
        echo "<span class='explicacion'>Selecione solo las opciones de tipos de entrega que admitirá.</span><br />";
        echo db_ui_checkboxes("flags_entrega[]", "ventas_flags_entrega", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"flags_entrega"));
    }
    switch(@$Buffer["rubro"])
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
            echo "<div style='display:inline-block'><a href=\"./imagen_".$archivo."\" title=\"IMAGEN CARGADA\" target=\"_blank\" rel=\"lightbox\"><img src=\"./imagen_".$archivo."m\" /></a><br />".ui_input("vender_deshabilitar[]",$archivo,"checkbox")."&nbsp;Eliminar</div>";
        }
        echo "<div style=\"clear:both\"></div>";
    }


    $NoMaxImg = (in_array(@$Buffer["rubro"], array("servicio"))) ? 1 : 5;
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
    echo ui_input("vender_publicar", "Publicar", "submit");
    echo ui_input("vender_cancelar", "Cancelar", "submit");
    echo "</center>";
    echo "</form>";
    echo JS_onload('$("a[rel=\'lightbox\']").lightBox();');
}
?>
