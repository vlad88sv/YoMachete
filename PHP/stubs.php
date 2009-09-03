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
    return "$.jGrowl('".addslashes($mensaje)."', {theme: 'aviso',life:5000})";
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

// http://www.sitepoint.com/article/hierarchical-data-database/
// Optimizado para 1 sub-nivel
function get_path($node,$url=true,$prefijo="categoria-") {

    $c = "SELECT b.id_categoria AS cPadre, a.id_categoria AS cHijo, b.nombre AS nPadre, a.nombre AS nHijo FROM ventas_categorias AS a LEFT JOIN ventas_categorias AS b ON b.id_categoria=a.padre WHERE a.id_categoria=$node";
    $r = db_consultar($c);
    $f = mysql_fetch_assoc($r);
    return get_path_format($f);
}
function get_path_format($f,$url=true,$prefijo="categoria-")
{
    if ($url)
    {
        $path = ui_href("",$prefijo.$f['cPadre']."-".SEO($f['nPadre']), $f['nPadre']) . " > " . ui_href("",$prefijo.$f['cHijo']."-".SEO($f['nHijo']), $f['nHijo']);
    }
    else
    {
        $path =  (!empty($f['nPadre']) ? $f['nPadre'] . " > " : "") . (!empty($f['nHijo']) ? $f['nHijo'] : "" );
    }
    return $path;
}

