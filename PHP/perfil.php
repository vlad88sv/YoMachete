<?php
function CONTENIDO_PERFIL()
{
    if (!_autenticado())
    {
        echo "Necesitas iniciar sesión para poder <b>ver perfiles</b>.<br />";
        require_once("PHP/inicio.php");
        CONTENIDO_INICIAR_SESION();
        return;
    }

    $usuario = empty($_GET['id']) ? $_SESSION['cache_datos_usuario'] : _F_usuario_datos($_GET['id']);
    if(!is_array($usuario))
    {
        echo Mensaje("Lo sentimos, al parecer este usuario ya no forma parte de este sitio",_M_ERROR);
        return;
    }

    echo "<p>" .  ui_href("","tienda_".(empty($usuario['tienda']) ? $usuario['id_usuario'] : $usuario['tienda']), "Ver tienda")."</p>";
    echo "<p>Nombre de usuario: " .  @$usuario['usuario']."</p>";
    echo "<p>e-mail de contacto: ". '<img src="imagen_c_'.$usuario['email'].'" />'." (o enviele un ". ui_href("","mp?id=".$usuario['id_usuario'],"Mensaje Privado").")</p>";
    echo "<p>Registrado desde: " .  @$usuario['registro']."</p>";
    echo "<p>Ultima actividad: " . fechatiempo_desde_mysql_datetime(@$usuario['ultimo_acceso'])."</p>";
    $usuario['cantidad_publicaciones'] = ObtenerEstadisticasUsuario(@$usuario['id_usuario'],_EST_CANT_PUB_ACEPT);
    echo "<p>Cantidad de publicaciones: " . $usuario['cantidad_publicaciones']."</p>";


}
?>
