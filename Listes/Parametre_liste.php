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
		echo 'Cette fonction ne peut pas être appelée directement (valeur : '.$valeur.')';
	}
}

class Parametre_valeurs extends Parametre_liste{
	var $valeurs_possibles= [];
	function __construct($texte,$valeurs,$valeur,$defaut) {
		$this->valeurs_possibles=$valeurs;
		parent::__construct($texte,$valeur,$defaut);
	}
	
	function verif($valeur) {
		return in_array($valeur, $this->valeurs_possibles);
	}
}

class Parametre_min_max extends Parametre_liste{
	var $min;
	var $max;
	
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