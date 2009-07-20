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
    $Publicacion = ObtenerDatos($ticket);
    if (!$Publicacion)
    {
        echo Mensaje("disculpe, la publicación solicitada no existe.", _M_INFO);
        return;
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

    $Vendedor = _F_usuario_datos(@$Publicacion['id_usuario']);
    $imagenes = ObtenerImagenesArr($ticket,"");
    // Grabamos cualquier consulta enviada
    if ( _autenticado() && isset($_POST['consulta']) && isset($_POST['enviar_consulta']) && _F_usuario_cache('id_usuario') != @$Vendedor['id_usuario'] )
    {
        // Consulta publica
        $datos['id_usuario'] = _F_usuario_cache('id_usuario');
        $datos['id_articulo'] = $ticket;
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
            $c = "UPDATE ventas_mensajes_publicaciones SET respuesta='$respuesta', fecha_respuesta='".mysql_datetime()."' WHERE id='$id' AND id_articulo='$ticket' LIMIT 1";
            $r = db_consultar($c);
            // Enviamos un mensaje al comprador
        }
    }

    echo "<h1>".@$Publicacion['titulo']."</h1>";
    echo "<hr /><div id=\"pub_descripcion_corta\">".@$Publicacion['descripcion_corta']."</div>";
    echo "<hr />";

    // Categoria en la que se encuentra ubicado el producto
    echo "<b>Categoría de la publicación:</b> " . join(" > ", get_path(@$Publicacion['id_categoria']));
    echo "<br />";

    // Formas de entrega para el producto (no disponible para ciertos rubros: inmuebles.
    echo "<b>Formas de entrega:</b>" ." <span  class=\"auto_mostrar\">[<a id=\"ver_mas_entrega\">ver...</a>]</span>";
    echo "<div id=\"detalle_entrega\" class=\"auto_ocultar\">";
    echo db_ui_checkboxes("flags_entrega[]", "ventas_flags_entrega", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"flags_entrega"),'disabled="disabled"');
    //echo db_ui_checkboxes("flags_ventas[]", "ventas_flags_ventas", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"flags_ventas"),'disabled="disabled"');
    echo "</div>";
    echo "<br />";

    // Caracteristicas adicionales:
    echo "<b>Características adicionales:</b>" ." <span  class=\"auto_mostrar\">[<a id=\"ver_mas_adicional\">ver...</a>]</span>";
    echo "<div id=\"detalle_adicional\" class=\"auto_ocultar\">";
    echo db_ui_checkboxes("flags_ventas[]", "ventas_flags_ventas", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"flags_ventas"),'disabled="disabled"');
    echo "</div>";
    echo "<br />";

    // Precio y formas de pago aceptadas
    echo "<b>Precio:</b> $" . number_format(@$Publicacion['precio'],2,".",",") ." <span  class=\"auto_mostrar\">[<a id=\"ver_mas_precio\">ver formas de pago...</a>]</span>";
    echo "<div id=\"detalle_precio\" class=\"auto_ocultar\">";
    echo db_ui_checkboxes("flags_pago[]", "ventas_flags_pago", "id_flag", "nombrep", "descripcion",ObtenerFlags($ticket,"flags_pago"),'disabled="disabled"');
    echo "</div>";
    echo "<br />";

    // Datos sobre el vendedor
    echo "<b>Vendedor:</b> " . ui_href("","perfil?id=".$Vendedor['id_usuario'],$Vendedor['usuario']) . " / <b>e-mail de contacto:</b> ". '<img src="imagen_c_'.$Vendedor['email'].'" />'." / enviar un <b>". ui_href("","mp?id=".$Vendedor['id_usuario'],"Mensaje Privado")."</b> "."<span  class=\"auto_mostrar\">[<a id=\"ver_mas_vendedor\">ver datos sobre el vendedor...</a>]</span>";
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
    echo nl2br(strip_html_tags(@$Publicacion['descripcion']));
    echo "</div></center>";

    if ($op != "previsualizacion")
    {
    echo '<hr /><div class="cuadro_importante">';
    $c = "SELECT id, id_usuario, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario=a.id_usuario) AS usuario, consulta, respuesta, respuesta, tipo, fecha_consulta, fecha_respuesta FROM ventas_mensajes_publicaciones AS a WHERE id_articulo=$ticket";
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
                $f['respuesta'] = htmlentities('<el vendedor aún no dado respuesta a esta consulta>',ENT_QUOTES,"utf-8");
            }
            // Si la consulta ha sido contestada
            else
            {
                $f['respuesta'] = htmlentities($f['respuesta'],ENT_QUOTES,"utf-8");
            }
            echo '<tr class="respuesta'.$Privada.'"><td class="col1">'.@$Vendedor['usuario'].'</td><td class="col2">'.$f['respuesta']."</td><td>".fechatiempo_h_desde_mysql_datetime($f['fecha_respuesta'])."</td></tr>";
        }
        if ($flag_activar_enviar_respuestas) echo '<tr><td id="envio" colspan="3">'.ui_input("cmdEnviarRespuesta","Enviar todas las respuestas","submit").'</td></tr>';
        echo '</table>';
        echo '</form>';
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
        echo VISTA_ArticuloEnBarra("id_articulo <> '".$Publicacion['id_categoria']."' AND id_articulo <> '".$Publicacion['id_articulo']."' AND id_usuario = '".$Vendedor['id_usuario']."' AND tipo='"._A_aceptado."'");
        echo '</div>';
    }

    // Mostrar "Productos similares". Escoger de la misma categoria los
    // productos que esten en el rango de +/-25% del precio actual

    $PrecioMin = (double) (@$Publicacion['precio']) * 0.50; // -50%
    $PrecioMax = (double) (@$Publicacion['precio']) * 1.50; // +50%

        echo '<hr />';
        echo '<div class="cuadro_importante centrado">';
        echo '<h1>Publicaciones similares</h1>';
        echo VISTA_ArticuloEnBarra("id_categoria='".$Publicacion['id_categoria']."' AND precio >= '$PrecioMin' AND precio <= '$PrecioMax' AND id_articulo <> '".$Publicacion['id_articulo']."' AND tipo='"._A_aceptado."'");
        echo '</div>';

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

}
function CONTENIDO_TIENDA()
{
$Vendedor = _F_usuario_datos($_GET['tienda']);
echo "Viendo tienda de: <b>".$Vendedor['usuario']."</b><hr /><br />";
$nivel = (!empty($_GET['categoria'])) ? "padre='".$_GET['categoria']."' AND " : "";
$c = "SELECT id_categoria, nombre FROM ventas_categorias WHERE $nivel id_categoria IN (SELECT padre FROM ventas_categorias WHERE id_categoria IN (SELECT id_categoria FROM ventas_articulos WHERE id_usuario='".$Vendedor['id_usuario']."')) ORDER BY nombre";
$resultado = db_consultar($c);
$n_campos = mysql_num_rows($resultado);
if ($n_campos > 1) {
$data = '';
$data = ' <div id="secc_categorias">';
$data .= (!empty($_GET['categoria'])) ? '<div class="item_cat item_cat_todos"><a href="./tienda_'.$Vendedor['id_usuario'].'.html">Ver todas las categorías</a><div style="clear:both"></div></div>' : "<h1>Categorías</h1>";
$data .= "<div id=\"contenedor_categorias\">";
for ($i = 0; $i < $n_campos; $i++) {
    $r = mysql_fetch_row($resultado);
    $data .= "<div class=\"item_cat\">".('<a title="'.$r[1].'" href="tienda_'.$Vendedor['id_usuario'].'_dpt-'.$r[0].'-'.SEO($r[1]).'">'. $r[1].'</a>')."</div> "; //Importante!, no quitar el espacio despues del </div>!!!
}
$data .= '</div></div>';
echo $data;
}
echo VISTA_ArticuloEnLista("id_usuario = '".$Vendedor['id_usuario']."' AND tipo='"._A_aceptado."'","LIMIT 10","tienda");
}
?>
