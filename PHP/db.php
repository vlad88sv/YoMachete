<?php
$db_link = NULL;
db_conectar(); // Iniciamos la conexión a la base de datos.

function db_conectar(){
    global $db_link;
    $db_link = @mysql_connect(db__host, db__usuario, db__clave) or die("Fue imposible conectarse a la base de datos, posiblemente no ha ejecutado el instalador (instalar.php) correctamente.<br /><hr />Detalles del error:<pre>" . mysql_error() . "</pre>");
    mysql_select_db(db__db, $db_link) or die("Imposible seleccionar la base de datos: ". mysql_error());
    mysql_query("set lc_time_names='es_ES'", $db_link);
}

function db_consultar($consulta){
    global $db_link;
    if ( !$db_link ) {
        db_conectar();
    }
    $resultado = @mysql_query($consulta, $db_link);
    if ( mysql_error($db_link) ) {
        echo '<pre>MySQL:' . mysql_error() . '</pre>';
    }
    return $resultado;
}
function db_codex($datos){
    global $db_link;
    if ( !$db_link ) {
        db_conectar();
    }
    return mysql_real_escape_string($datos, $db_link);
}
function db_afectados(){
    global $db_link;
    if ( $db_link ) {
        return mysql_affected_rows($db_link);
    }
    return -1;
}
function db_crear_tabla($tabla, $campos, $botarPrimero=false){
    $salida = "";
    if ( $botarPrimero ) {
        if ( db_consultar ("DROP TABLE IF EXISTS $tabla") ) {
            $salida .= "Tabla '$tabla' botada"."<br />";
        } else {
            $salida .= "Tabla '$tabla' no pudo ser botada"."<br />";
        }
    }
    if ( db_consultar ("CREATE TABLE IF NOT EXISTS $tabla ($campos)") ) {
        $salida .= "Tabla '$tabla' creada"."<br />";
        $c = "explain $tabla";
        $resultado = db_consultar($c);
        $salida .= db_ui_tabla($resultado,"",true,"¡oops!, ¡parece que no se creó!");
    } else {
        $salida .= "Tabla '$tabla' no pudo ser creada"."<br />";
    }
    return $salida;
}

function db_agregar_datos($tabla, $datos) {
    global $db_link;
    $campos = $valores = NULL;
    foreach ($datos as $clave => $valor) {
        //echo "clave: $clave; valor: $valor<br />\n";
        $arr_campos[]   = mysql_real_escape_string($clave);
        $arr_valores[]  = mysql_real_escape_string($valor);
    }
    $campos = implode (",", $arr_campos);
    $valores = "'".implode ("','", $arr_valores)."'";
    $c = "INSERT INTO $tabla ($campos) VALUES ($valores)";
    $resultado = db_consultar ($c);
    $id = @mysql_insert_id ($db_link);
    DEPURAR ($c, 0);
    return $id;
}

function db_reemplazar_datos($tabla, $datos) {
    global $db_link;
    $campos = $valores = NULL;
    foreach ($datos as $clave => $valor) {
        //echo "clave: $clave; valor: $valor<br />\n";
        $arr_campos[]   = mysql_real_escape_string($clave);
        $arr_valores[]  = mysql_real_escape_string($valor);
    }
    $campos = implode (",", $arr_campos);
    $valores = "'".implode ("','", $arr_valores)."'";
    $c = "REPLACE INTO $tabla ($campos) VALUES ($valores)";
    $resultado = db_consultar ($c);
    $id = @mysql_insert_id ($db_link);
    DEPURAR ($c, 0);
    return $id;
}

function db_actualizar_datos($tabla, $datos, $donde = "0") {
    global $db_link;
    $DATA = NULL;
    foreach ($datos as $clave => $valor) {
        $arr_DATA[] = mysql_real_escape_string($clave) . "='".mysql_real_escape_string($valor)."'";
    }
    $DATA = join(",",$arr_DATA);
    $c = "UPDATE $tabla SET $DATA WHERE $donde";
    $resultado = db_consultar ($c);
    $id = @mysql_insert_id ($db_link);
    DEPURAR ($c, 0);
    return $id;
}

function db_resultado($resultado, $campo, $posicion='0'){
    return @mysql_result($resultado, $posicion, $campo);
}

