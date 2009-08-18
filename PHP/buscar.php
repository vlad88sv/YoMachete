<?php
function CONTENIDO_BUSCAR()
{
    //Le mostramos la "busqueda avanzada"
    ?>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="get">
    <h2>Búsqueda avanzada</h2>
    <table class="ancha limpio">
        <tr><th><h1>Características del artículo</h1></th><th><h1>Formas de pago admitidas</h1></th><th><h1>Formas de entrega admitidas</h1><th></tr>
        <tr><td><?PHP echo db_ui_checkboxes("flags_ventas[]", "ventas_flags_ventas", "id_flag", "nombrep", "descripcion",""); ?></td><td><?PHP echo db_ui_checkboxes("flags_entrega[]", "ventas_flags_entrega", "id_flag", "nombrep", "descripcion",""); ?></td><td><?PHP echo db_ui_checkboxes("flags_pago[]", "ventas_flags_pago", "id_flag", "nombrep", "descripcion",""); ?></td></tr>
    </table>
    Precio entre $<input type="text" name="pmin" value="0.00"> y $<input type="text" name="pmax" value="99999.99"> (dolares USA | USD).<br />
    Antiguedad de la publicación entre <input type="text" name="amin" value="0"> días y <input type="text" name="amax" value="999"> días.<br />
    <input name="ba" type="hidden" value="1" />
    <br />
    <input type="submit" value="Refinar búsqueda" />
    </form>
    <hr />
    <?php
    //Será que ya envío la búsqueda?
    
    $flag_busq_valida = isset($_GET['b']) && isset($_GET['c']);
    
    if ($flag_busq_valida)
    {
    /*
        La busqueda se prioritiza en el siguiente orden:
        1. Tags
        2. Titulo
        3. Sub-Titulo / Descripcion corta
        4. Descripcion
    */
    $AND_categoria = !empty($_POST['c']) ? sprintf("AND id_categoria='%s'",db_codex($_GET['c'])) : "";
    $WHERE = sprintf("MATCH (titulo,descripcion_corta,descripcion) AGAINST ('%s' IN BOOLEAN MODE) $AND_categoria",db_codex($_GET['b']));
    echo '<h1>Resultados</h1>';
    echo VISTA_ListaPubs($WHERE);
    }
    
    echo '<h1>Opciones</h1>'; 
    echo ui_href("","./","Retornar a pagina principal");
}
?>