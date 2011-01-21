<?php
class it_M extends Edge {
    var $pays='it';
    var $magazine='M';
    var $intervalles_validite=array(53);

    static $largeur_defaut=8;
    static $hauteur_defaut=205;

    function it_M ($numero) {
        $this->numero=$numero;
        $this->largeur=8*Edge::$grossissement;
        $this->hauteur=205*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
    }
}
?>
