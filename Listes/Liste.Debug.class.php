<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Format_liste.php');
class Debug extends Format_liste {
	function Debug() {
		$this->les_plus=array(L::_('debug_plus_1'));
		$this->les_moins=array(L::_('debug_moins_1'),
							   L::_('debug_moins_2'),
							   L::_('debug_moins_3'),
							   L::_('debug_moins_4'));
		$this->description=L::_('debug_description');
	}
	
	function afficher($liste) {
		foreach($liste as $pays=>$numeros_pays) {
			echo '<u>'.$pays.':</u><br /><pre>';print_r($numeros_pays);echo '</pre>';
		}
	}
}
?>