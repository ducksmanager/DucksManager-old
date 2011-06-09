<?php
class fr_MPBH extends Edge {
	var $pays='fr';
	var $magazine='MPBH';
	var $intervalles_validite=array('1','2','3');

	static $largeur_defaut=16;
	static $hauteur_defaut=308;

	var $serie;
	var $numero_serie;

	function fr_MPBH ($numero) {
		$this->numero=$numero;
		switch($this->numero) {
			case '1': case '3':
				$this->largeur=16*Edge::$grossissement;
				$this->hauteur=308*Edge::$grossissement;
			break;
			case '2':
				$this->largeur=11*Edge::$grossissement;
				$this->hauteur=320*Edge::$grossissement;
			break;
		}

		$this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
		if ($this->image===false)
			xdebug_break ();
	}

	function dessiner() {
		return $this->image;
	}
}
?>