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
        echo Mensaje("disculpe, la publicación solicitada no existe.", _M_INFO);
        return;
    }
    $ticket = db_codex($_GET['publicacion']);
    $Buffer = ObtenerDatos($ticket);
    $imagenes = ObtenerImagenesArr($ticket,"");

    echo "<b>Ubicación:</b> " . join(" > ", get_path(@$Buffer['id_categoria']))."<hr />";
    echo "<h1>".@$Buffer['titulo']."</h1>";
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
    echo JS_onload('$("a[rel=\'lightbox\']").lightBox();');
}
?>
