<?php header('Content-Type: text/html; charset=utf-8');
require_once('Inducks.class.php');
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Format_liste.php');
class classique extends Format_liste {
	static $titre='Liste classique';
	function __construct() {
		$this->les_plus=array(CLASSIQUE_PLUS_1);
		$this->les_moins=array(CLASSIQUE_MOINS_1,CLASSIQUE_MOINS_2,CLASSIQUE_MOINS_3);
		$this->description=CLASSIQUE_DESCRIPTION;
	}

	function afficher($liste) {
		$publication_codes=array();
		foreach($liste as $pays=>$numeros_pays) {
			foreach(array_keys($numeros_pays) as $magazine) {
				$publication_codes[]=$pays.'/'.$magazine;
			}
		}
		list($noms_pays,$noms_magazines) = Inducks::get_noms_complets($publication_codes);
		foreach($liste as $pays=>$numeros_pays) {
			?><br /><b><i><?=$noms_pays[$pays]?></i></b><br /><?php
			foreach($numeros_pays as $magazine=>$numeros) {
				?><u><?=array_key_exists($pays.'/'.$magazine,$noms_magazines) ? $noms_magazines[$pays.'/'.$magazine] : $magazine?></u> <?php
				sort($numeros);
				$texte=array();
				foreach($numeros as $numero) {
					$texte[]=urldecode(is_array($numero) ? $numero[0] : $numero);
				}
				echo implode(', ',$texte);
				?><br /><?php
			}
		}
	}
}
?>