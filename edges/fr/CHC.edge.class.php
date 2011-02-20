<?php
class fr_CHC extends Edge {
    var $pays='fr';
    var $magazine='CHC';
    var $intervalles_validite=array('2');

    static $largeur_defaut=8;
    static $hauteur_defaut=296;

    function fr_CHC ($numero) {
        $this->numero=$numero;
        $this->hauteur=296*Edge::$grossissement;
        $this->largeur=8*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
    }
}
?>
