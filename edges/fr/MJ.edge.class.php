<?php
class MJ extends Edge {
    var $pays='fr';
    var $magazine='MJ';
    var $intervalles_validite=array(9,59,79,81,87,106,107,109,111,114,120,122,126,141,142,143);

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
            $this->largeur=6*Edge::$grossissement;
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
        elseif ($this->numero<=143) {
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
            $blanc=imagecolorallocate($image2, 255, 255, 255);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
            $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);
            imagefill($image2, 0, 0, $fond);
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(0,0,0),'Texte');
            if ($this->numero<=114)
                list($rouge_texte,$vert_texte,$bleu_texte)=array(255,255,255);

            $post=new MyFonts('agfa/futura/extra-bold',
                          rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                          rgb2hex($rouge,$vert,$bleu),
                          1300,
                          'MICKEY JEUX     .',
                          48);
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            if ($this->numero<=87)
                $nouvelle_largeur*=2.8;
            elseif ($this->numero<=126)
                $nouvelle_largeur*=2.6;
            elseif($this->numero<=141)
                $nouvelle_largeur*=2.6;
            else
                $nouvelle_largeur*=1.5;

            if ($this->numero<=87)
                $dst_x=$this->hauteur*0.74;
            elseif ($this->numero<=114)
                $dst_x=$this->hauteur*0.76;
            elseif ($this->numero<=126)
                $dst_x=$this->hauteur*0.715;
            elseif ($this->numero<=141)
                $dst_x=$this->hauteur*0.72;
            else
                $dst_x=$this->hauteur*0.83;
            $largeur=$this->numero<=87?$this->largeur*1.1:$this->largeur;
            imagecopyresampled ($image2, $texte, $dst_x, $this->largeur*0.2, 0, 0, $nouvelle_largeur, $largeur, $width, $height*0.6);

            if ($this->numero==126) {
                list($rouge_texte_numero,$vert_texte_numero,$bleu_texte_numero)=$this->getColorsFromDB(array(0,0,0),'Texte numéro');
                $post->color=rgb2hex($rouge_texte_numero,$vert_texte_numero,$bleu_texte_numero);
            }
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(0,0,0),'Texte');
            if ($this->numero>87)
                $post->color=rgb2hex($rouge_texte,$vert_texte,$bleu_texte);
            $post->text='N°'.($this->numero<=126?' ':'').$this->numero.'   .';
            $post->width=$this->numero<=126?650:600;
            $post->build();
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            if ($this->numero<=87)
                $nouvelle_largeur*=2.8;
            elseif ($this->numero<=126)
                $nouvelle_largeur*=2.2;
            elseif ($this->numero<=141)
                $nouvelle_largeur*=2.1;
            else
                $nouvelle_largeur*=1.5;
            if ($this->numero<=114)
                $dst_x=$this->hauteur*0.01;
            elseif ($this->numero<=126)
                $dst_x=$this->hauteur*0.05;
            else
                $dst_x=$this->hauteur*0.03;
                
            $largeur=$this->numero<=87?$this->largeur*1.1:$this->largeur;
            imagecopyresampled ($image2, $texte, $dst_x, $this->largeur*0.2, 0, 0, $nouvelle_largeur, $largeur, $width, $height*0.6);

            $this->image=imagerotate($image2, 90, $blanc);

            if ($this->numero>126) {
                $decalage_bas=$this->hauteur*($this->numero<=141 ? 0.605 : 0.72);
                $signature='MJ.signature_disney_'.($this->numero<=141?'noir':'blanc').'.png';
                $this->placer_image($signature,'bas',array(0,$decalage_bas));
            }
            if ($this->numero==87) {
                $this->placer_image('MJ.87.Dessin.png', 'bas');
            }
        }

        return $this->image;
    }
}
?>
