<?php
class Texte {
	var $texte;
	var $pos_x;
	var $pos_y;
	var $taille;
	var $angle;
	var $couleur;
	var $police;
	function Texte($texte,$pos_x,$pos_y,$taille,$angle,$couleur,$police) {
		$this->texte=$texte;
		$this->pos_x=$pos_x;$this->pos_y=$pos_y;
		$this->taille=$taille;
		$this->angle=$angle;
		$this->couleur=$couleur;
		$this->police='edges/'.$police;
	}

    function dessiner($image) {
        imagettftext($image,$this->taille,$this->angle,$this->pos_x,$this->pos_y,$this->couleur,$this->police,$this->texte);
    }
}