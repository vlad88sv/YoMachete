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

function db_ui_checkboxes($guid, $tabla, $valor, $texto, $explicacion, $default = array())
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

function VISTA_ArticuloEnLista($titulo,$lnkTitulo,$precio,$descripcion,$imagen,$ubicacion,$tipo="normal")
{
    $data = '';
    $data .= '<table class="articulo">';
    $data .= '<tbody>';
    $data .= '<tr>';
    $data .= '<td class= "imagen">'.$imagen.'</td>';
    $data .= '<td class="detalle">';
    $data .= '<table class="titular">';
    $data .= '<tr>';
    $data .= '<td class="titulo"><a id="titulo" href="'.$lnkTitulo.'">'.htmlentities(strip_tags($titulo),ENT_QUOTES,'utf-8').'</a></td>';
    $data .= '<td class="precio">$'.number_format($precio,2,".",",").'</td>';
    $data .= '</tr>'; // Titulo + Precio
    $data .= '<tr><td colspan="2" class="ubicacion">Ubicación: ' . $ubicacion.'</td></tr>';
    $data .= '<tr><td colspan="2" class="desc">' . htmlentities(strip_tags($descripcion),ENT_QUOTES,'utf-8').'</td></tr>';
    $data .= '</table>';
    $data .= '</td>';
    $data .= '</tr>';
    $data .= '</tbody>';
    $data .= '</table>';
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
 * limitada a articulos con estado _A_temporal.
 * Esto como medida extra de seguridad ante algún exploit.
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
    $c = "DELETE FROM ventas_articulos WHERE id_usuario='$id_usuario' AND id_articulo='$id_articulo' AND tipo='"._A_temporal."'";
    $r = db_consultar($c);
    $ret = db_afectados();
    if ($ret)
    {
        //Borrar los archivos de imagenes relacionadas
        EliminarArchivosArr(ObtenerImagenesArr($id_articulo));
        EliminarArchivosArr(ObtenerMiniImagenesArr($id_articulo));
        $c = "DELETE FROM ventas_imagenes WHERE id_articulo='$id_articulo'";
        $r = db_consultar($c);
    }
    $c = "DELETE FROM ventas_flags_art WHERE id_articulo='$id_articulo'";
    $r = db_consultar($c);
    return $ret;
}
/*
 * ComprobarTicketTMP()
 * Comprueba que un ticket corresponda al usuario especificado
 * Retorna true si corresponde, false de lo contrario
*/
function ComprobarTicketTMP($id_usuario,$id_articulo)
{
    $id_articulo = db_codex($id_articulo);
    $id_usuario = db_codex($id_usuario);
    $c = "SELECT id_articulo FROM ventas_articulos WHERE id_usuario='$id_usuario' AND id_articulo='$id_articulo' AND tipo='"._A_temporal."' LIMIT 1";
    $r = db_consultar($c);
    return (mysql_num_rows($r) == 1);
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
/*
 * ObtenerMiniImagenesArr()
 * Devuelve un array con las rutas (relativas) a las imagenes miniaturas de cierto articulo
*/
function ObtenerMiniImagenesArr($id_articulo,$preDir="RCS/IMG/")
{
    $arrImg = array();
    $id_articulo = db_codex($id_articulo);
    $c = "SELECT id_img FROM ventas_imagenes WHERE id_articulo='$id_articulo'";
    $r = db_consultar($c);
    while ($f = mysql_fetch_array($r)) {
        if ( file_exists($preDir.$f[0]."m") )
        {
            $arrImg[] = $preDir.$f[0]."m";
        }
    }
    return $arrImg;
}
/*
 * EliminarArchivosArr()
 * Elimina los archivos especificados en un array
 * Retorna false en cualquier si hubo algun error
 * $PararEnPerdido detiene la función si el archivo no existe
*/
function EliminarArchivosArr($arrArchivos,$PararEnPerdido=false)
{
    if (!is_array($arrArchivos))
    {
        return false;
    }
    foreach ($arrArchivos as $archivo)
    {
        if (!unlink($archivo) && $PararEnPerdido)
        {
            return false;
        }
    }
    return true;
}
/*
 * CargarArchivos()
 * Carga desde $_FILES todo el array de archivos.
 * Obtiene un ticket de ventas_imagenes y lo ocupa como nombre
 * de archivo (idealmente es único).
*/
function CargarArchivos($input,$id_articulo,$id_usuario)
{
    $id_articulo = db_codex($id_articulo);
    $id_usuario = db_codex($id_usuario);

	if (!ComprobarTicketTMP($id_usuario,$id_articulo))
	{
		return false;
	}

    if (@!is_array($_FILES[$input]['tmp_name']))
    {
        //echo "No hay archivos!";
        return false;
    }
	if (!is_writable("RCS/IMG/"))
	{
		echo Mensaje("lo sentimos, parece que hay un problema técnico con la carga de imagenes",_M_ERROR);
		return false;
	}
    foreach ($_FILES[$input]['tmp_name'] as $llave => $valor)
    {
        if (!$valor) continue;
        $datos['id_img'] = NULL;
        $datos['id_articulo'] = $id_articulo;
        $datos['mime'] = $_FILES[$input]['type'][$llave];
        if (!in_array($datos['mime'],array("image/jpeg")))
        {
            echo "La imagen \"" . $_FILES[$input]['name'][$llave] . "\" no es un tipo de imagen admitida; la imagen ha sido descartada.<br />";
            continue;
        }
        $ret = db_agregar_datos("ventas_imagenes",$datos);
        if ($ret)
        {
            move_uploaded_file($valor, "RCS/IMG/$ret");
        }
    }
    return true;
}
/*
 * DescargarArchivos()
 * Descarga de la base de datos de imagenes los id_img especificados en
 * el array.
*/
function DescargarArchivos($input,$id_articulo,$id_usuario)
{
    $id_articulo = db_codex($id_articulo);
    $id_usuario = db_codex($id_usuario);

	if (!ComprobarTicketTMP($id_usuario,$id_articulo))
	{
		return false;
	}

    if (@!is_array($_POST[$input])) return false;

    foreach ($_POST[$input] as $llave => $valor)
    {
        $c = "DELETE FROM ventas_imagenes WHERE id_img='". db_codex($valor) . "' LIMIT 1";
        $r = db_consultar($c);
        if (db_afectados() == 1)
        {
            @unlink("RCS/IMG/$valor");
        }
    }

    return true;
}
/*
 * Imagen__CrearMiniatura()
 * Crea una versión reducida de la imagen en $Origen
*/
function Imagen__CrearMiniatura($Origen, $Destino, $Ancho = 100, $Alto = 100)
{
    $image = new Imagick($Origen); // $filepath is a path to a TIFF file
    $image->resizeImage($Ancho, $Alto, imagick::FILTER_LANCZOS, 1);
    return $image->writeImage($Destino);
}
function CargarDatos($id_articulo,$id_usuario)
{
    $id_articulo = db_codex($id_articulo);
    $id_usuario = db_codex($id_usuario);

    $datos["id_articulo"] = $id_articulo;
    $datos["tipo"] = _A_temporal;
    $datos["fecha_ini"] = mysql_datetime();
    $datos["fecha_fin"] = mysql_datetime();
    $datos["id_categoria"] = _F_form_cache("id_categoria");
    $datos["id_usuario"] = $id_usuario;
    $datos["precio"] = _F_form_cache("precio");
    $datos["titulo"] = _F_form_cache("titulo");
    $datos["descripcion_corta"] = _F_form_cache("descripcion_corta");
    $datos["descripcion"] = _F_form_cache("descripcion");
    $ret = db_reemplazar_datos("ventas_articulos",$datos);
    unset($datos);

    // Flags

    // Hay que eliminar los flags antes que nada.
    $c = "DELETE FROM ventas_flags_art WHERE id_articulo='$id_articulo'";
    $r = db_consultar($c);

    $datos['id'] = NULL;
    $datos['id_articulo'] = $id_articulo;

    foreach(array("flags_ventas", "flags_pago", "flags_entrega") as $campo)
    {
        if (isset($_POST[$campo]) && is_array($_POST[$campo]))
        {
            foreach($_POST[$campo] as $llave => $valor)
            {
                $datos['id_flag'] = $valor;
                $datos['id_tabla'] = $campo;
                db_agregar_datos("ventas_flags_art", $datos);
            }
        }
    }
}
function ObtenerDatos($id_articulo)
{
    $id_articulo = db_codex($id_articulo);

    $c = "SELECT * FROM ventas_articulos WHERE id_articulo='$id_articulo' LIMIT 1";
    $r = db_consultar($c);

    return mysql_fetch_array($r);
}
function ObtenerFlags($id_articulo, $id_tabla)
{
    $id_articulo = db_codex($id_articulo);
    $id_tabla = db_codex($id_tabla);

    $c = "SELECT id_flag FROM ventas_flags_art WHERE id_articulo='$id_articulo' AND id_tabla='$id_tabla'";
    $r = db_consultar($c);

    $arr = array();
    while ($f = mysql_fetch_array($r))
    {
        $arr[] = $f['id_flag'];
    }
    return $arr;
}
?>
