<?php
class fr_PM extends Edge {
    var $pays='fr';
    var $magazine='PM';
    var $intervalles_validite=array(array('debut'=>1, 'fin'=>468));
    static $largeur_defaut=6;
    static $hauteur_defaut=254;

    function fr_PM ($numero) {
        $this->numero=$numero;
        if ($this->numero <=185) {
            $this->largeur=7*Edge::$grossissement;
            $this->hauteur=254*Edge::$grossissement;
        }
        elseif ($this->numero <=324) {
            $this->largeur=7*Edge::$grossissement;
            $this->hauteur=285*Edge::$grossissement;
        }
        elseif ($this->numero <=372) {
            $this->largeur=8*Edge::$grossissement;
            $this->hauteur=282*Edge::$grossissement;
        }
        else {
            $this->largeur=6*Edge::$grossissement;
            $this->hauteur=283*Edge::$grossissement;
        }
        /*
        else {
            $this->largeur=13*Edge::$grossissement;
            $this->hauteur=275*Edge::$grossissement;
        }*/
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        return $this->image;
    }

}