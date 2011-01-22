<?php
class fr_PL extends Edge {
    var $pays='fr';
    var $magazine='PL';
    var $intervalles_validite=array(array('debut'=>'1','fin'=>'156'));
    static $largeur_defaut=5;
    static $hauteur_defaut=298;

    function fr_PL ($numero) {
        $this->numero=$numero;
        $this->largeur=5*Edge::$grossissement;
        $this->hauteur=298*Edge::$grossissement;
        
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    
    function dessiner() {
        return $this->image;
    }

}