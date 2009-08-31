<?php

function CONTENIDO_VIP()
{
    echo "<h1>Inscripción para  <span style='color:F00'>Vendedor Distinguido</span></h1>";
    echo "Con tu cuenta de <span style='color:F00'>Vendedor Distinguido</span> disfrutas de los siguientes beneficios:";
    echo "<ul>";
    echo "<li>Ninguno por el momento, etc.</li>";
    echo "</ul>";
}

function CONTENIDO_PUBLICACION($op="")
{   
    if (!isset($_GET['publicacion']))
    {
        echo Mensaje("PUBLICACION: ERROR INTERNO", _M_ERROR);
        return;
    }

    $ticket = db_codex($_GET['publicacion']);
    $publicacion = ObtenerDatos($ticket);
    if (!$publicacion)
    {
        echo Mensaje("disculpe, la publicación solicitada no existe.", _M_INFO);
        return;
    }
    
    // Si no esta aprobado solo lo puede ver un Administrador
    
    if ($op != "previsualizacion" && $publicacion['tipo'] != _A_aceptado && _F_usuario_cache('nivel') != _N_administrador)
    {
        echo Mensaje("esta publicacion NO se encuentra disponible",_M_ERROR);
        return;
    }

    // Ya venció el tiempo de publicación?.
    if ($op != "previsualizacion" && strtotime($publicacion['fecha_fin']) < strtotime('+1 day'))
    {
        echo Mensaje("disculpe, el tiempo de publicación para la publicación solicitada ha caducado.", _M_INFO);
        echo "Esta publicacion caduco el ".$publicacion['fecha_fin']."<br />";
        if (_F_usuario_cache('id_usuario') == $publicacion['id_usuario'])
        {
            echo ui_href("","servicios?op=atp&pub=$ticket","¿Desea ampliar el tiempo de su publicación?");
        }
        return;
    }

    // Operaciones especiales con la publicación que no necesite permisos de administración
    if (isset($_GET['se']))
    {
        switch($_GET['se'])
        {
        case 'pub2pdf':
        break;
        case 'pub2mail':
            CONTENIDO_PUB2MAIL($publicacion);
            return;
        break;
        case 'pubrep':
            CONTENIDO_PUBREP($publicacion);
            return;
        break;
        }
    }

    // Preprocesamos cualquier codigo de operación
    if (isset($_GET['op']) && isset($_GET['id']) && _F_usuario_cache('nivel') == _N_administrador)
    {
        $id = db_codex($_GET['id']);
        switch ($_GET['op'])
        {
            case "eliminar":
                $c = "DELETE FROM ventas_mensajes_publicaciones WHERE id='$id' LIMIT 1";
            break;
            case "privado":
                $c = "UPDATE ventas_mensajes_publicaciones SET tipo='"._MeP_Privado."' WHERE id='$id' LIMIT 1";
            break;
            case "publico":
                $c = "UPDATE ventas_mensajes_publicaciones SET tipo='"._MeP_Publico."' WHERE id='$id' LIMIT 1";
            break;
        }
        $r = db_consultar($c);
        if ( db_afectados() == 1 )
        {
            echo Mensaje("Operación exitosa.", _M_INFO);
        }
        else
        {
            echo Mensaje("Operación erronea.", _M_ERROR);
        }
    }

    $Vendedor = _F_usuario_datos(@$publicacion['id_usuario']);
    $imagenes = ObtenerImagenesArr($ticket,"");
    // Grabamos cualquier consulta enviada
    if ( _autenticado() && isset($_POST['consulta']) && isset($_POST['enviar_consulta']) && _F_usuario_cache('id_usuario') != @$Vendedor['id_usuario'] )
    {
        // Consulta publica
        $datos['id_usuario'] = _F_usuario_cache('id_usuario');
        $datos['id_publicacion'] = $ticket;
        $datos['consulta'] = substr(strip_tags(db_codex($_POST['consulta'])),0,300);
        $datos['tipo'] = isset($_POST['tipo_consulta']) ? _MeP_Publico : _MeP_Privado;
        $datos['fecha_consulta'] = mysql_datetime();
        db_agregar_datos("ventas_mensajes_publicaciones",$datos);
        unset($datos);
        // Enviamos un mensaje al vendedor
    }

    // Grabamos cualquier respuesta enviada
    if ( _autenticado() && isset($_POST['cmdEnviarRespuesta']) && is_array($_POST['txtEnviarRespuesta']) && _F_usuario_cache('id_usuario') == @$Vendedor['id_usuario'] )
    {
        foreach ($_POST['txtEnviarRespuesta'] as $id => $respuesta)
        {
            $respuesta = substr(strip_tags(db_codex($respuesta)),0,300);
            $id = db_codex($id);
            $c = "UPDATE ventas_mensajes_publicaciones SET respuesta='$respuesta', fecha_respuesta='".mysql_datetime()."' WHERE id='$id' AND id_publicacion='$ticket' LIMIT 1";
            $r = db_consultar($c);
            // Enviamos un mensaje al comprador
        }
    }

    echo "<h1>".@$publicacion['titulo']."</h1>";
    echo "<hr /><div id=\"pub_descripcion_corta\">".@$publicacion['descripcion_corta']."</div>";
    echo "<hr />";

    // Categoria en la que se encuentra ubicado el producto
    echo "<b>Categoría de la publicación:</b> " . join(" > ", get_path(@$publicacion['id_categoria']));
    echo "<br />";

    // Categoria en la que se encuentra ubicado el producto
    echo "<b>Fin de la publicación:</b> " . fecha_desde_mysql_datetime(@$publicacion['fecha_fin']);
    echo "<br />";

    // Formas de entrega para el producto (no disponible para ciertos rubros: inmuebles.
    echo "<b>Formas de entrega:</b>" ." <span  class=\"auto_mostrar\">[<a id=\"ver_mas_entrega\">ver...</a>]</span>";
    echo "<div id=\"detalle_entrega\" class=\"auto_ocultar\">";
    echo db_ui_checkboxes("", "ventas_flags", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"entrega"),'disabled="disabled"',"tipo='entrega'");
    echo "</div>";
    echo "<br />";

    // Caracteristicas adicionales:
    echo "<b>Características adicionales:</b>" ." <span  class=\"auto_mostrar\">[<a id=\"ver_mas_adicional\">ver...</a>]</span>";
    echo "<div id=\"detalle_adicional\" class=\"auto_ocultar\">";
    echo db_ui_checkboxes("", "ventas_flags", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"venta"),'disabled="disabled"',"tipo='venta'");
    echo "</div>";
    echo "<br />";

    // Precio y formas de pago aceptadas
    echo "<b>Precio:</b> $" . number_format(@$publicacion['precio'],2,".",",") ." <span  class=\"auto_mostrar\">[<a id=\"ver_mas_precio\">ver formas de pago...</a>]</span>";
    echo "<div id=\"detalle_precio\" class=\"auto_ocultar\">";
    echo db_ui_checkboxes("", "ventas_flags", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"pago"),'disabled="disabled"',"tipo='pago'");
    echo "</div>";
    echo "<br />";

    // Datos sobre el vendedor
    echo "<b>Vendedor:</b> " . ui_href("","perfil?id=".$Vendedor['id_usuario'],$Vendedor['usuario']) . ( _F_usuario_cache('id_usuario') != $Vendedor['id_usuario'] ? " / enviar un <b>". ui_href("","mp?id=".$Vendedor['id_usuario'],"Mensaje Privado")."</b> " : " ")."<span  class=\"auto_mostrar\">[<a id=\"ver_mas_vendedor\">ver datos sobre el vendedor...</a>]</span>";
    echo "<div id=\"detalle_vendedor\" class=\"auto_ocultar\">";
    echo "<ul>";
    echo "<li>Registrado desde: " .  fechatiempo_desde_mysql_datetime(@$Vendedor['registro']) . "</li>";
    echo "<li>Ultima actividad: " . fechatiempo_desde_mysql_datetime(@$Vendedor['ultimo_acceso']) . "</li>";
    $Vendedor['cantidad_publicaciones'] = ObtenerEstadisticasUsuario(@$Vendedor['id_usuario'],_EST_CANT_PUB_ACEPT);
    echo "<li>Cantidad de publicaciones: " . $Vendedor['cantidad_publicaciones']  . " (" . ui_href("","tienda_".(empty($Vendedor['tienda']) ? $Vendedor['id_usuario'] : $Vendedor['tienda']), "ver tienda") . ")</li>";
    echo "</ul>";
    echo "</div>";

    if (isset($imagenes) && is_array($imagenes))
    {
        echo "<hr /><h1>Fotografías y/o ilustraciones</h1><center>";
        foreach($imagenes as $archivo)
        {
            echo "<div style='display:inline-block;margin:0 10px;'><a href=\"./imagen_".$archivo."\" title=\"IMAGEN CARGADA\" target=\"_blank\" rel=\"lightbox\"><img src=\"./imagen_".$archivo."m\" /></a><br /></div>";
        }
        echo "<div style=\"clear:both\"></div>";
        echo "</center>";
    }
    echo "<hr /><h1>Descripción</h1><center><div class=\"publicacion_descripcion\">";
    $descripcion = @$publicacion['descripcion'];
    if( !is_array( $descripcion ) ) {
            echo $descripcion;
    } else {
            print_r($descripcion);
    }
    echo "</div></center>";

    if ($op != "previsualizacion")
    {
    echo '<hr /><div class="cuadro_importante">';
    $c = "SELECT id, id_usuario, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario=a.id_usuario) AS usuario, consulta, respuesta, respuesta, tipo, fecha_consulta, fecha_respuesta FROM ventas_mensajes_publicaciones AS a WHERE id_publicacion=$ticket";
    $r = db_consultar($c);
    if ($r && mysql_num_rows($r) > 0)
    {
        echo "<h1>Consultas</h1>";
        echo '<form method="POST" action="publicacion_'.$ticket.'">';
        echo '<table id="tabla_consultas" class="ancha">';
        $flag_activar_enviar_respuestas = false;
        while ($f = mysql_fetch_array($r))
        {
            // Si es consulta privada solo se muestra si corresponde al usuario actual o al vendedor
            if ($f['tipo'] == _MeP_Privado && _F_usuario_cache('id_usuario') != $f['id_usuario'] && _F_usuario_cache('id_usuario') != @$Vendedor['id_usuario'])
            {
                continue;
            }
            // Determinamos si es pregunta privada o publica
            $Privada = $f['tipo'] == _MeP_Privado ? "_privada" : "";
            $ControlesAdmin = "";
            if (_F_usuario_cache('nivel') == _N_administrador) $ControlesAdmin = " [".ui_href("","./publicacion_$ticket?op=eliminar&id=".$f['id'],"X")."]". ($f['tipo'] == _MeP_Publico ? "[".ui_href("","publicacion_$ticket?op=privado&id=".$f['id'],"p")."]" : "[".ui_href("","publicacion_$ticket?op=publico&id=".$f['id'],"P")."]");
            echo '<tr class="pregunta'.$Privada.'"><td class="col1">'.$f['usuario'].'</td><td class="col2">'.htmlentities($f['consulta'],ENT_QUOTES,"utf-8")."</td><td class=\"col3\">".fechatiempo_h_desde_mysql_datetime($f['fecha_consulta']).$ControlesAdmin."</td></tr>";
            // Si es el dueño de la venta y no ha respondido la consulta le damos la opción de hacerlo.
            if ( !$f['respuesta'] && _F_usuario_cache('id_usuario') == @$Vendedor['id_usuario'] )
            {
                $f['respuesta'] = ui_input("txtEnviarRespuesta[".$f['id']."]","","input","txtRespuesta",'MAXLENGTH="300"');
                $flag_activar_enviar_respuestas = true;
            }
            // Si no es el dueño de la venta y la consulta no ha sido contestada
            elseif ( !$f['respuesta'] )
            {
                $f['respuesta'] = htmlentities('<el vendedor aún no ha dado respuesta a esta consulta>',ENT_QUOTES,"utf-8");
            }
            // Si la consulta ha sido contestada
            else
            {
                $f['respuesta'] = htmlentities($f['respuesta'],ENT_QUOTES,"utf-8");
            }
            echo '<tr class="respuesta'.$Privada.'"><td class="col1">'.@$Vendedor['usuario'].'</td><td class="col2">'.$f['respuesta'].'</td><td class="col3">'.fechatiempo_h_desde_mysql_datetime($f['fecha_respuesta'])."</td></tr>";
        }
        if ($flag_activar_enviar_respuestas) echo '<tr><td id="envio" colspan="3">'.ui_input("cmdEnviarRespuesta","Enviar todas las respuestas","submit").'</td></tr>';
        echo '</table>';
        echo '</form>';
    }
    else
    {
        echo Mensaje("No hay consultas realizadas por el momento", _M_INFO);
    }

    if (!S_iniciado())
    {
        echo "<hr />Necesitas iniciar sesión para poder <b>realizar consultas</b>.<br />";
        require_once("PHP/inicio.php");
        CONTENIDO_INICIAR_SESION();
    }
    elseif (_autenticado() && _F_usuario_cache('id_usuario') != @$Vendedor['id_usuario'])
    {
        echo '<div id="area_consulta"><form method="POST" action="'.$_SERVER['REQUEST_URI'].'"><p>Realizar consulta al vendedor:</p>' . ui_input("consulta","","input","","width:100%;",'MAXLENGTH="300"') . "<br />" . "<table><tr><td>". ui_input("tipo_consulta","publica","checkbox"). "&nbsp; marque esta opción si desea hacer pública esta consulta (<a title=\"Usela si Ud. cree que las demas personas deben leer esta pregunta y su respectiva respuesta\">?</a>).</td><td id=\"trbtn\">".ui_input("enviar_consulta","Enviar","submit")."</td></tr></table>" . '</form></div>';
    }
    echo '</div>';

    // Mostrar "Otros productos de este vendedor". Si tiene mas de un producto claro :)

    if ( $Vendedor['cantidad_publicaciones'] > 1 )
    {
        echo '<hr />';
        echo '<div class="cuadro_importante centrado">';
        echo '<h1>Otras publicaciones de este vendedor</h1>';
        echo VISTA_ArticuloEnBarra("id_publicacion <> '".$publicacion['id_categoria']."' AND id_publicacion <> '".$publicacion['id_publicacion']."' AND id_usuario = '".$Vendedor['id_usuario']."' AND tipo='"._A_aceptado."' AND fecha_fin >= '" . mysql_datetime() . "'");
        echo '</div>';
    }

    // Mostrar "Productos similares". Escoger de la misma categoria los
    // productos que esten en el rango de +/-25% del precio actual

    $PrecioMin = (double) (@$publicacion['precio']) * 0.50; // -50%
    $PrecioMax = (double) (@$publicacion['precio']) * 1.50; // +50%

        echo '<hr />';
        echo '<div class="cuadro_importante centrado">';
        echo '<h1>Publicaciones similares</h1>';
        echo VISTA_ArticuloEnBarra("id_categoria='".$publicacion['id_categoria']."' AND precio >= '$PrecioMin' AND precio <= '$PrecioMax' AND id_publicacion <> '".$publicacion['id_publicacion']."' AND tipo='"._A_aceptado."' AND fecha_fin >= '" . mysql_datetime() . "'");
        echo '</div>';

   
    // Mostrar opciones adicionales
    
    echo '
    <a href="'.$_SERVER['REQUEST_URI'].'?se=pub2pdf"><img src="IMG/pub_extop_ipdf.gif" title="Obtener una copia de esta venta en formato PDF" alt="[descargar venta en PDF]" /></a>
    <a href="'.$_SERVER['REQUEST_URI'].'?se=pub2mail"><img src="IMG/pub_extop_mail.gif" title="Enviar esta publicación a un amigo" alt="[enviar por email]" /></a>
    <a href="'.$_SERVER['REQUEST_URI'].'?se=pubrep"><img src="IMG/pub_extop_reportar.gif" title="Notificar a los administradores de una publicación fraudulenta" alt="[reportar publicación]" /></a>
    ';
    }
    
    echo JS_onload('
    $(".auto_ocultar").hide();
    $(".auto_mostrar").show();
    $("#ver_mas_precio").click(function() {$("#detalle_precio").toggle("fast");});
    $("#ver_mas_entrega").click(function() {$("#detalle_entrega").toggle("fast");});
    $("#ver_mas_adicional").click(function() {$("#detalle_adicional").toggle("fast");});
    $("#ver_mas_vendedor").click(function() {$("#detalle_vendedor").toggle("fast");});
    ');
}

