<?php
class fr_MCJ extends Edge {
    var $pays='fr';
    var $magazine='MCJ';
    var $intervalles_validite=array(1,2,3);

    static $largeur_defaut=18.5;
    static $hauteur_defaut=188;

    function fr_MCJ ($numero) {
        $this->numero=$numero;
        $this->hauteur=188*Edge::$grossissement;
        $this->largeur=18.5*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        imagefill($this->image,0,0,imagecolorallocate($this->image,255,255,255));
        $this->placer_image('MCJ.Signature.png','haut',array(0,$this->largeur*0.5));
        $this->placer_image('MCJ.'.$this->numero.'.Titre.png','haut',array(0,$this->largeur*2.7));
        $this->placer_image('Logo Hachette.png','bas');
        return $this->image;
    }
}
?>
