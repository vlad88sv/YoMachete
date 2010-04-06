<?php
// Aprueba el articulo y le agrega los días de vigencia (por ajuste de usuario) a partir de la fecha actual.
function Publicacion_Aprobar($id_publicacion)
{
    $id_usuario =  db_obtener('ventas_publicaciones','id_usuario',"id_publicacion=$id_publicacion");
    $DiasDeVigencia = db_obtener('ventas_usuarios','nDiasVigencia',"id_usuario=$id_usuario");
    $c = "UPDATE ventas_publicaciones SET tipo="._A_aceptado.", fecha_fin=date_add(CURDATE(), INTERVAL $DiasDeVigencia DAY) WHERE id_publicacion='$id_publicacion' AND id_usuario='$id_usuario' LIMIT 1";
    $r = db_consultar($c);
    $db_afectados_buffer = db_afectados();
    if ($db_afectados_buffer > 0)
    {
        require_once('PHP/anunciadores.php');
        $c = "SELECT id_publicacion, titulo FROM ventas_publicaciones WHERE id_publicacion=$id_publicacion";
        $r = db_consultar($c);
        $f = mysql_fetch_assoc($r);
        tweet('Nueva publicacion: '.$f['titulo'].' | http://www.yomachete.com/clasificados-en-el-salvador-vendo-'.$f['id_publicacion']."_".SEO($f['titulo']));
    }
    return $db_afectados_buffer;
}
?>
