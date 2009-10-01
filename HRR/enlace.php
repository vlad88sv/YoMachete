<?php
/*
 * enlace.php mostrará los datos SMF en XML de cualquier usuario utilizando los Settings.php del foro donde se le ubique.
 *
 * Version:     1.0
 *
 * Created by:  Vladimir Hidalgo © 2009
 *
 * Email:       vlad@todosv.com
 *
 * Link:        http://todosv.com
 *
 *
 * Return:
 *      ERROR   -> Si faltan parametros o no se pudo conectar a la base de datos
 *      NO      -> Si el usuario y contraseña no existen
 *      XML     -> XML con todos los datos si el usuario fue encontrado
 *
 * Use:         echo array2xml($products,'products');
 *              die;
*/
// Si no es YoMachete.com, morir
if ($_SERVER['REMOTE_ADDR'] != '96.30.8.8')
{
    die ("NO:".$_SERVER['REMOTE_ADDR']);
}

if (empty($_GET['m']) || empty($_GET['p']))
{
    die ("ERROR #1");
}
else
{
    $member_name = mysql_escape_string($_GET['m']);
    $passwd = mysql_escape_string($_GET['p']);
}
require_once('Settings.php');
if(mysql_connect($db_server,$db_user,$db_passwd))
{
    if(mysql_selectdb($db_name))
    {
        //mysql_set_charset('utf8');
        $c = sprintf("SELECT * FROM %s WHERE (LOWER(member_name)=LOWER('%s') OR LOWER(email_address)=LOWER('%s')) AND passwd=SHA1(CONCAT(LOWER(member_name),'%s')) LIMIT 1",$db_prefix."members",$member_name,$member_name,$passwd);
        //$c = sprintf("SELECT * FROM %s WHERE member_name='%s' AND passwd=SHA1(CONCAT('%s','%s')) LIMIT 1",$db_prefix."members",$member_name,$member_name,$passwd);
        $r = mysql_query($c);
        if (mysql_numrows($r) == 1)
        {
            $f = mysql_fetch_assoc($r);
            echo utf8_encode(array2xml($f,'smf'));
        }
        else
        {
            /*
            echo "<div>".mysql_error()."</div>";
            echo "<div>".$c."</div>";
            */
            die ("Su usuario/email tampoco se encontró en http://svcommunity.org");
        }
    }
    else
    {
        die ("ERROR #2 | No se pudo conectar con http://svcommunity.org por problemas técnicos, intente mas tarde.");
    }
}
else
{
    die ("ERROR #3 | No se pudo conectar con http://svcommunity.org, intente mas tarde.");
}

/*
 * array2xml() will convert any given array into a XML structure.
 *
 * Version:     1.0
 *
 * Created by:  Marcus Carver © 2008
 *
 * Email:       marcuscarver@gmail.com
 *
 * Link:        http://marcuscarver.blogspot.com/
 *
 * Arguments :  $array      - The array you wish to convert into a XML structure.
 *              $name       - The name you wish to enclose the array in, the 'parent' tag for XML.
 *              $standalone - This will add a document header to identify this solely as a XML document.
 *              $beginning  - INTERNAL USE... DO NOT USE!
 *
 * Return:      Gives a string output in a XML structure
 *
 * Use:         echo array2xml($products,'products');
 *              die;
*/
function array2xml($array, $name='array', $standalone=TRUE, $beginning=TRUE) {

  global $nested;

  if ($beginning) {
    if ($standalone) header("content-type:text/xml;charset=utf-8");
    $output .= '<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'>' . "\n";
    $output .= '<' . $name . '>' . "\n";
    $nested = 0;
  }

  // This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
  $ArrayNumberPrefix = 'ARRAY_NUMBER_';

   foreach ($array as $root=>$child) {
    if (is_array($child)) {
      $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
      $nested++;
      $output .= array2xml($child,NULL,NULL,FALSE);
      $nested--;
      $output .= str_repeat(" ", (2 * $nested)) . '  </' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
    }
    else {
      $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '><![CDATA[' . $child . ']]></' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
    }
  }

  if ($beginning) $output .= '</' . $name . '>';

  return $output;
}
?>
