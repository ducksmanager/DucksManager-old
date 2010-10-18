<?php
class MJ extends Edge {
    var $pays='fr';
    var $magazine='MJ';
    var $intervalles_validite=array(9,59,142,143);

    static $largeur_defaut=275;
    static $hauteur_defaut=6;

    function MJ ($numero) {
        $this->numero=$numero;

        if (in_array($this->numero,array(9,59))) {
            $this->hauteur=275*Edge::$grossissement;
            $this->largeur=5*Edge::$grossissement;
        }
        elseif($this->numero >=79 && $this->numero <=114) {
            $this->hauteur=254*Edge::$grossissement;
            $this->largeur=5*Edge::$grossissement;
        }
        elseif($this->numero >=120 && $this->numero <=143) {
            $this->hauteur=275*Edge::$grossissement;
            $this->largeur=7*Edge::$grossissement;
        }
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {

        include_once($this->getChemin().'/../../MyFonts.Post.class.php');
        if ($this->numero<=9) {
            list($r,$g,$b)=$this->getColorsFromDB();
            $fond=imagecolorallocate($this->image,$r,$g,$b);
            imagefill($this->image,0,0,$fond);
        }
        elseif ($this->numero<=59) {
            $blanc=imagecolorallocate($this->image, 255, 255, 255);
            imagefill($this->image,0,0,$blanc);
            $this->agrafer();
        }
        elseif ($this->numero<=78) {
            //Futura Extra Bold

        }
        elseif ($this->numero<=114) {
            //Futura Extra Bold

        }
        elseif ($this->numero<=119) {
            //Futura Extra Bold

        }
        elseif ($this->numero<=141) {
            //Futura Extra Bold

        }
        elseif ($this->numero<=143) {
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
            $blanc=imagecolorallocate($image2, 255, 255, 255);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
            $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);
            imagefill($image2, 0, 0, $fond);
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(0,0,0),'Texte');
            $post=new MyFonts('agfa/futura/extra-bold',
                          rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                          rgb2hex($rouge,$vert,$bleu),
                          1300,
                          'MICKEY JEUX     .',
                          48);
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, $this->hauteur*0.82, $this->largeur*0.2, 0, 0, $nouvelle_largeur*1.5, $this->largeur, $width, $height*0.6);
            
            $post->text='N°'.$this->numero.'   .';
            $post->width=600;
            $post->build();
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, $this->hauteur*0.03, $this->largeur*0.2, 0, 0, $nouvelle_largeur*1.5, $this->largeur, $width, $height*0.6);

            $this->image=imagerotate($image2, 90, $blanc);
            $this->placer_image('MJ.signature_disney_blanc.png','bas',array(0,$this->hauteur*0.72));
        }

        return $this->image;
    }
}
?>
