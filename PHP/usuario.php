<?php
$tablaUsuarios = 'ventas_usuarios';

function _F_usuario_existe($usuario,$campo="usuario"){
    global $tablaUsuarios;
    $usuario = db_codex($usuario);
    $resultado = db_consultar ("SELECT id_usuario FROM $tablaUsuarios where $campo='$usuario'");
if ($resultado) {
    $n_filas = mysql_num_rows($resultado);
    if ( $n_filas == 1 ) {
        return true;
    } else {
        return false;
    }
} else {
    return false;
}
}

function _F_usuario_datos($id_usuario,$campo){
    global $tablaUsuarios;
    $c = "SELECT * FROM $tablaUsuarios WHERE $campo='$id_usuario'";
    DEPURAR ($c, 0);
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

function _F_usuario_acceder($email, $clave){
    global $tablaUsuarios;
    $email = db_codex (trim($email));
    $clave = md5 (trim($clave));

    $c = "SELECT * FROM $tablaUsuarios WHERE email='$email' AND clave='$clave' AND estado!="._N_esp_activacion;
    DEPURAR($c,0);
    $resultado = db_consultar ($c);
    if ($resultado) {
    $n_filas = mysql_num_rows ($resultado);
    if ( $n_filas == 1 ) {
        $_SESSION['autenticado'] = true;
        $_SESSION['cache_datos_usuario'] = db_fila_a_array($resultado);
        $c = "UPDATE $tablaUsuarios SET ultimo_acceso='".mysql_datetime()."' WHERE email='$email'";
        $resultado = db_consultar ($c);
        return 1;
    } else {
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
