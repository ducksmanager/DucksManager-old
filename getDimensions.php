<?php
global $liste_magazines_recuperes;
$liste_magazines_recuperes=array();
function getDimensionsParDefaut($pays,$magazine,$numeros) {
	global $liste_magazines_recuperes;
	if (isset($liste_magazines_recuperes[$pays.'/'.$magazine]))
		$resultat_dimensions = $liste_magazines_recuperes[$pays.'/'.$magazine];
	else {
		include_once('Inducks.class.php');
		global $liste_numeros;
		$liste_numeros=Inducks::get_numeros($pays,$magazine,false,true);
		$liste_numeros=$liste_numeros[0];
		
		$requete_dimensions='SELECT Numero_debut, Numero_fin, Option_nom, Option_valeur FROM edgecreator_modeles_vue '
						   .'WHERE Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Nom_fonction=\'Dimensions\' AND username=\'brunoperel\'';
		$resultat_dimensions=Inducks::requete_select($requete_dimensions,'db301759616');
	}
	$liste_magazines_recuperes[$pays.'/'.$magazine]=$resultat_dimensions;
	
	$dimensions=array();
	foreach($numeros as $numero) {
		$x=null;
		$y=null;
		foreach($resultat_dimensions as $dimension) {
			if (!is_null($dimension['Option_nom']) && $dimension['Option_nom']=='Dimension_x' && est_dans_intervalle($numero,$dimension))
				$x=$dimension['Option_valeur'];
			if (!is_null($dimension['Option_nom']) && $dimension['Option_nom']=='Dimension_y' && est_dans_intervalle($numero,$dimension))
				$y=$dimension['Option_valeur'];
		}
		$dimensions[$numero]=(is_null($x) || is_null($y)) ? 'null' : ($x.'x'.$y);
	}
	return $dimensions;
}


function est_dans_intervalle($numero,$intervalle) {
	global $liste_numeros;
	
	$numeros_dispos=$liste_numeros;
	
	$numero_debut=$intervalle['Numero_debut'];
	$numero_fin=$intervalle['Numero_fin'];
	
	if ($numero_debut === $numero_fin)
		return $numero_debut === $numero;
	
	$numero_debut_trouve=false;
	foreach($numeros_dispos as $numero_dispo) {
		if ($numero_dispo==$numero_debut)
			$numero_debut_trouve=true;
		if ($numero_dispo==$numero && $numero_debut_trouve) {
			return true;
		}
	}
	
	return false;
}

if (isset($_GET['pays']) && isset($_GET['magazine']) && isset($_GET['numeros'])) {
	$pays=$_GET['pays'];
	$magazine=$_GET['magazine'];
	$numeros=explode(',',$_GET['numeros']);
	echo getDimensionsParDefaut($pays,$magazine,$numeros);
	
}