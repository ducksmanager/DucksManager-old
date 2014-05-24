<?php

class Etat {
    /**
     * @var Etat[]
     */
    static $liste = array();

    var $nom;
    var $libelle;
    var $couleur;

    function __construct($nom, $libelle, $couleur)
    {
        $this->nom = $nom;
        $this->libelle = $libelle;
        $this->couleur = $couleur;
    }

    static function add_to_list(Etat $etat) {
        self::$liste[$etat->nom] = $etat;
    }
}

Etat::add_to_list(new Etat('mauvais', MAUVAIS,'#FF0000'));
Etat::add_to_list(new Etat('moyen', MOYEN,'#FF8000'));
Etat::add_to_list(new Etat('bon', BON,'#2CA77B'));
Etat::add_to_list(new Etat('indefini', INDEFINI,'#808080'));
Etat::add_to_list(new Etat('non_possede', NON_POSSEDE,'#000000'));