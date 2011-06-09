<?php
class us_CBDT extends Edge {
	var $pays='us';
	var $magazine='CBDT';
	var $intervalles_validite=array('2');
	var $en_cours=array();
	static $largeur_defaut=9;
	static $hauteur_defaut=259;


	function us_CBDT($numero) {
		$this->numero=$numero;
		$this->largeur=9*Edge::$grossissement;
		$this->hauteur=259*Edge::$grossissement;
		
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}

	function dessiner() {
		
	}
}
?>
