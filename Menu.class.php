<?php

class Item {
	var $nom;
	var $est_prive;
	var $texte;
	var $beta=false;
	var $nouveau=false;
	static $beta_user=false;
	static $action="";
	
	function __construct($nom, $est_prive, $texte, $beta=false, $nouveau=false) {
		$this->nom = $nom;
		$this->est_prive = $est_prive;
		$this->texte = $texte;
		$this->beta = $beta;
		$this->nouveau = $nouveau;
	}
	
	function afficher() {
		if ($this->est_affiche()) {
   	        ?><br /><?php
			if (is_null($this->nom)) { // Menu
				?><span style="font-weight: bold; text-decoration: underline;"><?=$this->texte?></span><?php
			}
			else {
				?>&nbsp;&nbsp;<a href="?action=<?=$this->nom?>"><?=$this->texte?></a><?php
			}
   			if ($this->beta && self::$beta_user) {
		   		?><span class="beta"><?=BETA?></span><?php
   	        }
   			if (!$this->beta && $this->nouveau) {
		   		?><span class="nouveau"><?=NOUVEAU?></span><?php
   	        }
		}
	}
	
	function est_affiche() {
		return ($this->est_prive=='no'
		     || ($this->est_prive=='always' && isset($_SESSION['user']) &&!(self::$action=='logout'))
			 || ($this->est_prive=='never'  &&!(isset($_SESSION['user']) &&!(self::$action=='logout'))))
			&& (!$this->beta || self::$beta_user);
	}
}

class LigneVide extends Item{
	function __construct() {
		
	}
	
	function afficher() {
		?><br /><?php
	}
	
}

class Menu extends Item{
	var $items;

	function __construct($nom, $est_prive, $items) {
		parent::__construct(null,$est_prive,$nom,false);
		$this->items = $items;
	}
	
	public function afficher() {
		parent::afficher();
        foreach($this->items as $item) {
        	$item->afficher();
        }
        
        ?><br /><br /><?php
	}
	
	static function afficherMenus($menus) {
		foreach($menus as $menu) {
			$menu->afficher();
		}
	}
}

$menus=array(
	new Menu(COLLECTION, 'no',
			 array(new Item('new', 'never', NOUVELLE_COLLECTION),
				   new Item('open', 'never', OUVRIR_COLLECTION),
				   new Item('bibliotheque', 'always', BIBLIOTHEQUE_COURT),
				   new Item('gerer', 'always', GERER_COLLECTION),
				   new Item('stats', 'always', STATISTIQUES_COLLECTION),
				   new Item('agrandir', 'always', AGRANDIR_COLLECTION),
				   new Item('print', 'always', IMPRIMER_COLLECTION),
				   new Item('inducks', 'always', VOUS_POSSEDEZ_UN_COMPTE_INDUCKS),
				   new Item('logout', 'always', DECONNEXION)
			)),
	new Menu(DUCKHUNT_TOUR, 'no',
			array(new Item('duckhunt_tour', 'no', PRESENTATION_DUCKHUNT_TOUR),
				  new Item('bouquineries', 'no', RECHERCHER_BOUQUINERIES))
			),
	new LigneVide(),
	new Item('demo','never',DEMO_MENU)
);
?>