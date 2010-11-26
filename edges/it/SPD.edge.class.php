<?php
class it_SPD extends Edge {
    var $pays='it';
    var $magazine='SPD';
    var $intervalles_validite=array(47);
    static $largeur_defaut=9;
    static $hauteur_defaut=185;

    function it_SPD ($numero) {
        $this->numero=$numero;
        $this->largeur=9*Edge::$grossissement;
        $this->hauteur=185*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    
    function dessiner() {

        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
        $fond=imagecolorallocate($this->image, $rouge, $vert, $bleu);
        imagefill($this->image, 0, 0, $fond);
        
        $this->placer_image('SPD.Logo.'.$this->numero.'.png','haut');
        $this->placer_image('SPD.Titre.png','haut',array(0,$this->hauteur*0.35));
        $this->placer_image('SPD.Signature Disney.png','bas');
        return $this->image;
}}

?>
