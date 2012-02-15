<?php header('Content-Type: text/html; charset=utf-8');
require_once('DucksManager_Core.class.php');
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Format_liste.php');
class classique extends Format_liste {
	static $titre='Liste classique';
	function classique() {
		$this->les_plus=array(CLASSIQUE_PLUS_1);
		$this->les_moins=array(CLASSIQUE_MOINS_1,CLASSIQUE_MOINS_2,CLASSIQUE_MOINS_3);
		$this->description=CLASSIQUE_DESCRIPTION;
	}

	function afficher($liste) {
			$noms_complets_pays=DM_Core::$d->get_noms_complets_pays();
			foreach($liste as $pays=>$numeros_pays) {
				$noms_complets_magazines=DM_Core::$d->get_noms_complets_magazines($pays);
				?><br /><b><i><?=$noms_complets_pays[$pays]?></i></b><br /><?php
				foreach($numeros_pays as $magazine=>$numeros) {
					?><u><?=array_key_exists($magazine,$noms_complets_magazines) ? $noms_complets_magazines[$magazine] : $magazine?></u> <?php
					$debut=true;
					sort($numeros);
					$texte=array();
					foreach($numeros as $numero) {
						$texte[]=is_array($numero) ? $numero[0] : $numero;
					}
					echo ucfirst(count($numeros)==1 ? NUMERO:NUMEROS).' '.implode(', ',$texte);
					?><br /><?php
				}
			}
	}
}
?>