<?php
include_once('JS.class.php');
include_once('Etagere.class.php');
include_once('Edge.class.php');
include_once('Util.class.php');
include_once('getDimensions.php');

global $numeros_inducks;

$largeur=$_POST['largeur'];
$hauteur=$_POST['hauteur'];
?>
<html>
	<head>
		<?php
		new JS('js/scriptaculous/lib/prototype.js');
		new JS('js/scriptaculous/src/scriptaculous.js');
		new JS('js/edges2.js');
		?>

	</head>
	<body id="body" style="margin:0;padding:0" style="white-space:nowrap;">
		<?php
		Edge::$grossissement=$_POST['grossissement'];
		Etagere::$largeur=$largeur;
		Etagere::$hauteur=$hauteur;
		Etagere::$epaisseur=20;
		Etagere::$texture1=$_POST['texture1'];
		Etagere::$sous_texture1=$_POST['sous_texture1'];
		Etagere::$texture2=$_POST['texture2'];
		Etagere::$sous_texture2=$_POST['sous_texture2'];
		$regen=$_POST['regen']==1;
		list($width, $height, $type, $attr)=getimagesize('edges/textures/'.Etagere::$texture1.'/'.Etagere::$sous_texture1.'.jpg');
		if ($width<Etagere::$largeur)
			Etagere::$largeur=$width;
		echo Edge::getEtagereHTML();
		list($html, $pourcentage_visible)=Edge::getPourcentageVisible(true,$regen);
		echo $html;
		echo Edge::getEtagereHTML(false);
		//Util::ecrire_dans_fichier('./edges/_tmp/'.$rand.'.html', $html);
		echo '';
		/*?>
	<div style="display:none" id="num_gen" name="<?=$rand?>"></div><?php */?>
	<div id="largeur_etagere" style="display:none" name="<?=Etagere::$largeur?>"></div>
	<div id="nb_numeros_visibles" style="display:none" name="<?=$pourcentage_visible?>"></div>
	<div id="hauteur_etage" style="display:none" name="<?=Etagere::$hauteur_max_etage?>"></div>
	<div id="grossissement" style="display:none" name="<?=Edge::$grossissement?>"></div>
	</body>
</html>