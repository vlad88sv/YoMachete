<?php
// Este archivo se encargará de levantar la sesión y la conexión a la base de datos.
error_reporting             (E_STRICT | E_ALL);
ob_start                    ("ob_gzhandler");
setlocale                   (LC_ALL, 'es_AR.UTF-8', 'es_ES.UTF-8');
date_default_timezone_set   ('America/El_Salvador');
ini_set                     ('session.gc_maxlifetime', '600');
$base = dirname(__FILE__);
if(!file_exists("$base/secreto.php")) die("ERROR #1");
require_once ("$base/secreto.php"); // Datos para la conexión a la base de datos
require_once ("$base/ui.php"); // Generación de HTML: Comboboxes, etc.
require_once ("$base/const.php"); // Constantes
require_once ("$base/sesion.php"); // Sesión
require_once ("$base/db.php"); // Conexión hacia la base de datos [depende de secreto.php]
require_once ("$base/db-stubs.php"); // Generación de objetos UI desde la base de datos [depende de ui.php]
require_once ("$base/db-ui.php"); // Generación de objetos UI desde la base de datos [depende de ui.php]
require_once ("$base/stubs.php"); // Funciones varias
require_once ("$base/publicaciones.php"); // Gestión de publicaciones
require_once ("$base/usuario.php"); // Gestión de usuarios
require_once ("$base/todosv.com.php");  // Envío de SMS
function DEPURAR($s,$f=0){if($f||isset($_GET['depurar'])){echo '<pre>'.$s.'</pre><br />';}}
function Mensaje ($texto, $tipo=_M_INFO){
    switch ( $tipo ) {
        case _M_INFO:
        $id = "info";
        break;
        case _M_ERROR:
        $id = "error";
        break;
        case _M_NOTA:
        $id = "nota";
        break;
        default:
        return 'Error: no se definió el $tipo de mensaje';
    }

    return "<div id=\"$id\">".$texto."</div>";

}

?>
