<?php
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
if (isset($_GET['id']))
{
    $id_usuario = db_codex($_GET['id']);

    //No se estará enviando el mensaje a el mismo verdad? XD

    if ($id_usuario == _F_usuario_cache('id_usuario'))
    {
        echo Mensaje("auto-enviarse mensajes privados no es permitido",_M_ERROR);
        return;
    }

    if (isset($_POST['enviar_mp']) && isset($_POST['mensaje']))
    {
        //Agregamos el mensaje
        $datos["id_usuario_rmt"] =  _F_usuario_cache('id_usuario');
        $datos["mensaje"] = $_POST['mensaje'];
        $datos["tipo"] = _M_NOTA;
        $datos["contexto"] = _MC_privado;
        $datos["fecha"] = mysql_datetime();
        $id_msj = db_agregar_datos("ventas_mensajes",$datos);
        unset($datos);

        //Agregamos el destinatario
        $datos["id_msj"] = $id_msj;
        $datos["id_usuario_dst"] = $id_usuario;
        $datos["leido"] = 0;
        $datos["eliminado"] = 0;
        $id_msj = db_agregar_datos("ventas_mensajes_dst",$datos);
        unset($datos);

        // Notificación por email del MP:
        $usuario_destino = _F_usuario_datos($id_usuario);
        $mensaje = 'Acaban de enviarte un mensaje privado de parte de '._F_usuario_cache('usuario').' en '.PROY_NOMBRE.".<br /><br />\n\n";
        $mensaje .= 'IMPORTANTE: Recuerda, esto es solamente una notificación. Por favor, no respondas a este email.'."<br /><br />\n\n";
        $mensaje .= 'El mensaje que te enviaron fue:'."<br /><br />\n\n";
        $mensaje .= $_POST['mensaje'].".<br /><br />\n\n";
        $mensaje .= 'Responda a este mensaje privado aquí: ' . PROY_URL.'perfil?op=mp';
        @email($usuario_destino['email'], 'Nuevo Mensaje Privado: ' . $_POST['asunto'], $mensaje);
        
        //Notificación al usuario que envió el MP
        echo Mensaje("¡Su mensaje privado ha sido enviado!");
        echo '<h1>Opciones</h1>';
        echo '<ul>';
        echo '<li><a href="'.PROY_URL.'">Pagina de inicio</a></li>';
        echo '</ul>';
        return;
    }

    //Existe el usuario al cual quiere enviar el mensaje?
    if (_F_usuario_existe($id_usuario,'id_usuario'))
    {
        $usuario_destino = _F_usuario_datos($id_usuario);
        echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="POST">';
        echo 'Este mensaje será enviado al usuario <b>'.$usuario_destino['usuario'].'</b><br />';
        echo '<table>';
        echo ui_tr(ui_td('Asunto: '). ui_td(ui_input("asunto","","text","","width:100%")));
        echo ui_tr(ui_td('Mensaje: '). ui_td(ui_textarea("mensaje","","","width:100%")));
        echo '</table>';
        echo ui_input("enviar_mp","Enviar","submit").'<br />';
        echo '</form>';
    }
    else
    {
        echo Mensaje("ha especificado un usuario de destino no existente en el sitema",_M_ERROR);
        return;
    }
}
else
{
    echo '<h1>Mensajes privados</h1>';
    // Mostrale sus mensajes privados
    $c = "SELECT id_usuario_rmt, fecha, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario = a.id_usuario_rmt LIMIT 1) AS nombre_rmt, mensaje, tipo, contexto, asunto, fecha FROM ventas_mensajes AS a WHERE contexto="._MC_privado." AND id IN (SELECT id_msj FROM ventas_mensajes_dst WHERE id_usuario_dst='"._F_usuario_cache('id_usuario')."') ORDER BY fecha DESC";
    $r = db_consultar($c);
    if (mysql_num_rows($r) > 0)
    {    
        echo '<table class="ancha resultados">';
        while ($f = mysql_fetch_array($r))
        {
        echo ui_tr(ui_th('Nombre Remitente').ui_th('Fecha').ui_th('Asunto'));
        echo ui_tr(ui_td($f['nombre_rmt']).ui_td(fechatiempo_h_desde_mysql_datetime($f['fecha'])).ui_td($f['asunto']));
        echo '<tr><td colspan="3">'.$f['mensaje'].'</td></tr>';
        }
        echo '</table>';
    }
    else
    {
        echo Mensaje('no tienes mensajes privados',_M_INFO);
    }
    return; 
}
}

function CONTENIDO_PERFIL()
{
    if(isset($_GET['op']))
    {
    switch($_GET['op'])
    {
        case 'mp':
            CONTENIDO_MP();
            break;
    }
    return;
    }
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
    echo (_F_usuario_cache('id_usuario') != $usuario['id_usuario'] ? "<p><b>Contacto:</b> ". ui_href("",PROY_URL."perfil?id=".$usuario['id_usuario']."&amp;op=mp","Mensaje Privado") : ""."</p>");
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
    // Si el que esta viendo su propio perfil, mostrale sus mensajes del sistema
    if(_F_usuario_cache('id_usuario')==$usuario['id_usuario'])
    {
        $c = "SELECT id_usuario_rmt, fecha, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario = a.id_usuario_rmt LIMIT 1) AS nombre_rmt, mensaje, tipo, contexto, asunto, fecha FROM ventas_mensajes AS a WHERE contexto="._MC_broadcast." AND id IN (SELECT id_msj FROM ventas_mensajes_dst WHERE id_usuario_dst='"._F_usuario_cache('id_usuario')."') ORDER BY fecha DESC";
        $r = db_consultar($c);
        if (mysql_num_rows($r) > 0)
        {
            echo '<h1>Mensajes del sistema</h1>';
            echo '<table class="ancha resultados">';
            while ($f = mysql_fetch_array($r))
            {
            echo ui_tr(ui_th('Nombre Remitente').ui_th('Fecha').ui_th('Asunto'));
            echo ui_tr(ui_td($f['nombre_rmt']).ui_td(fechatiempo_h_desde_mysql_datetime($f['fecha'])).ui_td($f['asunto']));
            echo '<tr><td colspan="3">'.$f['mensaje'].'</td></tr>';
            }
            echo '</table>';
        }
    }
}
?>
