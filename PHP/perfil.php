<?php
// Esta función se encarga de mostrar los MPs o de enviarlos
function CONTENIDO_MP($opciones=array())
{

// Comprobamos que ya haya ingresado al sistema
if (!S_iniciado())
{
    echo "Necesitas iniciar sesión para poder <b>enviar Mensajes Privados</b>.<br />";
    require_once("PHP/inicio.php");
    CONTENIDO_INICIAR_SESION();
    return;
}

// Será que quiere eliminar un MP que recibio?.
if (!empty($_GET['ae']) && $_GET['ae'] == 'eliminar' && !empty($_GET['id_msj']))
{
    $c = "UPDATE ventas_mensajes_dst SET eliminado=1 WHERE id_msj='".db_codex($_GET['id_msj'])."' AND id_usuario_dst='"._F_usuario_cache('id_usuario')."'";
    $r = db_consultar($c);
    if (db_afectados() > 0)
    {
        echo Mensaje("Mensaje eliminado");
    }
    else
    {
        echo Mensaje("Mensaje no eliminado");
    }
}

// Será que quiere eliminar un MP que envió?.
if (!empty($_GET['ae']) && $_GET['ae'] == 'eliminar2' && !empty($_GET['id_msj']))
{
    $c = "UPDATE ventas_mensajes SET eliminado=1 WHERE id='".db_codex($_GET['id_msj'])."' AND id_usuario_rmt='"._F_usuario_cache('id_usuario')."'";
    $r = db_consultar($c);
    if (db_afectados() > 0)
    {
        echo Mensaje("Mensaje eliminado");
    }
    else
    {
        echo Mensaje("Mensaje no eliminado");
    }
}

// Será que quiere marcar un MP como leido?.
if (!empty($_GET['ae']) && $_GET['ae'] == 'leido' && !empty($_GET['id_msj']))
{
    $c = "UPDATE ventas_mensajes_dst SET leido=1 WHERE id_msj='".db_codex($_GET['id_msj'])."' AND id_usuario_dst='"._F_usuario_cache('id_usuario')."'";
    $r = db_consultar($c);
    if (db_afectados() > 0)
    {
        echo Mensaje("Mensaje marcado como leído");
    }
    else
    {
        echo Mensaje("Mensaje no pudo ser marcado como leído");
    }
}
// ----------------- ENVIAR MP ----------------- ENVIAR MP ----------------- ENVIAR MP -----------------
// Si hay un id entonces quiere enviar un MP a ese ID
if (!empty($_GET['id']))
{
    $id_usuario = db_codex($_GET['id']);

    // No se estará enviando el mensaje a el mismo verdad? XD
    if ($id_usuario == _F_usuario_cache('id_usuario'))
    {
        echo Mensaje("auto-enviarse mensajes privados no es permitido",_M_ERROR);
        return;
    }

    //Existe el usuario al cual quiere enviar el mensaje?
    if (!_F_usuario_existe($id_usuario,'id_usuario'))
    {
        echo Mensaje("ha especificado un usuario de destino no existente en el sitema",_M_ERROR);
        return;
    }

    // Hay envío de MP?
    if (isset($_POST['enviar_mp']) && isset($_POST['mensaje']))
    {
        // Será que quiere enviar una respuesta a otro MP
        if(!empty($_GET['id_msj']))
        {
            $c = "SELECT a.id, id_usuario_rmt, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario = a.id_usuario_rmt LIMIT 1) AS nombre_rmt, fecha, mensaje, tipo, contexto, asunto, fecha FROM ventas_mensajes AS a LEFT JOIN ventas_mensajes_dst as vmd ON a.id=vmd.id_msj WHERE vmd.id_msj='".$_GET['id_msj']."' AND a.contexto="._MC_privado." AND vmd.id_usuario_dst='"._F_usuario_cache('id_usuario')."' LIMIT 1";
            $r = db_consultar($c);
            
            // Será que se quiere pasar de vivo cambiando el id_msj
            if (mysql_num_rows($r) == 0)
            {
                echo Mensaje("No puedes responder Mensajes <b>Privados</b> de otras personas.",_M_ERROR);
                return;
            }

            $f = mysql_fetch_array($r);
            $_POST['asunto'] .= '[RESPUESTA A] '.$f['asunto'];
            $_POST['mensaje'] .= '<br />'. htmlentities("-En respuesta a:\n".$f['mensaje']); 
        }
        
        //Agregamos el mensaje
        $datos["id_usuario_rmt"] =  _F_usuario_cache('id_usuario');
        $datos["mensaje"] = $_POST['mensaje'];
        $datos["asunto"] = $_POST['asunto'];
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

        // Notificación por email del MP al destinatario:
        $usuario_destino = _F_usuario_datos($id_usuario);
        $mensaje  = 'Acaban de enviarte un mensaje privado de parte de '._F_usuario_cache('usuario').' en '.PROY_NOMBRE.".<br /><br />\n\n";
        $mensaje .= 'IMPORTANTE: Recuerda, esto es solamente una notificación. Por favor, no respondas a este email.'."<br /><br />\n\n";
        $mensaje .= 'El mensaje que te enviaron fue:'."<br /><br />\n\n";
        $mensaje .= $_POST['mensaje'].".<br /><br />\n\n";
        $mensaje .= 'Responda a este mensaje privado aquí: ' . PROY_URL.'perfil?op=mp';
        @email($usuario_destino['email'], 'Nuevo Mensaje Privado: ' . $_POST['asunto'], $mensaje);
        
        //Notificación visual al usuario que envió el MP
        echo Mensaje("¡Su mensaje privado ha sido enviado!");
        echo '<h1>Opciones</h1>';
        echo '<ul>';
        echo '<li><a href="'.PROY_URL.'">Ir pagina de inicio</a></li>';
        echo '<li><a href="'.curPageURL(true).'">Ir a mi perfil</a></li>';
        echo '</ul>';
        return;
    }
    // ----------------- ENVIAR MP ----------------- ENVIAR MP ----------------- ENVIAR MP -----------------

    // No ha enviado el MP aún, mostrar el formulario de envío.
    if(!empty($_GET['id_msj']))
    {
        $c = "SELECT a.id, id_usuario_rmt, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario = a.id_usuario_rmt LIMIT 1) AS nombre_rmt, fecha, mensaje, tipo, contexto, asunto, fecha FROM ventas_mensajes AS a LEFT JOIN ventas_mensajes_dst as vmd ON a.id=vmd.id_msj WHERE vmd.id_msj='".$_GET['id_msj']."' AND a.contexto="._MC_privado." AND vmd.id_usuario_dst='"._F_usuario_cache('id_usuario')."'";
        $r = db_consultar($c);
        // Será que se quiere pasar de vivo cambiando el id_msj
        if (mysql_num_rows($r) == 0)
        {
            echo Mensaje("No puedes responder Mensajes <b>Privados</b> de otras personas.",_M_ERROR);
            return;
        }

        $f = mysql_fetch_array($r);
        $Asunto = 'RESPUESTA A: '.$f['asunto'];
        $EnRespuestaA = htmlentities("---En respuesta a:\n".$f['mensaje'],ENT_QUOTES,'UTF-8');
    }
    $usuario_destino = _F_usuario_datos($id_usuario);
    echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="POST">';
    echo 'Este mensaje será enviado al usuario <b>'.$usuario_destino['usuario'].'</b><br />';
    echo '<table>';
    echo ui_tr(ui_td('Asunto: '). ui_td(ui_input('asunto', @$Asunto, "text", '', 'width:100%')));
    echo ui_tr(ui_td('Mensaje: '). ui_td(ui_textarea('mensaje', '', '', 'width:100%')));
    echo '</table>';
    if(!empty($_GET['id_msj']))
        echo "<p><b>Se anexará automaticamente a su mensaje el siguiente texto:</b><br />",@$EnRespuestaA,"</p>";
    echo ui_input("enviar_mp","Enviar","submit").'<br />';
    echo '</form>';

}
// Si no hay un Id entonces quiere ver sus propios MPs
else
{
    echo '<h1>Mensajes privados</h1>';

    echo '<h2>Categorías</h2>';
    echo '<p><a class="btnlnk" href="',PROY_URL,'perfil?op=mp">Nuevos</a> <a class="btnlnk" href="',PROY_URL,'perfil?op=mpl">Leidos</a> <a class="btnlnk" href="',PROY_URL,'perfil?op=mpe">Enviados</a></p>';
    
    echo '<h2>Mensajes</h2>';
    // Mostrale sus mensajes privados
    
    /* Necesito:
      * id de usuario del remitente
      * asunto
      * mensaje
      * fecha
      * nombre del remitente
    */
    if (empty($opciones['vista'])) $opciones['vista'] = "nuevos";
    
    switch ($opciones['vista'])
    {
        case 'nuevos':
        $c = "SELECT a.id, id_usuario_rmt, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario = a.id_usuario_rmt LIMIT 1) AS 'usuario', fecha, mensaje, tipo, contexto, asunto, fecha FROM ventas_mensajes AS a LEFT JOIN ventas_mensajes_dst AS vmd ON a.id=vmd.id_msj WHERE a.contexto="._MC_privado." AND vmd.leido=0 AND vmd.eliminado=0 AND vmd.id_usuario_dst='"._F_usuario_cache('id_usuario')."' ORDER BY fecha DESC";
            break;
        case 'leidos':
        $c = "SELECT a.id, id_usuario_rmt, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario = a.id_usuario_rmt LIMIT 1) AS 'usuario', fecha, mensaje, tipo, contexto, asunto, fecha FROM ventas_mensajes AS a LEFT JOIN ventas_mensajes_dst AS vmd ON a.id=vmd.id_msj WHERE a.contexto="._MC_privado." AND vmd.leido=1 AND vmd.eliminado=0 AND vmd.id_usuario_dst='"._F_usuario_cache('id_usuario')."' ORDER BY fecha DESC";
            break;
        case 'enviados':
        $c = "SELECT a.id, (SELECT usuario FROM ventas_usuarios AS b WHERE b.id_usuario = vmd.id_usuario_dst LIMIT 1) AS 'usuario', fecha, mensaje, tipo, contexto, asunto, fecha FROM ventas_mensajes AS a LEFT JOIN ventas_mensajes_dst AS vmd ON a.id=vmd.id_msj WHERE a.contexto="._MC_privado." AND a.eliminado=0 AND a.id_usuario_rmt='"._F_usuario_cache('id_usuario')."' ORDER BY fecha DESC";
            break;
    }
    
    $r = db_consultar($c);
    if (mysql_num_rows($r) > 0)
    {    
        echo '<table class="ancha resultados">';
        while ($f = mysql_fetch_array($r))
        {
        
        echo ui_tr(ui_th('Usuario').ui_th('Fecha').ui_th('Asunto'));
        echo ui_tr(ui_td($f['usuario']).ui_td(fechatiempo_h_desde_mysql_datetime($f['fecha'])).ui_td($f['asunto']));
        echo '<tr><td colspan="3">'.$f['mensaje'].'</td></tr>';

        switch ($opciones['vista'])
        {
            case 'nuevos':
            echo '<tr><td colspan="3"><a href="./perfil?op=mp&ae=responder&id='.$f['id_usuario_rmt'].'&id_msj='.$f['id'].'">responder</a> / <a href="./perfil?op=mp&ae=eliminar&id_msj='.$f['id'].'">eliminar</a> / <a href="./perfil?op=mp&ae=leido&id_msj='.$f['id'].'">marcar como leído</a></td></tr>';
                break;
            case 'leidos':
            echo '<tr><td colspan="3"><a href="./perfil?op=mpl&ae=responder&id='.$f['id_usuario_rmt'].'&id_msj='.$f['id'].'">responder</a> / <a href="./perfil?op=mp&ae=eliminar&id_msj='.$f['id'].'">eliminar</a></td></tr>';
                break;
            case 'enviados':
            echo '<tr><td colspan="3"><a href="./perfil?op=mpe&ae=eliminar2&id_msj='.$f['id'].'">eliminar</a></td></tr>';
                break;
        }


        echo '<tr><td colspan="3"><hr /></td></tr>';
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
        case 'mpl':
            CONTENIDO_MP(array('vista'=>'leidos'));
            break;
        case 'mpe':
            CONTENIDO_MP(array('vista'=>'enviados'));
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
        // Link a edición de perfil
        echo '<p><a href="http://yomachete.com/perfil?op=editar">Editar perfil</a></p>';
        
        // Link a sus mensajes privados
        echo '<p><a href="http://yomachete.com/perfil?op=mp">Ver mensajes privados</a></p>';
        
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
