<?php
include_once('../Database.class.php');

$requete_nb_tranches='SELECT COUNT(*) AS nb FROM tranches_pretes';
$resultat_nb_tranches=DM_Core::$d->requete_select($requete_nb_tranches);

echo $resultat_nb_tranches[0]['nb'].' tranches pr&ecirc;tes.';

echo '<hr />';
echo 'Recherche des images inexistantes : <br />';
$requete_noms_images='SELECT Pays, Magazine, Option_valeur, Numero_debut, Numero_fin '
					.'FROM edgecreator_modeles_vue '
					.'WHERE Option_nom="Source" AND Nom_fonction="Image" AND username="brunoperel"';
$resultat_noms_images=DM_Core::$d->requete_select($requete_noms_images);

ob_start();
$numeros_inducks= [];
foreach($resultat_noms_images as $image) {
	$pays=$image['Pays'];
	$magazine=$image['Magazine'];
	if (!array_key_exists($pays,$numeros_inducks))
		$numeros_inducks[$pays]= [];
	if (!array_key_exists($magazine,$numeros_inducks[$pays]))
		$numeros_inducks[$pays][$magazine]=Inducks::get_numeros($pays,$magazine,false,true);
	$nom_image=$image['Option_valeur'];
	$chemin_image=$image['Pays'].'/elements/'.$nom_image;
	if (!file_exists($chemin_image)) { // Dans ce cas on essaie en remplacant les templates
		$numero_debut=$image['Numero_debut'];
		$numero_fin=$image['Numero_fin'];
		if ($numero_debut === $numero_fin) {
			$chemin_image=$image['Pays'].'/elements/'.appliquer_templates($nom_image,$numero_debut);
			if (!file_exists($chemin_image)) {
				?><?=$chemin_image?> n'existe pas | 
				<form method="get" action="imagerename.php" style="display:inline">
					<input type="hidden" name="pays" value="<?=$image['Pays']?>" />
					<input type="hidden" name="rename_file" value="false" />
					
					Remplacer le nom <input class="text_input" autocomplete="off" type="text" name="ancien_nom"  value="<?=$nom_image?>" />
					par 			 <input class="text_input" autocomplete="off" type="text" name="nouveau_nom" value="<?=$nom_image?>" />
					<input type="submit" value="OK" />
					<br />
				</form><?php
			}
		}
		else {
			$numero_debut_trouve=false;
			foreach($numeros_inducks[$pays][$magazine] as $numero_dispo) {
				if ($numero_dispo==$numero_debut)
					$numero_debut_trouve=true;
				if ($numero_debut_trouve) {
					$chemin_image=$image['Pays'].'/elements/'.appliquer_templates($nom_image,$numero_dispo);
					if (!file_exists($chemin_image)) {
						echo $chemin_image.' n\'existe pas (Numero : '.$numero_dispo.')<br />';
					}
				}
				if ($numero_dispo==$numero_fin)
					break;
			}
		}
	}
}

$contenu_images_non_referencees = ob_get_contents();
if (empty($contenu_images_non_referencees)) {
	echo 'OK : pas d\'images non r&eacute;f&eacute;renc&eacute;s<br />';
}
else
	echo $contenu_images_non_referencees;
ob_end_flush();
	
function appliquer_templates($str,$numero) {
	$str=preg_replace('#\[Numero\]#is',$numero,$str);
	$regex='#\[Numero\[([0-9]+)\]\]#is';
	$spl=str_split($numero);
	if (0!=preg_match_all($regex, $str, $matches)) {
		foreach($matches[1] as $i=>$num_caractere) {
			if (!array_key_exists($num_caractere, $spl))
				$str=str_replace($matches[0][$i],'',$str);
			else
				$str=str_replace($matches[0][$i],preg_replace($regex, $spl[$num_caractere],$matches[0][$i]),$str);
		}
	}
	return $str;
}
?>