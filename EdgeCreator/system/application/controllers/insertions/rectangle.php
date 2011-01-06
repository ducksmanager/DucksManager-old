<?php

class Rectangle extends ElementGraphique {
    var $x1;
    var $y1;
    var $x2;
    var $y2;
    var $couleur;
    var $rempli;

    function Rectangle() {
        $this->rempli=new Booleen();
        $this->setType(get_class());

        parent::ElementGraphique();
    }

    function getCanevasW() {
        return 1+$this->x2-$this->x1;
    }

    function getCanevasH() {
        return 1+$this->y2-$this->y1;
    }

    function dessiner() {
        $largeur=$this->x2-$this->x1;
        $hauteur=$this->y2-$this->y1;
        if ($this->rempli->v)
            imagefilledrectangle($this->image->contenu, 0, 0, $largeur-1, $hauteur-1, $this->couleur->index);
        else
            imagerectangle($this->image->contenu, 0, 0, $largeur-1, $hauteur-1, $this->couleur->index);
        imagecopy($this->image_relative->contenu, $this->image->contenu, $this->x1, $this->y1, 0, 0, $largeur, $hauteur);
        parent::dessiner();
    }
}

?>
