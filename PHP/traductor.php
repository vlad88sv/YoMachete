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
    case 'publicacion':
        require_once ("PHP/contenido.php");
        CONTENIDO_PUBLICACION();
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
        if (!empty($_GET['op']))
        {
            $flag_Abortar = false;
            $op =  db_codex($_GET['op']);
            $c = "SELECT id_img, id_articulo, mime FROM ventas_imagenes WHERE id_img='$op' LIMIT 1";
            $r = db_consultar($c);
            $f = mysql_fetch_array($r);

            // Se encontró la imagen en la base de datos?
            $flag_Abortar = (mysql_num_rows($r) != 1);

            if (!$flag_Abortar)
            {
                // Se encontró en la base de datos, pero estará en el disco?
                $archivo = "../RCS/IMG/" . $f['id_img'];
                if (file_exists($archivo))
                {
                    $TipoContenido = $f['mime'];
                }
                else
                {
                    $flag_Abortar = true;
                }
            }

            if (!$flag_Abortar && isset($_GET['miniatura']))
            {
                // Se encontró el archivo principal, y se solicitó una minuatura de el.
                // Ya se estableció el tipo de contenido.
                $archivo_m = "../RCS/IMG/" . $f['id_img'] . "m";

                // Comprobamos si existe la miniatura o si debemos crearla
                if (!file_exists($archivo_m))
                {
                    // Si no se puede crear la miniatura, abortamos
                    $flag_Abortar = !Imagen__CrearMiniatura($archivo,$archivo_m);
                }
                $archivo = $archivo_m;
            }

            if ($flag_Abortar)
            {
                $TipoContenido =  "image/jpeg";
                $archivo = "../IMG/i404.jpg";
            }

            // Mostramos lo que se pidio o el 404
            header("Content-Type: " . $TipoContenido);
            echo file_get_contents($archivo);
        }
    break;
    default:
    echo "Petición erronea: ". $_GET['peticion'] .". Abortando";

}
?>
