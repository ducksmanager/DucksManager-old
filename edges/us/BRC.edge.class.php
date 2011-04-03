<?php
class us_BRC extends Edge {
    var $pays='us';
    var $magazine='BRC';
    var $intervalles_validite=array('1');
    var $en_cours=array();
    static $largeur_defaut=4;
    static $hauteur_defaut=258;


    function us_BRC($numero) {
        $this->numero=$numero;
        $this->largeur=4*Edge::$grossissement;
        $this->hauteur=258*Edge::$grossissement;
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        
    }
}
?>
