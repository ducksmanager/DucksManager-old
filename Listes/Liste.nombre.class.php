<?php
require_once 'Format_liste.php';
require_once 'Database.class.php';

class Nombre extends Format_liste {
    static $titre='Liste classique abrégée';

    function __construct() {
        $this->les_plus = [CLASSIQUE_ABREGE_PLUS_1];
        $this->les_moins = [CLASSIQUE_ABREGE_MOINS_1, CLASSIQUE_ABREGE_MOINS_2];
        $this->description = CLASSIQUE_ABREGE_DESCRIPTION;
    }

    function afficher($liste) {
        $noms_complets_pays=DM_Core::$d->get_noms_complets_pays();
        foreach ($liste as $pays => $numeros_pays) {
            if (count($liste) > 1) {
                ?><br /><b><i><?=$noms_complets_pays[$pays]?></i></b><br /><?php
            }
            ksort($numeros_pays);
            foreach ($numeros_pays as $magazine => $numeros) {
                $nom_magazine_complet=Inducks::get_nom_complet_magazine($pays, $magazine);
                if (count($numeros_pays) > 1) {
                    ?><u><?=$nom_magazine_complet?></u><?php
                }
                $cpt = count($numeros);
                echo $cpt.' '.($cpt > 1 ? NUMEROS : NUMERO);
                ?><br /><?php
            }
        }
    }

}

?>
