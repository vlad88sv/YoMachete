<?php
// Proyecto
define('PROY_NOMBRE','Clasificados en El Salvador');
define('PROY_URL',preg_replace("/\/?$/","","http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']))."/");
define('PROY_MAIL_POSTMASTER','mensajero@'.$_SERVER['HTTP_HOST']);
define('PROY_MAIL_REPLYTO',PROY_MAIL_POSTMASTER);

// Niveles
define('_N_administrador',      9);
define('_N_moderador',          7);
define('_N_vendedor',           3);

// Estados
define('_N_activo',             0);
define('_N_bloqueado_temp',     1);
define('_N_bloqueado_perm',     2);
define('_N_esp_activacion',     3);

// Estados para articulos
define('_A_bloqueado_temp',     1);
define('_A_bloqueado_perm',     2);
define('_A_esp_activacion',     3);
define('_A_temporal',           4);
define('_A_promocionado',       5);
define('_A_vendido',            6);
define('_A_sin_stock',          7);
define('_A_aceptado',           8);

/*
Constantes para mensajes
*/
define("_M_INFO", 0);
define("_M_ERROR", 1);
define("_M_NOTA",2);

/*
Tipos de mensajes
*/
define('_MC_broadcast',0);
define('_MC_ventas',1);
define('_MC_privado',2);

/*
Tipos de mensajes en publicaciones
*/
define('_MeP_Publico',0);
define('_MeP_Privado',1);

/*
Estadisticas de usuarios
*/
define('_EST_CANT_PUB',0); //Cantidad de publicaciones (totales)
define('_EST_CANT_PUB_ACEPT',1); //Cantidad de publicaciones (aprobadas)
define('_EST_CANT_PUB_NOTEMP',2); //Cantidad de publicaciones (aprobadas+Esperando)
define('_EST_CANT_MP_NUEVOS',3); //Cantidad de Mensajes Privados nuevos (sin leer)

/*
 Paginación
*/
define('_MAX_PUB_X_PAG',10);
define('_MAX_PAGS',10);
?>
