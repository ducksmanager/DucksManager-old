<?php
class gr_KX extends Edge {
	var $pays='gr';
	var $magazine='KX';
	var $intervalles_validite=array('195');

	static $largeur_defaut=4;
	static $hauteur_defaut=238;

	function gr_KX ($numero) {
		$this->numero=$numero;
		$this->hauteur=238*Edge::$grossissement;
		$this->largeur=4*Edge::$grossissement;
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}

	function dessiner() {
		return $this->image;
	}
}
?>
