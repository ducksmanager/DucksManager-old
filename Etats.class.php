<?php
include_once('ParametrageAjoutSuppr.class.php');

class Etat extends ParametreAjoutSuppr {

    var $couleur;

    function __construct($nom, $libelle, $couleur)
    {
        parent::__construct('etat', $nom, $libelle);
        $this->couleur = $couleur;
    }
}

class Etats extends ParametrageAjoutSuppr {

    static $instance;

    function __construct() {
        parent::__construct('etat', ETAT);

        $this->add_to_list(new Etat('mauvais', MAUVAIS,'#FF0000'));
        $this->add_to_list(new Etat('moyen', MOYEN,'#FF8000'));
        $this->add_to_list(new Etat('bon', BON,'#2CA77B'));
        $this->add_to_list(new Etat('indefini', INDEFINI,'#808080'));
        $this->add_to_list(new Etat('non_possede', NON_POSSEDE,'#000000'));
    }
}

Etats::$instance = new Etats();