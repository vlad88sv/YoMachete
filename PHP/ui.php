<?php
function ui_img ($id_gui, $src){
	return '<img id="'.$id_gui.'" src="'.$src.'" />';
}
function ui_href ($id_gui, $href, $texto, $clase="", $extra=""){
	return '<a id="'.$id_gui.'" href="'.$href.'" class="' . $clase . '" ' . $extra . '>'.$texto.'</a>';
}
function ui_combobox ($id_gui, $opciones, $selected = "", $clase="", $estilo="") {
	return '<select id="' . $id_gui . '" name="' . $id_gui . '" style="' . $estilo . '">'. $opciones . '</select>';
}
function ui_input ($id_gui, $valor="", $tipo="text", $clase="", $estilo="", $extra ="") {
	return '<input type="'.$tipo.'" id="' . $id_gui . '" name="' . $id_gui . '" class="' . $clase . '" style="' . $estilo . '" value="' . $valor .'" '.$extra.'></input>';
}
function ui_textarea ($id_gui, $valor="", $clase="", $estilo="") {
	return "<textarea id='$id_gui' name='$id_gui' class='$clase' style='$estilo'>$valor</textarea>";
}
function ui_th ($valor, $clase="") {
	return "<th class='$clase'>$valor</td>";
}
function ui_td ($valor, $clase="", $estilo="") {
	return "<td class='$clase' style='$estilo'>$valor</td>";
}
function ui_tr ($valor) {
	return "<tr>$valor</tr>";
}
function ui_optionbox_nosi ($id_gui, $valorNo = 0, $valorSi = 1, $TextoSi = "Si", $TextoNo = "No") {
	return "<input id='$id_gui' name='$id_gui' type='radio' checked='checked' value='$valorNo'>$TextoNo</input>" . '&nbsp;&nbsp;&nbsp;&nbsp;'."<input id='$id_gui' name='$id_gui' type='radio' value='$valorSi'>$TextoSi</input>";
}
function ui_combobox_o_meses (){
	$opciones = '';
	for ($i = 1; $i < 13; $i++) {
		$opciones .= '<option value=$i>'.strftime('%B', mktime (0,0,0,$i,1,2009)).'</option>';
	}
	return $opciones;
}
function ui_combobox_o_anios (){
	$opciones = '';
	for ($i = 0; $i < 13; $i++) {
		$opciones .= '<option value=$i>'.(date('Y') - $i).'</option>';
	}
	return $opciones;
}

function ui_js_ini_datepicker ($inicio = '', $fin = '', $extra = ''){
	if ($inicio) $inicio = ", minDate: '$inicio'";
	if ($fin) $fin = ", maxDate: '$fin'";
	return "$('.date-pick').datepicker({dateFormat: 'dd-mm-yy' $inicio $fin $extra});";
}
function ui_js_ini_slider ($id_gui, $objetivo = '', $value = '0', $inicio = '0', $fin = '100', $paso = '1'){

	return "$('#slider').slider({value:100, min: 0, max: 500, step: 50, slide: function(event, ui) {	$('#amount').val('$' + ui.value); }	});
		$('#amount').val( $('#slider').slider('value'));
		});
		";
}
?>
