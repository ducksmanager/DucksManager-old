<?php
class fr_CJM extends Edge {
    var $pays='fr';
    var $magazine='CJM';
    var $intervalles_validite=array(array('debut'=>0,'fin'=>37));

    static $largeur_defaut=5;
    static $hauteur_defaut=237;

    function fr_CJM ($numero) {
        $this->numero=$numero;
        $this->largeur=5*Edge::$grossissement;
        $this->hauteur=237*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
        return $this->image;
    }
}
?>
