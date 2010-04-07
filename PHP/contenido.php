<?php
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
    $data .= VISTA_ListaPubs($WHERE,"ORDER BY promocionado DESC, fecha_ini DESC","indice");
    return $data;
}

// Columna Izq.
function GENERAR_CATEGORIAS()
{
    $data = '';
    $data .= '<h1>Compartenos</h1>';
    $data .= '<center><span id="bookmarks"></span></center>';
    $data .= '<h1>Recuerdanos</h1>';
    $data .= '<center>
    <a id="bookmark"><img title="Favoritos" src="IMG/favoritos.jpg" /></a>
    <a title="RSS" target="_blank" href="http://www.yomachete.com/rss.xml"><img title="RSS" src="IMG/rss_logo.jpg" /></a>
    <a title="Twitter" target="_blank" href="http://www.twitter.com/YoMachete"><img title="Twitter" src="IMG/twitter_logo.jpg" /></a>
    <a title="Facebook" target="_blank" href="http://www.facebook.com/YoMachete"><img title="Facebook" src="IMG/facebook_logo.jpg" /></a>
    </center>';
    $data .= (isset($_GET['categoria'])) ? '<hr /><div class="item_cat item_cat_todos"><a href="./">Mostrar categorías</a><div style="clear:both"></div></div>' : "<h1>Categorías</h1>";
    $nivel = (isset($_GET['categoria'])) ? $_GET['categoria'] : 0;
    $c = "SELECT id_categoria, nombre FROM ventas_categorias WHERE padre=$nivel ORDER BY nombre";
    $resultado = db_consultar($c);
    $n_campos = mysql_num_rows($resultado);
    $data .= "<div id=\"contenedor_categorias\">";
    for ($i = 0; $i < $n_campos; $i++) {
        $r = mysql_fetch_row($resultado);
        $data .= "<div class=\"item_cat\">".('<a title="'.$r[1].'" href="clasificados-en-el-salvador-'.$r[0].'-'.SEO($r[1]).'">'. $r[1].'</a>')."</div> "; //Importante!, no quitar el espacio despues del </div>!!!
    }
    $data .= "</div>";
    return $data;
}
function GENERAR_TAG_CLOUD()
{
    $c = "SELECT (SELECT tag FROM ventas_tag AS b WHERE b.id = a.id_tag) as tag, count(id_tag) AS hits FROM (SELECT * FROM ventas_tag_uso AS b WHERE b.id_publicacion IN (SELECT c.id_publicacion FROM ventas_publicaciones AS c WHERE tipo IN ("._A_aceptado . ","._A_promocionado.") AND fecha_fin >= CURDATE())) AS a GROUP BY id_tag ORDER BY hits DESC LIMIT 40";
    $r = db_consultar($c);
    return '<h1>Nube de etiquetas</h1><div id="nube_etiquetas">'.tag_cloud($r).'</div>';
}

