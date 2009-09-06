<?php
function CONTENIDO_PERFIL()
{
    if(empty($_GET['id']) && !_autenticado())
    {
    // Un invitado quizo ver su propio perfil...
    if (!S_iniciado())
    {
        echo "Necesitas iniciar sesión para poder <b>ver tu perfil</b><br />";
        require_once("PHP/inicio.php");
        CONTENIDO_INICIAR_SESION();
        return;
    }
    }
    $usuario = empty($_GET['id']) && _autenticado() ? $_SESSION['cache_datos_usuario'] : _F_usuario_datos($_GET['id']);
    if(!is_array($usuario))
    {
        echo Mensaje("Lo sentimos, al parecer este usuario ya no forma parte de este sitio",_M_ERROR);
        return;
    }
    echo "<h1>Perfíl</h1>";
    echo "<p><b>Nombre de usuario:</b> " .  @$usuario['usuario']."</p>";
    echo (_F_usuario_cache('id_usuario') != $usuario['id_usuario'] ? "<p><b>Contacto:</b> ". ui_href("","mp?id=".$usuario['id_usuario'],"Mensaje Privado") : ""."</p>");
    echo "<p><b>Registrado desde:</b> " .  fechatiempo_desde_mysql_datetime(@$usuario['registro'])."</p>";
    echo "<p><b>Ultima actividad:</b> " . fechatiempo_desde_mysql_datetime(@$usuario['ultimo_acceso'])."</p>";
    $usuario['cantidad_publicaciones'] = ObtenerEstadisticasUsuario(@$usuario['id_usuario'],_EST_CANT_PUB_ACEPT);
    echo "<p><b>Cantidad de publicaciones:</b> " . $usuario['cantidad_publicaciones']."</p>";

    // Mostrar las tiendas - si tiene

    if ($usuario['tienda'])
    {
        $c = sprintf("SELECT tiendaURL, tiendaTitulo FROM ventas_tienda WHERE id_usuario='%s'",$usuario['id_usuario']);
        $r = db_consultar($c);
        if (mysql_num_rows($r) > 0)
        {
            echo '<h2>Tiendas</h2>';
            while ($f = mysql_fetch_assoc($r))
            {
                echo ui_href("","./+".$f['tiendaURL'],$f['tiendaTitulo']);
            }
        }
    }
    // Si el que esta viendo su propio perfil, mostrale sus mensajes del sistema y mensajes privados

    if(_F_usuario_cache('id_usuario')==$usuario['id_usuario'])
    {
        $c = "SELECT id_usuario_rmt, fecha, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario = a.id_usuario_rmt LIMIT 1) AS nombre_rmt, mensaje, tipo, contexto, fecha FROM ventas_mensajes AS a WHERE id IN (SELECT id_msj FROM ventas_mensajes_dst WHERE id_usuario_dst='"._F_usuario_cache('id_usuario')."') ORDER BY fecha DESC";
        $r = db_consultar($c);
        if (mysql_num_rows($r) > 0)
        {
            echo "<h1>Mensajes del sistema</h1>";
            echo '<table class="ancha resultados">';
            echo ui_tr(ui_th('Nombre Remitente').ui_th('Fecha').ui_th('Mensaje'));
            while ($f = mysql_fetch_array($r))
            {
            echo ui_tr(ui_td($f['nombre_rmt']).ui_td(fechatiempo_h_desde_mysql_datetime($f['fecha'])).ui_td($f['mensaje']));
            }
            echo '</table>';
        }

        // Mostrarle sus mensajes privados
        echo '<h1>Mensajes privados</h1>';
    }
}
?>
