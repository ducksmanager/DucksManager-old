<?php
class fr_GB extends Edge {
	var $pays='fr';
	var $magazine='GB';
	var $intervalles_validite=array('4');
	static $largeur_defaut=31;
	static $hauteur_defaut=353;

	function fr_GB ($numero) {
		$this->numero=$numero;
		$this->hauteur=353;
		$this->largeur=31;
		$this->hauteur*=Edge::$grossissement;
		$this->largeur*=Edge::$grossissement;
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}
	function dessiner() {
		return $this->image;
	}

}