function CONTENIDO_PUBLICACION($op="")
{
    global $HEAD_titulo, $HEAD_descripcion;

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

    echo ui_publicacion_barra_acciones('contenido', $publicacion);

    // Si ya fue vendido

    if ($publicacion['tipo'] == _A_vendido)
    {
        echo '<h1>Publicación concluida</h1>';
        echo '<p>Lo sentimos, el vendedor nos ha informado la venta ya fue realizada.</p>';
        echo '<p>¡Pero no se vaya!, puesto que puede revisar la categoría de esta publicación para encontrar uno similar! - <a href="clasificados-en-el-salvador-'.$publicacion['id_categoria'].'-.html">Revisar la categoría de este producto</a></p>';
        echo '<p>O bien puede aprovechar para realizar una publicación similar. - <a href="vender?op='.$publicacion['id_categoria'].'">Realizar publicación en esta categoría</a></p>';
        return;
    }
    // Si no esta aprobado solo lo puede ver un Administrador

    if ($op != "previsualizacion" && $publicacion['tipo'] != _A_aceptado && _F_usuario_cache('nivel') != _N_administrador)
    {
        echo Mensaje("esta publicacion NO se encuentra disponible",_M_ERROR);
        return;
    }
    // Ya venció el tiempo de publicación?.
    if (@$_SESSION['opciones']['deshabilitar_tiempo_de_caducidad'] == 1 && $op != "previsualizacion" && strtotime($publicacion['fecha_fin']) < strtotime(date('d-m-Y',time())))
    {
        echo Mensaje("disculpe, la publicación solicitada ha caducado.", _M_INFO);
        echo "Esta publicacion caducó el ".$publicacion['fecha_fin']."<br />";
        if (_F_usuario_cache('id_usuario') == $publicacion['id_usuario'])
        {
            echo 'Para asegurarnos que su venta sigue vigente y con datos actuales, Ud. debera revisar su publicacion y publicarla nuevamente.'. ui_href("","vender?ticket=$ticket","Presione en este enlace si desea extender el tiempo de su publicación");
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
        case 'cerrar':
            CONTENIDO_CERRAR($publicacion);
            return;
        break;
        case 'editar':
            CONTENIDO_EDITAR($publicacion);
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
        email($Vendedor['email'],PROY_NOMBRE . " - nueva consulta en la publicación: " . $publicacion['titulo'], "Le han realizado una consulta en la siguiente publicacion: <a href=\"http://yomachete.com/clasificados-en-el-salvador-vendo-".$publicacion['id_publicacion']."_".SEO($publicacion['titulo'])."\">".$publicacion['titulo'].'</a>');
    }

    // Grabamos cualquier respuesta enviada
    if ( _autenticado() && isset($_POST['cmdEnviarRespuesta']) && is_array($_POST['txtEnviarRespuesta']) && _F_usuario_cache('id_usuario') == @$Vendedor['id_usuario'] )
    {
        foreach ($_POST['txtEnviarRespuesta'] as $id => $respuesta)
        {
            $respuesta = substr(strip_tags(db_codex($respuesta)),0,300);
            $id = db_codex($id);
            $c = "UPDATE ventas_mensajes_publicaciones SET respuesta='$respuesta', fecha_respuesta=NOW() WHERE id='$id' AND id_publicacion='$ticket' LIMIT 1";
            $r = db_consultar($c);
            // Notificamos al dueño del mensaje
            if (db_afectados() > 0)
            {
                $c = 'SELECT email FROM ventas_usuarios WHERE id_usuario = (SELECT id_usuario FROM ventas_mensajes_publicaciones WHERE id='.$id.' AND id_publicacion='.$ticket.' LIMIT 1)';
                $r = db_consultar($c);
                $f = mysql_fetch_assoc($r);
                if(!empty($f['email']))
                    email($f['email'],PROY_NOMBRE . " - respuesta a su consulta en la publicación: " . $publicacion['titulo'], "Hay una respuesta a su consulta en la siguiente publicacion: <a href=\"http://yomachete.com/clasificados-en-el-salvador-vendo-".$publicacion['id_publicacion']."_".SEO($publicacion['titulo'])."\">".$publicacion['titulo'].'</a>');
            }
        }
    }

    echo "<h1>".@$publicacion['titulo']."</h1>";
    echo "<hr /><div id=\"pub_descripcion_corta\">".@$publicacion['descripcion_corta']."</div>";
    echo "<hr />";

    // Categoria en la que se encuentra ubicado el producto
    echo "<b>Categoría de la publicación:</b> " . get_path_format(@$publicacion);
    echo "<br />";

    // Fechas de publicación
    echo "<b>Inicio de la publicación:</b> " . fecha_desde_mysql_datetime(@$publicacion['fecha_ini']);
    if (@$_SESSION['opciones']['deshabilitar_tiempo_de_caducidad'] == 0)
        echo "<br /><b>Fin de la publicación:</b> " . fecha_desde_mysql_datetime(@$publicacion['fecha_fin']);
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
    echo "<b>Vendedor:</b> " . ui_href("","perfil?id=".$Vendedor['id_usuario'],$Vendedor['usuario']) . ( _F_usuario_cache('id_usuario') != $Vendedor['id_usuario'] ? " / enviar un <b>". ui_href("",PROY_URL."perfil?op=mp&amp;id=".$Vendedor['id_usuario'],"Mensaje Privado")."</b> " : " ")."<span  class=\"auto_mostrar\">[<a id=\"ver_mas_vendedor\">ver datos sobre el vendedor...</a>]</span>";
    echo "<div id=\"detalle_vendedor\" class=\"auto_ocultar\">";
    echo "<ul>";
    echo "<li>Registrado desde: " .  fechatiempo_desde_mysql_datetime(@$Vendedor['registro']) . "</li>";
    echo "<li>Ultima actividad: " . fechatiempo_desde_mysql_datetime(@$Vendedor['ultimo_acceso']) . "</li>";
    $Vendedor['cantidad_publicaciones'] = ObtenerEstadisticasUsuario(@$Vendedor['id_usuario'],_EST_CANT_PUB_ACEPT);
    echo "<li>Cantidad de publicaciones: " . $Vendedor['cantidad_publicaciones']  ."</li>";
    echo "</ul>";
    echo "</div>";

    if (isset($imagenes) && is_array($imagenes))
    {
        echo "<hr /><h1>Fotografías y/o ilustraciones</h1><center>";
        foreach($imagenes as $archivo)
        {
            echo "<div style='display:inline-block;margin:0 10px;'><a class=\"fancybox\" href=\"./imagen_".$archivo.".jpg\" target=\"_blank\" rel=\"contenido\"><img src=\"./imagen_".$archivo."m.jpg\" /></a><br /></div>";
        }
        echo "<div style=\"clear:both\"></div>";
        echo "</center>";
    }
    echo "<hr /><h1>Descripción</h1><center><div class=\"clasificados-en-el-salvador-vendo-descripcion\">";
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
        echo '<form method="POST" action="clasificados-en-el-salvador-vendo-'.$ticket.'">';
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
            if (_F_usuario_cache('nivel') == _N_administrador) $ControlesAdmin = " [".ui_href("","./clasificados-en-el-salvador-vendo-$ticket?op=eliminar&id=".$f['id'],"X")."]". ($f['tipo'] == _MeP_Publico ? "[".ui_href("","clasificados-en-el-salvador-vendo-$ticket?op=privado&id=".$f['id'],"p")."]" : "[".ui_href("","clasificados-en-el-salvador-vendo-$ticket?op=publico&id=".$f['id'],"P")."]");
            echo '<tr class="pregunta'.$Privada.'"><td class="col1">'.$f['usuario'].'</td><td class="col2">'.htmlentities($f['consulta'],ENT_QUOTES,"utf-8")."</td><td class=\"col3\">".fechatiempo_h_desde_mysql_datetime($f['fecha_consulta']).$ControlesAdmin."</td></tr>";
            // Si es el dueño de la venta y no ha respondido la consulta le damos la opción de hacerlo.
            if ( !$f['respuesta'] && _F_usuario_cache('id_usuario') == @$Vendedor['id_usuario'] )
            {
                $f['respuesta'] = ui_input("txtEnviarRespuesta[".$f['id']."]","","text","txtRespuesta",'MAXLENGTH="300"');
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

    // Enviar consultas
    if (!S_iniciado())
    {
        echo "<hr />Necesitas iniciar sesión para poder <b>realizar consultas</b>.<br />";
        require_once("PHP/inicio.php");
        CONTENIDO_INICIAR_SESION();
    }
    elseif (_autenticado() && _F_usuario_cache('id_usuario') != @$Vendedor['id_usuario'])
    {
        echo '<div id="area_consulta"><form method="POST" action="'.$_SERVER['REQUEST_URI'].'"><p>Realizar consulta al vendedor:</p>' . ui_input("consulta","","text","","width:100%;",'MAXLENGTH="300"') . "<br />" . "<table><tr><td>". ui_input("tipo_consulta","publica","checkbox"). "&nbsp; marque esta opción si desea hacer pública esta consulta (<a title=\"Usela si Ud. cree que las demas personas deben leer esta pregunta y su respectiva respuesta\">?</a>).</td><td id=\"trbtn\">".ui_input("enviar_consulta","Enviar","submit")."</td></tr></table>" . '</form></div>';
    }
    echo '</div>';

    // Mostrar "Otros productos de este vendedor". Si tiene mas de un producto claro :)

    if ( $Vendedor['cantidad_publicaciones'] > 1 )
    {
        echo '<hr />';
        echo '<div class="cuadro_importante centrado">';
        echo '<h1>Otras publicaciones de este vendedor</h1>';
        echo VISTA_ArticuloEnBarra("a.id_publicacion <> '".$publicacion['id_publicacion']."' AND a.id_usuario = '".$Vendedor['id_usuario']."' AND a.tipo='"._A_aceptado."' AND a.fecha_fin >= CURDATE()");
        echo '</div>';
    }

    // Mostrar "Productos similares". Escoger de la misma categoria los
    // productos que esten en el rango de +/-25% del precio actual

    $PrecioMin = (double) (@$publicacion['precio']) * 0.50; // -50%
    $PrecioMax = (double) (@$publicacion['precio']) * 1.50; // +50%

        echo '<hr />';
        echo '<div class="cuadro_importante centrado">';
        echo '<h1>Publicaciones similares</h1>';
        echo VISTA_ArticuloEnBarra("a.id_categoria IN (SELECT id_categoria FROM ventas_categorias WHERE padre = (SELECT padre from ventas_categorias WHERE id_categoria='".$publicacion['id_categoria']."' LIMIT 1)) AND precio >= '$PrecioMin' AND precio <= '$PrecioMax' AND id_publicacion <> '".$publicacion['id_publicacion']."' AND a.tipo='"._A_aceptado."' AND a.fecha_fin >= CURDATE()");
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

    $HEAD_titulo = PROY_NOMBRE . ' - ' . @$publicacion['titulo'];
    $HEAD_descripcion = @$publicacion['descripcion_corta'];
}

function CONTENIDO_TIENDA()
{
$data = '';
$c = sprintf("SELECT * FROM ventas_tienda WHERE tiendaURL='%s' LIMIT 1",db_codex($_GET['tienda']));
$r = db_consultar($c);
if (mysql_num_rows($r) != 1)
{
    echo Mensaje("La tienda solicitada [".$_GET['tienda']."] no existe");
    return;
}

$Tienda = db_fila_a_array($r);
$Vendedor = _F_usuario_datos($Tienda['id_usuario']);
echo "Viendo tienda de: <b>".$Vendedor['usuario']."</b><hr /><br />";
$nivel = (!empty($_GET['categoria'])) ? "padre='".db_codex($_GET['categoria'])."' AND " : "";
$c = "SELECT id_categoria, nombre FROM ventas_categorias WHERE $nivel id_categoria IN (SELECT padre FROM ventas_categorias WHERE id_categoria IN (SELECT id_categoria FROM ventas_publicaciones WHERE id_usuario='".$Vendedor['id_usuario']."')) ORDER BY nombre";
$resultado = db_consultar($c);
$n_campos = mysql_num_rows($resultado);
$data = ' <div id="secc_categorias">';
$data .= (!empty($_GET['categoria'])) ? '<div class="item_cat item_cat_todos"><a href="./+'.$Tienda['tiendaURL'].'">Ver todas las categorías</a><div style="clear:both"></div></div>' : "<h1>Categorías</h1>";
$data .= "<div id=\"contenedor_categorias\">";
for ($i = 0; $i < $n_campos; $i++) {
    $r = mysql_fetch_row($resultado);
    $data .= "<div class=\"item_cat\">".('<a title="'.$r[1].'" href="+'.$Tienda['tiendaURL'].'_dpt-'.$r[0].'-'.SEO($r[1]).'">'. $r[1].'</a>')."</div> "; //Importante!, no quitar el espacio despues del </div>!!!
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
        $WHERE = "z.id_categoria='$categoria'";
    }
    else
    {
        $WHERE = "(SELECT padre FROM ventas_categorias AS b where b.id_categoria=z.id_categoria)='$categoria' AND tipo IN ("._A_aceptado . ","._A_promocionado.")";
    }
}
else
{
    $data .= "<h1>Artículos mas recientes</h1>";
    // Mostrar todos los articulos en la categoría
    $WHERE = "tipo IN ("._A_aceptado . ","._A_promocionado.")";
}
$WHERE .= " AND id_usuario = ".$Tienda['id_usuario'];
$data .= VISTA_ListaPubs($WHERE,"","tienda","",$Tienda['tiendaURL']);
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
            if (email($_POST['correo'],"Publicación: " . $publicacion['titulo'], "Estimado(a) ".$_POST['nd'].",<br />\nQuiero que revises la siguiente publicación: " . curPageURL(true) . " en " . PROY_NOMBRE . "<br />\n".(!empty($_POST['comentario']) ? "<br />\nEl usuario también ha includio el siguiente comentario para Ud.<br />\n".$_POST['comentario']."<br />\n<br />\n" : "")."Gracias,<br />\n".$_POST['nr']))
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
    echo '<h1>Envío de publicación</h1>';
    echo '<p>Enviar la publicación "<strong>'.$publicacion['titulo'].'</strong>" a un amigo</p>';
    echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="POST">';
    echo '<table  class="semi-ancha limpio centrado">';
    echo ui_tr(ui_td('Nombre remitente','fDer'). ui_td(ui_input("nr",(_F_form_cache('nr') ? _F_form_cache('comentario') : _F_usuario_cache('usuario')),"text","","width:100%"),"fInput"));
    echo ui_tr(ui_td('Nombre destinatario','fDer'). ui_td(ui_input("nd",_F_form_cache('nd'),"text","","width:100%"),"fInput"));
    echo ui_tr(ui_td('Correo electrónico destinatario','fDer'). ui_td(ui_input("correo",_F_form_cache('correo'),"text","","width:100%"),"fInput"));
    echo ui_tr(ui_td('Comentario','fDer'). ui_td(ui_textarea("comentario",_F_form_cache('comentario'),"","width:100%"),"fInput"));
    echo '<tr><td colspan="2" class="fDer">'.ui_input("enviar_pub2mail","Enviar","submit").'</td></tr>';
    echo '</table>';
    echo '</form>';
    echo '<h1>Opciones</h1>';
    echo ui_href("",curPageURL(true),"Cancelar y retornar a la publicación");
}
function CONTENIDO_PUBREP($publicacion)
{
    // Comprobamos que ya haya ingresado al sistema
    if (!S_iniciado())
    {
        echo "Necesitas iniciar sesión para poder <b>reportar publicaciones</b>.<br />Esto es con el fin de evitar el mal uso de esta herramienta de moderacion.<br />";
        require_once("PHP/inicio.php");
        CONTENIDO_INICIAR_SESION();
        return;
    }

    if (isset($_POST['enviar']))
    {
        if (empty($_POST['razon']))
        {
            echo Mensaje("no se ingresó razón de reporte",_M_ERROR);
        }
        else
        {
        email_x_nivel(_N_administrador, "Reporte de publicación", sprintf("La siguiente publicación ha sido reportada:<br />\n\"%s\"<br />\nURL: %s<br />\nComentario del reportador:<br />\n%s",$publicacion['titulo'],curPageURL(true), @$_POST['razon']));
        echo Mensaje("Su reporte ha sido enviado, ¡gracias!");
        echo ui_href("",curPageURL(true),"Retornar a la publicación");
        return;
        }
    }
    echo '<h1>Reporte de publicaciones</h1>';
    echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="POST">';
    echo 'Razón del reporte <td class="fInput"><input name="razon" type="text" /><input name="enviar" type="submit" value="Enviar reporte" />';
    echo '</form>';
    echo '<h1>Opciones</h1>';
    echo ui_href("",curPageURL(true),"Cancelar y retornar a la publicación");
}

/* Marca una venta como 'vendida' y la cierra al publico */
function CONTENIDO_CERRAR($publicacion)
{
    if(isset($_POST['cerrar']) && _autenticado() && _F_usuario_cache('id_usuario') == $publicacion['id_usuario'])
    {
       db_consultar(sprintf('UPDATE ventas_publicaciones SET tipo=%s WHERE id_publicacion=%s',_A_vendido,$publicacion['id_publicacion']));
       if (db_afectados() > 0)
       {
        echo '<h1>Publicación concluida</h1>';
        echo sprintf('Su publicación ha sido cerrada y marcada como "vendida". Gracias por usar %s!<br />',PROY_NOMBRE);
        echo '<h1>Opciones</h1>';
        echo ui_href('',PROY_URL,'Retornar a la página principal');
        echo ui_href("","vender","Retornar a su lista de publicaciones");
       }
       return;
    }
    echo '<h1>Cerrar publicación</h1>';
    echo '<p>Presione "Cerrar" para marcar su publicación como vendida y cerrarla.<br />Tenga en cuenta que no podrá re-abrirla luego de esto, asi que procure no cerrarla antes de concluir la venta.</p>';
    echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="POST">';
    echo '<input name="cerrar" type="submit" value="Cerrar" />';
    echo '</form>';
    echo '<h1>Opciones</h1>';
    echo ui_href("","vender","Cancelar y retornar a su lista de publicaciones");
}

/* Regresa una publicación al tintero, tendrá que ser reaprobada */
function CONTENIDO_EDITAR($publicacion)
{
    echo '<h1>Editar publicación</h1>';
    echo '<p>Presione "Editar" para sacar de línea su publicación y <b>poder ser editada</b>.<br />Tenga en cuenta que esta publicación <b>retorna a su lista de publicaciones en la sección "<i><u>Publicaciones que no ha enviado a aprobación</i></u>" y luego de su edición tendrá que ser enviada nuevamente a aprobación</b>; estas son medidas para su seguridad con el fin de evitar abusos en el sistema, en el futuro, clientes honestos como Ud. se les permitirá editar las publicaciones sin tantos pasos intermedios. Gracias por su comprensión.</p>';
    echo '<form action="'.PROY_URL.'vender" method="GET">';
    echo '<input name="ticket" value="'.$publicacion['id_publicacion'].'" type="hidden"/>';
    echo '<input name="editar" type="submit" value="Editar" />';
    echo '</form>';
    echo '<h1>Opciones</h1>';
    echo ui_href("","vender","Cancelar y retornar a su lista de publicaciones");
}
?>
