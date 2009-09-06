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
        echo "<li>".ui_href("","admin_usuarios_admin","Usuarios: administración")."</li>";
        echo "<li>".ui_href("","admin_usuarios_agregar","Usuarios: agregar")."</li>";
        echo "<li>".ui_href("","admin_publicaciones_activacion","Publicaciones: aprobación")."</li>";
        echo "<li>".ui_href("","admin_tiendas","Tiendas: administración")."</li>";
        echo "<li>".ui_href("","admin_tiendas","Tiendas: agregar")."</li>";
        echo "</ul>";
        return;
    }

    $op = $_GET['op'];
    switch ($op)
    {
        case "usuarios_activacion":
            INTERFAZ__ACTIVACION_USUARIOS();
        break;
        case "usuarios_agregar":
            INTERFAZ__ADMIN_USUARIOS_AGREGAR();
        break;
        case "usuarios_admin":
            INTERFAZ__ADMIN_USUARIOS();
        break;
        case "publicaciones_activacion":
            INTERFAZ__PUBLICACIONES_ACTIVACION();
        break;
        case "publicaciones_admin":
            INTERFAZ__PUBLICACIONES_ADMIN();
        break;
        case "tiendas":
            INTERFAZ__ADMIN_TIENDAS();
        break;
        case "tienda_agregar":
            INTERFAZ__ADMIN_TIENDAS_AGREGAR();
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
            email($usuario['email'],"Su cuenta en " . PROY_NOMBRE . " ha sido aprobada","Gracias por su espera, su cuenta puede ser accedida en: ".PROY_URL."iniciar");
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
    if (!empty($_GET['operacion']) && !empty($_GET['id_publicacion']) && !empty($_GET['id_usuario']))
    {
        $id_publicacion = db_codex($_GET['id_publicacion']);
        $id_usuario = db_codex($_GET['id_usuario']);
        $ret = 0;
        $usuario = _F_usuario_datos($id_usuario);
        $publicacion = ObtenerDatos($_GET['id_publicacion']);

        switch ($_GET['operacion'])
        {
            case "aprobar":
                $ret = Publicacion_Aprobar($id_publicacion);
                if($ret)
                {
                    $msjNota="¡Su publicación \"<strong>".$publicacion['titulo']."</strong>\" ha sido aprobada! [".ui_href("",PROY_URL."publicacion_".$id_publicacion,"ver")."]";
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
                $ret = DestruirTicket($_GET['id_publicacion'],_A_esp_activacion);
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
                $c = "UPDATE ventas_publicaciones SET tipo="._A_temporal." WHERE tipo!='"._A_temporal."' AND id_publicacion='$id_publicacion' AND id_usuario='$id_usuario' LIMIT 1";
                $r = db_consultar($c);
                $ret = db_afectados();
                $msjNota="¡Su publicación \"<strong>".$publicacion['titulo']."</strong>\" ha sido retornada, favor verifiquela e intene de nuevo! [".ui_href("",PROY_URL."vender?ticket=".$id_publicacion,"ver y editar esta publicación")."]<br />El motivo del retorno de esta publicación es: \"". db_codex($_POST['motivo']) ."\"";
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
                $c = "UPDATE ventas_publicaciones SET tipo="._A_esp_activacion." WHERE tipo='"._A_aceptado."' AND id_publicacion='$id_publicacion' AND id_usuario='$id_usuario' LIMIT 1";
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
    echo VISTA_ListaPubs("tipo='"._A_esp_activacion."'","ORDER by fecha_ini","admin","No hay publicaciones esperando activación");

    echo JS_onload('$("a[rel=\'lightbox\']").lightBox();');
}

function INTERFAZ__PUBLICACIONES_ADMIN()
{
    if (!empty($_GET['operacion']) && !empty($_GET['id_publicacion']) && !empty($_GET['id_usuario']))
    {
        $id_publicacion = db_codex($_GET['id_publicacion']);
        $id_usuario = db_codex($_GET['id_usuario']);
        $ret = 0;

        switch ($_GET['operacion'])
        {
            case "promocionar":
                if ($_GET['estado'] == 0 || $_GET['estado'] == 1 )
                {
                    if (PromocionarPublicacion($id_publicacion, $_GET['estado']))
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

function INTERFAZ__ADMIN_USUARIOS_AGREGAR()
{
    if(!empty($_POST['registrar']))
    {
        $flag_registroExitoso=true;
        if (!empty($_POST['registrar_campo_email']))
        {
            if (!validEmail($_POST['registrar_campo_email']))
            {
                echo mensaje ("Este correo electrónico no es válido, por favor revise que este escrito correctamente o escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
            if (_F_usuario_existe($_POST['registrar_campo_email'],"email"))
            {
                echo mensaje ("Este correo electrónico ya existe en el sistema, por favor escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
                $datos['email'] = $_POST['registrar_campo_email'];
        }
        else
        {
            echo mensaje ("Por favor ingrese su email e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if (!empty($_POST['registrar_campo_usuario']))
        {
            if (_F_usuario_existe($_POST['registrar_campo_usuario']))
            {
                echo mensaje ("Este nombre de usuario ya existe en el sistema, por favor escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
            if (strpos(trim($_POST['registrar_campo_usuario'])," "))
            {
                echo mensaje ("Este nombre de usuario no es válido (contiene espacios), por favor escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
            $datos['usuario'] = trim($_POST['registrar_campo_usuario']);
        }
        else
        {
            echo mensaje ("Por favor ingrese su usuario e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if (!empty($_POST['registrar_campo_clave']) && !empty($_POST['registrar_campo_clave_2']))
        {
            //Contraseñas iguales?
            if (trim($_POST['registrar_campo_clave']) == trim($_POST['registrar_campo_clave_2']))
            {
                //Tamaño adecuado?
                if(strlen($_POST['registrar_campo_clave']) >= 6 && strlen($_POST['registrar_campo_clave']) <= 100)
                {
                    $datos['clave'] = md5(trim($_POST['registrar_campo_clave']));
                }
                else
                {
                    echo mensaje ("La contraseña debe tener mas de 6 caracteres",_M_ERROR);
                    $flag_registroExitoso=false;
                }
            }
            else
            {
                echo mensaje ("Las contraseñas no coinciden, por favor ingrese su contraseña e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
        }
        else
        {
            echo mensaje ("Por favor ingrese su contraseña e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if (!empty($_POST['registrar_campo_nombre']))
        {
            $datos['nombre'] = $_POST['registrar_campo_nombre'];
        }
        if (!empty($_POST['registrar_campo_telefono']))
        {
            if (_F_usuario_existe($_POST['registrar_campo_telefono'], "telefono1"))
            {
                echo mensaje ("Este teléfono ya existe en el sistema, por favor escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }

            $datos['telefono1'] = $_POST['registrar_campo_telefono'];
        }
        else
        {
            echo mensaje ("Por favor ingrese su número telefonico e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if ($flag_registroExitoso)
        {
            $datos["estado"] = _N_activo;
            $datos["nivel"] = $_POST['nivel'];
            $datos["ultimo_acceso"] = mysql_datetime();
            $datos["registro"]= mysql_datetime();
            if (db_agregar_datos("ventas_usuarios",$datos))
            {
                echo Mensaje("Usuario añadido exitosamente");
            }
            else{
                echo Mensaje("Usuario NO PUDO ser añadido",_M_ERROR);
            }
            email($datos['email'],sprintf("Estimado %s, Ud. ha sido registrado en ".PROY_NOMBRE." por un Administrador",$datos['usuario']),"Su registro de usuario  en ".PROY_NOMBRE." ha sido efectuado manualmente por un administrador, ¡su cuenta esta activa y esperando a ser utilizada!.<br />\n\n<hr><br />\n<h1>Datos registrados</h1><br />\nCorreo electrónico: <strong>".$datos['email']."</strong><br />\nUsuario: <strong>".$datos['usuario']."</strong><br />\nContraseña: <strong>".trim($_POST['registrar_campo_clave']))."</strong><br />".PROY_NOMBRE."<br />".PROY_URL; //$datos['clave'] en este punto ya contiene la contraseña encriptada
            return;
        }
    }
?>
<h1>Registro de usuario</h1>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" >
<table>
<tr><td>Correo electrónico</td><td class="fInput"><input name="registrar_campo_email" type="text" value="" /></tr>
<tr><td>Usuario</td><td class="fInput"><input name="registrar_campo_usuario" type="text" value="" /></tr>
<tr><td>Nombre</td><td class="fInput"><input name="registrar_campo_nombre" type="text" value="" /></tr>
<tr><td>Clave</td><td class="fInput"><input name="registrar_campo_clave" type="password" value="" /></tr>
<tr><td>Clave (confirmar)</td><td class="fInput"><input name="registrar_campo_clave_2" type="password" value="" /></tr>
<tr><td>Teléfono de contacto</td><td class="fInput"><input name="registrar_campo_telefono" type="text" value="" /></tr>
<tr>
<td>Nivel</td>
<td class="fInput">
<select name="nivel">
    <option value="<?php echo _N_administrador; ?>">Administrador</option>
    <option value="<?php echo _N_moderador; ?>">Moderador</option>
    <option value="<?php echo _N_vendedor; ?>">Vendedor</option>
</select>
</td>
</tr>
<tr><td class="fDer" colspan="2"><input name="registrar" value="Registrar" type="submit"/></tr>
</table>
<br /
</form>
<?php
echo JS_onload('
$("#registrar_campo_email").keyup(function(){$("#registrar_respuesta_email").load("./registro_correo_existe:"+$("#registrar_campo_email").val());});
$("#registrar_campo_usuario").keyup(function(){$("#registrar_respuesta_usuario").load("./registro_usuario_existe:"+$("#registrar_campo_usuario").val());});
');
}

function INTERFAZ__ADMIN_USUARIOS_EDITAR()
{
    if(!empty($_POST['modificar']))
    {
        $flag_registroExitoso=true;

        if (!empty($_POST['registrar_campo_email']))
        {
            if (!validEmail($_POST['registrar_campo_email']))
            {
                echo mensaje ("Este correo electrónico no es válido, por favor revise que este escrito correctamente o escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
            $datos['email'] = $_POST['registrar_campo_email'];
        }
        else
        {
            echo mensaje ("Por favor ingrese su email e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if (!empty($_POST['registrar_campo_usuario']))
        {
            if (strpos(trim($_POST['registrar_campo_usuario'])," "))
            {
                echo mensaje ("Este nombre de usuario no es válido (contiene espacios), por favor escoja otro e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
            $datos['usuario'] = trim($_POST['registrar_campo_usuario']);
        }
        else
        {
            echo mensaje ("Por favor ingrese su usuario e intente de nuevo",_M_ERROR);
            $flag_registroExitoso=false;
        }

        if (!empty($_POST['registrar_campo_clave']) && !empty($_POST['registrar_campo_clave_2']))
        {
            //Contraseñas iguales?
            if (trim($_POST['registrar_campo_clave']) == trim($_POST['registrar_campo_clave_2']))
            {
                //Tamaño adecuado?
                if(strlen($_POST['registrar_campo_clave']) >= 6 && strlen($_POST['registrar_campo_clave']) <= 100)
                {
                    $datos['clave'] = md5(trim($_POST['registrar_campo_clave']));
                }
                else
                {
                    echo mensaje ("La contraseña debe tener mas de 6 caracteres",_M_ERROR);
                    $flag_registroExitoso=false;
                }
            }
            else
            {
                echo mensaje ("Las contraseñas no coinciden, por favor ingrese su contraseña e intente de nuevo",_M_ERROR);
                $flag_registroExitoso=false;
            }
        }

        if (!empty($_POST['registrar_campo_nombre']))
        {
            $datos['nombre'] = $_POST['registrar_campo_nombre'];
        }
        if (isset($_POST['registrar_campo_telefono']))
        {
            $datos['telefono1'] = $_POST['registrar_campo_telefono'];
        }

        if ($flag_registroExitoso)
        {
            $datos["estado"] = _N_activo;
            $datos["nivel"] = $_POST['nivel'];
            $datos["nPubMax"] = $_POST['nPubMax'];
            $datos["nImgMax"] = $_POST['nImgMax'];
            $datos["nDiasVigencia"] = $_POST['nDiasVigencia'];
            $datos["ultimo_acceso"] = mysql_datetime();
            $datos["tienda"] = isset($_POST['tienda']) ? "1" : "0";
            if (db_actualizar_datos("ventas_usuarios",$datos,"id_usuario=".db_codex($_GET['usuario'])))
            {
                echo Mensaje("Usuario editado exitosamente");
                echo '<h1>Opciones</h1>';
                echo ui_href("","admin_usuarios_admin","Retornar a lista de usuarios");
            }
            else{
                echo Mensaje("Usuario NO PUDO ser editado o no se realizo ningun cambio",_M_ERROR);
            }
            if (isset($_POST['enviar_notificacion']))
            {
                email($datos['email'],sprintf("Estimado %s, sus datos han sido modificados en ".PROY_NOMBRE." por un Administrador",$datos['usuario']),"Sus datos de usuario  en ".PROY_NOMBRE." han sido modificados por un administrador.<br />\n\n<hr><br />\n<h1>Datos actuales</h1><br />\nCorreo electrónico: <strong>".$datos['email']."</strong><br />\nUsuario: <strong>".$datos['usuario']."</strong><br /><br />".PROY_NOMBRE."<br />".PROY_URL);
            }
            return;
        }
    }

$usuario = _F_usuario_datos($_GET['usuario']);
?>
<h1>Edición de usuario</h1>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" >
<table>
<tr><td>Correo electrónico</td><td><input name="registrar_campo_email" value="<?php echo $usuario['email']; ?>" /></tr>
<tr><td>Usuario</td><td><input name="registrar_campo_usuario" value="<?php echo $usuario['usuario']; ?>" /></tr>
<tr><td>Nombre</td><td><input name="registrar_campo_nombre" value="<?php echo $usuario['nombre']; ?>" /></tr>
<tr><td>Clave</td><td><input name="registrar_campo_clave" value="" /></tr>
<tr><td>Clave (confirmar)</td><td><input name="registrar_campo_clave_2" value="" /></tr>
<tr><td>Teléfono de contacto</td><td><input name="registrar_campo_telefono" value="<?php echo $usuario['telefono1']; ?>" /></tr>
<tr><td>Días de vigencia para publicaciones</td><td><input name="nDiasVigencia" value="<?php echo $usuario['nDiasVigencia']; ?>" /></tr>
<tr><td>Publicaciones máximas</td><td><input name="nPubMax" value="<?php echo $usuario['nPubMax']; ?>" /></tr>
<tr><td>Imagenes máximas</td><td><input name="nImgMax" value="<?php echo $usuario['nImgMax']; ?>" /></tr>
<tr>
<td>Nivel</td>
<td>
<select name="nivel">
    <option <? echo ($usuario['nivel'] == _N_administrador ? 'selected="selected"' : ""); ?> value="<?php echo _N_administrador; ?>">Administrador</option>
    <option <? echo ($usuario['nivel'] == _N_moderador ? 'selected="selected"' : ""); ?> value="<?php echo _N_moderador; ?>">Moderador</option>
    <option <? echo ($usuario['nivel'] == _N_vendedor? 'selected="selected"' : ""); ?> value="<?php echo _N_vendedor; ?>">Vendedor</option>
</select>
</td>
</tr>
</table>
<input name="tienda" value="1" <?php echo ($usuario['tienda'] == 1 ? 'checked="checked"' : "") ?> type="checkbox"/> Habilitar Tienda<br />
<input name="enviar_notificacion" value="Si" checked="checked" type="checkbox"/> Enviar notificación sobre este cambio al usuario<br />
<br />
<input name="modificar" value="Modificar" type="submit"/>
</form>
<?php
echo JS_onload('
$("#registrar_campo_email").keyup(function(){$("#registrar_respuesta_email").load("./registro_correo_existe:"+$("#registrar_campo_email").val());});
$("#registrar_campo_usuario").keyup(function(){$("#registrar_respuesta_usuario").load("./registro_usuario_existe:"+$("#registrar_campo_usuario").val());});
');
}

function INTERFAZ__ADMIN_USUARIOS_ELIMINAR()
{
    $c = "DELETE FROM ventas_usuarios WHERE id_usuario='".db_codex($_GET['usuario'])."'";
    db_consultar($c);
    if(db_afectados())
    {
        echo Mensaje("Usuario eliminado exitosamente");
    }
    else
    {
        echo Mensaje("Usuario no pudo ser eliminado",_M_ERROR);
    }

    echo '<h1>Opciones</h1>';
    echo ui_href("","admin_usuarios_admin","Retornar a lista de usuarios");
}

function INTERFAZ__ADMIN_USUARIOS()
{
    if(!empty($_GET['accion']))
    {
        switch($_GET['accion'])
        {
        case 'editar' :
            INTERFAZ__ADMIN_USUARIOS_EDITAR();
            return;
        break;
        case 'eliminar' :
            INTERFAZ__ADMIN_USUARIOS_ELIMINAR();
            return;
        break;
        }
    }

    $c = sprintf("SELECT `id_usuario`, `usuario`, `clave`, `nombre`, `email`, `telefono1`, `telefono2`, `avatar`, `notas`, CASE `nivel` WHEN %s THEN 'Administración' WHEN %s THEN 'Moderador' WHEN %s THEN 'Vendedor' ELSE `nivel` END AS 'nivel', `estado`, `contraclave`, `ultimo_acceso`, `registro`, `FLAGS`, `nDiasVigencia`, `nPubMax`, `nImgMax`, IF(tienda=1,'Si','No') as 'tienda' FROM ventas_usuarios WHERE 1",_N_administrador,_N_moderador,_N_vendedor);
    $r = db_consultar($c);
?>
<h1>Lista de usuarios</h1>
<table class="ancha">
    <tr><th>Usuario</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Nivel</th><th>Vigencia Pub.</th><th>Máx. Pubs.</th><th>Máx. Imgs.</th><th>Tienda</th><th>Acciones</th></tr>
<?php
    while ($f = mysql_fetch_array($r))
    {
        echo sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",$f['usuario'],$f['nombre'],$f['email'],$f['telefono1'],$f['nivel'],$f['nDiasVigencia']. " días",$f['nPubMax'],$f['nImgMax'],$f['tienda'],"[".ui_href("","admin_usuarios_admin?accion=editar&usuario=".$f['id_usuario'],"Editar")."] [".ui_href("","admin_usuarios_admin?accion=eliminar&usuario=".$f['id_usuario'],"Eliminar")."]");
    }
?>
</table>
<?php
echo '<h1>Opciones</h1>';
echo ui_href("","admin","Retornar a Administración");
}

function INTERFAZ__ADMIN_TIENDAS()
{
$c = "SELECT * FROM ventas_tienda";
$r = db_consultar($c);
if (mysql_numrows($r) == 0)
{
    echo Mensaje("No hay tiendas que mostrar");
    echo ui_href("","admin_tienda_agregar","¿Desea agregar una nueva tienda al sistema?");
    return;
}
?>
    <h1>Lista de tiendas</h1>
    <table class="ancha">
    <tbody>
    <tr><th width="10%">Id. Tienda</th><th width="15%">Usuario</th><th width="30%">URL</th><th width="35%">Titulo</th><th width="10%">Acción</th></tr>
    <?php
    while ($f = mysql_fetch_array($r))
    {
        echo sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",$f['id_tienda'],$f['id_usuario'],$f['tiendaURL'],$f['tiendaTitulo'],sprintf('[<a href="%s">E</a>][<a href="%s">X</a>]'));
    }
    ?>
    </tbody>
    <table>

<?php
}
function INTERFAZ__ADMIN_TIENDAS_AGREGAR()
{
if (isset($_POST['crear']))
{
    $flag_valido=true;
    $usuario = _F_usuario_datos($_POST['email'],'email');
    if (!is_array($usuario))
    {
        $flag_valido = false;
        echo Mensaje("abortado porque el usuario no existe");
    }
    if ($flag_valido)
    {
        $datos['id_usuario'] = $usuario['id_usuario'];
        $datos['tiendaURL'] = db_codex(@$_POST['url']);
        $datos['tiendaTitulo'] = db_codex(@$_POST['titulo']);
        $datos['tiendaSubtitulo'] = db_codex(@$_POST['subtitulo']);
        $datos['tiendaCSS'] = db_codex(@$_POST['css']);
        $r = db_agregar_datos('ventas_tienda',$datos);
        if ($r)
        {
            echo Mensaje("Agregado correctamente");
        }
    }
}
?>
<form action="./admin_tienda_agregar" method="post">
<table class="semi-ancha limpio">
    <tr><td class="fDer">Correo Usuario</td><td class="fInput"><input name="email" type="text" value=""/></td><tr>
    <tr><td class="fDer">URL</td><td class="fInput"><input name="url" type="text" value=""/></td><tr>
    <tr><td class="fDer">Titulo</td><td class="fInput"><input name="titulo" type="text" value=""/></td><tr>
    <tr><td class="fDer">Subtitulo</td><td class="fInput"><input name="subitulo" type="text" value=""/></td><tr>
    <tr><td class="fDer">CSS</td><td class="fInput"><textarea name="css"></textarea></td><tr>
</table>
<input type="submit" name="crear" value="Crear">
</form>
<?php
}
?>
