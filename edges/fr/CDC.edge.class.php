<?php
class fr_CDC extends Edge {
    var $pays='fr';
    var $magazine='CDC';
    var $intervalles_validite=array('4');

    static $largeur_defaut=8;
    static $hauteur_defaut=296;

    function fr_CDC ($numero) {
        $this->numero=$numero;
        $this->largeur=8*Edge::$grossissement;
        $this->hauteur=296*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
        return $this->image;
    }
}
?>
