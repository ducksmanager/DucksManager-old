<?php
class fr_DDD extends Edge {
	var $pays='fr';
	var $magazine='DDD';
	var $intervalles_validite=array(1,2,3);
	static $largeur_defaut=30;
	static $hauteur_defaut=247;

	var $serie;
	var $numero_serie;

	function fr_DDD($numero) {
		$this->numero=$numero;
		$this->largeur=30*Edge::$grossissement;
		$this->hauteur=247*Edge::$grossissement;
		
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}

	function dessiner() {
		
		return $this->image;
	}
}
?>
