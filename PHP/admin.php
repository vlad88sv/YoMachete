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
    if (!empty($_GET['aprobar']))
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
    if (!empty($_GET['operacion']) && !empty($_GET['id_articulo']) && !empty($_GET['id_usuario']))
    {
        $id_articulo = db_codex($_GET['id_articulo']);
        $id_usuario = db_codex($_GET['id_usuario']);
        $ret = 0;

        switch ($_GET['operacion'])
        {
            case "aprobar":
                $c = "UPDATE ventas_articulos SET tipo="._A_aceptado." WHERE tipo='"._A_esp_activacion."' AND id_articulo='$id_articulo' AND id_usuario='$id_usuario' LIMIT 1";
                $r = db_consultar($c);
                $ret = db_afectados();
                $msjNota="¡Su publicación ha sido aprobada! [".ui_href("","publicacion_".$id_articulo,"ver")."]";
            break;
            case "rechazar":
                $ret = DestruirTicket($_GET['cancelar'],_A_esp_activacion);
                $msjNota="¡Su publicación [$id_articulo] ha sido rechazada y eliminada, esto significa que su publicación era ilegal!";
            break;
            case "retornar":
                $c = "UPDATE ventas_articulos SET tipo="._A_temporal." WHERE tipo='"._A_aceptado."' AND id_articulo='$id_articulo' AND id_usuario='$id_usuario' LIMIT 1";
                $r = db_consultar($c);
                $ret = db_afectados();
                $msjNota="¡Su publicación ha sido retornada, favor verifiquela e intene de nuevo! [".ui_href("","vender?ticket=".$id_articulo,"ver y editar esta publicación")."]";
            break;
            // Esta opción tiene logica si la ejecutan una vez aprobada la publicación, Ej. desde una VISTA__articulos()
            case "desaprobar":
                $c = "UPDATE ventas_articulos SET tipo="._A_esp_activacion." WHERE tipo='"._A_aceptado."' AND id_articulo='$id_articulo' AND id_usuario='$id_usuario' LIMIT 1";
                $r = db_consultar($c);
                $ret = db_afectados();
                $msjNota="¡La publicación ha sido desaprobada, favor verifiquela e intene de nuevo! [".ui_href("","vender?ticket=".$id_articulo,"ver y editar esta publicación")."]";
            break;
        }
        if ($ret == 1)
        {
            echo Mensaje("Operación exitosa: ".$_GET['operacion'],_M_INFO);
            EnviarNota($msjNota,$id_usuario,$Tipo=_M_INFO,$Contexto=_MC_ventas);
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
?>
