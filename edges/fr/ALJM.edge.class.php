<?php
class fr_ALJM extends Edge {
    var $pays='fr';
    var $magazine='ALJM';
    var $intervalles_validite=array(100);
    var $en_cours=array(118,119,193,195);
    static $largeur_defaut=22;
    static $hauteur_defaut=282;

    var $serie;
    var $numero_serie;

    function fr_ALJM($numero) {
        $this->numero=$numero;
        $this->largeur=18*Edge::$grossissement;
        $this->hauteur=273*Edge::$grossissement;
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');

        $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
        $blanc=imagecolorallocate($image2, 255, 255, 255);
        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
        $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);

        $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
        list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
        $couleur_texte=imagecolorallocate($image2, $rouge_texte,$vert_texte,$bleu_texte);
        imagefill($image2, 0, 0, $couleur_texte);
        imagefilledpolygon($image2, array($this->largeur*0.75,                   $this->largeur*0.2,
                                          $this->hauteur-$this->largeur*2.1,     $this->largeur*0.2,
                                          $this->hauteur-$this->largeur*2.7,     $this->largeur*0.5,
                                          $this->hauteur-$this->largeur*2.1,     $this->largeur*0.8,
                                          $this->largeur*0.75,                   $this->largeur*0.8,
                                          $this->largeur*0.75+$this->largeur*0.6,$this->largeur*0.5)
                                  , 6, $fond);
        imagefilledpolygon($image2, array($this->largeur*1.15,                   $this->largeur*0.3,
                                          $this->hauteur-$this->largeur*2.5,     $this->largeur*0.3,
                                          $this->hauteur-$this->largeur*2.9,     $this->largeur*0.5,
                                          $this->hauteur-$this->largeur*2.5,     $this->largeur*0.7,
                                          $this->largeur*1.15,                   $this->largeur*0.7,
                                          $this->largeur*0.95+$this->largeur*0.6,$this->largeur*0.5)
                                  , 6, $couleur_texte);
        imagefilledpolygon($image2, array($this->largeur*1.35,                   $this->largeur*0.35,
                                          $this->hauteur-$this->largeur*2.7,     $this->largeur*0.35,
                                          $this->hauteur-$this->largeur*3,       $this->largeur*0.5,
                                          $this->hauteur-$this->largeur*2.7,     $this->largeur*0.65,
                                          $this->largeur*1.35,                   $this->largeur*0.65,
                                          $this->largeur*1.05+$this->largeur*0.6,$this->largeur*0.5)
                                  , 6, $fond);
        
        $post=new MyFonts('wiescherdesign/unita/bold',
                          rgb2hex($rouge_texte, $vert_texte, $bleu_texte),
                          rgb2hex($rouge, $vert, $bleu),
                          2400,
                          'ALBUM DU JOURNAL DE MICKEY         .',
                          84);
        $chemin_image=$post->chemin_image;
        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
        $nouvelle_largeur=$this->largeur*($width/$height)*0.8;
        imagecopyresampled ($image2, $texte, $this->hauteur*0.3, $this->largeur*0.39, 0, 0, $nouvelle_largeur*0.7, $this->largeur*0.25, $width, $height*0.5);
        
        $this->image=imagerotate($image2, 90, $blanc);
        $this->placer_image('Logo ALJM.png',array(0,$this->largeur/2));
        return $this->image;
    }
}
?>
