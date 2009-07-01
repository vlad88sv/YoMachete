<?php

function CONTENIDO_VIP()
{
    echo "<h1>Inscripción para  <span style='color:F00'>Vendedor Distinguido</span></h1>";
    echo "Con tu cuenta de <span style='color:F00'>Vendedor Distinguido</span> disfrutas de los siguientes beneficios:";
    echo "<ul>";
    echo "<li>Ninguno por el momento, etc.</li>";
    echo "</ul>";
}

function CONTENIDO_PUBLICACION()
{
    if (!isset($_GET['publicacion']))
    {
        echo Mensaje("PUBLICACION: ERROR INTERNO", _M_ERROR);
        return;
    }
    $ticket = db_codex($_GET['publicacion']);
    $Buffer = ObtenerDatos($ticket);
    if (!$Buffer)
    {
        echo Mensaje("disculpe, la publicación solicitada no existe.", _M_INFO);
        return;
    }
    $Vendedor = _F_usuario_datos(@$Buffer['id_usuario']);
    $imagenes = ObtenerImagenesArr($ticket,"");
    // Grabamos cualquier consulta enviada
    if ( _autenticado() && isset($_POST['consulta']) && isset($_POST['enviar_consulta']) && _F_usuario_cache('id_usuario') != @$Vendedor['id_usuario'] )
    {
        // Consulta publica
        $datos['id_usuario'] = _F_usuario_cache('id_usuario');
        $datos['id_articulo'] = $ticket;
        $datos['consulta'] = db_codex($_POST['consulta']);
        $datos['tipo'] = isset($_POST['tipo_consulta']) ? _MeP_Publico : _MeP_Privado;
        db_agregar_datos("ventas_mensajes_publicaciones",$datos);
        unset($datos);
    }

    if ( _autenticado() && isset($_POST['cmdEnviarRespuesta']) && is_array($_POST['txtEnviarRespuesta']) && _F_usuario_cache('id_usuario') == @$Vendedor['id_usuario'] )
    {
        foreach ($_POST['txtEnviarRespuesta'] as $id => $respuesta)
        {
            $respuesta = db_codex($respuesta);
            $id = db_codex($id);
            $c = "UPDATE ventas_mensajes_publicaciones SET respuesta='$respuesta' WHERE id='$id' AND id_articulo='$ticket' LIMIT 1";
            $r = db_consultar($c);
        }
    }


    echo "<h1>".@$Buffer['titulo']."</h1>";
    echo "<hr />";
    echo "<b>Ubicación:</b> " . join(" > ", get_path(@$Buffer['id_categoria']));
    echo "<br />";
    echo "<b>Vendedor:</b> " . $Vendedor['nombre'] . ", <b>contacto:</b> ". '<img src="imagen_c_'.$Vendedor['email'].'" />' ." [<a id=\"ver_mas_vendedor\" >más..</a>]";
    echo "<div id=\"detalle_vendedor\">";
    echo "<ul>";
    echo "<li>Registrado desde: " .  @$Vendedor['registro'] . "</li>";
    echo "<li>Ultima actividad: " . fechatiempo_desde_mysql_datetime(@$Vendedor['ultimo_acceso']) . "</li>";
    echo "</ul>";
    echo "</div>";
    if (isset($imagenes) && is_array($imagenes))
    {
        echo "<hr /><h1>Fotografías y/o ilustraciones</h1><center>";
        foreach($imagenes as $archivo)
        {
            echo "<div style='display:inline-block'><a href=\"./imagen_".$archivo."\" title=\"IMAGEN CARGADA\" target=\"_blank\" rel=\"lightbox\"><img src=\"./imagen_".$archivo."m\" /></a><br /></div>";
        }
        echo "<div style=\"clear:both\"></div>";
        echo "</center>";
    }
    echo "<hr /><h1>Descripción</h1><center><div class=\"publicacion_descripcion\">";
    echo @$Buffer['descripcion'];
    echo "</div></center>";

    $c = "SELECT id, id_usuario, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario=a.id_usuario) AS usuario, consulta, respuesta, respuesta, tipo FROM ventas_mensajes_publicaciones AS a WHERE id_articulo=$ticket";
    $r = db_consultar($c);
    if (mysql_num_rows($r) > 0)
    {
        echo "<hr /><h1>Consultas realizadas</h1>";
        echo '<form method="POST" action="'.$_SERVER['REQUEST_URI'].'">';
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
            echo '<tr class="pregunta'.$Privada.'"><td class="col1">'.$f['usuario'].'</td><td class="col2">'.htmlentities($f['consulta'],ENT_QUOTES,"utf-8")."</td></tr>";
            // Si es el dueño de la venta y no ha respondido la consulta le damos la opción de hacerlo.
            if ( !$f['respuesta'] && _F_usuario_cache('id_usuario') == @$Vendedor['id_usuario'] )
            {
                $f['respuesta'] = ui_input("txtEnviarRespuesta[".$f['id']."]","","input","txtRespuesta");
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
            echo '<tr class="respuesta'.$Privada.'"><td class="col1">'.@$Vendedor['usuario'].'</td><td class="col2">'.$f['respuesta']."</td></tr>";
        }
        if ($flag_activar_enviar_respuestas) echo '<tr><td id="envio" colspan="2">'.ui_input("cmdEnviarRespuesta","Enviar todas las respuestas","submit").'</td></tr>';
        echo '</table>';
        echo '</form>';
    }
    if (_autenticado() && _F_usuario_cache('id_usuario') != @$Vendedor['id_usuario'])
    {
        echo "<hr /><h1>Contactar al vendedor</h1>";
        echo '<div id="area_consulta"><form method="POST" action="'.$_SERVER['REQUEST_URI'].'">' . ui_textarea("consulta","","","width:100%;") . "<br />" . "<table><tr><td>". ui_input("tipo_consulta","publica","checkbox"). "&nbsp;<- marquelo si desea hacer pública esta consulta.</td><td id=\"trbtn\">".ui_input("enviar_consulta","Enviar","submit")."</td></tr></table>" . '</form></div>';
    }
    else
    {
        if (!S_iniciado())
        {
            echo '<div class="cuadro_importante">';
            echo "Necesitas iniciar sesión para poder <b>realizar consultas</b>.<br />";
            require_once("PHP/inicio.php");
            CONTENIDO_INICIAR_SESION();
            echo '</div>';
        }
    }
    echo JS_onload('$("#detalle_vendedor").hide();$("#ver_mas_vendedor").click(function() {$("#detalle_vendedor").toggle("fast");});$("a[rel=\'lightbox\']").lightBox();');
}
?>
