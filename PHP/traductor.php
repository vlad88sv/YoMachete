<?php
require_once ("vital.php");
if (!isset($_GET['peticion']))
{
    echo "Petición faltante. Abortando";
    return;
}
switch ($_GET['peticion'])
{
    case 'iniciar':
        require_once("PHP/inicio.php");
        CONTENIDO_INICIAR_SESION();
    break;
    case 'finalizar':
        _F_sesion_cerrar();
    break;
    case 'registrar':
        require_once("PHP/registrar.php");
        CONTENIDO_REGISTRAR();
        break;
    case 'vender':
    case 'vender_servicio':
    case 'vender_articulo':
        require_once("PHP/vender.php");
        CONTENIDO_VENDER();
    break;
    case 'vip':
        require_once ("PHP/contenido.php");
        CONTENIDO_VIP();
    break;
    case 'registro_usuarios_correo':
        if (isset($_GET['op']))
        {
            $email=db_codex(trim($_GET['op']));
            if ($email)
            {
                echo _F_usuario_existe($email,"email") ? "este correo ya esta registrado" : "";
            }
        }
    break;
    case 'registro_usuarios_usuario':
        if (isset($_GET['op']))
        {
            $usuario=db_codex(trim($_GET['op']));
            if ($usuario)
            {
                echo _F_usuario_existe($usuario) ? "este nombre de usuario ya esta registrado" : "";
            }
        }
    break;
    case 'admin':
        $op = empty($_GET['op']) ? "" : $_GET['op'];
        require_once ("PHP/admin.php");
        CONTENIDO_ADMIN();
    break;
    case 'imagen':
        $op = empty($_GET['op']) ? "" : $_GET['op'];

    break;
    default:
    echo "Petición erronea: ". $_GET['peticion'] .". Abortando";

}
?>
