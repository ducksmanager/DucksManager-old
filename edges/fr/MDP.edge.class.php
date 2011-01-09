<?php
class fr_MDP extends Edge {
    var $pays='fr';
    var $magazine='MDP';
    var $intervalles_validite=array(1);

    static $largeur_defaut=192;
    static $hauteur_defaut=7;

    function fr_MDP ($numero) {
        $this->numero=$numero;
        $this->hauteur=192*Edge::$grossissement;
        $this->largeur=7*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
    }
}
?>
