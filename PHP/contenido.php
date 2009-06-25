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

    echo "<h1>".@$Buffer['titulo']."</h1>";
    echo "<hr />";
    echo "<b>Ubicación:</b> " . join(" > ", get_path(@$Buffer['id_categoria']));
    echo "<br />";
    echo "<b>Vendedor:</b> " . $Vendedor['nombre'] ." [<a id=\"ver_mas_vendedor\" >más..</a>]";
    echo "<div id=\"detalle_vendedor\">";
    echo "<ul>";
    echo "<li>Registrado desde: " .  @$Vendedor['registro'] . "</li>";
    echo "<li>Ultima actividad: " . fechatiempo_desde_mysql_datetime(@$Vendedor['ultimo_acceso']) . "</li>";
    echo "</ul>";
    echo "</div>";
    if (isset($imagenes) && is_array($imagenes))
    {
        echo "<hr /><center>";
        foreach($imagenes as $archivo)
        {
            echo "<div style='display:inline-block'><a href=\"./imagen_".$archivo."\" title=\"IMAGEN CARGADA\" target=\"_blank\" rel=\"lightbox\"><img src=\"./imagen_".$archivo."m\" /></a><br /></div>";
        }
        echo "<div style=\"clear:both\"></div>";
        echo "</center><hr />";
    }
    echo "<div class=\"publicacion_descripcion\">";
    echo @$Buffer['descripcion'];
    echo "</div>";
    echo JS_onload('$("#detalle_vendedor").hide();$("#ver_mas_vendedor").click(function() {$("#detalle_vendedor").toggle("fast");});$("a[rel=\'lightbox\']").lightBox();');
}
?>
