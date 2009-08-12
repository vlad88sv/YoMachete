<?php
function CONTENIDO_ADMIN()
{
    if (_F_usuario_cache("nivel") != _N_administrador)
    {
        echo Mensaje("Oops!, parece que esta intentando acceder directamente a un lugar sin los permisos adecuados.",_M_ERROR);
        // Comprobamos que ya haya ingresado al sistema
        if (!S_iniciado())
        {
            echo "Necesitas iniciar sesión de Administrador para poder acceder a esta área.<br />";
            require_once("PHP/inicio.php");
            CONTENIDO_INICIAR_SESION();
            return;
        }

        return;
    }
    if (empty($_GET['op']))
    {
        echo "<h1>Bienvenido a la interfaz de administración</h1>";
        echo "Por favor seleccione el área a administrar:";
        echo "<ul>";
        echo "<li>".ui_href("","admin_usuarios_activacion","Usuarios: activación de cuentas")."</li>";
        echo "<li>".ui_href("","admin_usuarios_admin","Usuarios: administración general")."</li>";
        echo "<li>".ui_href("","admin_categorias_admin","Categorias: administración general")."</li>";
        echo "<li>".ui_href("","admin_publicaciones_activacion","Publicaciones: aprobación")."</li>";
        echo "<li>".ui_href("","admin_publicaciones_admin","Publicaciones: administración general")."</li>";
        echo "</ul>";
        return;
    }

    $op = $_GET['op'];
    switch ($op)
    {
        case "usuarios_activacion":
            INTERFAZ__ACTIVACION_USUARIOS();
        break;
        case "publicaciones_activacion":
            INTERFAZ__PUBLICACIONES_ACTIVACION();
        break;
        case "publicaciones_admin":
            INTERFAZ__PUBLICACIONES_ADMIN();
        break;
        default:
            echo "ERROR: Interfaz '$op' no implementada";
    }
}

