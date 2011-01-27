<?php
class fr_ALMP extends Edge {
    var $pays='fr';
    var $magazine='ALMP';
    var $intervalles_validite=array('A3','A4','A5','A9','A10','A11','A15','A16','A17','A21','A27','A30','A34','C1','C2','C3','C5','C6','C7','C34','C35','C38','C46','C47','C48');
    static $largeur_defaut=35;
    static $hauteur_defaut=200;

    var $serie;
    var $numero_serie;

    function fr_ALMP($numero) {
        $this->numero=$numero;
        $lettres=str_split($this->numero);
        $this->numero_serie=$lettres[0];
        if ($this->serie < 'C' || $this->numero<='C9') {
            $this->largeur=37*Edge::$grossissement;
            $this->hauteur=182*Edge::$grossissement;
        }
        else {
            $this->largeur=35*Edge::$grossissement;
            $this->hauteur=200*Edge::$grossissement;
        }
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
    }
}
?>
