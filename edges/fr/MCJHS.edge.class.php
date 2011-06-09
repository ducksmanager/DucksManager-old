<?php
class fr_MCJHS extends Edge {
	var $pays='fr';
	var $magazine='MCJHS';
	var $intervalles_validite=array(1,2);

	static $largeur_defaut=20;
	static $hauteur_defaut=190;

	function fr_MCJHS ($numero) {
		$this->numero=$numero;
		$this->hauteur=190*Edge::$grossissement;
		$this->largeur=20*Edge::$grossissement;
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}

	function dessiner() {
		
	}
}
?>
