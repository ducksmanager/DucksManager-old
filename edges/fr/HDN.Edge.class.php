<?php
class HDN extends Edge {
    var $pays='fr';
    var $magazine='HDN';
    var $intervalles_validite=array('1');

    static $largeur_defaut=16;
    static $hauteur_defaut=308;

    var $serie;
    var $numero_serie;

    function HDN ($numero) {
        $this->numero=$numero;
        $this->serie=$numero[0];
        $this->numero_serie=substr($this->numero, strrpos($this->numero, ' ')+1, strlen($this->numero));
        
        $this->largeur=16*Edge::$grossissement;
        $this->hauteur=308*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        list($r,$g,$b)=$this->getColorsFromDB();
        $fond=imagecolorallocate($this->image, $r, $g, $b);
        imagefill($this->image, 0, 0, $fond);
        $this->placer_image('HDN.icone.'.$this->numero.'.png','haut',array(0,$this->largeur/2));
        $this->placer_image('HDN.Signature_Disney.png','haut',array($this->largeur*0.15,$this->largeur*2),0.7,0.7);
        $this->placer_image('HDN.Texte.1.png','bas',array($this->largeur*0.15,$this->largeur*3),0.75,0.75);
        $this->placer_image('HDN.Logo_Glenat.png','bas',array($this->largeur*0.25,-$this->largeur*1.2),0.5,0.5);
        return $this->image;
    }
}
?>