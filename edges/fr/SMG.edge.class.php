<?php
class fr_SMG extends Edge {
    var $pays='fr';
    var $magazine='SMG';
    var $intervalles_validite=array(1408,1430,1623,1667,1719,1727,1771);

    static $largeur_defaut=10;
    static $hauteur_defaut=292;

    function fr_SMG ($numero) {
        $this->numero=$numero;
        $this->largeur=10*Edge::$grossissement;
        $this->hauteur=292*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
        return $this->image;
    }
}
?>
