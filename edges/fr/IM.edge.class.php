<?php
class fr_IM extends Edge {
    var $pays='fr';
    var $magazine='IM';
    var $intervalles_validite=array('2');

    static $largeur_defaut=10;
    static $hauteur_defaut=320;

    function fr_IM ($numero) {
        $this->numero=$numero;
        $this->hauteur=320*Edge::$grossissement;
        $this->largeur=10*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
    }
}
?>
