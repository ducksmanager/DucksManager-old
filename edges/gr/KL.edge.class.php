<?php
class gr_KL extends Edge {
	var $pays='gr';
	var $magazine='KL';
	var $intervalles_validite=array('224');

	static $largeur_defaut=6;
	static $hauteur_defaut=183;

	function gr_KL ($numero) {
		$this->numero=$numero;
		$this->hauteur=183*Edge::$grossissement;
		$this->largeur=6*Edge::$grossissement;
		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}

	function dessiner() {
		return $this->image;
	}
}
?>
