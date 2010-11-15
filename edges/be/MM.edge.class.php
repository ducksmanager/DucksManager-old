<?php
class be_MM extends Edge {
    var $pays='be';
    var $magazine='MM';
    var $intervalles_validite=array(array('debut'=>1, 'fin'=>468));
    static $largeur_defaut=4;
    static $hauteur_defaut=290;

    function be_MM ($numero) {
        $this->numero=$numero;
        $this->largeur=4;
        $this->hauteur=290;
        $this->hauteur*=Edge::$grossissement;
        $this->largeur*=Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        $blanc = imagecolorallocate($this->image, 255, 255, 255);
        $noir = imagecolorallocate($this->image, 0, 0, 0);
        imagefill($this->image, 0, 0, $blanc);
        $this->agrafer_detail($this->hauteur*0.326,  $this->hauteur*0.652, 3*$this->largeur);
        
        return $this->image;
    }

}