<?php

class Trait extends ElementGraphique {
    var $x1;
    var $y1;
    var $x2;
    var $y2;
    var $couleur;

    function Trait() {
        $this->setType(get_class());
    }

    function getCanevasW() {
        return $this->x2-$this->x1;
    }

    function getCanevasH() {
        return $this->y2-$this->y1;
    }

    function dessiner() {
        $largeur=$this->x2-$this->x1;
        $hauteur=$this->y2-$this->y1;
        imageline(ElementGraphique::$getImageRelative, $this->x1, $this->y1, $this->x2, $this->y2, $this->couleur->index);
        imagecopy($this->image_relative->contenu, $this->image->contenu, $this->x1, $this->y1, 0, 0, $largeur, $hauteur);
        parent::dessiner();
    }
}

?>