function CONTENIDO_MP()
{

// Comprobamos que ya haya ingresado al sistema
if (!S_iniciado())
{
    echo "Necesitas iniciar sesión para poder <b>enviar Mensajes Privados</b>.<br />";
    require_once("PHP/inicio.php");
    CONTENIDO_INICIAR_SESION();
    return;
}

// Si hay un id entonces quiere enviar un MP a ese ID
if (!empty($_GET['id']))
{
    $id_usuario = db_codex($_GET['id']);

    //No se estará enviando el mensaje a el mismo verdad? XD

    if ($id_usuario == _F_usuario_cache('id_usuario'))
    {
        echo Mensaje("auto-enviarse mensajes privados no es permitido",_M_ERROR);
        return;
    }

    //Existe el usuario al cual quiere enviar el mensaje?
    if (_F_usuario_existe($id_usuario,'id_usuario'))
    {
        $usuario_destino = _F_usuario_datos($id_usuario);
        echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="POST">';
        echo 'Este mensaje será enviado al usuario <b>'.$usuario_destino['usuario'].'</b><br />';
        echo '<table>';
        echo ui_tr(ui_td('Asunto: '). ui_td(ui_input("asunto","","","","width:100%")));
        echo ui_tr(ui_td('Mensaje: '). ui_td(ui_textarea("mensaje","","","width:100%")));
        echo '</table>';
        echo ui_input("enviar_mp","Enviar","Button").'<br />';
        echo '</form>';
    }
    else
    {
        echo Mensaje("ha especificado un usuario de destino no existente en el sitema",_M_ERROR);
        return;
    }
}
}

