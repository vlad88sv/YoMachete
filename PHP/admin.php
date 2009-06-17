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
        default:
            echo "ERROR: Interfaz '$op' no implementada";
    }
}

function INTERFAZ__ACTIVACION_USUARIOS()
{
    if (!empty($_GET['activar']))
    {
        $c = "UPDATE ventas_usuarios SET estado=NULL WHERE estado='"._N_esp_activacion."' AND id_usuario='" . db_codex($_GET['activar'])."' LIMIT 1";
        $r = db_consultar($c);
        $ret = db_afectados();
        if ($ret == 1)
        {
            echo Mensaje("Usuario exitosamente activado",_M_INFO);
        }
        else
        {
            echo Mensaje("Usuario NO PUDO ser activado",_M_ERROR);
        }
    }
    if (!empty($_GET['cancelar']))
    {
        $c = "DELETE FROM ventas_usuarios WHERE estado='"._N_esp_activacion."' AND id_usuario='" . db_codex($_GET['cancelar'])."' LIMIT 1";
        $r = db_consultar($c);
        $ret = db_afectados();
        if ($ret == 1)
        {
            echo Mensaje("Usuario exitosamente eliminado",_M_INFO);
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
    if (!empty($_GET['activar']))
    {
        $c = "UPDATE ventas_articulos SET tipo="._A_aceptado." WHERE tipo='"._A_esp_activacion."' AND id_articulo='" . db_codex($_GET['activar'])."' LIMIT 1";
        $r = db_consultar($c);
        $ret = db_afectados();
        if ($ret == 1)
        {
            echo Mensaje("Publicación exitosamente aprobada",_M_INFO);
        }
        else
        {
            echo Mensaje("Publicación NO PUDO ser aprobada",_M_ERROR);
        }
    }
    if (!empty($_GET['cancelar']))
    {
        $c = "DELETE FROM ventas_articulos WHERE tipo='"._A_esp_activacion."' AND id_articulo='" . db_codex($_GET['cancelar'])."' LIMIT 1";
        $r = db_consultar($c);
        $ret = db_afectados();
        if ($ret == 1)
        {
            echo Mensaje("Publicación exitosamente eliminada",_M_INFO);
        }
        else
        {
            echo Mensaje("Publicación NO PUDO ser eliminada",_M_ERROR);
        }
    }
    $c = "SELECT id_categoria, id_articulo, (SELECT id_img FROM ventas_imagenes as b WHERE b.id_articulo = a.id_articulo LIMIT 1) as imagen, titulo, descripcion_corta, id_usuario, precio FROM ventas_articulos AS a WHERE tipo='"._A_esp_activacion."' ORDER by fecha_ini";
    $r = db_consultar($c);
    if (mysql_num_rows($r) == 0)
    {
        echo Mensaje("No hay publicaciones esperando activación",_M_INFO);
        return;
    }

    while ($f = mysql_fetch_array($r))
    {
        echo "[".ui_href("","./admin_publicaciones_activacion?activar=".$f['id_articulo'],"ACTIVAR") . "] / [".ui_href("","./admin_publicaciones_activacion?cancelar=".$f['id_articulo'],"CANCELAR") . "]";
        echo VISTA_ArticuloEnLista($f['titulo'],"publicacion_".$f['id_articulo'],$f['precio'],substr($f['descripcion_corta'],0,200),"<a href=\"./imagen_".$f['imagen']."\" target=\"_blank\" rel=\"lightbox\" title=\"VISTA DE ARTÍCULO\"><img src=\"./imagen_".$f['imagen']."m\" /></a>",join(" > ", get_path($f['id_categoria'])));
        echo "<br />";
    }

    echo JS_onload('$("a[rel=\'lightbox\']").lightBox();');
}
?>
