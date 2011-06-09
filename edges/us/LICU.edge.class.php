<?php
class us_LICU extends Edge {
	var $pays='us';
	var $magazine='LICU';
	var $intervalles_validite=array('19');
	var $en_cours=array();
	static $largeur_defaut=5;
	static $hauteur_defaut=279;


	function us_LICU ($numero) {
		$this->numero=$numero;
		$this->largeur=5*Edge::$grossissement;
		$this->hauteur=279*Edge::$grossissement;
		
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}

	function dessiner() {
		
	}
}
?>
