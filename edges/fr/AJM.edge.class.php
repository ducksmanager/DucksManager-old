<?php
class AJM extends Edge {
    var $pays='fr';
    var $magazine='AJM';
    var $intervalles_validite=array(65,68/*,82,83*/);
    static $largeur_defaut=7;
    static $hauteur_defaut=254;

    function AJM ($numero) {
        $this->numero=$numero;
        if ($this->numero<=64) {
            
        }
        elseif($this->numero<=68) {
            $this->largeur=10.5*Edge::$grossissement;
            $this->hauteur=238*Edge::$grossissement;
        }
        else {
            $this->largeur=7*Edge::$grossissement;
            $this->hauteur=254*Edge::$grossissement;
        }
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    
    function dessiner() {
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');

        if ($this->numero<=64) {
            
        }
        elseif($this->numero<=68) {            
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $fond=imagecolorallocate($image2,255,255,255);
            
            imagefill($image2,0,0,$fond);
            
            $post=new MyFonts('redrooster/block-gothic-rr/demi-extra-condensed',
                              rgb2hex(0,0,0),
                              rgb2hex(255,255,255),
                              700,
                              'D U   J O U R N A L    D E      .');
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, $this->largeur*10, $this->largeur*0.1, 0, 0, $nouvelle_largeur*2, $this->largeur, $width, $height/2);
            
            $this->image=imagerotate($image2, 90, $fond);
            
            $image_almanach=imagecreatetruecolor($this->hauteur*0.25, $this->largeur);
            $ecriture_almanach=imagecolorallocate($image_almanach,216,1,77);
            $fond_almanach=imagecolorallocate($image_almanach, 255, 255, 255);
            imagefill($image_almanach,0,0,$fond_almanach);
            $texte_almanach=new Texte('ALMANACH',0,100,
                                7*Edge::$grossissement,0,$ecriture_almanach,'Gill Sans Bold.ttf');
            $texte_almanach->dessiner($image_almanach);
            $image_almanach=imagerotate($image_almanach, 90, $fond_almanach);
            list($width,$height)=array(imagesx($image_almanach),imagesy($image_almanach));
            imagecopyresampled ($this->image, $image_almanach, -$this->largeur*0.2, $this->hauteur-$this->largeur*9.5, 0, 0, $width*1.05, $height*0.85, $width, $height);

            
            $image_mickey=imagecreatetruecolor($this->hauteur*0.25, $this->largeur);
            $ecriture_mickey=imagecolorallocate($image_mickey,216,1,77);
            $fond_mickey=imagecolorallocate($image_mickey, 255, 255, 255);
            imagefill($image_mickey,0,0,$fond_mickey);
            $texte_mickey=new Texte('MICKEY',0,100,
                                7*Edge::$grossissement,0,$ecriture_mickey,'Gill Sans Bold.ttf');
            $texte_mickey->dessiner($image_mickey);
            $image_mickey=imagerotate($image_mickey, 90, $fond_mickey);
            list($width,$height)=array(imagesx($image_mickey),imagesy($image_mickey));
            imagecopyresampled ($this->image, $image_mickey, -$this->largeur*0.2, $this->largeur*2, 0, 0, $width*1.05, $height*0.85, $width, $height);

            
            $post->text='19'.$this->numero.'   .';
            $post->width=200;
            $post->build();
            $chemin_image=$post->chemin_image;
            list($date,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($this->image, $date, $this->largeur*0.1, $this->largeur, 0, 0, $nouvelle_largeur*1.255, $this->largeur*0.625, $width, $height/2);
            imagecopyresampled ($this->image, $date, $this->largeur*0.1, $this->hauteur-$this->largeur*2, 0, 0, $nouvelle_largeur*1.255, $this->largeur*0.625, $width, $height/2);
            
            
        }
        else {
            $fond=imagecolorallocate($this->image, 213, 210, 164);
            imagefill($this->image, 0, 0, $fond);
            $noir=imagecolorallocate($this->image, 0, 0, 0);
            $texte_numero=new Texte('BANDES DESSINEES   JEUX   HUMOUR',$this->largeur*0.7,$this->hauteur,
                                    3.5*Edge::$grossissement,90,$noir,'ARIAL.TTF');
            $texte_numero->dessiner($this->image);
        }
        return $this->image;
    }

}