function INTERFAZ__ACTIVACION_USUARIOS()
{
    if (!empty($_GET['activar']))
    {
        $usuario = _F_usuario_datos($_GET['activar']);
        $c = "UPDATE ventas_usuarios SET estado='"._N_activo."' WHERE id_usuario='" . db_codex($_GET['activar'])."' LIMIT 1";
        $r = db_consultar($c);
        $ret = db_afectados();
        if ($ret == 1)
        {
            $usuario = _F_usuario_datos($_GET['activar']);
            email($usuario['email'],"Su cuenta en " . PROY_NOMBRE . " ha sido aprobada","Gracias por su espera, su cuenta puede ser accedida en: ".PROY_URL."/iniciar");
            echo Mensaje("Usuario exitosamente activado",_M_INFO);
        }
        else
        {
            echo Mensaje("Usuario NO PUDO ser activado",_M_ERROR);
        }
    }
    if (!empty($_GET['cancelar']))
    {
        $usuario = _F_usuario_datos($_GET['cancelar']);
        $c = "DELETE FROM ventas_usuarios WHERE estado='"._N_esp_activacion."' AND id_usuario='" . db_codex($_GET['cancelar'])."' LIMIT 1";
        $r = db_consultar($c);
        $ret = db_afectados();
        if ($ret == 1)
        {
            echo Mensaje("Usuario exitosamente eliminado",_M_INFO);
            email($usuario['email'],"Su cuenta en " . PROY_NOMBRE . " no fue aprobada","Su solicitud de nueva cuenta fue rechazada por politicas de la empresa");
        }
        else
        {
            echo Mensaje("Usuario NO PUDO ser eliminado",_M_ERROR);
        }
    }
    $c = "SELECT id_usuario, usuario, email, telefono1, notas, ultimo_acceso FROM ventas_usuarios WHERE estado="._N_esp_activacion;
    $r = db_consultar($c);
    if (mysql_num_rows($r) == 0)
    {
        echo Mensaje("No hay usuarios esperando activación",_M_INFO);
        return;
    }
    echo "<table class=\"ancha\">";
    echo "<tbody>";
    echo "<tr><th>Usuario</th><th>Email</th><th>Teléfono</th><th>Notas</th><th>Último Acceso</th><th>Acciones</th></tr>";
    while ($f = mysql_fetch_array($r))
    {
        echo "<tr><td>".$f['usuario']."</td><td>".$f['email']."</td><td>".$f['telefono1']."</td><td>".$f['notas']."</td><td>".$f['ultimo_acceso']."</td><td>[".ui_href("","./admin_usuarios_activacion?activar=".$f['id_usuario'],"ACTIVAR") . "] / [".ui_href("","./admin_usuarios_activacion?cancelar=".$f['id_usuario'],"CANCELAR") . "]</td></tr>";
    }
    echo "</tbody>";
    echo "</table>";
}
function INTERFAZ__PUBLICACIONES_ACTIVACION()
{
    if (!empty($_GET['operacion']) && !empty($_GET['id_articulo']) && !empty($_GET['id_usuario']))
    {
        $id_articulo = db_codex($_GET['id_articulo']);
        $id_usuario = db_codex($_GET['id_usuario']);
        $ret = 0;
        $usuario = _F_usuario_datos($id_usuario);
        $publicacion = ObtenerDatos($_GET['id_articulo']);
        
        switch ($_GET['operacion'])
        {
            case "aprobar":
                $ret = Publicacion_Aprobar($id_articulo);
                if($ret)
                {
                    $msjNota="¡Su publicación \"<strong>".$publicacion['titulo']."</strong>\" ha sido aprobada! [".ui_href("",PROY_URL."/publicacion_".$id_articulo,"ver")."]";
                }
            break;
            case "rechazar":
                if (empty($_POST['motivo']))
                {
                    echo '<form action="'. $_SERVER['REQUEST_URI'] . '" method="post">';
                    echo '<h1>Rechazar publicación</h1>';
                    echo 'Por favor introduzca el motivo por el cual Ud. esta <strong>rechanzando (y eliminando) la publicación</strong> del vendedor<br />';
                    echo 'Motivo: <input name="motivo" value="" />';
                    echo '<input type="submit" value="Rechazar y eliminar"';
                    echo '</form>';
                    return;
                }
                $ret = DestruirTicket($_GET['id_articulo'],_A_esp_activacion);
                $msjNota="¡Su publicación \"<strong>".$publicacion['titulo']."</strong>\" ha sido rechazada y eliminada!<br />El motivo del rechazo y eliminación de esta publicación es: \"". db_codex($_POST['motivo']) ."\"";
            break;
            case "retornar":
                if (empty($_POST['motivo']))
                {
                    echo '<form action="'. $_SERVER['REQUEST_URI'] . '" method="post">';
                    echo '<h1>Retornar publicación</h1>';
                    echo 'Por favor introduzca el motivo por el cual Ud. esta <strong>retornando la publicación a la bandeja de publicaciones</strong> del vendedor<br />';
                    echo 'Motivo: <input name="motivo" value="" />';
                    echo '<input type="submit" value="retornar"';
                    echo '</form>';
                    return;
                }
                $c = "UPDATE ventas_articulos SET tipo="._A_temporal." WHERE tipo!='"._A_temporal."' AND id_articulo='$id_articulo' AND id_usuario='$id_usuario' LIMIT 1";
                $r = db_consultar($c);
                $ret = db_afectados();
                $msjNota="¡Su publicación \"<strong>".$publicacion['titulo']."</strong>\" ha sido retornada, favor verifiquela e intene de nuevo! [".ui_href("",PROY_URL."/vender?ticket=".$id_articulo,"ver y editar esta publicación")."]<br />El motivo del retorno de esta publicación es: \"". db_codex($_POST['motivo']) ."\"";
            break;
            // Esta opción tiene logica si la ejecutan una vez aprobada la publicación, Ej. desde una VISTA__articulos()
            case "desaprobar":
                if (empty($_POST['motivo']))
                {
                    echo '<form action="'. $_SERVER['REQUEST_URI'] . '" method="post">';
                    echo '<h1>Desaprobar publicación</h1>';
                    echo 'Por favor introduzca el motivo por el cual Ud. esta <strong>desaprobando la publicación</strong> del vendedor<br />';
                    echo 'Motivo: <input name="motivo" value="" />';
                    echo '<input type="submit" value="desprobar"';
                    echo '</form>';
                    return;
                }
                $c = "UPDATE ventas_articulos SET tipo="._A_esp_activacion." WHERE tipo='"._A_aceptado."' AND id_articulo='$id_articulo' AND id_usuario='$id_usuario' LIMIT 1";
                $r = db_consultar($c);
                $ret = db_afectados();
                $msjNota="¡La publicación \"<strong>".$publicacion['titulo']."</strong>\" ha sido desaprobada!<br />\nEsto significa que un administrador esta realizando una revisión de la publicación y no estará disponible al público mientras no sea re-aprobada.<br />\nEl motivo de desaprobación de esta publicación es: \"". db_codex($_POST['motivo']) ."\"";
            break;
        }
        if ($ret == 1)
        {
            echo Mensaje("Operación exitosa: ".$_GET['operacion'],_M_INFO);
            EnviarNota($msjNota,$id_usuario,$Tipo=_M_INFO,$Contexto=_MC_ventas);
            email($usuario['email'],PROY_NOMBRE . " - Cambio en el estado de su publicación: \"". $publicacion['titulo']."\"",$msjNota);
        }
        else
        {
            echo Mensaje("Operación erronea: ".$_GET['operacion'],_M_ERROR);
        }
    }

    // Obtenemos las publicaciones pendientes
    echo VISTA_ArticuloEnLista("tipo='"._A_esp_activacion."'","ORDER by fecha_ini","admin","No hay publicaciones esperando activación");

    echo JS_onload('$("a[rel=\'lightbox\']").lightBox();');
}

function INTERFAZ__PUBLICACIONES_ADMIN()
{
    if (!empty($_GET['operacion']) && !empty($_GET['id_articulo']) && !empty($_GET['id_usuario']))
    {
        $id_articulo = db_codex($_GET['id_articulo']);
        $id_usuario = db_codex($_GET['id_usuario']);
        $ret = 0;

        switch ($_GET['operacion'])
        {
            case "promocionar":
                if ($_GET['estado'] == 0 || $_GET['estado'] == 1 )
                {
                    if (PromocionarPublicacion($id_articulo, $_GET['estado']))
                    {
                        echo 'Estado de promoción alternado.';
                    }
                    else
                    {
                        echo "Articulo NO pudo ser promocionado!";
                    }
                }
            break;
        }
        echo '<br /><a href="'.$_SERVER['HTTP_REFERER'].'">Regresar</a>';
    }
}
?>
