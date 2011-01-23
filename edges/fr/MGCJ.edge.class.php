<?php
class fr_MGCJ extends Edge {
    var $pays='fr';
    var $magazine='MGCJ';
    var $intervalles_validite=array('1');

    static $largeur_defaut=55;
    static $hauteur_defaut=195;

    function fr_MGCJ ($numero) {
        $this->numero=$numero;
        $this->largeur=55*Edge::$grossissement;
        $this->hauteur=195*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
        return $this->image;
    }
}
?>
