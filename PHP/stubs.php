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
    $c = "SELECT telefono1 FROM ventas_usuarios WHERE nivel="._N_administrador;
    $r = db_consultar($c);
    while ($f = mysql_fetch_array($r)) {
        tsv_sms_enviar($f[0],$mensaje,'Ventas');
    }
}

function despachar_notificaciones_email($mensaje){
    $c = "SELECT email FROM ventas_usuarios WHERE nivel="._N_administrador;
    $r = db_consultar($c);
    while ($f = mysql_fetch_array($r)) {
        mail($f[0],$mensaje,"No responda a este mensaje. Gracias");
    }
}

// http://www.sitepoint.com/article/hierarchical-data-database/
function get_path($node,$url=true) {
   // look up the parent of this node
   $result = db_consultar("SELECT id_categoria, padre, nombre FROM ventas_categorias WHERE id_categoria='$node'");
   $row = mysql_fetch_array($result);

   // save the path in this array
   $path = array();

   // only continue if this $node isn't the root node (that's the node with no parent)
   if ($row['padre']!='') {
       // the last part of the path to $node, is the name of the parent of $node
        if ($url)
        {
            $path[] = ui_href("principal_ubicacion_link[]","categoria-".$row['id_categoria']."-".urlencode($row['nombre']), $row['nombre']);
        }
        else
        {
            $path[] =  $row['nombre'];
        }
       // we should add the path to the parent of this node to the path
       $path = array_merge(get_path($row['padre'],$url), $path);
   }
   return $path;
}

// http://www.sitepoint.com/article/hierarchical-data-database/
function ver_hijos($padre, $rubro="articulo", $nivel = 0, $profundidad = 5) {
$r = db_consultar("SELECT id_categoria, padre, nombre FROM ventas_categorias WHERE padre='$padre' AND rubro='$rubro' ORDER BY nombre ASC");
$arbol = array();
while ($row = mysql_fetch_array($r)) {
    if ($nivel == 0)
    {
        $arbol[] = '<optgroup label="'.$row['nombre'].'">';
    }
    else
    {
        $arbol[] = '<option value="'.$row['id_categoria'].'">' . str_repeat('·',$nivel-1).$row['nombre'] . '</option>';
    }
    if ($nivel+1 < $profundidad)
    {
        $arbol = array_merge($arbol, ver_hijos($row['id_categoria'], $rubro, $nivel+1));
    }
}
return $arbol;
}

function db_ui_checkboxes($guid, $tabla, $valor, $texto, $explicacion, &$default = array())
{
    $c = "SELECT $valor, $texto, $explicacion FROM $tabla";
    $r = db_consultar($c);
    $html = '';
    if (is_array($default)) $arr = array_flip($default); else $arr = array();
    while ($row = mysql_fetch_array($r)) {
        $strDefault = isset($arr[$row[$valor]]) ? "checked=\"checked\"" : "";
        $html .= "<span title='".$row[$explicacion]."'>" . ui_input($guid, $row[$valor], "checkbox","","",$strDefault) . $row[$texto] . "</span><br />";
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


function _F_form_cache($campo)
{
    if (!isset($_POST))
        return '';
    if (array_key_exists($campo, $_POST))
    {
        return $_POST[$campo];
    }
    else
    {
        return '';
    }
}

// http://www.linuxjournal.com/article/9585
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

function VISTA_ArticuloEnLista($titulo,$precio,$descripcion,$imagen,$tipo="normal")
{
    $data = '';
    $data .= '<div class="art">';
        $data .= '<div class="art_izq">';
            $data .= '<div class="art_imagen">'.$imagen.'</div>';
        $data .= '</div>';
        $data .= '<div class="art_der">';
            $data .= '<div class="art_titulo"><span class="art_precio">$'.number_format($precio,2,".",",").'</span>'.$titulo.'<div style="clear:both"></div></div>';
            $data .= '<div class="art_desc">'.$descripcion.'</div>';
        $data .= '</div>';
        $data .= '<div style="clear:both"></div>';
    $data .= '</div>';
    return $data;
}

/*
 * ObtenerTicketTMP()
 * Función encargada de conseguir un número (ticket) para almacenar
 * temporalmente los datos de la previsualización de las ventas
 *
 * Retorna 0 si no pudo obtener uno por cualquier motivo
*/
function ObtenerTicketTMP($id_usuario)
{
    if (!_F_usuario_existe($id_usuario,"id_usuario"))
    {
        return 0;
    }
    $datos["id_articulo"] = NULL;
    $datos["tipo"] = _A_temporal;
    $datos["fecha_ini"] = mysql_datetime();
    $datos["fecha_fin"] = mysql_datetime();
    $datos["id_categoria"] = "0";
    $datos["id_usuario"] = $id_usuario;
    $datos["precio"] = "0";
    $datos["titulo"] = "";
    $datos["descripcion_corta"] = "";
    $datos["descripcion"] = "";
    return db_agregar_datos("ventas_articulos",$datos);
}
/*
 * DestruirTicketTMP()
 * Destruye un ticket temporal e imagenes relacionados.
 * La diferencia con DestruirArticulo() radica en que esta funcion esta
 * limitada a articulos con estado _A_temporal y no toca las tablas de
 * FLAGS. Esto como medida extra de seguridad ante algún exploit.
 * Retorna 0 si no pudo borrar nada por algun motivo
*/
function DestruirTicketTMP($id_usuario, $id_articulo)
{
    if (!_F_usuario_existe($id_usuario,"id_usuario"))
    {
        return 0;
    }
    $id_articulo = db_codex($id_articulo);
    $id_usuario = db_codex($id_usuario);
    $c = "DELETE FROM ventas_articulos WHERE id_usuario='id_usuario' AND id_articulo='$id_articulo' AND tipo='"._A_temporal."'";
    $r = db_consultar($c);
    $ret = db_afectados();
    if ($ret)
    {
        //Borrar los archivos de imagenes relacionadas [To do: Borrar archivos]
        $c = "DELETE FROM ventas_imagenes WHERE id_articulo='$id_articulo'";
        $r = db_consultar($c);
    }
    return $ret;
}
/*
 * ObtenerImagenesArr()
 * Devuelve un array con las rutas (relativas) a las imagenes de cierto articulo
*/
function ObtenerImagenesArr($id_articulo,$preDir="RCS/IMG/")
{
    $arrImg = array();
    $id_articulo = db_codex($id_articulo);
    $c = "SELECT id_img FROM ventas_imagenes WHERE id_articulo='$id_articulo'";
    $r = db_consultar($c);
    while ($f = mysql_fetch_array($r)) {
        $arrImg[] = $preDir.$f[0];
    }
    return $arrImg;
}
?>
