<?php
$select = isset($_GET['select']);
$liste=array();
$rep='./';
$dir = opendir($rep);
$texte='';
if ($select)
	$texte='<select id="liste_polices">';
while ($f = readdir($dir)) {
	if (strpos($f,'.ttf')===false)
		continue;
	if(is_file($rep.$f)) {
		$nom=substr($f,0,strlen($f)-strlen('.ttf'));
		if ($select)
			$texte.='<option name="'.$nom.'">'.$nom.'</option>';
		else
			$texte.=$nom.'<br />';
	}
}
if ($select)
	$texte.='</select>';
echo $texte;
?>
