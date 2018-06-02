<?php
include_once 'Parametre_liste.php';

class Format_liste {
	static $titre;
	var $parametres= [];
	var $description;
	var $les_plus= [];
	var $les_moins= [];
	static $regex_numero_double='#([0-9]{2})([0-9]{2})\-([0-9]{2})#is';
	
	function est_valide() {
		foreach($this->parametres as $parametre) {
			if (!$parametre->verif()) {
                return false;
            }
		}
		return true;
	}
	
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
	
	function parametre_est_modifiable($parametre) {
		return is_object($parametre) && !$parametre instanceof \Parametre_fixe;
	}
	
	function getListeParametresModifiables() {
		$parametres_filtres= [];
		foreach((array)$this->parametres as $nom_parametre=>$parametre) {
			if ($this->parametre_est_modifiable($parametre)) {
                $parametres_filtres[$nom_parametre] = $parametre;
            }
		}
		return (object)$parametres_filtres;
		
	}
	function getListeParametres() {
		return (array)$this->parametres;
	}
}