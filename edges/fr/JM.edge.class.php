<?php
class fr_JM extends Edge {
    var $pays='fr';
    var $magazine='JM';
    static $numeros_doubles=array('2411-12','2454-55','2463-64','2479-80','2506-07','2515-16','2531-32','2558-59','2584-85','2610-11','2619-20','2636-37','2662-63','2671-72','2688-89','2715-16','2723-24','2767-68','2819-20','2828-29','2844-45','2871-72','2879-80','2896-97','2923-24','2932-33','2948-49','2975-76','2984-85','3027-28');
    var $intervalles_validite=array(array('debut'=>1, 'fin'=>3044,'sauf'=>array('2506-07','2515-16','2531-32','2558-59','2584-85','2610-11','2619-20','2636-37','2662-63','2671-72','2688-89','2723-24','2767-68','2819-20','2828-29','2844-45','2871-72','2879-80','2896-97','2923-24','2932-33','2948-49','2975-76','2984-85','3027-28')));
    static $largeur_defaut=5;
    static $hauteur_defaut=275;

    function fr_JM ($numero) {
        $this->numero=$numero;
        $this->largeur=5;
        if (in_array($this->numero, fr_JM::$numeros_doubles)) {
            $this->hauteur=275;
            $this->largeur=6;
        }
        elseif ($this->numero >=2963) {
            $this->hauteur=275;
        }
        elseif ($this->numero >=2500) {
            $this->hauteur=285;
            $this->largeur=6;
        }
        elseif ($this->numero >=2400) {
            $this->hauteur=279;
            $this->largeur=4;
        }
        elseif ($this->numero >=1973) {
            $this->hauteur=270;
        }
        elseif ($this->numero >=1554) {
            $this->hauteur=285;
            $this->largeur=6;
        }
        elseif ($this->numero >=1323) {
            $this->hauteur=295;
        }
        elseif ($this->numero >=1229) {
            $this->hauteur=298;
        }
        elseif ($this->numero >=500) {
            $this->hauteur=295;
            $this->largeur=6;
        }
        else {
            $this->hauteur=297;
            $this->largeur=3;
        }
        $this->hauteur*=Edge::$grossissement;
        $this->largeur*=Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        if (in_array($this->numero,fr_JM::$numeros_doubles)) {
            include_once($this->getChemin().'/../../MyFonts.Post.class.php');
            $numero1=substr($this->numero, 0, strpos($this->numero,'-'));
            $numero2=$numero1+1;
            if ($numero1>=2715) {
                list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
                list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
                $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
                $fond=imagecolorallocate($image2, $rouge,$vert,$bleu);
                imagefill($image2, 0, 0, $fond);
                $post=new MyFonts('urw/engschrift/d-2377',
                                  rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                                  rgb2hex($rouge,$vert,$bleu),
                                  1700,
                                  'LE JOURNAL DE MICKEY N° 2715/2716    .');
                $chemin_image=$post->chemin_image;
                list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                $nouvelle_largeur=$this->largeur*($width/$height);
                imagecopyresampled ($image2, $texte, $this->largeur*0.9, $this->largeur*0.2, 0, 0, $nouvelle_largeur*1.25*1.35, $this->largeur*0.51*1.35, $width, $height*0.51);
                $blanc = imagecolorallocate($this->image, 255, 255, 255);

                $this->image=imagerotate($image2, 90, $blanc);
            }
            else {
                list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
                list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
                list($rouge_texte2,$vert_texte2,$bleu_texte2)=$this->getColorsFromDB(array(255,255,255),'Texte 2');

                $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
                $fond=imagecolorallocate($image2, $rouge,$vert,$bleu);
                imagefill($image2, 0, 0, $fond);
                $post=new MyFonts('ef-typeshop/montreal/bold',
                                  rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                                  rgb2hex($rouge,$vert,$bleu),
                                  1700,
                                  'LE JOURNAL DE MICKEY    .');
                $chemin_image=$post->chemin_image;
                list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                $nouvelle_largeur=$this->largeur*($width/$height);
                imagecopyresampled ($image2, $texte, $this->largeur*0.9, $this->largeur*0.2, 0, 0, $nouvelle_largeur*1.25*1.35, $this->largeur*0.51*1.35, $width, $height*0.51);
                $post->text='N    '.$numero1.'/'.$numero2.'       .';
                $post->color=rgb2hex($rouge_texte2,$vert_texte2,$bleu_texte2);
                $post->width=1200;
                $post->build();

                $chemin_image=$post->chemin_image;
                list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                $nouvelle_largeur=$this->largeur*($width/$height);
                $pos_x=($this->numero=='2411-12'?($this->hauteur*0.35):($this->hauteur*0.3));
                imagecopyresampled ($image2, $texte, $pos_x, $this->largeur*0.2, 0, 0, $nouvelle_largeur*1.25*1.35, $this->largeur*0.51*1.35, $width, $height*0.51);

                if (in_array($this->numero,array('2463-64'))) {
                    $post->text='NUMÉRO DOUBLE       .';
                    $post->color=rgb2hex($rouge_texte,$vert_texte,$bleu_texte);
                    $post->font='paratype/futura-book/heavy';
                    $post->width=1200;
                    $post->build();

                    $chemin_image=$post->chemin_image;
                    list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                    $nouvelle_largeur=$this->largeur*($width/$height);
                    imagecopyresampled ($image2, $texte, $this->hauteur*0.85, $this->largeur*0.05, 0, 0, $nouvelle_largeur*1.25*1.35, $this->largeur*0.75*1.35, $width, $height*0.51);
                }

                $post->text='os       .';
                $post->color=rgb2hex($rouge_texte2,$vert_texte2,$bleu_texte2);
                $post->width=200;
                $post->build();

                $chemin_image=$post->chemin_image;
                list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                $nouvelle_largeur=$this->largeur*($width/$height);
                imagecopyresampled ($image2, $texte, $pos_x+$this->hauteur*0.015, $this->largeur*0.2, 0, 0, $nouvelle_largeur*0.625*0.65, $this->largeur*0.51*0.65, $width, $height);
                $blanc = imagecolorallocate($this->image, 255, 255, 255);
                $this->image=imagerotate($image2, -90, $blanc);
            }


        }
        else {
            $blanc = imagecolorallocate($this->image, 255, 255, 255);
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->hauteur, $blanc);
            $this->agrafer();
        }
        return $this->image;
    }

}