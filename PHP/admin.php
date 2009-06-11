<?php
function CONTENIDO_ADMIN()
{
    if (empty($_GET['op']))
    {
        echo "<h1>Bienvenido a la interfaz de administración</h1>";
        echo "Por favor seleccione el área a administrar:";
        echo "<ul>";
        echo "<li>".ui_href("admin_usuarios_activacion","admin_usuarios_activacion","Usuarios: activación de cuentas")."</li>";
        echo "<li>".ui_href("admin_usuarios_admin","admin_usuarios_admin","Usuarios: administración general")."</li>";
        echo "<li>".ui_href("admin_categorias_admin","admin_categorias_admin","Categorias: administración general")."</li>";
        echo "<li>".ui_href("admin_articulos_admin","admin_categorias_admin","Publicaciones: aprobación")."</li>";
        echo "<li>".ui_href("admin_articulos_admin","admin_categorias_admin","Publicaciones: administración general")."</li>";
        echo "</ul>";
        return;
    }

    $op = $_GET['op'];
    switch ($op)
    {
        case "usuarios_activacion":
            INTEFAZ__ACTIVACION_USUARIOS();
        break;
        default:
            echo "ERROR: Interfaz '$op' no implementada";
    }
}

function INTEFAZ__ACTIVACION_USUARIOS()
{
    $c = "SELECT * FROM ventas_usuarios WHERE estado="._N_esp_activacion;
    $r = db_consultar($c);
    echo db_ui_tabla($r);
}
?>
