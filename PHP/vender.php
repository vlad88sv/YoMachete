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
    echo "<ol class=\"ventas\">";
    echo "<li>Selección de categoría para el artículo</li>";
    echo "<span class='explicacion'>Ubique su árticulo en la categoría que consideres apropiada.</span><br />";
    echo "Mi árticulo corresponde a la siguiente categoría<br />".ui_combobox("vender_categoria",join("",ver_hijos("",0,1)))."<br />";
    echo "<li>Título del artículo</li>";
    echo "<span class='explicacion'>Utilice un título corto, descriptivo y llamativo, máximo 30 carácteres. No se admite código HTML.</span><br />";
    echo "Titulo " . ui_input("vender_titulo","","","","width:30ex","MAXLENGTH='30'")."<br />";
    echo "<li>Descripción corta del artículo</li>";
    echo "<span class='explicacion'>Describa brevemente tu artículo, solo los detalles más importantes, máximo 100 carácteres. No se admite código HTML.</span><br />";
    echo "Descripción corta<br />" . ui_textarea("vender_descripcion_corta","","","width:50em;height:4em;") . "<br />";
    echo "<li>Descripción del artículo</li>";
    echo "<span class='explicacion'>Describa en detalle tu artículo, incluye todos los datos relevantes que desees, máximo 5000 carácteres.<br />Se admite código HTML (".ui_href("vender_ayuda_limitacionesHMTL","ayuda#limitacionesHTML","con algunas limitantes","",'target="_blank"').").</span><br />";
    echo "Descripción larga<br />" . ui_textarea("vender_descripcion_larga","","","width:50em;height:20em;")."<br />";
    echo "<li>Características del artículo</li>";
    echo "<span class='explicacion'>Seleccione solo las opciones que ayuden a describir de forma precisa tu producto.</span><br />";
    echo db_ui_checkboxes("vender_chkFlags[]", "ventas_flags_ventas", "nombre", "nombrep", "descripcion");
    echo "<li>Precio</li>";
    echo "<span class='explicacion'>Précio en dólares de Estados Unidos de America ($ USA).</span><br />";
    echo "Précio " . ui_input("vender_precio","","","","width:30ex","MAXLENGTH='30'")."<br />";
    echo "<li>Formas de pago admitidas</li>";
    echo "<span class='explicacion'>Selecione solo las opciones de pago que admitirá para esta venta.</span><br />";
    echo db_ui_checkboxes("vender_opcionespago_chkFlags[]", "ventas_flags_pago", "nombre", "nombrep", "descripcion");
    echo "<li>Formas de entrega admitidas</li>";
    echo "<span class='explicacion'>Selecione solo las opciones de tipos de entrega que admitirá para esta venta.</span><br />";
    echo db_ui_checkboxes("vender_opcionesentrega_chkFlags[]", "ventas_flags_entrega", "nombre", "nombrep", "descripcion");
    echo "<li>Fotografías del artículo</li>";
    echo "<span class='explicacion'>Cargue las fotografías reales de su artículo, se necesita al menos una para aprobar su venta.<br />Imagenes tomadas de la página del fabricante o similires son permitidas con un máximo de dos imagenes.<br />En total se admiten cinco imagenes</span><br />";
    echo "Imagen 1: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    echo "Imagen 2: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    echo "Imagen 3: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    echo "Imagen 4: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    echo "Imagen 5: Cargar ". ui_input("vender_imagenes[]","","file") . " <b>o</b> usar enlace externo ". ui_input("vender_enlaces[]","") ."<br />";
    echo "<li>Previsualizar y Publicar</li>";
    echo "</li>";
    echo "<span class='explicacion'>Puede observar como quedaría su publicación utilizando el botón 'Previsualizar'.<br />Cuando este satisfecho con el resultado presione el botón 'Publicar'.</span><br />";
    echo "<br />";
    echo "<center>" . ui_input("vender_proceder", "Previsualizar", "submit") . "</center>";
    echo "</form>";
}
?>
