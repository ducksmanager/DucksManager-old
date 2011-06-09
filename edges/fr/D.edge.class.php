<?php
class fr_D extends Edge {
	var $pays='fr';
	var $magazine='D';
	var $intervalles_validite=array('173');
	static $largeur_defaut=3;
	static $hauteur_defaut=202;

	function fr_D ($numero) {
		$this->numero=$numero;
		$this->largeur=3*Edge::$grossissement;
			$this->hauteur=202*Edge::$grossissement;
		
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}
	
	function dessiner() {
		return $this->image;
	}

}