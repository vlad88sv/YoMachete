<?php
function CONTENIDO_BUSCAR()
{
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
        5. Todo lo demas / x orden
    */
    
    // Será que es una búsqueda avanzada?
    $flag_busq_adv = isset($_GET['ba']);
    
    // Construimos el Query de la búsqueda
    $AND_categoria = !empty($_POST['c']) ? sprintf("AND id_categoria='%s'",db_codex($_GET['c'])) : "";
    
    // Construimos la parte avanzada si fué solicitada
    if($flag_busq_adv)
    {
        $AND_adv ="AND precio>='%s' AND preccio<='%s' fecha_fin";
    }
    $cadenaBusq = db_codex($_GET['b']);
    $WHERE = sprintf("fecha_fin >= CURDATE() AND (MATCH (titulo,descripcion_corta,descripcion) AGAINST ('%s' IN BOOLEAN MODE) $AND_categoria OR id_publicacion IN (SELECT id_publicacion FROM ventas_tag_uso WHERE id_tag IN (SELECT ventas_tag.id FROM ventas_tag WHERE MATCH(ventas_tag.tag) AGAINST('%s' IN BOOLEAN MODE))))",$cadenaBusq,$cadenaBusq);
    echo '<h1>Resultados</h1>';
    echo VISTA_ListaPubs($WHERE);
    }
    //Le mostramos la "busqueda avanzada"
    ?>
    <h1>Refinar búsqueda</h1>
    <div id="buscador">
    <form action="buscar" method="get">
        <input id="busqueda" name="b" type="text" value="<?php echo @$_GET["b"]; ?>" />
        <?php echo ui_combobox("c",'<option value="">Todas las categorias</option>'.join("",ver_hijos("","")),@$_GET["c"]); ?>
    <table class="ancha limpio marginado-y col1-3">
        <tr><th><h2>Características del artículo</h2></th><th><h2>Formas de pago admitidas</h2></th><th><h2>Formas de entrega admitidas</h2><th></tr>
        <tr><td><?PHP echo db_ui_checkboxes("v[]", "ventas_flags_ventas", "id_flag", "nombrep", "descripcion",@$_GET['v']); ?></td><td><?PHP echo db_ui_checkboxes("e[]", "ventas_flags_entrega", "id_flag", "nombrep", "descripcion",@$_GET['e']); ?></td><td><?PHP echo db_ui_checkboxes("p[]", "ventas_flags_pago", "id_flag", "nombrep", "descripcion",@$_GET['p']); ?></td></tr>
    </table>
    Precio entre $<input type="text" name="pmin" value="0.00"> y $<input type="text" name="pmax" value="99999.99"> (dolares USA | USD).<br />
    <select name="tp"><option value="0">Publicado hace no menos de</option><option value="1">Publicado hace no más de</option><option value="2">Terminando en menos de</option><option value="3">Terminando en más de</option></select><select name="tpv"><option value="1">1 hora</option><option value="2">2 horas</option><option value="6">6 horas</option><option value="12">12 horas</option><option value="24">1 día</option><option value="48">2 días</option><option value="72">3 días</option><option value="96">4 días</option><option value="120">5 días</option><option value="144">6 días</option><option value="168">7 días</option></select><br />
    <input name="ba" type="hidden" value="1" />
    <br />
    <input type="submit" value="Realizar búsqueda refinada" />
    </form>
    </div>
    <h1>Opciones</h1>
    <a href="./">Retornar a pagina principal</a>
<?php
}
?>