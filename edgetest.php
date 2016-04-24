<?php
include_once('Etagere.class.php');
include_once('Edge.class.php');
include_once('Util.class.php');
include_once('getDimensions.php');

global $numeros_inducks;

?>
<html>
	<head>
		<script type="text/javascript" src="js/scriptaculous/lib/prototype.js"></script>
		<script type="text/javascript" src="js/scriptaculous/src/scriptaculous.js"></script>
		<script type="text/javascript" src="js/edges2.js"></script>
	</head>
	<body id="body" style="margin:0;padding:0;white-space:nowrap;">
		<?php
		list($width, $height, $type, $attr)=getimagesize('edges/textures/'.Etagere::$texture1.'/'.Etagere::$sous_texture1.'.jpg');
		if ($width<Etagere::$largeur)
			Etagere::$largeur=$width;
		echo Edge::getEtagereHTML();
		list($html, $pourcentage_visible)=Edge::getPourcentageVisible($id_user, true);
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
