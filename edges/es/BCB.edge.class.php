<?php
class es_BCB extends Edge {
    var $pays='es';
    var $magazine='BCB';
    var $intervalles_validite=array(1);
    static $largeur_defaut=22;
    static $hauteur_defaut=292;

    function es_BCB ($numero) {
        $this->numero=$numero;
        $this->largeur=22*Edge::$grossissement;
        $this->hauteur=292*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    
    function dessiner() {
        include_once($this->getChemin().'/../../util.php');
        $couleur_gauche=imagecolorallocate($this->image, 108, 57, 96);
        imagefill($this->image, 0, 0, $couleur_gauche);
        $couleur_droite=imagecolorallocate($this->image, 66, 19, 38);
        imagefilledrectangle($this->image, $this->largeur/2, 0, $this->largeur,$this->hauteur,$couleur_droite);
        $couleurs_degrade=getMidColors(array(108, 57, 96), array(66, 19, 38), $this->largeur/2);
        foreach($couleurs_degrade as $i=>$couleur) {
            list($r,$g,$b)=$couleur;
            imageline($this->image,$i+3*$this->largeur/8,0,$i+$this->largeur/4,$this->hauteur,imagecolorallocate($this->image,$r,$g,$b));
        }
        $this->placer_image('BCB.Disney.png','haut',array(0,$this->largeur*3/4));
        $this->placer_image('BCB.Numero.1.png','haut',array($this->largeur*0.3,$this->largeur*1.5),0.4,0.4);
        $this->placer_image('BCB.Titre.png','haut',array($this->largeur*0.15,$this->hauteur*0.38),0.65,0.65);
        $this->placer_image('BCB.Logo.png','bas',array($this->largeur*0.25,$this->largeur*0.25),0.5,0.5);
        return $this->image;
    }

}