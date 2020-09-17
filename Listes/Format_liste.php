<?php
include_once 'Parametre_liste.php';

class Format_liste {
    var $parametres= [];
    var $description;
    var $les_plus= [];
    var $les_moins= [];
    static $regex_numero_double='#([0-9]{2})([0-9]{2})\-([0-9]{2})#is';

    function p($nom) {
        return $this->parametres->$nom->valeur ?? $this->parametres->$nom;
    }

    function ajouter_parametres($tab_parametres) {
        foreach($tab_parametres as &$parametre) {
            if (!is_object($parametre)) {
                $parametre = new Parametre_fixe ($parametre);
            }
        }
        $parametres=array_merge((array)$this->parametres,$tab_parametres);
        $this->parametres=(object)$parametres;
    }
}
