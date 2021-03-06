<?php require_once ('PHP/vital.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>Sistema de ventas en línea</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta name="generator" content="vlad@todosv.com" />
    <link rel="stylesheet" type="text/css" href="estilo.css" />
</head>

<body>
<?php
$forzar = isset($_GET['forzar']);
// Tabla de usuarios
$campos = "id_usuario INT NOT NULL AUTO_INCREMENT PRIMARY KEY, usuario VARCHAR(100) UNIQUE not null, clave VARCHAR(40) not null, nombre VARCHAR(32) not null, email VARCHAR(50)  UNIQUE not null, telefono1 VARCHAR(20), telefono2 VARCHAR(20), avatar INT, notas TEXT, nivel TINYINT UNSIGNED NOT NULL, estado TINYINT UNSIGNED NOT NULL, contraclave VARCHAR(32), ultimo_acceso DATETIME, registro DATETIME, FLAGS LONGTEXT, nDiasVigencia INT NOT NULL DEFAULT '7', nPubMax INT NOT NULL DEFAULT '5', nImgMax INT NOT NULL DEFAULT '5', tienda TINYINT DEFAULT '0'";
echo db_crear_tabla("ventas_usuarios", $campos, false||$forzar);

// Agregamos al usuario Admin
$usuario['usuario'] = 'admin';
$usuario['clave']   = sha1($usuario['usuario'].'admin');
$usuario['nombre']  = 'Administrador';
$usuario['email']   = 'admin@localhost.com';
$usuario['nivel']   = _N_administrador;
$usuario['ultimo_acceso']= mysql_datetime();
$usuario['registro']= mysql_datetime();
_F_usuario_agregar ($usuario);

// Agregamos al usuario Vendedor
$usuario['usuario'] = 'vendedor';
$usuario['clave']   = sha1($usuario['usuario'].'vendedor');
$usuario['nombre']  = 'Vendedor Ejemplo';
$usuario['email']   = 'vendedor@localhost.com';
$usuario['nivel']   = _N_vendedor;
$usuario['ultimo_acceso']= mysql_datetime();
$usuario['registro']= mysql_datetime();
_F_usuario_agregar ($usuario);

// Agregamos al usuario Tienda
$usuario['usuario'] = 'tienda';
$usuario['clave']   = sha1($usuario['usuario'].'tienda');
$usuario['nombre']  = 'Tienda Ejemplo';
$usuario['email']   = 'tienda@localhost.com';
$usuario['nivel']   = _N_vendedor;
$usuairo['tienda']  = 1;
$usuario['ultimo_acceso']= mysql_datetime();
$usuario['registro']= mysql_datetime();
_F_usuario_agregar ($usuario);

unset ($usuario);

// Tabla de tiendas
$campos = "id_tienda INT NOT NULL AUTO_INCREMENT PRIMARY KEY, id_usuario INT, tiendaURL VARCHAR(50), tiendaTitulo VARCHAR(100), tiendaSubtitulo VARCHAR(200), tiendaCSS LONGTEXT";
echo db_crear_tabla("ventas_tienda", $campos, false||$forzar);

// Tabla de categorias
$campos = "id_categoria INT NOT NULL AUTO_INCREMENT PRIMARY KEY, padre INT, nombre VARCHAR(200), descripcion VARCHAR(500), rubro VARCHAR(15)";
echo db_crear_tabla("ventas_categorias", $campos, false||$forzar);
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Inmuebles", "descripcion" => "", "rubro" => "inmueble"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Campo", "descripcion" => "", "rubro" => "inmueble"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Alquiler de apartamentos", "descripcion" => "", "rubro" => "inmueble"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Alquiler de apartamentos amueblados", "descripcion" => "", "rubro" => "inmueble"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Alquiler de casas", "descripcion" => "", "rubro" => "inmueble"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Alquiler de casas amuebladas", "descripcion" => "", "rubro" => "inmueble"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Venta de casas", "descripcion" => "", "rubro" => "inmueble"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Venta de casas amuebladas", "descripcion" => "", "rubro" => "inmueble"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Automotores", "descripcion" => "", "rubro" => "automotor"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Autos Sedan", "descripcion" => "", "rubro" => "automotor"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Vehiculos Utilitarios", "descripcion" => "", "rubro" => "automotor"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Camionetas", "descripcion" => "", "rubro" => "automotor"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Camiones", "descripcion" => "", "rubro" => "automotor"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Autos antiguos", "descripcion" => "", "rubro" => "automotor"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Motos", "descripcion" => "", "rubro" => "automotor"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Nautica", "descripcion" => "", "rubro" => "automotor"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Industria y Oficina", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Agricultura", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Balanzas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Componentes Eléctricos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Construcción", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Embalajes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Equipamiento comercial", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Equipamiento Médico", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Equipamiento para Oficinas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Herramientas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Industria Gastronómica", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Industria Textil", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Material de Promoción", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Arquitectura y Diseño", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Seguridad Industrial", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Uniformes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Computación", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Accesorios para PC portatiles", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Apple", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "CDs y DVDs virgenes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cartuchos, Toner y Papeles", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Computadoras completas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Discos rígidos y portatiles", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Fuentes, UPS y Reguladores", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Mesas para PC", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Grabadoras de DVDs y CDs", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Impresoras", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Memorias RAM", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Modems", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Monitores y Proyectores", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Motherboards", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Multimedia", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Netbooks", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Notebooks", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Palms y Handhelds", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Memorias USB", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Periféricos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Tarjetas de video", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Procesadores", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Redes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Scanners", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Servidores", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Software", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Electrónica, Audio y Video", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Accesorios Audio/Video", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Audio Portátil", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Audio profesional y DJs", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Audio para Vehículos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Audio para el Hogar", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Calculadoras y Agendas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Componentes Electrónicos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Aparatos DVD y Video", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Fotocopiadoras", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "GPS", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Teatro en Casa", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Reproductores MP3", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Pilas, Cargadores y Baterías", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Proyectores y Pantallas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Seguridad y Vigilancia", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Televisores, LCD y Plasmas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Video Camaras", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "iPod", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Consolas y Videojuegos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Arcade", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Game Boy", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Game Boy Color", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Game Boy Advance", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Game Boy SP", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "GameCube", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Nintendo 64", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Nintendo DS-DSi", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Nintendo Wii", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "PSP - PlayStation Portable", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "PlayStation 1", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "PlayStation 2", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "PlayStation 3", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Sega Dreamcast", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Sega Genesis", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Super Nintendo", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Xbox", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Xbox 360", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Celulares y Telefonía", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Accesorios", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Celulares", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Centrales Telefónicas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Faxes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Radiofrecuencia", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Telefonos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Instrumentos Musicales", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Amplificadores", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Bajos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Baterías y Percusión", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Consolas de Sonido", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Efectos de Sonido", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Guitarras", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Instrumentos de Cuerdas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Instrumentos de Viento", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Micrófonos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Parlantes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Partituras y Letras", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Teclados y Pianos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Deportes y Salud", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Aerobics", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Artes Marciales y Boxeo", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Basketball", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Bicicletas y Ciclismo", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Camping", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Deportes Acuáticos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Deportes Extremos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Equitación y Polo", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Fútbol", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Golf", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Juegos de Salón", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Lentes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros y Revistas de Deportes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Montañismo", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Patinaje", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Pesca", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Pulsómetros y Cronómetros", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Softball", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Suplementos Alimenticios", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Tenis y Squash", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Voleyball", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Zapatillas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Hogar, Muebles y Jardín", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Baño", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cocina", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Decoración del Hogar", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Dormitorio", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Iluminación para el Hogar", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Jardines y Exteriores", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Muebles para Oficinas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Pisos, Paredes y Aberturas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Sala de Estar y Comedor", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Seguridad para el Hogar", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Electrodomésticos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Aires Acondicionados", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Artefactos para el hogar", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cocina", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Heladeras y Freezers", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Lavadoras y Secadoras", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Ventiladores", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Indumentaria y Accesorios", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Accesorios de Moda", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Carteras, Bolsos y Valijas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Disfraces y Cotillón", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Lentes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Ropa Deportiva", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Ropa Femenina", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Ropa Masculina", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Ropa para Niñas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Ropa para Niños", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Uniformes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Zapatillas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Zapatos y Sandalias", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Salud y Belleza", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cuidado Bucal", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cuidado de la Piel", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cuidado de la Salud", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cuidado del cabello", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cuidado del cuerpo", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cuidado para manos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Equipamiento Médico", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Maquillaje", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Perfumes y Fragancias", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Suplementos Alimenticios", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Terapias Naturales", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Vitaminas y Complementos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Bebés", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Accesorios", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Alimentación", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Andadores y Caminadores", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Artículos para Baño", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Asientos para Autos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cochecitos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Corralitos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cunas y Catres", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Juguetes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Ropa de Cuna", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Ropa", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Seguridad", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Sillas de Comer", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Juegos y Juguetes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Autos de juguete", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cartas y Naipes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Juegos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Juegos de Aire Libre y Agua", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Juguetes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Modelismo", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Muñecas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Muñecos y Accesorios", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Peloteros y Castillos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Vehiculos para Niños", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Animales y Mascotas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Aves", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Caballos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Conejos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Gatos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Animales", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Peces", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Perros", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Reptiles y Anfibios", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Roedores", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Coleccionables y Hobbies", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cartas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cigarrillos y Afines", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Colecciones Diversas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Comics", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Figuras de Acción", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Álbumes y Cromos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Filatelia", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Plumas y Bolígrafos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Latas, Botellas y Afines", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Militaría y Afines", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Modelismo", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Monedas y Billetes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Muñecos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Posters, Carteles y Fotos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Tarjetas Coleccionables", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Vehículos en Miniatura", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Joyas y Relojes", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Fantasía Fina", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Joyas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Joyas Antiguas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Materiales para Joyería", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Relojes Antiguos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Relojes Pulsera", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Relojes para el Hogar", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Antigüedades", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Articulos Marítimos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Audio", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Balanzas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Carteles", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cristalería", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cámaras Fotográficas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Decoración", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Electrodomésticos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Equipos Cientificos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Herramientas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Iluminación", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Llaves y Canados", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Muebles", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Máquinas de Escribir", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Vajillas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Teléfonos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otras antigüedades", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Libros, Revistas y Comics", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Agendas y Diarios Íntimos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Comics e Historietas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Diccionarios y Enciclopedias", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Ensayos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros Antiguos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros Esotéricos-Paranormales", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros Técnicos y Cursos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Arquitectura y Diseño", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Arte", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Autoayuda", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Ciencias Económicas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Ciencias Exactas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Ciencias Sociales", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Computación-Internet", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Cs Humanísticas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Cs Médicas-Naturales", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Derecho", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Ficción", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Idiomas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Ingeniería", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Recreación y Hobbies", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Religión", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Texto y Escolares", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Libros de Revistas", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros", "descripcion" => "", "rubro" => "articulo"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Servicios varios", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Belleza y Cuidado Personal", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Clases y Capacitaciones", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Fiestas y Eventos", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Imprenta", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Mantenimiento de Vehículos", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Mantenimiento para el Hogar", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Profesionales", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Servicio Técnico", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Transporte General", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Viajes y Turismo", "descripcion" => "", "rubro" => "servicio"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otros Servicios", "descripcion" => "", "rubro" => "servicio"));
$uid = db_agregar_datos("ventas_categorias", array("padre" => NULL, "nombre" => "Entradas para Eventos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Eventos Deportivos", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Recitales", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Teatro", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Cine", "descripcion" => "", "rubro" => "articulo"));
    db_agregar_datos("ventas_categorias", array("padre" => $uid, "nombre" => "Otras Entradas", "descripcion" => "", "rubro" => "articulo"));

// Tabla de articulos
$campos = "id_publicacion INT NOT NULL AUTO_INCREMENT PRIMARY KEY, tipo INT, promocionado TINYINT(1), fecha_ini DATETIME, fecha_fin DATETIME, id_categoria INT, id_usuario INT, precio DECIMAL(12,2), titulo VARCHAR(200), descripcion_corta VARCHAR(500), descripcion LONGTEXT";
echo db_crear_tabla("ventas_publicaciones", $campos, false||$forzar);

// Tabla imagenes
$campos = "id_img INT NOT NULL AUTO_INCREMENT PRIMARY KEY, id_publicacion INT, mime VARCHAR(100)";
echo db_crear_tabla("ventas_imagenes", $campos, false||$forzar);

// Tabla FLAGS entregas + Pagos + Ventas
$campos = "id_flag INT NOT NULL AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(50), nombrep VARCHAR(200), descripcion VARCHAR(200), tipo VARCHAR(10)";
echo db_crear_tabla("ventas_flags", $campos, false||$forzar);

// Entregas
db_agregar_datos("ventas_flags",array("nombre" => "entrega_sv", "nombrep" => "Entrega a domicilio nivel a nacional", "descripcion" => "Este producto puede ser entregado a domicilio a cualquier parte del país", "tipo" => "entrega"));
db_agregar_datos("ventas_flags",array("nombre" => "entrega_pactada", "nombrep" => "El lugar de entrega será acordado mutuamente", "descripcion" => "Este producto puede ser entregado personalmente según acuerdo mutuo entre el comprador y vendedor", "tipo" => "entrega"));
db_agregar_datos("ventas_flags",array("nombre" => "entrega_courier", "nombrep" => "Entrega vía courier o compañías de mensajería", "descripcion" => "El producto es envíado mediante un courier o una empresa de entrega de paquetes", "tipo" => "entrega"));
db_agregar_datos("ventas_flags",array("nombre" => "entrega_correo", "nombrep" => "Entrega vía correo nacional", "descripcion" => "El producto es envíado a travez de correo nacional", "tipo" => "entrega"));

// Pagos
db_agregar_datos("ventas_flags",array("nombre" => "efectivo", "nombrep" => "Acepta efectivo", "descripcion" => "Marque esta opción si Ud. acepta el pago de este articulo en efectivo", "tipo" => "pago"));
db_agregar_datos("ventas_flags",array("nombre" => "cheques", "nombrep" => "Acepta cheques", "descripcion" => "Marque esta opción si Ud. acepta el pago de este articulo con cheques", "tipo" => "pago"));
db_agregar_datos("ventas_flags",array("nombre" => "tarjetas", "nombrep" => "Acepta tarjetas", "descripcion" => "Marque esta opción si Ud. acepta el pago de este articulo con tarjetas de credito", "tipo" => "pago"));
db_agregar_datos("ventas_flags",array("nombre" => "transferencia", "nombrep" => "Acepta transferencias", "descripcion" => "Marque esta opción si Ud. acepta el pago de este articulo vía transferencia bancaria", "tipo" => "pago"));

// Ventas
db_agregar_datos("ventas_flags",array("nombre" => "local_propio", "nombrep" => "Local disponible para ver el producto", "descripcion" => "Marque esta opción si Ud. dispone de un local o establecimiento donde exhiba el producto para su venta.", "tipo" => "venta"));
db_agregar_datos("ventas_flags",array("nombre" => "nuevo", "nombrep" => "Árticulo nuevo, jamás usado", "descripcion" => "Marque esta opción si el articulo se encuentra totalmente nuevo", "tipo" => "venta"));
db_agregar_datos("ventas_flags",array("nombre" => "negociable", "nombrep" => "Negociable", "descripcion" => "Marque esta opción si acepta ofertas por un menor precio al establecido", "tipo" => "venta"));
db_agregar_datos("ventas_flags",array("nombre" => "cambalache", "nombrep" => "Cambalache", "descripcion" => "Marque esta opción si acepta otro producto en pago por este (incluido pago de diferencia)", "tipo" => "venta"));
db_agregar_datos("ventas_flags",array("nombre" => "facturas", "nombrep" => "Entrega factura", "descripcion" => "Marque esta opción si Ud. puede entregar una factura *legal* sobre esta venta", "tipo" => "venta"));
db_agregar_datos("ventas_flags",array("nombre" => "credito_fiscal", "nombrep" => "Acepta Crédito Físcal", "descripcion" => "Marque esta opción si Ud. puede aceptar esta compra con crédito físcal", "tipo" => "venta"));
db_agregar_datos("ventas_flags",array("nombre" => "credito", "nombrep" => "Ofrece Crédito", "descripcion" => "Marque esta opción si Ud. puede ofrecer crédito o alguna otra forma de pago a plazos para esta venta", "tipo" => "venta"));

$campos = "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, tipo VARCHAR(100), id_flag INT, id_publicacion INT";
echo db_crear_tabla("ventas_flags_pub", $campos, false||$forzar);

$campos = "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, id_usuario_rmt INT, mensaje VARCHAR(500), tipo TINYINT, contexto INT, fecha DATETIME";
echo db_crear_tabla("ventas_mensajes", $campos, false||$forzar);

$campos = "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, id_msj INT, id_usuario_dst INT, leido TINYINT(1), eliminado TINYINT(1)";
echo db_crear_tabla("ventas_mensajes_dst", $campos, false||$forzar);

$campos = "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, id_usuario INT, id_publicacion INT, consulta VARCHAR(1000), respuesta VARCHAR(1000), tipo INT, fecha_consulta DATETIME, fecha_respuesta DATETIME";
echo db_crear_tabla("ventas_mensajes_publicaciones", $campos, false||$forzar);

$campos = "`id` INT NOT NULL AUTO_INCREMENT ,`tag` VARCHAR( 100 ) NOT NULL, PRIMARY KEY ( `id` ), UNIQUE (`tag`)";
echo db_crear_tabla("ventas_tag", $campos, false||$forzar);

$campos = "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, id_publicacion INT, id_tag INT";
echo db_crear_tabla("ventas_tag_uso", $campos, false||$forzar);

?>
</body>
</html>
