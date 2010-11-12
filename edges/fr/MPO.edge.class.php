<?php
class fr_MPO extends Edge {
    var $pays='fr';
    var $magazine='MPO';
    var $intervalles_validite=array(13,31,40,117);

    static $largeur_defaut=10;
    static $hauteur_defaut=120;

    function fr_MPO ($numero) {
        $this->numero=$numero;

        if ($this->numero<=119) {
            $this->hauteur=120*Edge::$grossissement; // Approximatif
            $this->largeur=10*Edge::$grossissement; // Approximatif
        }
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {

        include_once($this->getChemin().'/../../MyFonts.Post.class.php');
        if ($this->numero<=12) {// La série termine peut-être + tôt

        }
        elseif ($this->numero<=98) { // La série termine peut-être + tôt
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
            $blanc=imagecolorallocate($image2, 255, 255, 255);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
            $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);
            imagefill($image2, 0, 0, $fond);

            $post=new MyFonts('itc/blair/medium-medium',
                          rgb2hex(0,0,0),
                          rgb2hex($rouge,$vert,$bleu),
                          2300,
                          'MICKEY  POCHE   N°  '.$this->numero.'   .',
                          48);
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, $this->hauteur*0.15, $this->largeur*0.2, 0, 0, $nouvelle_largeur*0.8, $this->largeur*0.7, $width, $height*0.6);

            $post=new MyFonts('itc/blair/medium-medium',
                          rgb2hex(0,0,0),
                          rgb2hex($rouge,$vert,$bleu),
                          1200,
                          'MENSUEL     .',
                          48);
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, $this->hauteur*0.7, $this->largeur*0.5, 0, 0, $nouvelle_largeur*0.5, $this->largeur*0.4, $width, $height*0.6);

            $this->image=imagerotate($image2, -90, $blanc);
        }
        elseif ($this->numero<=119) {
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
            $blanc=imagecolorallocate($image2, 255, 255, 255);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
            $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);
            imagefill($image2, 0, 0, $fond);

            $texte='MICKEY POCHE  N° ';
            if ($this->numero < 100)
                $texte.=' ';
            $post=new MyFonts('agfa/blair-itc/bold',
                          rgb2hex(0,0,0),
                          rgb2hex($rouge,$vert,$bleu),
                          2300,
                          $texte.$this->numero.'   .',
                          48);
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, $this->hauteur*0.15, $this->largeur*0.2, 0, 0, $nouvelle_largeur*1.5, $this->largeur, $width, $height*0.6);

            $this->image=imagerotate($image2, -90, $blanc);
        }
        return $this->image;
    }
}
?>
