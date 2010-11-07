<?php
class PM extends Edge {
    var $pays='it';
    var $magazine='PM';
    var $intervalles_validite=array(264);

    static $largeur_defaut=10;
    static $hauteur_defaut=209;

    function PM ($numero) {
        $this->numero=$numero;
        $this->hauteur=209*Edge::$grossissement;
        $this->largeur=10*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');

        $noir=imagecolorallocate($this->image,0,0,0);
        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
        $fond=imagecolorallocate($this->image, $rouge, $vert, $bleu);
        imagefill($this->image, 0, 0, $fond);
        $this->placer_image('PM.couronne.png','haut',array(0,$this->largeur*1.5));
        $texte_numero=new Texte($this->numero,$this->largeur*0.25,$this->largeur*2.65,
                                2.5*Edge::$grossissement,0,$noir,'Kabel Demi.ttf');
        $texte_numero->dessiner($this->image);
        $this->placer_image('PM.signature_disney.png','haut',array(0,$this->hauteur*0.25));
        $this->placer_image('PM.Etoile.png','haut',array(0,$this->hauteur*0.54));
        $this->placer_image('PM.Paperino.png','bas',array(0,$this->largeur*1.3));
        
        return $this->image;
    }
}
?>
