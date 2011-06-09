<?php
class fr_Z extends Edge {
	var $pays='fr';
	var $magazine='Z';
	var $intervalles_validite=array('2');
	var $en_cours=array();
	static $largeur_defaut=21;
	static $hauteur_defaut=322;


	function fr_Z($numero) {
		$this->numero=$numero;
		$this->largeur=21*Edge::$grossissement;
		$this->hauteur=322*Edge::$grossissement;
		
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}

	function dessiner() {
		
	}
}
?>
