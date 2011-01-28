<?php
class it_TD extends Edge {
    var $pays='it';
    var $magazine='TD';
    var $intervalles_validite=array(47);

    static $largeur_defaut=14;
    static $hauteur_defaut=199;

    function it_TD ($numero) {
        $this->numero=$numero;
        $this->largeur=14*Edge::$grossissement;
        $this->hauteur=199*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
    }
}
?>
