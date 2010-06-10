<?php
require_once('Format_liste.php');
class Classique_abrege extends Format_liste {
	function Classique_abrege() {
		$this->les_plus=array(CLASSIQUE_ABREGE_PLUS_1);
		$this->les_moins=array(CLASSIQUE_ABREGE_MOINS_1,CLASSIQUE_ABREGE_MOINS_2);
		$this->description=CLASSIQUE_ABREGE_DESCRIPTION;
	}

	function afficher($liste) {
		foreach($liste as $pays=>$numeros_pays) {
			foreach($numeros_pays as $magazine=>$numeros) {
				echo '('.$pays.') <u>'.$magazine.'</u> : ';
				$cpt=0;
				foreach($numeros as $numero) {
					$cpt++;
				}
				if ($cpt>1)
					echo $cpt.' '.NUMEROS;
				else
					echo $cpt.' '.NUMERO;
				echo '<br />';
			}
		}
	}
}
?>