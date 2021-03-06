<?php
function CONTENIDO_BUSCAR()
{
    //Será que ya envío la búsqueda?
    $flag_busq_valida = isset($_GET['b']);

    // Será que es una búsqueda avanzada?
    $flag_busq_adv = isset($_GET['ba']);

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

    // Construimos el Query de la búsqueda
    $AND_categoria = !empty($_POST['c']) ? sprintf("AND id_categoria='%s'",db_codex($_GET['c'])) : "";
    $cadenaBusq = db_codex($_GET['b']);

    // Construimos la parte avanzada si fué solicitada
    if($flag_busq_adv)
    {
        $ANDs = array();
        $LUGARES = array();

        // Lugares
        if (!empty($_GET['inc_titulo'])) $LUGARES[] = 'z.titulo';
        if (!empty($_GET['inc_sub'])) $LUGARES[] = 'z.descripcion_corta';
        if (!empty($_GET['inc_desc'])) $LUGARES[] = 'z.descripcion';

        $AND_match1 = (count($LUGARES) > 0) ? sprintf("AND MATCH (%s) AGAINST ('%s' IN BOOLEAN MODE)",implode(",",$LUGARES),$cadenaBusq) : "";

        // Tags
        $AND_match2 = (!empty($_GET['inc_etiq'])) ? sprintf("AND id_publicacion IN (SELECT id_publicacion FROM ventas_tag_uso WHERE id_tag IN (SELECT ventas_tag.id FROM ventas_tag WHERE MATCH(ventas_tag.tag) AGAINST('%s' IN BOOLEAN MODE)))",$cadenaBusq) : "";

        // Precios

        if (!empty($_GET['pmin']) && is_numeric($_GET['pmin'])) $ANDs[] = sprintf("AND precio>='%s'",db_codex($_GET['pmin']));
        if (!empty($_GET['pmax']) && is_numeric($_GET['pmax'])) $ANDs[] = sprintf("AND precio<='%s'",db_codex($_GET['pmax']));

        // Horas
        if (!empty($_GET['inc_tiempo']) && isset($_GET['tp']) && isset($_GET['tpv']) && is_numeric($_GET['tp']) && is_numeric($_GET['tpv']) )
        {
            // Determinamos el operador
            switch($_GET['tp'])
            {
                case 3:
                    $operacion = "((DATEDIFF(fecha_fin,CURDATE())) * 24) > '%s'"; break;
                case 2:
                    $operacion = "((DATEDIFF(fecha_fin,CURDATE())) * 24) < '%s'"; break;
                case 1:
                    $operacion = "((DATEDIFF(CURDATE(),fecha_ini)) * 24) < '%s'"; break;
                case 0:
                default:
                    $operacion = "((DATEDIFF(CURDATE(),fecha_ini)) * 24) > '%s'"; break;
            }
            $ANDs[] = sprintf("AND $operacion",db_codex($_GET['tpv']));
        }

        // Opciones
        //-Características del artículo
        if (isset($_GET['f']) && is_array($_GET['f']) && isset($_GET['mf']))
        {
        sort($_GET['f']);
        switch ($_GET['mf'])
        {
        // Cualquier coincidencia
        case 2:
            $ANDs[] = sprintf("AND id_publicacion IN (SELECT id_publicacion FROM ventas_flags_pub WHERE id_flag IN ('%s'))", implode("','",db_codex($_GET['f'])));
            break;
        // Coincidencia parcial
        case 1:
            $ANDs[] = sprintf("AND id_publicacion IN (SELECT id_publicacion FROM ventas_flags_pub WHERE id_flag IN ('%s') GROUP BY id_publicacion HAVING COUNT(DISTINCT id_flag) = %s)", implode("','",db_codex($_GET['f'])), count($_GET['f']));
            break;
        // Coincidencia exacta
        case 0:
        default:
            $ANDs[] = sprintf("AND id_publicacion IN (SELECT id_publicacion FROM ventas_flags_pub group BY id_publicacion HAVING GROUP_CONCAT(id_flag ORDER BY id_flag ASC) ='%s');", implode(",",db_codex($_GET['f'])));
        }
        }
        $WHERE = sprintf("fecha_fin >= CURDATE() $AND_match1 $AND_match2 $AND_categoria %s",implode(" ",$ANDs));
    }
    else
    {
        // Búsqueda simple
        $WHERE = sprintf("fecha_fin >= CURDATE() AND (MATCH (z.titulo,z.descripcion_corta,z.descripcion) AGAINST ('%s' IN BOOLEAN MODE) $AND_categoria OR id_publicacion IN (SELECT id_publicacion FROM ventas_tag_uso WHERE id_tag IN (SELECT ventas_tag.id FROM ventas_tag WHERE MATCH(ventas_tag.tag) AGAINST('%s' IN BOOLEAN MODE))))",$cadenaBusq,$cadenaBusq);
    }
    $WHERE .= "  AND z.tipo IN ("._A_aceptado . ","._A_promocionado.") AND fecha_fin >= CURDATE()";

    echo '<h1>Resultados</h1>';
    echo VISTA_ListaPubs($WHERE);
    }
    //Le mostramos la "busqueda avanzada"
    ?>
    <h1>Refinar búsqueda</h1>
    <div id="buscador">
    <form action="buscar" method="get">
    <fieldset>
    <legend>Texto a búscar y campos de búsqueda</legend>
        <input id="busqueda" name="b" type="text" value="<?php echo @$_GET["b"]; ?>" />
        <?php echo ui_combobox("c",'<option value="">Todas las categorias</option>'.join("",ver_hijos("","")),@$_GET["c"]); ?>
        <br />
        Incluir
        <input type="checkbox" name="inc_titulo" value="1" <?php echo (isset($_GET['inc_titulo']) || !$flag_busq_adv ? 'checked="checked"' : ""); ?> /> Título
        <input type="checkbox" name="inc_sub" value="1" <?php echo (isset($_GET['inc_sub']) || !$flag_busq_adv ? 'checked="checked"' : ""); ?> /> Sub-título
        <input type="checkbox" name="inc_desc" value="1" <?php echo (isset($_GET['inc_desc']) ? 'checked="checked"' : ""); ?> /> Descripción
        <input type="checkbox" name="inc_etiq" value="1" <?php echo (isset($_GET['inc_etiq']) || !$flag_busq_adv ? 'checked="checked"' : ""); ?> /> Etiquetas
    </fieldset>
    <fieldset>
    <legend>Opciones de la publicación</legend>
    <table class="ancha limpio marginado-y col1-3">
        <tr><th><h2>Características del artículo</h2></th><th><h2>Formas de pago admitidas</h2></th><th><h2>Formas de entrega admitidas</h2><th></tr>
        <tr>
            <td>
            <?php echo db_ui_checkboxes("f[]", "ventas_flags", "id_flag", "nombrep", "descripcion",@$_GET['f'],"","tipo='venta'"); ?>
            </td>
            <td>
            <?php echo db_ui_checkboxes("f[]", "ventas_flags", "id_flag", "nombrep", "descripcion",@$_GET['f'],"","tipo='entrega'"); ?>
            </td>
            <td>
            <?php echo db_ui_checkboxes("f[]", "ventas_flags", "id_flag", "nombrep", "descripcion",@$_GET['f'],"","tipo='pago'"); ?>
            </td>
        </tr>
    </table>
    Método: <input title="las publicaciones encontradas cumplen exactamente con todos los criterios y ninguna más o ninguno menos" name="mf" type="radio" value="0" <?php echo (isset($_GET['mf']) && ($_GET['mf'] == 0) ? 'checked="checked"' : ""); ?> > Coincidencia exacta <input title="las publicaciones encontradas contienen al menos todos los criterios seleccionados" name="mf" type="radio" value="1" <?php echo (!isset($_GET['mf']) || ($_GET['mf'] == 1) || !$flag_busq_adv ? 'checked="checked"' : ""); ?> > Coincidencia parcial <input title="las publicaciones encontradas cumplen con al menos 1 criterio" name="mf" type="radio" value="2" <?php echo (isset($_GET['mf']) && ($_GET['mf'] == 2) ? 'checked="checked"' : ""); ?> > Cualquier coincidencia
    </fieldset>
    <fieldset>
    <legend>Restricciones de precio y tiempo de publicación</legend>
    Precio entre $<input type="text" name="pmin" value="<?php echo (isset($_GET['pmin']) ? @$_GET['pmin'] : '0.00'); ?>"> y $<input type="text" name="pmax" value="<?php echo (isset($_GET['pmax']) ? @$_GET['pmax'] : '99999.00'); ?>"> (dolares USA | USD).<br />
    <input type="checkbox" name="inc_tiempo" value="1" <?php echo (isset($_GET['inc_tiempo']) ? 'checked="checked"' : ""); ?> />
    <?php echo ui_combobox("tp",'<option value="0">Publicado hace no menos de</option><option value="1">Publicado hace no más de</option><option value="2">Terminando en menos de</option><option value="3">Terminando en más de</option>',@$_GET['tp']); echo ui_combobox("tpv", '<option value="1">1 hora</option><option value="2">2 horas</option><option value="6">6 horas</option><option value="12">12 horas</option><option value="24">1 día</option><option value="48">2 días</option><option value="72">3 días</option><option value="96">4 días</option><option value="120">5 días</option><option value="144">6 días</option><option value="168">7 días</option>',@$_GET['tpv']); ?><br />
    </input>
    </fieldset>
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
