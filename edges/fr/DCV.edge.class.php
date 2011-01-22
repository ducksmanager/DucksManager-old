<?php
class fr_DCV extends Edge {
    var $pays='fr';
    var $magazine='DCV';
    var $intervalles_validite=array(array('debut'=>'1','fin'=>'19'));
    static $largeur_defaut=9;
    static $hauteur_defaut=255;

    function fr_DCV ($numero) {
        $this->numero=$numero;
        if ($this->numero <=15) {
            $this->largeur=5*Edge::$grossissement;
            $this->hauteur=298*Edge::$grossissement;
        }
        else {
            $this->largeur=9*Edge::$grossissement;
            $this->hauteur=255*Edge::$grossissement;
        }
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    
    function dessiner() {
        list($rouge,$vert,$bleu)=$this->getColorsFromDB();
        $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
        imagefill($this->image, 0, 0, $fond);
        $this->placer_image('DCV.Titre.png', 'haut',array(0,0),0.9,0.9);
        $this->placer_image('DCV.'.$this->numero.'.bas.png', 'bas');
        return $this->image;
    }

}