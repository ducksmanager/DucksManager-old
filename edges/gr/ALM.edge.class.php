<?php
class gr_ALM extends Edge {
	var $pays='gr';
	var $magazine='ALM';
	var $intervalles_validite=array('163','224');

	static $largeur_defaut=4;
	static $hauteur_defaut=206;

	function gr_ALM ($numero) {
		$this->numero=$numero;
		$this->hauteur=206*Edge::$grossissement;
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
