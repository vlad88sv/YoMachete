<?php
function CONTENIDO_PERFIL()
{

    $usuario = empty($_GET['id']) && _autenticado() ? $_SESSION['cache_datos_usuario'] : _F_usuario_datos($_GET['id']);
    if(!is_array($usuario))
    {
        echo Mensaje("Lo sentimos, al parecer este usuario ya no forma parte de este sitio",_M_ERROR);
        return;
    }

    echo "<p><b>Nombre de usuario:</b> " .  @$usuario['usuario']."</p>";
    echo "<p><b>e-mail de contacto:</b> ". '<img src="imagen_c_'.$usuario['id_usuario'].'" />'." (enviar un ". ui_href("","mp?id=".$usuario['id_usuario'],"Mensaje Privado").")</p>";
    echo "<p><b>Registrado desde:</b> " .  fechatiempo_desde_mysql_datetime(@$usuario['registro'])."</p>";
    echo "<p><b>Ultima actividad:</b> " . fechatiempo_desde_mysql_datetime(@$usuario['ultimo_acceso'])."</p>";
    $usuario['cantidad_publicaciones'] = ObtenerEstadisticasUsuario(@$usuario['id_usuario'],_EST_CANT_PUB_ACEPT) . " (" . ui_href("","tienda_".(empty($usuario['tienda']) ? $usuario['id_usuario'] : $usuario['tienda']).".html", "ver tienda") . ")";
    echo "<p><b>Cantidad de publicaciones:</b> " . $usuario['cantidad_publicaciones']."</p>";

    // Si el que esta viendo su propio perfil, mostrale sus alertas y notificaciones

    if(_F_usuario_cache('id_usuario')==$usuario['id_usuario'])
    {
        $c = "SELECT id_usuario_rmt, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario = a.id_usuario_rmt LIMIT 1) AS nombre_rmt, mensaje, tipo, contexto, fecha FROM ventas_mensajes AS a WHERE id IN (SELECT id_msj FROM ventas_mensajes_dst WHERE id_usuario_dst='"._F_usuario_cache('id_usuario')."')";
        $r = db_consultar($c);
        if (mysql_num_rows($r) > 0)
        {
            echo '<table class="ancha resultados">';
            echo ui_tr(ui_th('Nombre Remitente').ui_th('Mensaje'));
            while ($f = mysql_fetch_array($r))
            {
            echo ui_tr(ui_td($f['nombre_rmt']).ui_td($f['mensaje']));
            }
            echo '</table>';
        }
    }

}
?>
