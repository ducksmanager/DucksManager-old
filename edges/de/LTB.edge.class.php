<?php
class de_LTB extends Edge {
    var $pays='de';
    var $magazine='LTB';
    var $intervalles_validite=array('63');

    static $largeur_defaut=13;
    static $hauteur_defaut=183;

    function de_LTB ($numero) {
        $this->numero=$numero;
        $this->hauteur=183*Edge::$grossissement;
        $this->largeur=13*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
        return $this->image;
    }
}
?>
