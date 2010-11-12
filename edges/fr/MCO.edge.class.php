<?php
class fr_MCO extends Edge {
    var $pays='fr';
    var $magazine='MCO';
    var $intervalles_validite=array(1);
    static $largeur_defaut=7.5;
    static $hauteur_defaut=297;

    function fr_MCO ($numero) {
        $this->numero=$numero;
        $this->largeur=7.5*Edge::$grossissement;
        $this->hauteur=297*Edge::$grossissement;
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');

        $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
        
        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
        $fond=imagecolorallocate($image2, $rouge,$vert,$bleu);
        imagefill($image2, 0, 0, $fond);
        list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
        $post=new MyFonts('urw/kipp-clean/one',
                      rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                      rgb2hex($rouge,$vert,$bleu),
                      1100,
                      'HISTOIRES DE COW-BOYS    .');
        $chemin_image=$post->chemin_image;
        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
        $nouvelle_largeur=$this->largeur*($width/$height);
        imagecopyresampled ($image2, $texte, $this->hauteur*0.4, $this->largeur*0.05, 0, 0, $nouvelle_largeur*2, $this->largeur*0.5*2, $width, $height/2);
        
        $this->image=imagerotate($image2, 90, $blanc);
        $this->placer_image('MCO.Tete.png', 'haut', array(0,$this->largeur*0.7));
        $this->placer_image('MCO.Signature_Disney.png', 'haut', array(0,$this->largeur*1.6));
        $this->placer_image('MCO.Logo_Glenat.png', 'bas', array(0,$this->largeur*1.5));
        
        return $this->image;
    }

}