// Original: http://www.sitepoint.com/article/hierarchical-data-database/
// Optimizado con: http://blog.richardknop.com/2009/05/adjacency-list-model/
function ver_hijos($padre = "", $rubro="articulo", $nivel = 0, $profundidad = 5) {
    $AND_padre = $padre ? "AND padre='$padre'" : "";
    $AND_rubro = $rubro ? "AND t0.rubro='$rubro'" : "";

    // ---- CACHE ->
    if (isset($_SESSION['cache']['cmbCat'][$rubro][$padre]))
    return $_SESSION['cache']['cmbCat'][$rubro][$padre];
    // ---- CACHE ->

    $c = "SELECT t0.id_categoria AS id_padre, t0.nombre AS nombre_padre, t1.id_categoria AS id_categoria, t1.nombre as nombre_categoria FROM ventas_categorias AS t0 LEFT JOIN ventas_categorias AS t1 ON t1.padre = t0.id_categoria WHERE t1.padre IS NOT NULL $AND_rubro ORDER BY t0.nombre, t1.nombre";
    $r = db_consultar($c);
    $arbol = array();
    $Categoria = "";
    while ($f = mysql_fetch_array($r)) {
        if ($Categoria != $f['nombre_padre'])
        {
            $Categoria = $f['nombre_padre'];
            if (count($arbol) > 0) $arbol[] = '</optgroup>';
            $arbol[] = '<optgroup label="'.$f['nombre_padre'].'">';
        }
        else
        {
            $arbol[] = '<option value="'.$f['id_categoria'].'">'.$f['nombre_categoria'] . '</option>';
        }
    }
    $arbol[] = '</optgroup>';
    unset($_SESSION['cache']['cmbCat']);
    $_SESSION['cache']['cmbCat'][$rubro][$padre] = $arbol;
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

function VISTA_ListaPubs($Where="1",$OrderBy="",$tipo="normal",$SiVacio="No se encontraron publicaciones")
{
    $data = '';
    $JOIN_UBICACION = " LEFT JOIN ventas_categorias AS y ON y.id_categoria=z.id_categoria LEFT JOIN ventas_categorias AS x ON x.id_categoria=y.padre";
    $c = "SELECT x.nombre AS nPadre, x.id_categoria AS cPadre, y.nombre AS nHijo, z.id_categoria cHijo, z.id_publicacion, z.promocionado, (SELECT GROUP_CONCAT(tag ORDER BY tag ASC SEPARATOR ', ') FROM ventas_tag AS b WHERE id IN (SELECT id_tag FROM ventas_tag_uso AS c WHERE c.id_publicacion=z.id_publicacion)) AS tags, (SELECT id_img FROM ventas_imagenes as b WHERE b.id_publicacion = z.id_publicacion ORDER BY RAND() LIMIT 1) as imagen, IF(titulo='','<sin título>', titulo) AS titulo, descripcion_corta, z.id_usuario, z.precio FROM ventas_publicaciones AS z $JOIN_UBICACION WHERE 1 AND $Where $OrderBy";
    $r = db_consultar($c);
    if (mysql_num_rows($r) < 1)
    {
        return Mensaje($SiVacio, _M_INFO);
    }
    while ($f = mysql_fetch_array($r))
    {
    $titulo=$f['titulo'];
    $lnkTitulo="publicacion_".$f['id_publicacion']."_".SEO($f['titulo']);
    $precio=$f['precio'];
    $descripcion=substr($f['descripcion_corta'],0,300);
    $imagen="<a class=\"fancybox\" href=\"./imagen_".$f['imagen'].".jpg\" title=\"VISTA DE ARTÍCULO\"><img src=\"./imagen_".$f['imagen']."m\" alt=\"articulo\" /></a>";
    $ubicacion=get_path_format($f,($tipo != "previsualizacion"),($tipo == "tienda" ? "tienda_".$f['id_usuario']."_dpt-" : "categoria-"));
    $id_publicacion = $f['id_publicacion'];
    $tags = $f['tags'];
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
        $data .= '<td class="titulo"><a href="'.$lnkTitulo.'">'.htmlentities($titulo,ENT_QUOTES,'utf-8').'</a></td>';
    }
    else
    {
        $data .= '<td class="titulo"><a>'.htmlentities(strip_tags($titulo),ENT_QUOTES,'utf-8').'</a></td>';
    }
    $data .= '<td class="precio">$'.number_format($precio,2,".",",").'</td>';
    $data .= '</tr>'; // Titulo + Precio
    $data .= '<tr><td colspan="2" class="ubicacion">Ubicación: ' . $ubicacion.'</td></tr>';
    $data .= '<tr><td colspan="2" class="desc">' . htmlentities(strip_tags($descripcion),ENT_QUOTES,'utf-8').'</td></tr>';
    $data .= '<tr><td colspan="2"><strong>Etiquetas:</strong> ' . $tags.'</td></tr>';
    if (_F_usuario_cache('nivel') == _N_administrador && ($tipo != "previsualizacion"))
    {

        if ($f['promocionado'] == "1")
        {
            $PROMOCIONAR = ui_href("","admin_publicaciones_admin?operacion=promocionar&id_publicacion=$id_publicacion&id_usuario=$id_usuario&estado=0","DESPROMOCIONAR");
        }
        else
        {
            $PROMOCIONAR = ui_href("","admin_publicaciones_admin?operacion=promocionar&id_publicacion=$id_publicacion&id_usuario=$id_usuario&estado=1","PROMOCIONAR");
        }
        switch ($tipo)
        {
            case "admin":
                $data .= '<tr><td colspan="2" class="adm">['.$PROMOCIONAR.'] / ['.ui_href("","vender?ticket=$id_publicacion","EDITAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=rechazar&id_publicacion=$id_publicacion&id_usuario=$id_usuario","ELIMINAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=aprobar&id_publicacion=$id_publicacion&id_usuario=$id_usuario","APROBAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=retornar&id_publicacion=$id_publicacion&id_usuario=$id_usuario","RETORNAR").']</td></tr>';
            break;
            default:
            $data .= '<tr><td colspan="2" class="adm">['.$PROMOCIONAR.'] / ['.ui_href("","vender?ticket=$id_publicacion","EDITAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=rechazar&id_publicacion=$id_publicacion&id_usuario=$id_usuario","ELIMINAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=desaprobar&id_publicacion=$id_publicacion&id_usuario=$id_usuario","DESAPROBAR").'] / ['.ui_href("","admin_publicaciones_activacion?operacion=retornar&id_publicacion=$id_publicacion&id_usuario=$id_usuario","RETORNAR").']</td></tr>';
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
    $c = "SELECT id_categoria, id_publicacion, (SELECT id_img FROM ventas_imagenes as b WHERE b.id_publicacion = a.id_publicacion ORDER BY RAND() LIMIT 1) as imagen, titulo, precio FROM ventas_publicaciones AS a WHERE $Where ORDER BY promocionado DESC, RAND() $Limite";
    $r = db_consultar($c);
    if (mysql_num_rows($r) < 1)
    {
        return Mensaje($SiVacio, _M_INFO);
    }
    while ($f = mysql_fetch_array($r))
    {
    $titulo=$f['titulo'];
    $lnkTitulo="publicacion_".$f['id_publicacion']."_".SEO($f['titulo']);
    $precio=$f['precio'];
    $ubicacion=get_path($f['id_categoria']);
    $id_publicacion = $f['id_publicacion'];
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
    $datos["id_publicacion"] = NULL;
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
    return db_agregar_datos("ventas_publicaciones",$datos);
}
/*
 * DestruirTicket()
 * Destruye un ticket con sus imagenes y flags relacionados.
 * Retorna 0 si no pudo borrar nada por algun motivo, idealmente 1 si fue exitosa.
 * Mas de 1 significa que se pasió en todo :P
*/
function DestruirTicket($id_publicacion,$tipo=_A_temporal)
{
    $AND_usuario = $AND_tipo = '';
    if (_F_usuario_cache('nivel') != _N_administrador)
    {
        $id_usuario =  _F_usuario_cache('id_usuario');
        $AND_usuario = "AND id_usuario='$id_usuario'";
        $AND_tipo = "AND tipo="._A_temporal;
    }
    $id_publicacion = db_codex($id_publicacion);
    $c = "DELETE FROM ventas_publicaciones WHERE id_publicacion='$id_publicacion' $AND_usuario $AND_tipo LIMIT 1";
    $r = db_consultar($c);
    $ret = db_afectados();
    if ($ret)
    {
        //Borrar los archivos de imagenes relacionadas
        EliminarArchivosArr(ObtenerImagenesArr($id_publicacion));
        EliminarArchivosArr(ObtenerMiniImagenesArr($id_publicacion));
        $c = "DELETE FROM ventas_imagenes WHERE id_publicacion='$id_publicacion'";
        $r = db_consultar($c);

        // Borrar los flags relacionados
        $c = "DELETE FROM ventas_flags_pub WHERE id_publicacion='$id_publicacion'";
        $r = db_consultar($c);

        //Borrar los tags relacionados
        $c = "DELETE FROM ventas_tag_uso WHERE id_publicacion='$id_publicacion'";
        $r = db_consultar($c);
    }
    return $ret;
}

/*
 * ComprobarTicket()
 * Comprueba que un ticket corresponda al usuario especificado
 * Retorna true si corresponde, false de lo contrario
*/
function ComprobarTicket($id_publicacion)
{
    $id_publicacion = db_codex($id_publicacion);
    $AND_usuario = '';
    $AND_tipo = '';
    if (_F_usuario_cache('nivel') != _N_administrador)
    {
        $id_usuario =  _F_usuario_cache('id_usuario');
        $AND_usuario = "AND id_usuario='$id_usuario'";
        $AND_tipo = "AND tipo="._A_temporal;
    }

    $c = "SELECT id_publicacion FROM ventas_publicaciones WHERE id_publicacion='$id_publicacion' $AND_usuario $AND_tipo LIMIT 1";
    $r = db_consultar($c);
    return (mysql_num_rows($r) == 1);
}
/*
 * ObtenerImagenesArr()
 * Devuelve un array con las rutas (relativas) a las imagenes de cierto articulo
*/
function ObtenerImagenesArr($id_publicacion,$preDir="RCS/IMG/")
{
    $arrImg = array();
    $id_publicacion = db_codex($id_publicacion);
    $c = "SELECT id_img FROM ventas_imagenes WHERE id_publicacion='$id_publicacion'";
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
function ObtenerMiniImagenesArr($id_publicacion,$preDir="RCS/IMG/")
{
    $arrImg = array();
    $id_publicacion = db_codex($id_publicacion);
    $c = "SELECT id_img FROM ventas_imagenes WHERE id_publicacion='$id_publicacion'";
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
function CargarArchivos($input,$id_publicacion,$id_usuario)
{
    $id_publicacion = db_codex($id_publicacion);
    $id_usuario = db_codex($id_usuario);
    $usuario = _F_usuario_datos($id_usuario);
    if (!ComprobarTicket($id_publicacion))
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
        // Cuantas imagenes tiene?
        $NImgAct = db_contar('ventas_imagenes',"id_publicacion='$id_publicacion'");
        if ($NImgAct >= $usuario['nImgMax'])
        {
            echo Mensaje(sprintf("ha sobrepasado el límite aceptado de imagenes para Ud. (".$usuario['nImgMax']."). Descartando '%s'",$_FILES[$input]['name'][$llave]),_M_ERROR);
            continue;
        }
        $datos['id_img'] = NULL;
        $datos['id_publicacion'] = $id_publicacion;
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
            Imagen__Redimenzionar("RCS/IMG/$ret",600,468);
        }
    }
    return true;
}
/*
 * DescargarArchivos()
 * Descarga de la base de datos de imagenes los id_img especificados en
 * el array.
*/
function DescargarArchivos($input,$id_publicacion,$id_usuario)
{
    $id_publicacion = db_codex($id_publicacion);
    $id_usuario = db_codex($id_usuario);

    if (!ComprobarTicket($id_publicacion))
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
function Imagen__Redimenzionar($Origen,$Ancho,$Alto)
{
    return Imagen__CrearMiniatura($Origen,$Origen,$Ancho,$Alto,1);
}
/*
 * Imagen__CrearMiniatura()
 * Crea una versión reducida de la imagen en $Origen
*/
function Imagen__CrearMiniatura($Origen, $Destino, $Ancho = 100, $Alto = 100, $Aspecto=0)
{
    $image = new Imagick($Origen);
    $image->resizeImage($Ancho, $Alto, imagick::FILTER_LANCZOS, $Aspecto);
    return $image->writeImage($Destino);
}
function CargarDatos($id_publicacion,$id_usuario)
{
    // HTML purifier
    require_once("PHP/HTMLPurifier.standalone.php");
    $purifier = new HTMLPurifier();

    $id_publicacion = db_codex($id_publicacion);
    $id_usuario = db_codex($id_usuario);

    if (_F_usuario_cache('nivel') != _N_administrador)
    {
        $datos["tipo"] = _A_temporal;
        $datos["fecha_ini"] = mysql_datetime();
        $datos["fecha_fin"] = mysql_datetime();
    }
    $datos["id_categoria"] = _F_form_cache("id_categoria");
    // $datos["id_usuario"] = $id_usuario; // No usar.
    $datos["precio"] = _F_form_cache("precio");
    $datos["titulo"] = _F_form_cache("titulo");
    $datos["descripcion_corta"] = strip_html_tags(_F_form_cache("descripcion_corta"));
    $datos["descripcion"] = $purifier->purify(_F_form_cache("descripcion"));
    $ret = db_actualizar_datos("ventas_publicaciones",$datos,"id_publicacion='$id_publicacion'");
    unset($datos);

    // Tags

    // Procesamos los nuevos tags (eliminamos los espacios, las comas finales y hacemos array)
    // Nota: no evaluamos las comas finales con posibles espacios porque se eliminan con la primera pasada
    $tags = explode(",",preg_replace(array('/\s*/','/,$/'), '',@$_POST['tags']),5);

    // Insertamos los nuevos tags
    $val_tags = implode("'),('",$tags);
    db_consultar("INSERT IGNORE INTO ventas_tag (tag) VALUES('$val_tags')");

    // Ponemos los tags en referencia a la publicación actual

    // +Eliminados los tags de esta publicación primero+++++++++++++++++++++++++
    $val_tags = implode("','",$tags);
    $c = "DELETE FROM ventas_tag_uso WHERE id_publicacion='$id_publicacion'";
    $r = db_consultar($c);
    // +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    db_consultar("INSERT INTO ventas_tag_uso (id_tag,id_publicacion) SELECT id, $id_publicacion FROM ventas_tag WHERE tag IN ('$val_tags')");

    // Hay que eliminar los flags antes que nada.
    $c = "DELETE FROM ventas_flags_pub WHERE id_publicacion='$id_publicacion'";
    $r = db_consultar($c);

    $datos['id'] = NULL;
    $datos['id_publicacion'] = $id_publicacion;

    foreach(array("venta", "pago", "entrega") as $campo)
    {
        if (isset($_POST[$campo]) && is_array($_POST[$campo]))
        {
            foreach($_POST[$campo] as $llave => $valor)
            {
                $datos['id_flag'] = $valor;
                $datos['tipo'] = $campo;
                db_agregar_datos("ventas_flags_pub", $datos);
            }
        }
    }
}
function ObtenerDatos($id_publicacion)
{
    $id_publicacion = db_codex($id_publicacion);

    $c = "SELECT id_publicacion, tipo, fecha_ini, fecha_fin, id_categoria, (SELECT rubro FROM ventas_categorias AS b WHERE b.id_categoria=a.id_categoria) AS rubro, id_usuario, precio, titulo, descripcion_corta, descripcion FROM ventas_publicaciones AS a WHERE id_publicacion='$id_publicacion' LIMIT 1";
    $r = db_consultar($c);

    // Obtenemos los tags en el indice "tags".
    $ret = mysql_fetch_array($r);

    $tags_array = (mysql_fetch_array(db_consultar(sprintf("SELECT GROUP_CONCAT(tag ORDER BY tag ASC SEPARATOR ', ') AS tags FROM ventas_tag WHERE id IN (SELECT id_tag FROM ventas_tag_uso WHERE id_publicacion='%s') LIMIT 1",$ret['id_publicacion']))));
    $ret['tags'] = $tags_array['tags'];

    return $ret;
}
function ObtenerFlags($id_publicacion, $tipo)
{
    $id_publicacion = db_codex($id_publicacion);
    $tipo = db_codex($tipo);

    $c = "SELECT id_flag FROM ventas_flags_pub WHERE id_publicacion='$id_publicacion' AND tipo='$tipo'";
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
    $datos['fecha'] = mysql_datetime();
    $id_msj = db_agregar_datos("ventas_mensajes",$datos);
    unset($datos);

    $datos['id_usuario_dst'] = $Usuario;
    $datos['id_msj'] = $id_msj;
    $datos['leido'] = 0;
    $datos['eliminado'] = 0;
    $ret = db_agregar_datos("ventas_mensajes_dst",$datos);

    return true;
}

function ObtenerEstadisticasUsuario($id_usuario, $tipo)
{
    switch ($tipo)
    {
        case _EST_CANT_PUB:
            $c = "SELECT COUNT(*) AS cuenta FROM ventas_publicaciones WHERE id_usuario='$id_usuario'";
        break;
        case _EST_CANT_PUB_ACEPT:
            $c = "SELECT COUNT(*) AS cuenta FROM ventas_publicaciones WHERE id_usuario='$id_usuario' AND tipo='"._A_aceptado."'";
        break;
        case _EST_CANT_PUB_NOTEMP:
            $c = "SELECT COUNT(*) AS cuenta FROM ventas_publicaciones WHERE id_usuario='$id_usuario' AND tipo != '"._A_temporal."'";
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
function PromocionarPublicacion($id_publicacion, $promocionado="1")
{
    $id_publicacion = db_codex($id_publicacion);
    $promocionado = db_codex($promocionado);
    $datos["promocionado"] = $promocionado;
    $ret = db_actualizar_datos("ventas_publicaciones",$datos,"id_publicacion='$id_publicacion'");
    unset($datos);
    return db_afectados();
}
function SEO($URL){
    $URL = preg_replace("`\[.*\]`U","",$URL);
    $URL = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$URL);
    $URL = htmlentities($URL, ENT_COMPAT, 'utf-8');
    $URL = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $URL );
    $URL = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $URL);
    return strtolower(trim($URL, '-')).".html";
}
// http://www.webcheatsheet.com/PHP/get_current_page_url.php
// Obtiene la URL actual, $stripArgs determina si eliminar la parte dinamica de la URL
function curPageURL($stripArgs=false) {
 $pageURL = 'http';
 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 if ($stripArgs) {$pageURL = preg_replace("/\?.*/", "",$pageURL);}
 return $pageURL;
}

// Wrapper de envío de correo electrónico. HTML/utf-8
function email($para, $asunto, $mensaje)
{
    $headers = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n";
    $headers .= 'From: '. PROY_NOMBRE .' <'. PROY_MAIL_POSTMASTER . ">\r\n";
    $mensaje = sprintf('<html><head><title>%s</title></head><body>%s</body>',PROY_NOMBRE,$mensaje);
    return mail($para,'=?UTF-8?B?'.base64_encode($asunto).'?=',$mensaje,$headers);
}

function email_x_nivel($id_nivel, $asunto, $mensaje)
{
    $c = "SELECT email FROM ventas_usuarios WHERE nivel=$id_nivel";
    $r = db_consultar($c);
    while ($f = mysql_fetch_array($r)) {
        email($f['email'],PROY_NOMBRE." - $asunto",$mensaje);
    }
}

// http://www.v-nessa.net/2007/02/12/how-to-make-a-sexy-tag-cloud
function tag_cloud($r) {

$min_size = 10;
$max_size = 30;

while($row = mysql_fetch_array($r)) {
    $tags[$row['tag']] = $row['hits'];
}

// No hay tags
if (! isset($tags))
{
    return;
}

ksort($tags);

$minimum_count = min(array_values($tags));
$maximum_count = max(array_values($tags));
$spread = $maximum_count - $minimum_count;

if($spread == 0) {
$spread = 1;
}

$cloud_html = '';
$cloud_tags = array();

foreach ($tags as $tag => $count) {
$size = $min_size + ($count - $minimum_count)
* ($max_size - $min_size) / $spread;
$cloud_tags[] = '<a style="font-size: '. floor($size) . 'px'
. '" class="tag_cloud" href="'.curPageURL(true).'e+' . $tag
. '" title="\'' . $tag . '\' returned a count of ' . $count . '">'
. htmlspecialchars(stripslashes($tag)) . '</a>';
}
$cloud_html = join("\n", $cloud_tags) . "\n";
return $cloud_html;
}
?>
