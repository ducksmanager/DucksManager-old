<?php
class fr_AD extends Edge {
    var $pays='fr';
    var $magazine='AD';
    var $intervalles_validite=array('D1','P1');

    static $largeur_defaut=8;
    static $hauteur_defaut=297;

    function fr_AD ($numero) {
        $this->numero=$numero;
        $this->hauteur=297*Edge::$grossissement;
        $this->largeur=8*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
    }
}
?>
