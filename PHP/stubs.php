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

//MYSQL DATETIME a fecha y hora (mas humana)
function fechatiempo_h_desde_mysql_datetime($tiempo){
    if (!$tiempo)
    {
        return "";
    }
    return date( 'd-m-Y h:i:sa',strtotime($tiempo) );
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

function VISTA_ArticuloEnLista($Where="1",$OrderBy="",$tipo="normal",$SiVacio="No se encontraron articulos")
{
    $data = '';
    $c = "SELECT id_categoria, id_articulo, promocionado, (SELECT id_img FROM ventas_imagenes as b WHERE b.id_articulo = a.id_articulo ORDER BY RAND() LIMIT 1) as imagen, titulo, descripcion_corta, id_usuario, precio FROM ventas_articulos AS a WHERE 1 AND $Where $OrderBy";
    $r = db_consultar($c);
    if (mysql_num_rows($r) < 1)
    {
        return Mensaje($SiVacio, _M_INFO);
    }
    while ($f = mysql_fetch_array($r))
    {
    $titulo=$f['titulo'];
    $lnkTitulo="publicacion_".$f['id_articulo']."_".urlencode($f['titulo']);
    $precio=$f['precio'];
    $descripcion=substr($f['descripcion_corta'],0,200);
    $imagen="<a href=\"./imagen_".$f['imagen']."\" target=\"_blank\" rel=\"lightbox\" title=\"VISTA DE ARTÍCULO\"><img src=\"./imagen_".$f['imagen']."m\" /></a>";
    $ubicacion=join(" > ", get_path($f['id_categoria'],($tipo != "previsualizacion")));
    $id_articulo = $f['id_articulo'];
    $id_usuario = $f['id_usuario'];
    // ->
    $promocionado = ($f['promocionado'] == "1") ? " promocionado" : "";
    $data .= '<table class="articulo'.$promocionado.'">';
    $data .= '<tbody>';
    $data .= '<tr>';
    $data .= '<td class= "imagen">'.$imagen.'</td>';
    $data .= '<td class="detalle">';
    $data .= '<table class="titular">';
    $data .= '<tr>';
    if ($tipo != "previsualizacion")
    {
        $data .= '<td class="titulo"><a id="titulo" href="'.$lnkTitulo.'">'.htmlentities(strip_tags($titulo),ENT_QUOTES,'utf-8').'</a></td>';
    }
    else
    {
        $data .= '<td class="titulo"><a id="titulo">'.htmlentities(strip_tags($titulo),ENT_QUOTES,'utf-8').'</a></td>';
    }
    $data .= '<td class="precio">$'.number_format($precio,2,".",",").'</td>';
    $data .= '</tr>'; // Titulo + Precio
    $data .= '<tr><td colspan="2" class="ubicacion">Ubicación: ' . $ubicacion.'</td></tr>';
    $data .= '<tr><td colspan="2" class="desc">' . htmlentities(strip_tags($descripcion),ENT_QUOTES,'utf-8').'</td></tr>';
    if (_F_usuario_cache('nivel') == _N_administrador && ($tipo != "previsualizacion"))
    {

        if ($f['promocionado'] == "1")
        {
            $PROMOCIONAR = ui_href("","admin_publicaciones_admin?operacion=promocionar&id_articulo=$id_articulo&id_usuario=$id_usuario&estado=0","DESPROMOCIONAR");
        }
        else
        {
            $PROMOCIONAR = ui_href("","admin_publicaciones_admin?operacion=promocionar&id_articulo=$id_articulo&id_usuario=$id_usuario&estado=1","PROMOCIONAR");
        }
        if ($tipo != "admin")
        {
            $data .= '<tr><td colspan="2" class="adm">['.$PROMOCIONAR.'] / ['.ui_href("","vender?ticket=$id_articulo","EDITAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=eliminar&id_articulo=$id_articulo&id_usuario=$id_usuario","ELIMINAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=desaprobar&id_articulo=$id_articulo&id_usuario=$id_usuario","DESAPROBAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=retornar&id_articulo=$id_articulo&id_usuario=$id_usuario","RETORNAR").']</td></tr>';
        }
        else
        {
            $data .= '<tr><td colspan="2" class="adm">['.$PROMOCIONAR.'] / ['.ui_href("","vender?ticket=$id_articulo","EDITAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=eliminar&id_articulo=$id_articulo&id_usuario=$id_usuario","ELIMINAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=aprobar&id_articulo=$id_articulo&id_usuario=$id_usuario","APROBAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=retornar&id_articulo=$id_articulo&id_usuario=$id_usuario","RETORNAR").']</td></tr>';
        }
    }
    $data .= '</table>';
    $data .= '</td>';
    $data .= '</tr>';
    $data .= '</tbody>';
    $data .= '</table>';
    $data .= '<br />';
    }
    return $data;
}

function VISTA_ArticuloEnBarra($Where="1",$Limite="LIMIT 6", $SiVacio="No se encontraron publicaciones")
{
    $data = '';
    $c = "SELECT id_categoria, id_articulo, (SELECT id_img FROM ventas_imagenes as b WHERE b.id_articulo = a.id_articulo ORDER BY RAND() LIMIT 1) as imagen, titulo, precio FROM ventas_articulos AS a WHERE $Where ORDER BY promocionado DESC, RAND() $Limite";
    $r = db_consultar($c);
    if (mysql_num_rows($r) < 1)
    {
        return Mensaje($SiVacio, _M_INFO);
    }
    while ($f = mysql_fetch_array($r))
    {
    $titulo=$f['titulo'];
    $lnkTitulo="publicacion_".$f['id_articulo']."_".urlencode($f['titulo']);
    $precio=$f['precio'];
    $ubicacion=join(" > ", get_path($f['id_categoria']));
    $id_articulo = $f['id_articulo'];
    // ->
    $data .= "<div style='display:inline-block;margin:0 10px;'><a href=\"./$lnkTitulo\"><img src=\"./imagen_".$f['imagen']."m\" /></a></div>";
    }
    $data .= "<div style=\"clear:both\"></div>";
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
    $datos["id_categoria"] = @$_POST['id_categoria'];
    $datos["id_usuario"] = $id_usuario;
    $datos["precio"] = "0";
    $datos["titulo"] = "";
    $datos["descripcion_corta"] = "";
    $datos["descripcion"] = "";
    $datos["promocionado"] = "0";
    return db_agregar_datos("ventas_articulos",$datos);
}
/*
 * DestruirTicket()
 * Destruye un ticket con sus imagenes y flags relacionados.
 * Retorna 0 si no pudo borrar nada por algun motivo, idealmente 1 si fue exitosa.
 * Mas de 1 significa que se pasió en todo :P
*/
function DestruirTicket($id_articulo,$tipo=_A_temporal)
{
    $AND_usuario = '';
    if (_F_usuario_cache('nivel') != _N_administrador)
    {
        $id_usuario =  _F_usuario_cache('id_usuario');
        $AND_usuario = "AND id_usuario='$id_usuario'";
    }
    $id_articulo = db_codex($id_articulo);
    $c = "DELETE FROM ventas_articulos WHERE id_articulo='$id_articulo' $AND_usuario AND tipo='".$tipo."' LIMIT 1";
    $r = db_consultar($c);
    $ret = db_afectados();
    if ($ret)
    {
        //Borrar los archivos de imagenes relacionadas
        EliminarArchivosArr(ObtenerImagenesArr($id_articulo));
        EliminarArchivosArr(ObtenerMiniImagenesArr($id_articulo));
        $c = "DELETE FROM ventas_imagenes WHERE id_articulo='$id_articulo'";
        $r = db_consultar($c);
        $c = "DELETE FROM ventas_flags_art WHERE id_articulo='$id_articulo'";
        $r = db_consultar($c);
    }
    return $ret;
}

/*
 * ComprobarTicket()
 * Comprueba que un ticket corresponda al usuario especificado
 * Retorna true si corresponde, false de lo contrario
*/
function ComprobarTicket($id_articulo)
{
    $id_articulo = db_codex($id_articulo);
    $AND_usuario = '';
    $AND_tipo = '';
    if (_F_usuario_cache('nivel') != _N_administrador)
    {
        $id_usuario =  _F_usuario_cache('id_usuario');
        $AND_usuario = "AND id_usuario='$id_usuario'";
        $AND_tipo = "AND tipo="._A_temporal;
    }

    $c = "SELECT id_articulo FROM ventas_articulos WHERE id_articulo='$id_articulo' $AND_usuario $AND_tipo LIMIT 1";
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

    if (!ComprobarTicket($id_articulo))
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
        if (!in_array($datos['mime'],array("image/jpeg","image/png")))
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

    if (!ComprobarTicket($id_articulo))
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
    $image = new Imagick($Origen);
    $image->resizeImage($Ancho, $Alto, imagick::FILTER_LANCZOS, 0);
    return $image->writeImage($Destino);
}
function CargarDatos($id_articulo,$id_usuario)
{
    $id_articulo = db_codex($id_articulo);
    $id_usuario = db_codex($id_usuario);

    $datos["tipo"] = _A_temporal;
    $datos["fecha_ini"] = mysql_datetime();
    $datos["fecha_fin"] = mysql_datetime();
    $datos["id_categoria"] = _F_form_cache("id_categoria");
    // $datos["id_usuario"] = $id_usuario; // No usar.
    $datos["precio"] = _F_form_cache("precio");
    $datos["titulo"] = _F_form_cache("titulo");
    $datos["descripcion_corta"] = _F_form_cache("descripcion_corta");
    $datos["descripcion"] = _F_form_cache("descripcion");
    $ret = db_actualizar_datos("ventas_articulos",$datos,"id_articulo='$id_articulo'");
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

    $c = "SELECT id_articulo, tipo, fecha_ini, fecha_fin, id_categoria, (SELECT rubro FROM ventas_categorias AS b WHERE b.id_categoria=a.id_categoria) AS rubro, id_usuario, precio, titulo, descripcion_corta, descripcion FROM ventas_articulos AS a WHERE id_articulo='$id_articulo' LIMIT 1";
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

/*
 * EnviarNota()
 * Envia un mensaje de diferentes posibles tipos a uno o mas destinatarios
 * Devuelve 1 en exito y 0 en error
*/
function EnviarNota($Mensaje,$Usuario=NULL,$Tipo=_M_INFO,$Contexto=_MC_broadcast)
{
    // Solo los administradores pueden enviar mensajes a TODOS los usuarios.
    if (_F_usuario_cache('nivel') != _N_administrador && !$Usuario)
    {
        return 1;
    }
    // Solo los administradores pueden enviar mensajes "BroadCast"
    if (_F_usuario_cache('nivel') != _N_administrador && $Contexto=_MC_broadcast)
    {
        return 1;
    }
    $datos['id_usuario_rmt'] = _F_usuario_cache('id_usuario');
    $datos['mensaje'] = $Mensaje;
    $datos['tipo'] = $Tipo;
    $datos['contexto'] = $Contexto;
    $datos['id_usuario_dst'] = $Usuario;
    $ret = db_agregar_datos("ventas_mensajes",$datos);
    return db_afectados();
}

function ObtenerEstadisticasUsuario($id_usuario, $tipo)
{
    switch ($tipo)
    {
        case _EST_CANT_PUB:
            $c = "SELECT COUNT(*) AS cuenta FROM ventas_articulos WHERE id_usuario='$id_usuario'";
        break;
        case _EST_CANT_PUB_ACEPT:
            $c = "SELECT COUNT(*) AS cuenta FROM ventas_articulos WHERE id_usuario='$id_usuario' AND tipo='"._A_aceptado."'";
        break;
        default:
        return "#ERROR# constante _EST_ '$tipo' no registrada";
    }
    $r = db_consultar($c);
    $f = mysql_fetch_array($r);
    return @$f['cuenta'];
}
/**
 * Remove HTML tags, including invisible text such as style and
 * script code, and embedded objects.  Add line breaks around
 * block-level tags to prevent word joining after tag removal.
 * URL:http://nadeausoftware.com/articles/2007/09/php_tip_how_strip_html_tags_web_page
 */
function strip_html_tags( $text )
{
    $text = preg_replace(
        array(
          // Remove invisible content
            '@<head[^>]*?>.*?</head>@siu',
            '@<style[^>]*?>.*?</style>@siu',
            '@<script[^>]*?.*?</script>@siu',
            '@<object[^>]*?.*?</object>@siu',
            '@<embed[^>]*?.*?</embed>@siu',
            '@<applet[^>]*?.*?</applet>@siu',
            '@<noframes[^>]*?.*?</noframes>@siu',
            '@<noscript[^>]*?.*?</noscript>@siu',
            '@<noembed[^>]*?.*?</noembed>@siu',
          // Add line breaks before and after blocks
            '@</?((address)|(blockquote)|(center)|(del))@iu',
            '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
            '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
            '@</?((table)|(th)|(td)|(caption))@iu',
            '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
            '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
            '@</?((frameset)|(frame)|(iframe))@iu',
        ),
        array(
            ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
            "\n\$0", "\n\$0",
        ),
        $text );
    return strip_tags( $text );
}
function PromocionarPublicacion($id_articulo, $promocionado="1")
{
    $id_articulo = db_codex($id_articulo);
    $promocionado = db_codex($promocionado);
    $datos["promocionado"] = $promocionado;
    $ret = db_actualizar_datos("ventas_articulos",$datos,"id_articulo='$id_articulo'");
    unset($datos);
    return db_afectados();
}
?>