function CONTENIDO_TIENDA()
{
$data = '';
$Vendedor = _F_usuario_datos($_GET['tienda']);
echo "Viendo tienda de: <b>".$Vendedor['usuario']."</b><hr /><br />";
$nivel = (!empty($_GET['categoria'])) ? "padre='".db_codex($_GET['categoria'])."' AND " : "";
$c = "SELECT id_categoria, nombre FROM ventas_categorias WHERE $nivel id_categoria IN (SELECT padre FROM ventas_categorias WHERE id_categoria IN (SELECT id_categoria FROM ventas_publicaciones WHERE id_usuario='".$Vendedor['id_usuario']."')) ORDER BY nombre";
$resultado = db_consultar($c);
$n_campos = mysql_num_rows($resultado);
$data = ' <div id="secc_categorias">';
$data .= (!empty($_GET['categoria'])) ? '<div class="item_cat item_cat_todos"><a href="./tienda_'.$Vendedor['id_usuario'].'.html">Ver todas las categorías</a><div style="clear:both"></div></div>' : "<h1>Categorías</h1>";
$data .= "<div id=\"contenedor_categorias\">";
for ($i = 0; $i < $n_campos; $i++) {
    $r = mysql_fetch_row($resultado);
    $data .= "<div class=\"item_cat\">".('<a title="'.$r[1].'" href="tienda_'.$Vendedor['id_usuario'].'_dpt-'.$r[0].'-'.SEO($r[1]).'">'. $r[1].'</a>')."</div> "; //Importante!, no quitar el espacio despues del </div>!!!
}
$data .= '</div></div>';

$categoria = !empty($_GET['categoria']) ? db_codex($_GET['categoria']) : 0;
if ($categoria)
{
    $c = "SELECT * FROM ventas_categorias WHERE id_categoria='$categoria'";
    $resultado = db_consultar($c);


    if (db_resultado($resultado, 'padre') > 0)
    {
        $data .= "<hr />";
        $data .= "Deseo publicar una <a href=\"./vender?op=$categoria\">venta</a> en esta categoría<br />";
        $data .= "<hr />";
        $WHERE = "id_categoria='$categoria' AND tipo IN ("._A_aceptado . ","._A_promocionado.")";
    }
    else
    {
        $WHERE = "(SELECT padre FROM ventas_categorias AS b where b.id_categoria=a.id_categoria)='$categoria' AND tipo IN ("._A_aceptado . ","._A_promocionado.")";
    }
}
else
{
    $data .= "<h1>Artículos mas recientes</h1>";
    // Mostrar todos los articulos en la categoría
    $WHERE = "tipo IN ("._A_aceptado . ","._A_promocionado.")";
}
$WHERE .= " AND fecha_fin >= '" . mysql_datetime() . "'";
$data .= VISTA_ListaPubs($WHERE,"ORDER by promocionado DESC,fecha_fin DESC LIMIT 10","tienda");
echo $data;
}

