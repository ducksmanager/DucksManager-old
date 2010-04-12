<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Liste.class.php');
require_once('JS.class.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1"
 http-equiv="content-type">
  <title>DucksManager : <?php L::_('impression_collection');?></title>
  <link rel="stylesheet" type="text/css" href="style.css" />
  <!--[if IE]>
	<style type="text/css" media="all">@import "fix-ie.css";</style>
  <![endif]-->
  <link rel="stylesheet" type="text/css" href="scriptaculous.css" />
  <link rel="icon" type="image/png" href="favicon.png" />
  <?php 
  new JS('prototype.js');
  new JS('js/scriptaculous/src/scriptaculous.js');
  new JS('js/my_scriptaculous.js');
  new JS('js/ajax.js');?>
</head>
<body onload="implement_drags();observe_options_clicks();get_types_listes();">
<?php
$d=new Database();
if (!$d) {
	echo L::_('probleme_bd');
	exit(-1);
}
$id_user=$d->user_to_id($_SESSION['user']);
$l=$d->toList($id_user);
foreach($l->collection as $pays=>$magazines) { 
	$i=new Inducks();
	$noms_complets_magazines=$i->get_noms_complets_magazines($pays);
	foreach($magazines as $nom_magazine=>$magazine) {
		$type_liste_affichage='DMtable';
		/*if ($nom_magazine=='D') {
			$sous_liste=new Liste();
			$sous_liste=$l->sous_liste($pays,$nom_magazine);
			
			$sous_liste2=new Liste();
			$sous_liste2=$l->sous_liste('fr','JM');
			$sous_liste->fusionnerAvec($sous_liste2);
		}
		else {*/
			$sous_liste=new Liste();
			$sous_liste=$l->sous_liste($pays,$nom_magazine);
		//}
		//echo '<div id="'.$pays.'_'.$nom_magazine.'" ><span>';
		echo '<span id="type_liste'.$pays.'_'.$nom_magazine.'" style="display:none">'.$type_liste_affichage.'</span>';
		echo '<table class="draggable_box"><tr><td><table width="100%"><tr><td valign="top">';
		echo $noms_complets_magazines[$nom_magazine];
		echo '</td><td width="140">';
		echo '<div id="options'.$pays.'_'.$nom_magazine.'" class="options_text">&nbsp;+&nbsp;</div><p id="box_options'.$pays.'_'.$nom_magazine.'" class="box_options"></p>';
		echo '</td></tr></table>';
		echo '</td></tr><tr id="'.$pays.'_'.$nom_magazine.'"><td>';
		echo $sous_liste->afficher($type_liste_affichage);
		echo '</td><td valign="top">';
		echo '</td></tr></table><br /><br />';
	}
}
?>
</body>
</html>