<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Format_liste.php');
class Classique extends Format_liste {
	function Classique() {
		$this->les_plus=array(CLASSIQUE_PLUS_1);
		$this->les_moins=array(CLASSIQUE_MOINS_1,CLASSIQUE_MOINS_2,CLASSIQUE_MOINS_3);
		$this->description=CLASSIQUE_DESCRIPTION;
	}

	function afficher($liste) {
            require_once('Database.class.php');
            $d=new Database();
            foreach($liste as $pays=>$numeros_pays) {
                ?><br /><b><i><?=utf8_encode($d->get_nom_complet_pays($pays))?></i></b><br /><?php
                foreach($numeros_pays as $magazine=>$numeros) {
                    list($nom_pays_complet,$nom_magazine_complet)=$d->get_nom_complet_magazine($pays, $magazine);
                    ?><u><?=utf8_encode($nom_magazine_complet)?></u><?php
                    $debut=true;
                    sort($numeros);
                    foreach($numeros as $numero) {
                        if (!$debut) echo ',';
                        if (is_array($numero))
                                echo ' '.$numero[0];
                        else
                                echo ' '.$numero;
                        $debut=false;
                    }
                    ?><br /><?php
                }
            }
	}
}
?>