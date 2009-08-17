<?php
function CONTENIDO_BUSCAR()
{
    // Determinamos que tipo de busqueda desea
    
    $flag_modoAdv = isset($_POST['buscarAdv']);
    
    // Realizamos la busqueda basica primero, si escogio avanzada lo manejamos luego...
    
    /*
        La busqueda se prioritiza en el siguiente orden:
        1. Tags
        2. Titulo
        3. Sub-Titulo / Descripcion corta
        4. Descripcion
    */
    $AND_categoria = !empty($_POST['categoria_busqueda']) ? sprintf("AND id_categoria='%s'",db_codex($_POST['categoria_busqueda'])) : "";
    $WHERE = sprintf("MATCH (titulo,descripcion_corta,descripcion) AGAINST ('%s' IN BOOLEAN MODE) $AND_categoria",db_codex($_POST['busqueda']));
    echo VISTA_ListaPubs($WHERE);
}
?>