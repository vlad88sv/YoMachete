<?php
function CONTENIDO_VENDER()
{
    global $arrJS,$arrHEAD;
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
        // Será que aún tiene ventas disponibles?

        if (ObtenerEstadisticasUsuario(_F_usuario_cache('id_usuario'),_EST_CANT_PUB_NOTEMP) >= _F_usuario_cache('nPubMax'))
        {
            echo Mensaje("Ud. ha alcanzado su límite de publicaciones ("._F_usuario_cache('nPubMax')."), si desea agregar más publicaciones puede eliminar una publicación actual o adquirir una cuenta premium.");
        }
        else
        {
            // No ha escogido categoría, le mostramos las opciones.

            echo "<h1>Realizar una nueva publicación</h1>" .
            "Por favor seleccione la categoría mayor a la que pertenece su publicación. Esto es necesario para ofrecerle únicamente las opciones relevantes a su publicación, en el siguiente paso podrá definir la sub-categoría." .
            '<br />' .
            '<ul>' .
            '<li>' . ui_href("vender_ir_inmueble","vender?op=inmueble", "Inmueble") . "<br /><span class='explicacion'>venta o alquiler de casas, apartamentos y demás bienes inmuebles</span></li>" .
            '<li>' . ui_href("vender_ir_inmueble","vender?op=automotor", "Automotor") . "<br /><span class='explicacion'>venta o alquiler de automores (carros, vehículos, motocicletas y toda máquina propulsada por un motor)</span></li>" .
            '<li>' . ui_href("vender_ir_servicio","vender?op=servicio", "Servicio") . " <br /><span class='explicacion'>servicios profesiales (electricista, programador, diseñador, albañil, constructor, arquitecto, etc.)</span></li>" .
            '<li>' . ui_href("vender_ir_articulo","vender?op=articulo", "<strong>Artículo</strong>") . "<br /><span class='explicacion'>encontrarás sub categorías para todo lo que las anteriores 3 categorías mayores no cubren</span></li>" .
            '</ul>';
        }

        // Mostrar las ventas publicadas:

        echo '<h1>Mis publicaciones</h1>';
        $c = "SELECT id_publicacion, titulo, id_categoria, DATE(fecha_fin) AS fecha_fin, IF((SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria) is NULL,'<sin categoría>',(SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria)) AS categoria, (SELECT rubro FROM ventas_categorias AS b WHERE b.id_categoria=a.id_categoria) AS rubro FROM ventas_publicaciones AS a WHERE id_usuario='"._F_usuario_cache('id_usuario')."' AND tipo='"._A_aceptado."' AND fecha_fin >='".mysql_datetime()."'";
        $r = db_consultar($c);
        if ( mysql_num_rows($r) > 0 )
        {
            echo "<h2>Ventas  publicadas actualmente</h2>";
            echo '<table class="ancha">';
            echo '<tr><th>Título</th><th>Expira</th><th>Categoría</th><th>Tipo</th><th>Acciones</th></tr>';
            while ($f = mysql_fetch_array($r))
            {
                echo "<tr><td><a href=\"publicacion_".$f['id_publicacion']."_".SEO($f['titulo'])."\">" . htmlentities($f['titulo'],ENT_QUOTES,'UTF-8') . "</a></td><td>".$f['fecha_fin']."</td><td>" . htmlentities($f['categoria'],ENT_QUOTES,'UTF-8') . "</td><td>" . htmlentities($f['rubro'],ENT_QUOTES,'UTF-8') . "</td><td><a href=\"publicacion_".$f['id_publicacion']."_".SEO($f['titulo'])."?se=editar\">editar</a>|<a href=\"publicacion_".$f['id_publicacion']."_".SEO($f['titulo'])."?se=cerrar\">¡vendido!</a></td></tr>";
            }
            echo "</table>";
        }

        // Mostrar las ventas incompletas:

        $c = "SELECT id_publicacion, IF(titulo='','<sin título>', titulo) AS titulo2, id_categoria, IF((SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria) is NULL,'<sin categoría>',(SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria)) AS categoria, (SELECT rubro FROM ventas_categorias AS b WHERE b.id_categoria=a.id_categoria) AS rubro FROM ventas_publicaciones AS a WHERE id_usuario='"._F_usuario_cache('id_usuario')."' AND tipo='"._A_temporal."'";
        $r = db_consultar($c);
        if ( mysql_num_rows($r) > 0 )
        {
            echo "<h2>Publicaciones que no ha enviado a aprobación</h2>";
            echo '<table class="ancha">';
            echo '<tr><th>Título</th><th>Categoría</th><th>Tipo</th><th>Acciones</th></tr>';
            while ($f = mysql_fetch_array($r))
            {
                echo "<tr><td>". htmlentities($f['titulo2'],ENT_QUOTES,'UTF-8') . "</td><td>" . htmlentities($f['categoria'],ENT_QUOTES,'UTF-8') . "</td><td>" . htmlentities($f['rubro'],ENT_QUOTES,'UTF-8') . "</td><td>".ui_href("","vender?ticket=".$f['id_publicacion'],"continuar") ."|" . ui_href("","vender?ticket=".$f['id_publicacion']."&eliminar=proceder","eliminar")."</td></tr>";
            }
            echo "</table>";
        }

        // Mostrar las ventas esperando aprobación

        $c = "SELECT id_publicacion, (SELECT rubro FROM ventas_categorias AS b WHERE b.id_categoria=a.id_categoria) AS rubro, IF(titulo='','<sin título>', titulo) AS titulo2, id_categoria, IF((SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria) is NULL,'<sin categoría>',(SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria)) AS categoria FROM ventas_publicaciones AS a WHERE id_usuario='"._F_usuario_cache('id_usuario')."' AND tipo='"._A_esp_activacion."'";
        $r = db_consultar($c);
        if ( mysql_num_rows($r) > 0 )
        {
            echo "<h2>Publicaciones enviadas en espera de aprobación</h2>";
            echo '<table class="ancha">';
            echo '<tr><th>Título</th><th>Categoría</th><th>Tipo</th></tr>';
            while ($f = mysql_fetch_array($r))
            {
                echo "<tr><td>" . htmlentities($f['titulo2'],ENT_QUOTES,'UTF-8') . "</td><td>" . htmlentities($f['categoria'],ENT_QUOTES,'UTF-8') . "</td><td>" . htmlentities($f['rubro'],ENT_QUOTES,'UTF-8') . "</td></tr>";
            }
            echo "</table>";
        }

        // Mostrar las ventas caducadas

        $c = "SELECT id_publicacion, (SELECT rubro FROM ventas_categorias AS b WHERE b.id_categoria=a.id_categoria) AS rubro, IF(titulo='','<sin título>', titulo) AS titulo2, id_categoria, IF((SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria) is NULL,'<sin categoría>',(SELECT nombre FROM ventas_categorias AS b WHERE b.id_categoria = a.id_categoria)) AS categoria FROM ventas_publicaciones AS a WHERE id_usuario='"._F_usuario_cache('id_usuario')."' AND tipo = '"._A_aceptado."' AND fecha_fin < CURDATE()";
        $r = db_consultar($c);
        if ( mysql_num_rows($r) > 0 )
        {
            echo "<hr />";
            echo "<h2>Publicaciones que han caducado</h2>";
            echo '<table class="ancha">';
            echo '<tr><th>Título</th><th>Categoría</th><th>Tipo</th><th>Acciones</th></tr>';
            while ($f = mysql_fetch_array($r))
            {
                echo "<tr><td><a href=\"publicacion_".$f['id_publicacion']."_".SEO($f['titulo2'])."\">" . htmlentities($f['titulo2'],ENT_QUOTES,'UTF-8') . "</a></td><td>" . htmlentities($f['categoria'],ENT_QUOTES,'UTF-8') . "</td><td>" . htmlentities($f['rubro'],ENT_QUOTES,'UTF-8') . "</td><td><a href=\"publicacion_".$f['id_publicacion']."_".SEO($f['titulo'])."?se=republicar\">republicar</a></td></tr>";
            }
            echo "</table>";
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

    $arrHEAD[] = '<script type="text/javascript" src="JS/tiny_mce/tiny_mce_gzip.js"></script>
    <script type="text/javascript">
    tinyMCE_GZ.init({
            plugins : \'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,\'+
            \'searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras\',
            themes : \'advanced\',
            languages : \'es\',
            disk_cache : true,
            debug : false
    });
    </script>
    <script type="text/javascript">
    tinyMCE.init({
        language : "es",
	elements : "descripcion",
        theme : "advanced",
        mode : "exact",
        plugins : "safari,style,layer,table,advhr,advimage,advlink,media,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template",
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect,cleanup,code",
        theme_advanced_buttons2 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,advhr,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        button_tile_map : true,
    });</script>
    ';

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

    if(isset($_POST['vender_eliminar']))
    {
        header("location: ./");
        if (!empty($_GET['ticket']))
        {
            DestruirTicket($_GET['ticket']);
        }
        echo "Cancelando venta...";
        return;
    }

    /* Advertencia:
      Hay que recargar los datos luego de la edición para evitar problemas de que los cambios anteriores queden "en cache"
    */

    $Publicacion = ObtenerDatos($ticket);

    if ($flag_modo_escritura)
    {
        DescargarArchivos("vender_deshabilitar",$ticket,$Publicacion['id_usuario']);
        CargarArchivos("vender_imagenes",$ticket,$Publicacion['id_usuario']);
        CargarDatos($ticket,$Publicacion['id_usuario']);
        // Refrescamos los datos de la publicación
        $Publicacion = ObtenerDatos($ticket);
    }

    $Vendedor=_F_usuario_datos($Publicacion['id_usuario']);
    $imagenes = ObtenerImagenesArr($ticket,"");

    if ($flag_op_y_saltar)
    {
        header("location: ./vender?ticket=$ticket");
    }

    if ($flag_enviar)
    {
        // Al fin lo terminó de editar y lo esta enviando... Aleluya!
        //-
        // Si es Admin entonces aprobar automaticamente, si no pues mandarlo a esperar activacion
        $c = "UPDATE ventas_publicaciones SET tipo='"._A_esp_activacion."' WHERE id_publicacion=$ticket LIMIT 1";
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
                $vendedor = _F_usuario_datos($Publicacion['id_usuario']);
                email($vendedor['email'],PROY_NOMBRE." - Publicación \"".$Publicacion['titulo']."\" ha sido recibida","Su publicación ha sido recibida en nuestro sistema y se encuentra en proceso de activación.<br />\nEsta activación puede demorar entre <strong>1 minuto y 1 hora</strong> dependiendo de la disponibilidad de los administradores en línea.<br />Esta corta espera es necesaria para realizar una revisión de las publiciaciones y así poder ofrecer el mejor contenido a nuestros visitantes.<br />\n!Gracias por preferir ".PROY_NOMBRE." para realizar sus publicaciones!");
                email_x_nivel(_N_administrador,'Nueva publicacion: '.$f['titulo'],'<a href="http://www.yomachete.com/publicacion_'.$f['id_publicacion']."_".SEO($f['titulo']).'">Ver</a>');
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

    if($flag_modo_previsualizacion || $flag_publicar)
    {
        echo mensaje("esta es una previsualización. Sus información no será ingresada al sistema hasta que presione el botón \"Enviar\"",_M_INFO);
        echo "<hr style=\"margin-top:50px\" />";
        echo "Ud. ha escogido la siguiente categoría: <b>" . get_path(db_codex(@$Publicacion['id_categoria']),false)."</b><br/><br/>";
        echo "Su publicación (una vez aprobada) se verá de la siguiente forma en la lista de publicaciones de la categoria seleccionada:<br /><br />";
        echo VISTA_ListaPubs("id_publicacion=$ticket","","previsualizacion","Woops!, ¡problemas intentando cargar la previsualización!");
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
        echo ui_input("vender_editar","Editar","submit");
        echo ui_input("vender_enviar","Enviar","submit");
        echo "</center>";
        return;
    }
    echo "<ol class=\"ventas\">";
    echo "<li>Selección de categoría</li>";
    echo "<span class='explicacion'>Ubique su árticulo en la categoría que consideres apropiada.</span><br />";
    echo "Mi árticulo corresponde a la siguiente categoría<br />".ui_combobox("id_categoria",join("",ver_hijos("",@$Publicacion["rubro"])), @$Publicacion["id_categoria"])."<br />";

    echo "<li>Título de la publicación</li>";
    echo "<span class='explicacion'>Utilice un título corto, descriptivo y llamativo, máximo 50 carácteres. No se admite código HTML.</span><br />";
    echo "Titulo " . ui_input("titulo",@$Publicacion["titulo"],"","","width:50ex","MAXLENGTH='50'")."<br />";
    echo "<li>Tags (palabras clave) para publicación</li>";
    echo "<span class='explicacion'>Utilice palabras cortas separadas por coma (5 como máximo, no utilice espacios).</span><br />";
    echo "Tags " . ui_input("tags",@$Publicacion["tags"],"","","width:50ex","MAXLENGTH='50'")."<br />";
    echo "<li>Descripción corta de la publicación</li>";
    echo "<span class='explicacion'>Describa brevemente su venta (o prestación de servicio), solo los detalles más importantes, máximo 300 carácteres. No se admite código HTML.</span><br />";
    echo "Descripción corta<br />" . ui_textarea("descripcion_corta",@$Publicacion["descripcion_corta"],"","width:50em;height:4em;") . "<br />";
    echo "<li>Descripción del artículo</li>";
    echo "<span class='explicacion'>Describa en detalle tu artículo, incluye todos los datos relevantes que desees, máximo 5000 carácteres.<br />¡Puedes usar <a href=\"http://www.bbcode-to-html.com/\">bbcode-to-html</a> para convertir tus mensajes de SVCommunity.org a HTML!, si lo haces de esta forma utiliza el botón \"html\" para ingresar el texto resultante.</span><br />";
    echo "Descripción larga<br />" . ui_textarea("descripcion",@$Publicacion["descripcion"],"","width:50em;height:20em;")."<br />";

    if (in_array(@$Publicacion["rubro"], array("articulo","automotor")))
    {
        echo "<li>Características del artículo</li>";
        echo "<span class='explicacion'>Seleccione solo las opciones que ayuden a describir de forma precisa tu producto.</span><br />";
        echo db_ui_checkboxes("venta[]", "ventas_flags", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"venta"),"","tipo='venta'");
    }
    echo "<li>Precio</li>";
    echo "<span class='explicacion'>Précio en dólares de Estados Unidos de America ($ USA).</span><br />";
    echo "Précio " . ui_input("precio",@$Publicacion["precio"],"","","width:30ex","MAXLENGTH='30'")."<br />";
    echo "<li>Formas de pago admitidas</li>";
    echo "<span class='explicacion'>Selecione solo las opciones de pago que admitirá.</span><br />";
    echo db_ui_checkboxes("pago[]", "ventas_flags", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"pago"),"","tipo='pago'");
    if (in_array(@$Publicacion["rubro"], array("articulo")))
    {
        echo "<li>Formas de entrega admitidas</li>";
        echo "<span class='explicacion'>Selecione solo las opciones de tipos de entrega que admitirá.</span><br />";
        echo db_ui_checkboxes("entrega[]", "ventas_flags", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"entrega"),"","tipo='entrega'");
    }
    switch(@$Publicacion["rubro"])
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
            echo "<div style='display:inline-block'><a href=\"./imagen_".$archivo."\" title=\"IMAGEN CARGADA\" target=\"_blank\"><img src=\"./imagen_".$archivo."m\" /></a><br />".ui_input("vender_deshabilitar[]",$archivo,"checkbox")."&nbsp;Eliminar</div>";
        }
        echo "<div style=\"clear:both\"></div>";
    }


    $NoMaxImg = (in_array(@$Publicacion["rubro"], array("servicio"))) ? 1 : $Vendedor['nImgMax'];
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
    echo ui_input("vender_previsualizar", "Guardar y Previsualizar", "submit");
    echo ui_input("vender_publicar", "Publicar", "submit");
    echo ui_input("vender_eliminar", "Eliminar", "submit");
    echo "</center>";
    echo "</form>";
}
?>
