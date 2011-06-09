<?php
class us_LICGY extends Edge {
	var $pays='us';
	var $magazine='LICGY';
	var $intervalles_validite=array('1','2','3','4','5','6');
	var $en_cours=array();
	static $largeur_defaut=5;
	static $hauteur_defaut=279;


	function us_LICGY($numero) {
		$this->numero=$numero;
		$this->largeur=5*Edge::$grossissement;
		$this->hauteur=279*Edge::$grossissement;
		
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}

	function dessiner() {
		return $this->image;
	}
}
?>
