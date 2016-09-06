<?php
require_once('DucksManager_Core.class.php');
$requete_tous_magazines_possedes='SELECT DISTINCT Pays,Magazine FROM numeros ORDER BY Pays, Magazine';
$resultat_tous_magazines_possedes=DM_Core::$d->requete_select_distante($requete_tous_magazines_possedes);
$pays_tmp='';

foreach($resultat_tous_magazines_possedes as $pays_magazine) {
    $pays=$pays_magazine['Pays'];
    $magazine=$pays_magazine['Magazine'];
	if ($pays_tmp != $pays)
		$magazines_inducks=Inducks::get_liste_magazines($pays);
    if (!array_key_exists($magazine, $magazines_inducks)) {
        echo $pays.'/'.$magazine.' n\'existe plus<br />';
        continue;
    }
    $pays_tmp=$pays;
}