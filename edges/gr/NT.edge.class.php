<?php
class gr_NT extends Edge {
	var $pays='gr';
	var $magazine='NT';
	var $intervalles_validite=array('159');

	static $largeur_defaut=4;
	static $hauteur_defaut=208;

	function gr_NT ($numero) {
		$this->numero=$numero;
		$this->hauteur=208*Edge::$grossissement;
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
