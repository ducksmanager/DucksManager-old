<?php
require_once('Format_liste.php');
class Classique_abrege extends Format_liste {
	function Classique_abrege() {
		$this->les_plus=array(L::_('classique_abrege_plus_1'));
		$this->les_moins=array(L::_('classique_abrege_moins_1'),
							   L::_('classique_abrege_moins_2'));
		$this->description=L::_('classique_abrege_description');
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
					echo $cpt.' '.L::_('numeros');
				else
					echo $cpt.' '.L::_('numero');
				echo '<br />';
			}
		}
	}
}
?>