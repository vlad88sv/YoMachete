<?php
function CONTENIDO_VENDER()
{
    if (!S_iniciado())
    {
        echo "Necesitas iniciar sesión para poder <b>publicar</b> y <b>vender</b>.<br />";
        require_once("PHP/inicio.php");
        CONTENIDO_INICIAR_SESION();
        return;
    }
    echo "<form action=\"vender\" method=\"POST\">";
    echo "<b>Nota:</b> Esta utilizando una cuenta gratuita, actualicese a una cuenta de ".ui_href("vender_vip","vip","Vendedor Distinguido","",'target="_blank"')." y disfrute de las ventajas!";
    echo "<h2>Paso 1. Selección de categoría para el artículo</h2>";
    echo "<span class='explicacion'>Ubique su árticulo en la categoría que consideres apropiada.</span><br />";
    echo "Mi árticulo corresponde a la siguiente categoría<br />".ui_combobox("vender_paso1_categoria",join("",ver_hijos("")))."<br />";
    echo "<h2>Paso 2. Título del artículo</h2>";
    echo "<span class='explicacion'>Utilice un título corto, descriptivo y llamativo, máximo 30 carácteres. No se admite código HTML.</span><br />";
    echo "Titulo " . ui_input("vender_paso2_titulo","","","","width:30ex","MAXLENGTH='30'")."<br />";
    echo "<h2>Paso 3. Descripción corta del artículo</h2>";
    echo "<span class='explicacion'>Describa brevemente tu artículo, solo los detalles más importantes, máximo 100 carácteres. No se admite código HTML.</span><br />";
    echo "Descripción corta<br />" . ui_textarea("vender_paso3_descripcion_corta","","","width:50em;height:4em;") . "<br />";
    echo "<h2>Paso 4. Descripción del artículo</h2>";
    echo "<span class='explicacion'>Describa en detalle tu artículo, incluye todos los datos relevantes que desees, máximo 5000 carácteres.<br />Se admite código HTML (".ui_href("vender_ayuda_limitacionesHMTL","ayuda#limitacionesHTML","con algunas limitantes","",'target="_blank"').").</span><br />";
    echo "Descripción larga<br />" . ui_textarea("vender_paso4_descripcion_larga","","","width:50em;height:50em;")."<br />";
    echo "<h2>Paso 5. Características del artículo</h2>";
    echo "<span class='explicacion'>Seleccione solo las opciones que ayuden a describir de forma precisa tu producto.</span><br />";
    echo db_ui_checkboxes("vender_paso5_chkFlags[]", "ventas_flags_ventas", "nombre", "nombrep", "descripcion");
    echo "<h2>Paso 6. Precio</h2>";
    echo "<span class='explicacion'>Précio en dólares de Estados Unidos de America ($ USA).</span><br />";
    echo "Précio " . ui_input("vender_paso6_precio","","","","width:30ex","MAXLENGTH='30'")."<br />";
    echo "<h2>Paso 7. Formas de pago admitidas</h2>";
    echo "<span class='explicacion'>Selecione solo las opciones de pago que admitirá para esta venta.</span><br />";
    echo db_ui_checkboxes("vender_paso7_chkFlags[]", "ventas_flags_pago", "nombre", "nombrep", "descripcion");
    echo "<h2>Paso 8. Formas de entrega admitidas</h2>";
    echo "<span class='explicacion'>Selecione solo las opciones de tipos de entrega que admitirá para esta venta.</span><br />";
    echo db_ui_checkboxes("vender_paso8_chkFlags[]", "ventas_flags_entrega", "nombre", "nombrep", "descripcion");
    echo "<h2>Paso 9. Fotografías del artículo</h2>";
    echo "<span class='explicacion'>Cargue las fotografías reales de su artículo, se necesita al menos una para aprobar su venta.<br />Imagenes tomadas de la página del fabricante o similires son permitidas con un máximo de dos imagenes.<br />En total se admiten cinco imagenes</span><br />";
    echo "Imagen 1: Cargar ". ui_input("vender_paso9_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_paso9_enlaces[]","") ."<br />";
    echo "Imagen 2: Cargar ". ui_input("vender_paso9_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_paso9_enlaces[]","") ."<br />";
    echo "Imagen 3: Cargar ". ui_input("vender_paso9_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_paso9_enlaces[]","") ."<br />";
    echo "Imagen 4: Cargar ". ui_input("vender_paso9_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_paso9_enlaces[]","") ."<br />";
    echo "Imagen 5: Cargar ". ui_input("vender_paso9_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_paso9_enlaces[]","") ."<br />";
    echo "<h2>Paso 10. Previsualizar y Publicar</h2>";
    echo "<span class='explicacion'>Presione 'Previsualizar' para ver como quedará su anuncio ya publicado, puede reeditar la públicación tanto como guste.<br />Cuando este satisfecho con el resultado entonces presione el botón 'Publicar'.</span><br />";
    echo "<br />";
    echo "<center>" . ui_input("vender_proceder", "Previsualizar", "submit") . "</center>";
    echo "</form>";
}
?>
