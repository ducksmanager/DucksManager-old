<?php

class Item {
    var $nom;
    var $est_prive;
    var $texte;
    var $beta=false;
    
    function __construct($nom, $est_prive, $texte, $beta=false) {
        $this->nom = $nom;
        $this->est_prive = $est_prive;
        $this->texte = $texte;
        $this->beta = $beta;
    }
}

class Menu {
    var $nom;
    var $items;

    function __construct($nom, $items) {
        $this->nom = $nom;
        $this->items = $items;
    }
}

$menus=array(
    new Menu(COLLECTION,
             array(new Item('new', 'never', NOUVELLE_COLLECTION),
                   new Item('open', 'never', OUVRIR_COLLECTION),
                   new Item('bibliotheque', 'always', BIBLIOTHEQUE_COURT),
                   new Item('gerer', 'always', GERER_COLLECTION),
                   new Item('stats', 'always', STATISTIQUES_COLLECTION),
                   new Item('agrandir', 'always', AGRANDIR_COLLECTION),
                   new Item('print', 'always', IMPRIMER_COLLECTION,true),
                   new Item('logout', 'always', DECONNEXION)
            )),
    new Menu(COLLECTION_INDUCKS,
             array(new Item('inducks', 'no', VOUS_POSSEDEZ_UN_COMPTE_INDUCKS)/*
                   new Item('export', 'no', EXPORTER_INDUCKS)*/

)));
?>