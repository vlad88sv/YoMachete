<?php
$tablaUsuarios = 'ventas_usuarios';

function _F_usuario_existe($usuario,$campo="usuario"){
    global $tablaUsuarios;
    $usuario = db_codex($usuario);
    $resultado = db_consultar ("SELECT id_usuario FROM $tablaUsuarios where $campo='$usuario'");
    if ($resultado) {
        if ( mysql_num_rows($resultado) == 1 )
        {
            return true;
        }
        else
        {
            $url = "http://www.svcommunity.org/forum/enlace.php?e=$usuario";
            $SVC = @file_get_contents($url);
            return (strstr($SVC,'<?xml version="1.0" encoding="UTF-8"?>'));
        }
    }
}

function _F_usuario_datos($id_usuario,$campo="id_usuario"){
    global $tablaUsuarios;
    $c = "SELECT * FROM $tablaUsuarios WHERE $campo='$id_usuario'";
    $resultado = db_consultar ($c);
    return (mysql_num_rows($resultado) > 0) ? db_fila_a_array($resultado) : false;
}

function _F_usuario_agregar($datos){
    global $tablaUsuarios;
    if ( !_F_usuario_existe($datos['usuario']) ){
        return db_agregar_datos ($tablaUsuarios, $datos);
    } else {
        return false;
    }
}

function _F_usuario_acceder($email, $clave,$enlazar=true){
    global $tablaUsuarios;
    $email = db_codex (trim($email));
    $clave =db_codex (trim($clave));

    $c = "SELECT * FROM $tablaUsuarios WHERE (LOWER(email)=LOWER('$email') OR LOWER(usuario)=LOWER('$email')) AND clave=SHA1(CONCAT(LOWER(usuario),'$clave')) AND estado!="._N_esp_activacion;
    DEPURAR($c,0);
    $resultado = db_consultar ($c);
    if ($resultado) {
    $n_filas = mysql_num_rows ($resultado);
    if ( $n_filas == 1 ) {
        $_SESSION['autenticado'] = true;
        $_SESSION['cache_datos_usuario'] = db_fila_a_array($resultado);
        $c = "UPDATE $tablaUsuarios SET ultimo_acceso=NOW() WHERE id_usuario="._F_usuario_cache('id_usuario');
        $resultado = db_consultar ($c);
        return 1;
    } else {
        if ($enlazar)
        {
            // 30/09/2009
            /*
             Con la integración de enlace.php en svcommunity.org, intentaremos
             verificar si el usuario existe ahí y crear la cuenta acá.
             Si no existe ni en SVC entonces fallar silenciosamente.
            */
            $url = "http://www.svcommunity.org/forum/enlace.php?m=$email&p=$clave";
            $SVC = @file_get_contents($url);
            if (strstr($SVC,'<?xml version="1.0" encoding="UTF-8"?>'))
            {
                $XML = new SimpleXMLElement($SVC);
                $datos["estado"] = _N_activo;
                $datos["nivel"] = _N_vendedor;
                $datos["ultimo_acceso"] = mysql_datetime();
                $datos["registro"]=date( 'Y-m-d H:i:s',(double) $XML->date_registered);
                $datos["usuario"]=$XML->member_name;
                $datos["nombre"]=$XML->real_name;
                $datos["email"]=$XML->email_address;
                $datos["clave"]=$XML->passwd;

                db_agregar_datos("ventas_usuarios",$datos);
                echo "DATOS IMPORTADOS<br />";
                return _F_usuario_acceder($email, $clave,false);
            }
            else
            {
                echo $SVC;
            }
        }
        unset ($_SESSION['autenticado']);
        unset ($_SESSION['id_usuario']);
        return -1;
    }
    } else {
        unset ($_SESSION['autenticado']);
        unset ($_SESSION['id_usuario']);
        echo "Error general al autenticar!"."<br />";
        return 0;
    }
}

function _F_usuario_cache($campo){
    if ( isset($_SESSION) && array_key_exists('cache_datos_usuario', $_SESSION) ) {
        if ( array_key_exists ($campo, $_SESSION['cache_datos_usuario']) ) {
            return $_SESSION['cache_datos_usuario'][$campo];
        }else{
            return NULL;
        }
    }else{
        return NULL;
    }
}

function _autenticado()
{
    return (isset($_SESSION['autenticado']) && $_SESSION['autenticado'] = true);
}
?>
