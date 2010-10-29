<?php
class MC extends Edge {
    var $pays='de';
    var $magazine='MC';
    var $intervalles_validite=array(77);

    static $largeur_defaut=27;
    static $hauteur_defaut=179;

    function MC ($numero) {
        $this->numero=$numero;
        $this->hauteur=179*Edge::$grossissement;
        $this->largeur=27*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        $gris_clair=imagecolorallocate($this->image, 240, 240, 240);
        imagefill($this->image,0,0,$gris_clair);
        $this->placer_image('MC.BAND.png','haut',array($this->largeur*0.05,$this->largeur*0.2),0.5,0.5);
        foreach(str_split($this->numero) as $i=>$chiffre) {
            $this->placer_image('MC.Chiffre.'.$chiffre.'.png','haut',array($this->largeur*(0.6+$i*0.11),$this->largeur*0.25),0.1,0.1);
            
        }
        $this->placer_image('MC.BAND.png','haut',array($this->largeur*0.05,$this->largeur*0.2),0.5,0.5);
        $this->placer_image('MC.Donald.png');
        $this->placer_image('MC.Logo.png','bas');
        $this->placer_image('MC.signature_disney_noir.png','bas',array(0.09*$this->largeur,-1.5*$this->largeur),0.17,0.17);
        return $this->image;
    }
}
?>
