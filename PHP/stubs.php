<?php

// Genera un simple contenedor JavaScript
function JS($script){
    return "<script type='text/javascript'>".$script."</script>";
}

// Genera un simple contenedor JavaScript para JQuery ON DOM READY
function JS_onload($script){
    return "<script type='text/javascript'>$(document).ready(function(){".$script."});</script>";
}

// Genera un pequeño GROWL
function JS_growl($mensaje){
    return JS_onload("$.jGrowl('".addslashes($mensaje)."', {theme: 'aviso',life:5000})");
}

//Timestamp to MYSQL DATETIME
function mysql_datetime($tiempo = 'now'){
    return date( 'Y-m-d H:i:s',strtotime($tiempo) );
}

//Timestamp to MYSQL DATE
function mysql_date($tiempo = 'now'){
    return date( 'Y-m-d',strtotime($tiempo) );
}

//Timestamp to MYSQL TIME
function mysql_time($tiempo = 'now'){
    return date( 'H:i:s',strtotime($tiempo) );
}

//MYSQL DATETIME a fecha normal (sin hora)
function fecha_desde_mysql_datetime($tiempo){
    return date( 'd-m-Y',strtotime($tiempo) );
}

//MYSQL DATETIME a hora (sin fecha)
function tiempo_desde_mysql_datetime($tiempo){
    return date( 'H:i:s',strtotime($tiempo) );
}

//MYSQL DATETIME a fecha y hora
function fechatiempo_desde_mysql_datetime($tiempo){
    return date( 'd-m-Y H:i:s',strtotime($tiempo) );
}

function suerte($una, $dos){
    if (rand(0,1)) {
        return $una;
    } else {
        return $dos;
    }
}

function despachar_notificaciones_sms($mensaje){
    tsv_sms_enviar('77521234',$mensaje,'Ventas');
}

function despachar_notificaciones_email($mensaje){

}

// http://www.sitepoint.com/article/hierarchical-data-database/
function get_path($node) {
   // look up the parent of this node
   $result = db_consultar("SELECT padre,  CONCAT('<a href=\"categoria_' , id_categoria, '_', nombre , '\">', nombre, '</a>') AS nombre FROM ventas_categorias WHERE id_categoria='$node'");
   $row = mysql_fetch_array($result);

   // save the path in this array
   $path = array();

   // only continue if this $node isn't the root node (that's the node with no parent)
   if ($row['padre']!='') {
       // the last part of the path to $node, is the name of the parent of $node
       $path[] = $row['nombre'];

       // we should add the path to the parent of this node to the path
       $path = array_merge(get_path($row['padre']), $path);
   }
   return $path;
}

// http://www.sitepoint.com/article/hierarchical-data-database/
function ver_hijos($padre, $nivel = 0, $profundidad = 5) {
$r = db_consultar("SELECT id_categoria, padre, nombre FROM ventas_categorias WHERE padre='$padre'");
$arbol = array();
while ($row = mysql_fetch_array($r)) {
    $arbol[] = '<option value="'.$row['id_categoria'].'">' . str_repeat('··',$nivel).$row['nombre'] . '</option>';
    if ($nivel+1 < $profundidad)
    {
        $arbol = array_merge($arbol, ver_hijos($row['id_categoria'], $nivel+1));
    }
}
return $arbol;
}

function db_ui_checkboxes($guid, $tabla, $valor, $texto, $explicacion)
{
    $c = "SELECT $valor, $texto, $explicacion FROM $tabla";
    $r = db_consultar($c);
    $html = '';
    while ($row = mysql_fetch_array($r)) {
        $html .= "<span title='".$row[$explicacion]."'>" . ui_input($guid, $row[$valor], "checkbox") . $row[$texto] . "</span><br />";
    }
    return $html;
}

function Truncar($cadena, $largo) {
    if (strlen($cadena) > $largo) {
        $cadena = substr($cadena,0,($largo -3));
            $cadena .= '...';
    }
    return $cadena;
}
?>
