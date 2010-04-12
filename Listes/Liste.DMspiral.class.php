<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Format_liste.php');
class DMspiral extends Format_liste {
	function DMspiral() {
		$this->les_plus=array(L::_('dmspiral_plus_1'),
							  L::_('dmspiral_plus_2'),
							  L::_('dmspiral_plus_3'));
		$this->les_moins=array(L::_('dmspiral_moins_1'),
							   L::_('dmspiral_moins_2'),
							   L::_('dmspiral_moins_3'));
		$this->description=L::_('dmspiral_description');
	}
	
	function afficher($liste) {
		foreach($liste as $pays=>$numeros_pays) {
			foreach($numeros_pays as $magazine=>$numeros) {
				$chaine='';
				foreach($numeros as $numero_et_etat) { 
					$numero=$numero_et_etat[0];
					$etat=$numero_et_etat[1];
					$chaine.=$magazine.'!'.
					$numero.'!'.
					$etat.'!'.
					'2005-00-00'.'!'.
					'a'.',';
				}
				//echo '<div id="mon_image"><table border="1"><tr><td>';
				echo '<img src="image.php?chaine='.$chaine.'&amp;mag='.$magazine.'" />';
				//echo '</td><td>'.$magazine.'('.$pays.')</td></tr></table></div>';
			}
		}
	}
}
?>