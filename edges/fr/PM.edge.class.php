<?php
class PM extends Edge {
    var $pays='fr';
    var $magazine='PM';
    var $intervalles_validite=array(array('debut'=>1, 'fin'=>464));
    static $largeur_defaut=6;
    static $hauteur_defaut=254;

    function PM ($numero) {
        $this->numero=$numero;
        if ($this->numero <=185) {
            $this->largeur=7*Edge::$grossissement;
            $this->hauteur=254*Edge::$grossissement;
        }
        elseif ($this->numero <=324) {
            $this->largeur=7*Edge::$grossissement;
            $this->hauteur=285*Edge::$grossissement;
        }
        elseif ($this->numero <=372) {
            $this->largeur=8*Edge::$grossissement;
            $this->hauteur=282*Edge::$grossissement;
        }
        else {
            $this->largeur=6*Edge::$grossissement;
            $this->hauteur=283*Edge::$grossissement;
        }
        /*
        else {
            $this->largeur=13*Edge::$grossissement;
            $this->hauteur=275*Edge::$grossissement;
        }*/
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        if ($this->numero <=185) {
            $separation_haut_bas=$this->hauteur*1.9/6;
            $blanc = imagecolorallocate($this->image, 255, 255, 255);
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            if ($this->numero <= 84 || $this->numero == 88) {
                list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255),'Haut');
                $couleur_haut=imagecolorallocate($this->image, $rouge, $vert, $bleu);
                imagefilledrectangle($this->image, .5*Edge::$grossissement, .5*Edge::$grossissement, $this->largeur-.5*Edge::$grossissement, $separation_haut_bas, $couleur_haut);

                list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255),'Bas');
                $couleur_bas=imagecolorallocate($this->image, $rouge, $vert, $bleu);
                imagefilledrectangle($this->image, .5*Edge::$grossissement, $separation_haut_bas, $this->hauteur/3.5, $this->hauteur-.5*Edge::$grossissement, $couleur_bas);
            }
            else {
                list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
                $couleur=imagecolorallocate($this->image, $rouge, $vert, $bleu);
                imagefilledrectangle($this->image, .5*Edge::$grossissement, .5*Edge::$grossissement, $this->largeur-.5*Edge::$grossissement, $this->hauteur, $couleur);
            }
            
            switch($this->numero) {
                case 37:
                    $autre_couleur1=imagecolorallocate($this->image, 64, 170, 160);
                    $autre_couleur2=imagecolorallocate($this->image, 236, 227, 108);
                    imagefilledrectangle($this->image, 0, $separation_haut_bas + 55*Edge::$grossissement, $this->largeur, $this->hauteur, $autre_couleur1);
                    $points=array(0, $separation_haut_bas + 88*Edge::$grossissement,
                                  $this->largeur, $separation_haut_bas + 85*Edge::$grossissement,
                                  $this->largeur, $this->hauteur,
                                  0, $this->hauteur);
                    imagefilledpolygon($this->image, $points, count($points)/2, $autre_couleur2);
                break;

                case 88:
                    $couleur1_rgb=$this->getColorsFromDB(array(255,255,255),'Haut');
                    $couleur2_rgb=$this->getColorsFromDB(array(255,255,255),'Bas');

                    include_once($this->getChemin().'/../../util.php');
                    $couleurs_inter=getMidColors($couleur1_rgb, $couleur2_rgb, $this->largeur*0.8);
                    foreach($couleurs_inter as $i=>$couleur) {
                        list($rouge_inter,$vert_inter,$bleu_inter)=$couleur;
                        $couleur_allouee=imagecolorallocate($this->image, $rouge_inter,$vert_inter,$bleu_inter);
                        imageline($this->image, 0, $i+$this->largeur*11, $this->largeur, $i+$this->largeur*11, $couleur_allouee);
                    }
                break;

                case 104:
                    $this->placer_image('PM.104.dessin.png', 'haut', array(0,$separation_haut_bas+18*Edge::$grossissement));
                break;

                case 125:
                    $epaisseur_trait=0.7*Edge::$grossissement;
                    $points=array(0, $this->hauteur*0.8,
                                  $this->largeur, $this->hauteur*0.8 -6*Edge::$grossissement,
                                  $this->largeur, $this->hauteur*0.8 -6*Edge::$grossissement+$epaisseur_trait,
                                  0, $this->hauteur*0.8 + $epaisseur_trait);
                    imagefilledpolygon($this->image, $points, 4, $noir);

                    $points=array(0, $this->hauteur*0.87,
                                  $this->largeur, $this->hauteur*0.87 -6*Edge::$grossissement,
                                  $this->largeur, $this->hauteur*0.87 -6*Edge::$grossissement+$epaisseur_trait,
                                  0, $this->hauteur*0.87 + $epaisseur_trait);
                    imagefilledpolygon($this->image, $points, 4, $noir);
                    $jaune_intermediaire=imagecolorallocate($this->image, 244, 222, 88);
                    imagefill($this->image, $this->largeur/2, $this->hauteur*0.85, $jaune_intermediaire);
                break;

                case 145:
                    imagefilledrectangle($this->image, 0, $this->hauteur/3, $this->largeur, $this->hauteur/3 + 20*Edge::$grossissement, $blanc);
                break;

                case 149:
                    $points=array(0, $this->hauteur*0.72,
                                  $this->largeur, $this->hauteur*0.72 -10*Edge::$grossissement,
                                  $this->largeur, $this->hauteur*0.72 +18*Edge::$grossissement,
                                  0, $this->hauteur*0.72 +16*Edge::$grossissement);
                    $couleur_poly=imagecolorallocate($this->image, 10, 157, 220);
                    imagefilledpolygon($this->image, $points, 4, $couleur_poly);
                    $points=array(0, $this->hauteur*0.72 +16*Edge::$grossissement,
                                  $this->largeur, $this->hauteur*0.72 +18*Edge::$grossissement,
                                  $this->largeur, $this->hauteur*0.72 +44*Edge::$grossissement,
                                  0, $this->hauteur*0.72 +54*Edge::$grossissement);
                    $couleur_poly=imagecolorallocate($this->image, 16, 150, 77);
                    imagefilledpolygon($this->image, $points, 4, $couleur_poly);

                break;

                case 155:
                    imagefilledrectangle($this->image, 0, $this->hauteur/3, $this->largeur, $this->hauteur/3 + 20*Edge::$grossissement, $blanc);
                    imagefilledrectangle($this->image,0,$this->hauteur/3, $this->largeur, $this->hauteur/3 + 0.5*Edge::$grossissement, $noir);
                    imagefilledrectangle($this->image,0,$this->hauteur/3 + 20*Edge::$grossissement, $this->largeur, $this->hauteur/3 + 20.5*Edge::$grossissement, $noir);
                     
                    $this->placer_image('PM.155.dessin.png', 'bas', array(0,20*Edge::$grossissement));
                break;

                case 158:
                    imagefilledrectangle($this->image, 0, $this->hauteur/3 + 10*Edge::$grossissement, $this->largeur, $this->hauteur/3 + 33*Edge::$grossissement, $blanc);
                break;

                case 160: case 167: case 173: case 174: case 175: case 180: case 181:
                    list($dessin,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/PM.'.$this->numero.'.dessin.png');
                    $transparent = imagecolorallocatealpha($dessin, 0, 0, 0, 127);
                    imagefill($dessin, 0, 0, $transparent);
                    imagefill($dessin, $width-1, 0, $transparent);
                    $nouvelle_hauteur=$this->largeur*($height/$width);
                    $this->placer_image($dessin, 'bas');
                    if ($this->numero == 180) {
                       list($dessin,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/PM.180.bulle.png');
                        $transparent = imagecolorallocatealpha($dessin, 0, 0, 0, 127);
                        imagefill($dessin, 0, 0, $transparent);
                        imagefill($dessin, $width-1, 0, $transparent);
                        $nouvelle_hauteur=$this->largeur*($height/$width);
                        $this->placer_image($dessin, 'haut', array(0,45.5*Edge::$grossissement), 0.667, 0.667);
                    }
                break;

                case 176:
                    $rose=imagecolorallocate($this->image, 223, 40, 122);
                    imagefilledrectangle($this->image, 0, $this->hauteur*5/6 + 5*Edge::$grossissement, $this->largeur, $this->hauteur, $rose);
                break;

                case 177:
                    $rose_clair=imagecolorallocate($this->image, 216, 203, 199);
                    $rose_fonce=imagecolorallocate($this->image, 179, 102, 107);
                    imagefilledrectangle($this->image, 0, $this->hauteur*2/3, $this->largeur, $this->hauteur, $rose_clair);
                    imagefilledrectangle($this->image, 0, $this->hauteur*2/3, $this->largeur, $this->hauteur*2/3+0.5*Edge::$grossissement, $rose_fonce);
                break;
            }
            if ($this->numero == 119)
                list($titre,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/Titre PM2 blanc.png');
            else
                list($titre,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/Titre PM2.png');
            $transparent=imagecolortransparent($titre);
            $ecriture_vers_haut=$this->numero<33 || $this->numero == 165;
            if (!$ecriture_vers_haut)
                $titre=imagerotate ($titre, 180, $transparent);
            $hauteur_sous_image=0.75*$this->largeur*($height/$width);
            $this->placer_image($titre, 'haut', array($this->largeur/5, ($ecriture_vers_haut ? 10 : 5)*Edge::$grossissement), 0.75, 0.75);
            $texte_numero=new Texte($this->numero,$ecriture_vers_haut ? $this->largeur*8/10 : $this->largeur/3, $ecriture_vers_haut ? 10*Edge::$grossissement : $hauteur_sous_image + 5*Edge::$grossissement,
									3.5*Edge::$grossissement,$ecriture_vers_haut ? 90 : -90,$this->numero == 119 ? $blanc : $noir,'ArialBlack.ttf');
            $texte_numero->dessiner($this->image);

            if ($this->numero <= 86) {
                if ($this->numero == 49) {  }
                else {
                    $hauteur_rectangle=Edge::$grossissement*1.1;
                    imagefilledrectangle($this->image, 0, $separation_haut_bas, $this->largeur, $separation_haut_bas + $hauteur_rectangle, $this->numero == 85 ? $noir : $blanc);
                }
            }
        }
        elseif ($this->numero <=372) {
            $blanc = imagecolorallocate($this->image, 255, 255, 255);
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->hauteur, $blanc);
            $this->agrafer();
        }
        else {
            include_once($this->getChemin().'/../classes/PM.titres.php');
            include_once($this->getChemin().'/../../MyFonts.Post.class.php');
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
            $blanc=imagecolorallocate($image2, 255, 255, 255);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
            switch ($this->numero) {
                case 404 : case 415 : case 426 :
                    $couleur1=$this->getColorsFromDB(array(0,0,0),'Dégradé 2');
                    $couleur2=$this->getColorsFromDB(array(255,255,255),'Dégradé 1');

                    list($width,$height)=getimagesize($this->getChemin().'/logo PM.png');
                    $largeur_logo=$this->largeur*($height/$width);
                    $largeur_degrade=$this->hauteur-$largeur_logo;

                    include_once($this->getChemin().'/../../util.php');
                    $couleurs_inter=getMidColors($couleur1, $couleur2, $largeur_degrade);
                    foreach($couleurs_inter as $i=>$couleur) {
                        list($rouge_inter,$vert_inter,$bleu_inter)=$couleur;
                        $couleur_allouee=imagecolorallocate($image2, $rouge_inter,$vert_inter,$bleu_inter);
                        imageline($image2, $i+$largeur_logo, 0, $i+$largeur_logo, $this->largeur, $couleur_allouee);
                    }
                break;
                default :
                    $couleur1=imagecolorallocate($this->image, $rouge, $vert, $bleu);
                    imagefill($image2,0,0,$couleur1);
                break;
            }
            list($rouge2,$vert2,$bleu2)=$this->getColorsFromDB(array(255,255,255),'Couleur 2');
            $couleur2=imagecolorallocate($this->image, $rouge2, $vert2, $bleu2);
            imagefilledrectangle($image2, 0, 0, 9*$this->largeur, $this->largeur, $couleur2);
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
            $couleur_texte=imagecolorallocate($this->image, $rouge_texte, $vert_texte, $bleu_texte);
            list($rouge_texte_num,$vert_texte_num,$bleu_texte_num)=$this->getColorsFromDB(array(255,255,255),'Texte numéro');
            $couleur_texte_num=imagecolorallocate($this->image, $rouge_texte_num,$vert_texte_num,$bleu_texte_num);

            $texte_numero='';
            for($i=0;$i<strlen($this->numero);$i++)
                $texte_numero.=$this->numero[$i].' ';
            $post=new MyFonts('storm/zeppelin/53-bold-italic',
                              rgb2hex($rouge_texte_num,$vert_texte_num,$bleu_texte_num),
                              rgb2hex($rouge2,$vert2,$bleu2),
                              800,
                              $texte_numero);
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, 25*Edge::$grossissement, $this->largeur*0.05, 0, 0, $nouvelle_largeur*1.4, $this->largeur*1.8, $width, $height);

            if (defined('PM_'.$this->numero)) {
                $texte=constant('PM_'.$this->numero);
                $longueur_texte=strlen($texte);
                for ($i=$longueur_texte;$i<100;$i++)
                    $texte.=' ';
                $texte.='.';
                $post=new MyFonts('ortizlopez/ol-london/ollondon-black',
                                  rgb2hex($rouge_texte, $vert_texte, $bleu_texte),
                                  rgb2hex($rouge, $vert, $bleu),
                                  4900,
                                  $texte);
                $chemin_image=$post->chemin_image;
                list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                $nouvelle_largeur=$this->largeur*0.8*($width/$height);
                imagecopyresampled ($image2, $texte, 10*$this->largeur, $this->largeur*0.2, 0, 0, $nouvelle_largeur*1.4, $this->largeur*0.7, $width, $height*0.5);
            }

            $this->image=imagerotate($image2, 90, $blanc);

            if ($this->numero == 389)
                $nom_logo='logo PM_jaune.png';
            else
                $nom_logo='logo PM.png';
            list($logo,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/'.$nom_logo);
            $hauteur_logo=$this->largeur*($height/$width);
            if ($this->numero > 392 || $this->numero == 389) {
                if ($this->numero==393) {
                    imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->largeur*3, $couleur_texte);
                    $this->placer_image($nom_logo,'haut',array(0,$this->largeur*3.5));
                }
                elseif (in_array($this->numero, array(394,395,454,455,456,457))) {
                    imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->largeur*3, $couleur2);
                    $this->placer_image($nom_logo,'haut',array(0,$this->largeur*3.5));
                }
                else {
                    if ($this->numero == 389 || $this->numero == 449 || $this->numero == 450)
                        imagefilledrectangle($this->image, 0, 0, $this->largeur, $hauteur_logo, $couleur_texte);
                    else
                        imagefilledrectangle($this->image, 0, 0, $this->largeur, $hauteur_logo, $couleur2);
                    $this->placer_image($nom_logo);
                }
            }
            else
                $this->placer_image($nom_logo);
            if (!defined('PM_'.$this->numero) || $this->numero == 441) {
                $this->placer_image('PM.'.$this->numero.'.dessin.png','haut',array(0,$hauteur_logo));
            }
            if (file_exists('PM.'.$this->numero.'.detail.png'))
                $this->placer_image ('PM.'.$this->numero.'.detail.png');
            $this->placer_image('Tete PM.png', 'bas', array(0,$this->largeur/2));
            
        }
        return $this->image;
    }

}