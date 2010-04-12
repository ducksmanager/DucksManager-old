<?php
header('Content-type: image/png');
include_once('Texte.class.php');
class Edge {
	var $pays;
	var $magazine;
	var $numero;
	var $textes=array();
	var $largeur;
	var $hauteur;
	static $grossissement=8;
	function Edge($pays,$magazine,$numero) {
		$this->pays=$pays;$this->magazine=$magazine;$this->numero=$numero;
		if (file_exists($this->pays.'/'.$this->magazine.'.edge.class.php')) {
			/*include_once($this->pays.'/'.$this->magazine.'.edge.class.php');
			$tranche=new $this->magazine();*/
			require_once($this->pays.'/'.$this->magazine.'.edge.class.php');
			$o=new $this->magazine($this->numero);
			$image=$o->dessiner();
			if (!isset($image)) $image=$o->dessiner_defaut();
			foreach($o->textes as $texte) {
				imagettftext($image,$texte->taille,$texte->angle,$texte->pos_x,$texte->pos_y,$texte->couleur,$texte->police,$texte->texte);
			}
			imageantialias($image, true);
			imagepng($image);
		}
	}
	
	function dessiner_defaut() {
		$this->largeur=20;
		$this->hauteur=219.7;
		$image=imagecreatetruecolor($this->largeur,$this->hauteur);
		$blanc=imagecolorallocate($image,255,255,255);
		$noir = imagecolorallocate($image, 0, 0, 0);
		imagefilledrectangle($image, 0, 0, $this->largeur-2, $this->hauteur-2, $blanc);
		imagerectangle($image, 0, 0, $this->largeur, $this->hauteur, $noir);
		return $image;
	}
}

$e=new Edge($_GET['pays'],$_GET['magazine'],$_GET['numero']);