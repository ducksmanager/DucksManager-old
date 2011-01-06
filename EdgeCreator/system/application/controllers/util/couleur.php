<?php
class Couleur {
    var $r;
    var $g;
    var $b;
    var $index;
    
    static function indexToRGB($image,$index) {
        $rgbalpha=imagecolorsforindex(Outil::$liste_images[$this->image], $this->$num_couleur);
        return array($rgbalpha['red'],$rgbalpha['green'],$rgbalpha['blue']);
    }

    function __construct(Image $image,$r,$g,$b) {
        $this->r=$r;$this->g=$g;$this->b=$b;
        $this->index=imagecolorallocate($image->contenu, $r, $g, $b);
    }

    function getRGB() {
        return array($this->r,$this->g,$this->b);
    }

    function __toString() {
        return 'R'.$this->r.' G'.$this->g.' B'.$this->b;
    }
}

?>
