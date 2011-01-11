<?php
class fr_MDP extends Edge {
    var $pays='fr';
    var $magazine='MDP';
    var $intervalles_validite=array(1);

    static $largeur_defaut=186;
    static $hauteur_defaut=6;

    function fr_MDP ($numero) {
        $this->numero=$numero;
        $this->hauteur=186*Edge::$grossissement;
        $this->largeur=6*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
    }
}
?>
