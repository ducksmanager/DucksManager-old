<?php
class es_SO extends Edge {
	var $pays='es';
	var $magazine='SO';
	var $intervalles_validite=array(1);
	static $largeur_defaut=7;
	static $hauteur_defaut=207.5;

	function es_SO ($numero) {
		$this->numero=$numero;
		$this->largeur=7*Edge::$grossissement;
		$this->hauteur=207.5*Edge::$grossissement;

		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}
	
	function dessiner() {
		
		return $this->image;
	}

}