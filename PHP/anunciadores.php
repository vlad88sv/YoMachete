<?php
/*Tweeter*/
function tweet($status)
{
$username = 'YoMachete';
$password = '#tw1tter';

if (!$status)
    return false;

$tweetUrl = 'http://www.twitter.com/statuses/update.xml';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "$tweetUrl");
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, "status=$status");
curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");

$result = curl_exec($curl);
$resultArray = curl_getinfo($curl);

curl_close($curl);

return ($resultArray['http_code'] == 200);

}

function RSS()
{
    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title>YoMachete.com</title>
<link>http://www.yomachete.com/</link>
<language>es-SV</language>
<description>Sitio de ventas en línea en El Salvador</description>
<generator>ENLACE WEB S.A. de C.V.</generator>
<atom:link href="http://yomachete.com/rss.xml" rel="self" type="application/rss+xml" />
';

    $r=db_consultar("SELECT pub.id_publicacion, pub.titulo, pub.fecha_ini, pub.descripcion_corta, pub.descripcion, cat.nombre AS categoria FROM ventas_publicaciones AS pub LEFT JOIN ventas_categorias AS cat ON pub.id_categoria = cat.id_categoria WHERE tipo IN ("._A_aceptado . ","._A_promocionado.") AND fecha_fin >= CURDATE() ORDER BY promocionado DESC, fecha_ini DESC LIMIT 0,30");
    while($f=mysql_fetch_assoc($r))
    {
        $descripcion=substr($f['descripcion_corta'],0,300)."...";
        $url = "clasificados-en-el-salvador-vendo-".$f['id_publicacion']."_".SEO($f['titulo']);
        echo '<item>
<title>'.$f['titulo'].'</title>
<link>http://www.yomachete.com/'.$url.'</link>
<pubDate>'.date("D, j M Y H:i:s O", strtotime($f['fecha_ini'])).'</pubDate>
<category>'.$f['categoria'].'</category>
<description><![CDATA['.$descripcion.']]></description>
<guid>http://www.yomachete.com/'.$url.'</guid>
</item>
';
    }
echo '</channel>
</rss>
';
}
?>
