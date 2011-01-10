<?php
class fr_ALMP extends Edge {
    var $pays='fr';
    var $magazine='ALMP';
    var $intervalles_validite=array('C38','C47');
    static $largeur_defaut=35;
    static $hauteur_defaut=200;

    var $serie;
    var $numero_serie;

    function fr_ALMP($numero) {
        $this->numero=$numero;
        $this->largeur=35*Edge::$grossissement;
        $this->hauteur=200*Edge::$grossissement;
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
    }
}
?>
