<?php
class fr_ACJM extends Edge {
    var $pays='fr';
    var $magazine='ACJM';
    var $intervalles_validite=array(2,3,4,6);

    static $largeur_defaut=27;
    static $hauteur_defaut=240;

    function fr_ACJM ($numero) {
        $this->numero=$numero;
        $this->hauteur=240*Edge::$grossissement;
        $this->largeur=27*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
    }
}
?>
