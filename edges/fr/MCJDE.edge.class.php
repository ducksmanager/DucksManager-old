<?php
class fr_MCJDE extends Edge {
    var $pays='fr';
    var $magazine='MCJDE';
    var $intervalles_validite=array(1,2,3,4,5,6);

    static $largeur_defaut=15;
    static $hauteur_defaut=186;

    function fr_MCJDE ($numero) {
        $this->numero=$numero;
        $this->hauteur=186*Edge::$grossissement;
        $this->largeur=15*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
    }
}
?>