function db_fila_a_array($resultado, $posicion='0'){
 $_arr = NULL;
 $n_campos = mysql_num_fields($resultado);
 $r = mysql_fetch_row($resultado);
 for ($i = 0; $i < $n_campos; $i++) {
     $clave = mysql_field_name($resultado, $i);
     $valor = $r[$i];
     $_arr[$clave] = $valor;
 }
 return $_arr;
}

function db_ui_opciones($clave, $valor, $tabla, $cuales="", $orden="", $grupo_ui="", $seleccionada="") {
 $html = NULL;
 //La función es crear un combobox con name=id=$clave y value=$valor y HTML a partir de un SELECT $clave, $valor FROM $tabla
 $c = "SELECT $clave, $valor FROM $tabla $cuales $orden";
 DEPURAR ($c, 0);
 $resultado = db_consultar ($c);
 $n_campos = mysql_num_rows($resultado);
 if ( $grupo_ui ) {
    $html .= "<optgroup label='$grupo_ui'>";
 }
for ($i = 0; $i < $n_campos; $i++) {
  $t_clave = mysql_result($resultado, $i, $clave);
  $t_valor = mysql_result($resultado, $i, $valor);
  if ($t_clave == $seleccionada) {
      $selected = ' selected="selected"';
  } else {
      $selected = "";
  }
  $html .= '<option value="' . $t_clave . '"' . $selected . '>' . $t_valor . '</option>';
}
return $html;
}

function db_ui_lista($resultado)
{
 $html = NULL;
 $html .= "<table>";
 while ($r = mysql_fetch_row($resultado)) {
    foreach ($r as $column)
    {
    $html .= "<tr><td>$column</td></tr>";
    }
 }
  $html .= "</table>";
 return $html;
}

function db_ui_tabla($resultado, $CSS="", $Titulos=true, $NoMas = "No hay datos") {
 global $db_link;
 if ( !mysql_num_rows($resultado) ) {
    return $NoMas;
 }

 $table = "";
 $table .= "<table $CSS>\n";
 $noFields = mysql_num_fields($resultado);
 if ($Titulos)
 {
 $table .= "<tr>";
 for ($i = 0; $i < $noFields; $i++) {
 $field = mysql_field_name($resultado, $i);
 $table .= "<th>$field</th>\n";
 }
 $table .= "</tr>\n";
 }
 while ($r = mysql_fetch_row($resultado)) {
 $table .= "<tr>";
 foreach ($r as $column) {
 $table .= "<td>$column</td>";
 }
 $table .= "</tr>\n";
 }
 $table .= "</table>";
 return $table;
 }

function db_ui_tabla_vertical($resultado, $CSS="") {
 global $db_link;
 if ( !mysql_num_rows($resultado) ) {
    return "No se encontraron datos";
 }

 $table = "";
 $table .= "<table $CSS>\n\n";
 $noFields = mysql_num_fields($resultado);
 $r = mysql_fetch_row($resultado);

 for ($i = 0; $i < $noFields; $i++) {
 $field = mysql_field_name($resultado, $i);
 $table .= "<tr>";
 $table .= "<td>$field</td><td>".$r[$i]."</td>";
 $table .= "</tr>\n";
 }
 return $table;
 }

function db_t_where($clave, $comparador, $valor, $prefijo = "", $sufijo = ""){
    return " AND $clave $comparador '$prefijo".mysql_real_escape_string($valor)."$sufijo'";
}

//Obtiene TODA la tabla y dibuja la jerarquia.
//No utilizar con tablas muy grandes.
function db_ui_jerarquia($tabla, $padre, $dato)
{
$c = "SELECT $padre, $dato FROM $tabla";
$resultado = db_consultar($c);

}

function db_ui_checkboxes($guid, $tabla, $valor, $texto, $explicacion, $default = array(), $extra="")
{
    $c = "SELECT $valor, $texto, $explicacion FROM $tabla";
    $r = db_consultar($c);
    $html = '';
    if (is_array($default)) $arr = array_flip($default); else $arr = array();
    while ($row = mysql_fetch_array($r)) {
        $strDefault = isset($arr[$row[$valor]]) ? "checked=\"checked\"" : "";
        $html .= "<span title='".$row[$explicacion]."'>" . ui_input($guid, $row[$valor], "checkbox","","",$strDefault. " " . $extra) . $row[$texto] . "</span><br />";
    }
    return $html;
}

?>
