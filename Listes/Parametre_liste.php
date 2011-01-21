<?php

class Parametre_liste {
    var $valeur_defaut;
    var $valeur;
    var $texte;
    function __construct($texte,$valeur,$defaut) {
        $this->texte=$texte;
        $this->valeur=$valeur;
        $this->valeur_defaut=$defaut;
    }
    function verif($valeur) {
        echo 'Cette fonction ne peut pas être appelée directement';
    }
}

class Parametre_valeurs extends Parametre_liste{
    var $valeurs_possibles=array();
    function __construct($texte,$valeurs,$valeur,$defaut) {
        $this->valeurs_possibles=$valeurs;
        parent::__construct($texte,$valeur,$defaut);
    }
    
    function verif($valeur) {
        return in_array($valeur, $this->valeurs_possibles);
    }
}

class Parametre_min_max extends Parametre_liste{
    var $min=null;
    var $max=null;
    
    function  __construct($texte,$min,$max,$valeur,$defaut) {
        $this->min=$min;
        $this->max=$max;
        parent::__construct($texte,$valeur,$defaut);
    }
    
    function verif($valeur) {
        return $valeur >= $this->min && $valeur <= $this->max;
    }
}

class Parametre_fixe  {
    var $valeur;
    function __construct($valeur) {
        $this->valeur=$valeur;
    }
}

class parametres_generaux extends Format_liste {
    function parametres_generaux() {
        $this->ajouter_parametres(array(
            'espacement_boites'=>new Parametre_min_max('Espacement inter-boites',5,40,25,25),
            'bordure_boites_r'=>new Parametre_min_max('Bordure - rouge',0,255,255,255),
            'bordure_boites_v'=>new Parametre_min_max('Bordure - vert',0,255,0,0),
            'bordure_boites_b'=>new Parametre_min_max('Bordure - bleu',0,255,0,0)));
        
    }
} 

?>