function CONTENIDO_PUB2MAIL($publicacion)
{
    if (!empty($_POST['nr']) && !empty($_POST['nd']) && !empty($_POST['correo']) && !empty($_POST['enviar_pub2mail']))
    {
        // Nos conformamos con que exista el destinatario:
        if (validEmail($_POST['correo']))
        {
            $MensajeMail="";
            if (email($_POST['correo'],"Publicación: " . $publicacion['titulo'], "Estimado(a) ".$_POST['nd'].",<br />\nQuiero que revises la siguiente publicación: " . curPageURL(true) . " en " . PROY_NOMBRE . "<br />\nGracias,<br />\n".$_POST['nr']))
            {
                echo Mensaje("Su mensaje ha sido enviado");
                echo ui_href("",curPageURL(true),"Retornar a la publicación");
                
            }
            else
            {
                echo Mensaje("Su mensaje NO ha sido enviado debido a fallas técnicas, por favor intente en otro momento");
            }
        return;
        }
        else
        {
        echo Mensaje("Parece que el correo electronico esta mal escrito",_M_ERROR);
        }
    }
    echo '<p>Enviar la publicación "<strong>'.$publicacion['titulo'].'</strong>" a un amigo</p>';
    echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="POST">';
    echo '<table>';
    echo ui_tr(ui_td('Nombre remitente: '). ui_td(ui_input("nr",(_F_form_cache('nr') ? _F_form_cache('comentario') : _F_usuario_cache('usuario')),"","","width:100%")));
    echo ui_tr(ui_td('Nombre destinatario: '). ui_td(ui_input("nd",_F_form_cache('nd'),"","","width:100%")));
    echo ui_tr(ui_td('Correo Electrónico: '). ui_td(ui_input("correo",_F_form_cache('correo'),"","","width:100%")));
    echo ui_tr(ui_td('Comentario: '). ui_td(ui_textarea("comentario",_F_form_cache('comentario'),"","width:100%")));
    echo '</table>';
    echo ui_input("enviar_pub2mail","Enviar","submit").'<br />';
    echo '</form>';
}
function CONTENIDO_PUBREP($publicacion)
{
    email_x_nivel(_N_administrador, "Reporte de publicación", "La siguiente publicación ha sido reportada:\n\"%s\"\nURL: %s",$publicacion['titulo'],curPageURL(true));
}
?>
