<?php header('Content-Type: text/html; charset=utf-8');
include_once 'locales/lang.php';
require_once 'Inducks.class.php';
require_once 'Format_liste.php';
class classique extends Format_liste {
	static $titre='Liste classique';
	function __construct() {
		$this->les_plus= [CLASSIQUE_PLUS_1];
		$this->les_moins= [CLASSIQUE_MOINS_1,CLASSIQUE_MOINS_2,CLASSIQUE_MOINS_3];
		$this->description=CLASSIQUE_DESCRIPTION;
	}

	function afficher($liste) {
		$publication_codes= [];
		foreach($liste as $pays=>$numeros_pays) {
			foreach(array_keys($numeros_pays) as $magazine) {
				$publication_codes[]=$pays.'/'.$magazine;
			}
		}
		$noms_pays = Inducks::get_noms_complets_pays($publication_codes);
		$noms_magazines = Inducks::get_noms_complets_magazines($publication_codes);

		foreach($liste as $pays=>$numeros_pays) {
			?><br /><b><i><?=$noms_pays[$pays]?></i></b><br /><?php
			foreach($numeros_pays as $magazine=>$numeros) {
				?><u><?=array_key_exists($pays.'/'.$magazine,$noms_magazines) ? $noms_magazines[$pays.'/'.$magazine] : $magazine?></u> <?php
				sort($numeros);
				echo implode(', ', array_map(function($numero_data) {
				    [,,$numero] = $numero_data;
				    return $numero;
                }, $numeros));
				?><br /><?php
			}
		}
	}
}
?>