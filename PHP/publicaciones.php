<?php
// Aprueba el articulo y le agrega los días de vigencia (por ajuste de usuario) a partir de la fecha actual.
function Publicacion_Aprobar($id_publicacion)
{
    $id_usuario =  db_obtener('ventas_publicaciones','id_usuario',"id_publicacion=$id_publicacion");
    $DiasDeVigencia = db_obtener('ventas_usuarios','nDiasVigencia',"id_usuario=$id_usuario");
    $c = "UPDATE ventas_publicaciones SET tipo="._A_aceptado.", fecha_fin=date_add(CURDATE(), INTERVAL $DiasDeVigencia DAY) WHERE id_publicacion='$id_publicacion' AND id_usuario='$id_usuario' LIMIT 1";
    $r = db_consultar($c);
    return @db_afectados();
}
?>
