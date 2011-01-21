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
            foreach($liste as $pays=>$numeros_pays) {
                if (count($liste) > 1) {
                    ?><br /><b><i><?=DM_Core::$d->get_nom_complet_pays($pays)?></i></b><br /><?php
                }
                foreach($numeros_pays as $magazine=>$numeros) {
                    list($nom_pays_complet,$nom_magazine_complet)=DM_Core::$d->get_nom_complet_magazine($pays, $magazine);
                    if (count($numeros_pays) > 1) {
                        ?><u><?=$nom_magazine_complet?></u><?php
                    }
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