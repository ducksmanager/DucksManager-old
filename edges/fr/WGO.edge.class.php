<?php
class fr_WGO extends Edge {
    var $pays='fr';
    var $magazine='WGO';
    var $intervalles_validite=array(1);

    static $largeur_defaut=14;
    static $hauteur_defaut=270;

    function fr_WGO ($numero) {
        $this->numero=$numero;
        $this->largeur=14*Edge::$grossissement;
        $this->hauteur=270*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
    }
}
?>
