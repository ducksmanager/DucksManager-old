<?php
class fr_JMS extends Edge {
    var $pays='fr';
    var $magazine='JMS';
    var $intervalles_validite=array(2730);

    static $largeur_defaut=6;
    static $hauteur_defaut=285;

    function fr_JMS ($numero) {
        $this->numero=$numero;
        $this->largeur=6*Edge::$grossissement;
        $this->hauteur=285*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
        return $this->image;
    }
}
?>
