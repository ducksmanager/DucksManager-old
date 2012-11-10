<?header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include_once('../Database.class.php');
include_once('../authentification.php');
?>
<form action="" method="post">
<textarea name="query" rows="20" cols="150"><?php 
	if (isset($_POST['query'])) {
		echo $_POST['query'];
	}
?></textarea>
<input type="submit" value="Go" />
</form>

<?php
if (isset($_POST['query'])) {
	$numeros=array();
	$abc=array();
	$code_images='';
	$code_ajouts=array();
	for ($i='A';$i<='Z' && strlen($i)==1;$i++)
		$abc[]=$i;
	
	$lignes=explode("\r\n",$_POST['query']);
	foreach($lignes as $ligne) {
		if (!empty($ligne)) {
			$regex="#INSERT INTO `tranches_pretes` VALUES \('([^']+)', '([^']+)', (?:(?:NULL)|(?:'[0-9,]*')), (?:(?:NULL)|(?:'[0-9,]*')), #";
			preg_match($regex,$ligne,$matches);
			list($pays,$magazine)=explode('/',$matches[1]);
			$numero=$matches[2];
			$url='http://ducksmanager.net/edges/'.$pays.'/gen/'.$magazine.'.'.$numero.'.png?'.$abc[rand(0,25)];
			$code_images.='[img]'.$url.'[/img]';
			if (!array_key_exists($pays.'/'.$magazine,$numeros))
				$numeros[$pays.'/'.$magazine]=array();
			$numeros[$pays.'/'.$magazine][]=$numero;
			DM_Core::$d->requete($ligne);
		}
	}
	
	list($noms_pays,$noms_magazines) = Inducks::get_noms_complets(array_keys($numeros));
	foreach($numeros as $publicationcode=>$numeros_associes) {
		list($pays,$magazine)=explode('/',$publicationcode);
		$code_ajouts[]= '[Biblioth&egrave;que][Tranches][Ajout]'
					   .$noms_magazines[$publicationcode]
					   .($pays=='fr' ? '':' ('.$noms_pays[$pays].')')
					   .' n&deg; '.implode(', ',$numeros_associes);
	}
	echo '[code]'.implode('<br />',$code_ajouts).'[/code]';
	echo '<br /><br />';
	echo $code_images;
}
?>