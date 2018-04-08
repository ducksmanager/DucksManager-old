<?php
global $dimensions;

function getDimensionsParDefaut($publication_codes) {
	global $dimensions;
	$dimensions= [];
	
	foreach($publication_codes as $i=>$publication_code) {
		$publication_codes[$i]="'".$publication_code."'";
	}
	include_once 'Inducks.class.php';
	$publication_codes_subarrays=array_chunk($publication_codes, 50);
	foreach($publication_codes_subarrays as $publication_codes) {
		$requete_dimensions='SELECT Pays,Magazine, Numero_debut, Numero_fin, Option_nom, Option_valeur FROM edgecreator_modeles_vue '
						   .'WHERE CONCAT(Pays,\'/\',Magazine) IN ('.implode(',', $publication_codes).') '
						   .'  AND Nom_fonction=\'Dimensions\' AND username=\'brunoperel\'';
		$resultat_dimensions=Inducks::requete_select($requete_dimensions,ServeurDb::$nom_db_DM);
		foreach($resultat_dimensions as $resultat) {
			$pays=$resultat['Pays'];
			$magazine=$resultat['Magazine'];
            $publicationCode = $pays . '/' . $magazine;
            if (!isset($dimensions[$publicationCode])) {
				$dimensions[$publicationCode]= [];
			}
			$dimensions[$publicationCode][]=$resultat;
		}
	}
}

function getDimensionsParDefautMagazine($pays,$magazine,$numeros) {
	global $dimensions;
	if (!isset($dimensions)) {
		return 'null';
	}
	
	foreach($numeros as $numero) {
		$x=null;
		$y=null;
		if (array_key_exists($pays.'/'.$magazine, $dimensions)) {
			foreach($dimensions[$pays.'/'.$magazine] as $dimension) {
				if ($dimension['Option_nom']==='Dimension_x' && est_dans_intervalle($pays.'/'.$magazine,$numero,$dimension)) {
                    $x = $dimension['Option_valeur'];
                }
				if ($dimension['Option_nom']==='Dimension_y' && est_dans_intervalle($pays.'/'.$magazine,$numero,$dimension)) {
                    $y = $dimension['Option_valeur'];
                }
			}
		}
		$dimensions[$numero]=(is_null($x, $y)) ? 'null' : ($x.'x'.$y);
	}
	return $dimensions;
}


function est_dans_intervalle($publicationcode,$numero,$intervalle) {
	global $numeros_inducks;
	
	$numero_debut=$intervalle['Numero_debut'];
	$numero_fin=$intervalle['Numero_fin'];
	
	if ($numero_debut === $numero_fin) {
        return $numero_debut === $numero;
    }
	
	$numero_debut_trouve=false;
	foreach($numeros_inducks[$publicationcode] as $numero_dispo) {
		if ($numero_dispo==$numero_debut) {
            $numero_debut_trouve = true;
        }
		if ($numero_dispo==$numero && $numero_debut_trouve) {
			return true;
		}
	}
	
	return false;
}

if (isset($_GET['pays'], $_GET['magazine'], $_GET['numeros'])) {
	$pays=$_GET['pays'];
	$magazine=$_GET['magazine'];
	$numeros=explode(',',$_GET['numeros']);
	echo getDimensionsParDefautMagazine($pays,$magazine,$numeros);
	
}