<?php
header('Content-Type: text/xml;charset=utf-8');
echo(utf8_encode("<?xml version='1.0' encoding='UTF-8' ?><options>"));
if (isset($_GET['debut'])) {
    $debut = utf8_decode($_GET['debut']);
} else {
    $debut = "";
}
$debut = strtolower($debut);

$url='http://coa.inducks.org/legend-country.php?xch=1&lg=4';
$handle = @fopen($url, "r");
if ($handle) {
	$buffer="";
   	while (!feof($handle)) {
     	$buffer.= fgets($handle, 4096);
   	}
   	fclose($handle);
}
else {
	echo 'Erreur de connexion &agrave; Inducks!';
	return false;
}
$regex_pays='#<a href=country\.php\?c=([^>]+)>([^<]+)</a>#i';
$liste=array();
preg_match_all($regex_pays,$buffer,$liste_pays);
foreach ($liste_pays[0] as $pays) {
	array_push($liste,preg_replace($regex_pays,'$2 ($1)',$pays));
}
function generateOptions($debut,$liste) {
    $MAX_RETURN = 10;
    $i = 0;
    foreach ($liste as $element) {
        if ($i<$MAX_RETURN && substr(strtolower($element), 0, strlen($debut))==$debut) {
            echo "<option>".$element."</option>";
            $i++;
        }
    }
}


generateOptions($debut,$liste);

echo("</options>");
?>
