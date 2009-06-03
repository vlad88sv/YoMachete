<?php
session_start();

function _F_sesion_cerrar(){
   unset ( $_SESSION );
   session_destroy ();
   header("location: ./");
}

function S_iniciado(){
return isset($_SESSION['autenticado']);
}
?>
