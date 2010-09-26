<?php
class MP extends Edge {
    var $pays='fr';
    var $magazine='MP';
    var $intervalles_validite=array(array('debut'=>1,'fin'=>21,'sauf'=>array(14)),array('debut'=>25,'fin'=>63,'sauf'=>array(41)),
                                    array('debut'=>66,'fin'=>84,'sauf'=>array(72)),
                                    87,array('debut'=>89,'fin'=>93),95,98,100,101,103,array('debut'=>106,'fin'=>109),array('debut'=>114,'fin'=>123, 'sauf'=>array(117)),array('debut'=>125,'fin'=>132),
                                    array('debut'=>133,'fin'=>139),
                                    array('debut'=>140,'fin'=>192),
                                    array('debut'=>193,'fin'=>204),
                                    array('debut'=>205,'fin'=>216),
                                    array('debut'=>217,'fin'=>228),
                                    array('debut'=>229,'fin'=>235),
                                    array('debut'=>236,'fin'=>253),
                                    array('debut'=>255,'fin'=>256),258,259,260,262,
                                    array('debut'=>265,'fin'=>271),273,275,276,278,279,280,281,283,284,285,286,288,array('debut'=>290,'fin'=>317));
    
    static $largeur_defaut=20;
    static $hauteur_defaut=219.7;
    function MP($numero) {
        $this->numero=$numero;
        if($this->numero <=84) {
            $this->largeur=14*Edge::$grossissement;
            $this->hauteur=186*Edge::$grossissement;
        }
        elseif ($this->numero <= 139) {
            $this->largeur=11.5*Edge::$grossissement;
            $this->hauteur=186*Edge::$grossissement;
        }
        elseif($this->numero<=234) {
            $this->largeur=11.5*Edge::$grossissement;
            $this->hauteur=210*Edge::$grossissement;
        }
        elseif($this->numero<=253) {
            $this->largeur=11*Edge::$grossissement;
            $this->hauteur=210*Edge::$grossissement;
        }
        elseif($this->numero<=263) {
            $this->largeur=10*Edge::$grossissement;
            $this->hauteur=210*Edge::$grossissement;
        }
        else {
            $this->largeur=17*Edge::$grossissement;
            $this->hauteur=240*Edge::$grossissement;
        }
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        if ($this->numero <= 84) {
            include_once($this->getChemin().'/../../MyFonts.Post.class.php');
            list($rouge,$vert,$bleu)=$this->getColorsFromDB();
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(0,0,0),'Texte');
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $blanc=imagecolorallocate($image2,255,255,255);

            $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
            $couleur_texte=imagecolorallocate($this->image,$rouge_texte, $vert_texte, $bleu_texte);
            $noir=imagecolorallocate($this->image,0,0,0);
            
            imagefill($image2,0,0, $fond);
            $im=imagecreatefrompng($this->getChemin().'/MP.Titre.Premiers.png');
            $post=new MyFonts('itfmecanorma/eurostile/extended-bold',
                              rgb2hex($rouge_texte, $vert_texte, $bleu_texte),
                              rgb2hex($rouge, $vert, $bleu),
                              1650,
                              'MICKEY  PARADE      .');
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, $this->largeur*3.2, $this->largeur*0.25, 0, 0, $nouvelle_largeur*0.85, $this->largeur*0.45, $width, $height/2);
            imagefilledrectangle($image2, $this->largeur*6.08, $this->largeur*0.42, $this->largeur*6.18, $this->largeur*0.52, $couleur_texte);
            $this->image=imagerotate($image2, -90, $blanc);
        }
        elseif ($this->numero <=132) {
            $blanc = imagecolorallocate($this->image, 255,255,255);
            $noir = imagecolorallocate($this->image, 0,0,0);
            $gris = imagecolorallocate($this->image, 192,192,192);

            if ($this->numero <= 120)
                list($logo,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/logo MP.png');
            else
                list($logo,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/logo MP 1990.png');
            $hauteur_logo=($this->largeur-Edge::$grossissement)*($height/$width);

            imagecopyresampled ($this->image, $logo, .5*Edge::$grossissement, 0, 0, 0, $this->largeur, $hauteur_logo, $width, $height);

            list($sous_image,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/MP.'.$this->numero.'.dessin.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagecopyresampled ($this->image, $sous_image, .5*Edge::$grossissement, $hauteur_logo, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);


        }
        elseif ($this->numero <= 139) {
            include_once($this->getChemin().'/../../MyFonts.Post.class.php');
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $blanc=imagecolorallocate($image2,255,255,255);
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
            $couleur_texte=imagecolorallocate($this->image,$rouge_texte,$vert_texte,$bleu_texte);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
            $fond=imagecolorallocate($image2,$rouge,$vert,$bleu);
            imagefill($image2, 0, 0, $fond);
            $post=new MyFonts('urw/nimbus-sans/l-black-condensed-italic',
                              rgb2hex($rouge_texte, $vert_texte, $bleu_texte),
                              rgb2hex($rouge, $vert, $bleu),
                              4500,
                              'N° '.$this->numero.'                                                     MICKEY PARADE    .');
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*0.9*($width/$height);
            imagecopyresampled ($image2, $texte, 2*Edge::$grossissement, $this->largeur*0.25, 0, 0, $nouvelle_largeur*1.3, $this->largeur*0.6, $width, $height/2);

            $this->image=imagerotate($image2, 90, $blanc);
            
        }
        elseif ($this->numero <= 192) {
            include_once($this->getChemin().'/../../MyFonts.Post.class.php');
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
            $couleur_texte=imagecolorallocate($this->image,$rouge_texte,$vert_texte,$bleu_texte);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB();
            $fond=imagecolorallocate($image2,$rouge,$vert,$bleu);
            $blanc=imagecolorallocate($image2,255,255,255);
            imagefill($image2,0,0,$fond);

            list($tranche,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/Titre MP2.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagefilledrectangle($this->image,$this->largeur/8, $this->hauteur/3, $this->largeur/8+$this->largeur*2/3-Edge::$grossissement/2, $this->hauteur/3+$nouvelle_hauteur*2/3-Edge::$grossissement/3,$couleur_texte);
            imagecopyresampled ($this->image, $tranche, $this->largeur/8, $this->hauteur/3, 0, 0, $this->largeur*2/3, $nouvelle_hauteur*2/3, $width, $height);

            $post=new MyFonts('itc/franklin-gothic/franklin-got-cmp-demi-italic',
                              rgb2hex($rouge_texte, $vert_texte, $bleu_texte),
                              rgb2hex($rouge, $vert, $bleu),
                              2200,
                              'N° '.$this->numero.'                           MICKEY PARADE    .');
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*0.9*($width/$height);
            imagecopyresampled ($image2, $texte, 2*Edge::$grossissement, $this->largeur*0.2, 0, 0, $nouvelle_largeur*1.3, $this->largeur*0.7, $width, $height/2);

            $this->image=imagerotate($image2, 90, $blanc);

            $this->placer_image('MP.tete.png', 'haut', array(0,$this->largeur/2));
        }
        elseif ($this->numero <= 204) {
            include_once($this->getChemin().'/../../MyFonts.Post.class.php');

            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $blanc=imagecolorallocate($image2, 255, 255, 255);

            list($rouge,$vert,$bleu)=$this->getColorsFromDB();
            $fond=imagecolorallocate($image2,$rouge,$vert,$bleu);
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
            $couleur_texte=imagecolorallocate($this->image,$rouge_texte,$vert_texte,$bleu_texte);
            imagefill($image2,0,0,$fond);

            $lettres='MICKEYPARADE';
            $post=new MyFonts('ef-typeshop/koblenz/bold',
                          rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                          rgb2hex($rouge,$vert,$bleu),
                          1500,
                          'MICKEY         PARADE       .');
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, $this->hauteur*0.3, $this->largeur*0.28, 0, 0, $nouvelle_largeur, $this->largeur*0.5, $width, $height/2);


            $this->image=imagerotate($image2, 90, $blanc);
            $blanc=imagecolorallocate($this->image, 255, 255, 255);
            for ($i=0.57;$i>=0.51;$i-=0.015)
                imagefilledrectangle($this->image, 0, $this->hauteur*$i, $this->largeur, $this->hauteur*$i-$this->largeur*0.05, $blanc);
            $texte_numero=new Texte($this->numero,$this->largeur*0.7,$this->hauteur-$this->largeur*0.8,
                                    5.5*Edge::$grossissement,90,$blanc,'Kabel Demi.ttf');
            $texte_numero->dessiner($this->image);

            $lettre_serie=new Texte($lettres[$this->numero-193],-1,$this->largeur,
                                    8.5*Edge::$grossissement,0,$blanc,'Kabel Demi.ttf');
            $lettre_serie->dessiner_centre($this->image);

            imagefilledarc($this->image, $this->largeur/2, $this->largeur*1.9, $this->largeur*0.9, $this->largeur*0.9, 0, 360, $couleur_texte, IMG_ARC_PIE);
            //imagefilledellipse($this->image, , , $couleur_texte);
            $texte_numero_serie=new Texte($this->numero-192,-1,$this->largeur*2.2,
                                    7*Edge::$grossissement,0,$fond,'Kabel Demi.ttf');
            if ($this->numero-192 < 10)
                $texte_numero_serie->dessiner_centre($this->image);
            else {
                $texte_numero_serie->dessiner_centre($this->image,array(0.9,1),array($rouge_texte,$vert_texte,$bleu_texte));
            }
        }
        elseif ($this->numero<=216) {
            $this->placer_image('MP.'.$this->numero.'.tranche.png');
        }
        elseif ($this->numero<=235) {
            $blanc=imagecolorallocate($this->image, 255, 255, 255);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB();
            $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
            imagefill($this->image,0,0,$fond);

            $chemin_image='Titre MP 217-235'
                         .(($this->numero == 227 ||$this->numero == 230 )?'_rouge':'')
                         .'.png';
            $logo=$this->placer_image($chemin_image);

            $width=imagesx($logo);
            $height=imagesy($logo);
            $hauteur_logo=$this->largeur*($height/$width);
            
            if ($this->numero <= 228)
                $texte=$this->numero;
            else
                $texte=html_entity_decode ("NUM&Eacute;RO $this->numero",ENT_NOQUOTES,'utf-8');
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
            $couleur_texte=imagecolorallocate($this->image,$rouge_texte,$vert_texte,$bleu_texte);

            $texte_numero=new Texte($texte,$this->largeur*7/10,$this->hauteur-$this->largeur*4/5,
                                    5.5*Edge::$grossissement,90,$couleur_texte,'Kabel Demi.ttf');
            $texte_numero->dessiner($this->image);
            switch ($this->numero) {
                case 230:
                    $texte_central=html_entity_decode ("SP&Eacute;CIAL MYST&Egrave;RE",ENT_NOQUOTES,'utf-8');
                    $pos_texte_central=$this->hauteur*7/12;
                break;
                case 232:
                    $texte_central=html_entity_decode ("SP&Eacute;CIAL AVENTURE",ENT_NOQUOTES,'utf-8');
                    $pos_texte_central=$this->hauteur*7/12;
                break;
                case 234:
                    $texte_central=html_entity_decode ("SP&Eacute;CIAL SCIENCE-FICTION",ENT_NOQUOTES,'utf-8');
                    $pos_texte_central=$this->hauteur*2/3;
                break;
                default:
                    list($tranche,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/MP.'.$this->numero.'.tranche.png');
                    $nouvelle_hauteur=($this->largeur)*($height/$width);
                    imagecopyresampled ($this->image, $tranche, 0, $hauteur_logo, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
                break;
            }
            if (isset($texte_central)) {
                $texte=new Texte($texte_central,$this->largeur*7/10,$pos_texte_central,
                                    5.5*Edge::$grossissement,90,$couleur_texte,'Kabel Demi.ttf');
                $texte->dessiner($this->image);
            }
        }
        elseif ($this->numero <= 253) {
            $fond=imagecolorallocate($this->image,255,208,18);
            imagefill($this->image,0,0,$fond);

            $this->placer_image('MP.'.$this->numero.'.haut.png','bas',array(0,$this->hauteur-$this->largeur*1.5));
            $this->placer_image('MP.Planete2000.haut.png','haut',array(0,$this->largeur*1.5));
            $this->placer_image('MP.'.$this->numero.'.dessin.png','haut',array(0,$this->largeur*2.5));
            $this->placer_image('MP.'.$this->numero.'.bas.png','bas');
            
            $this->placer_image('MP.Planete2000.titre.png','bas',array(0,$this->largeur));
        }
        elseif ($this->numero <= 263) {
            $couleur_fond=imagecolorallocate($this->image, 239, 165, 32);
            imagefill($this->image,0,0,$couleur_fond);
            $this->placer_image('MP.255-263.Fond_haut.png');
            foreach(str_split($this->numero) as $i=>$chiffre) {
                $this->placer_image('MP.255-263.Chiffre.'.$chiffre.'.png','haut',array(0,$this->largeur*(.5+0.6*(2-$i))));
            }
            $this->placer_image('motif MP 254-263.png','haut',array(0,2.5*$this->largeur));
            $this->placer_image('logo MP 254-263.png','haut',array(0,3*$this->largeur));
            $dessin=$this->placer_image('MP.'.$this->numero.'.Dessin.png','haut',array(0,6.2*$this->largeur));
            $hauteur_sous_image=$this->largeur*(imagesy($dessin)/imagesx($dessin));
            $this->placer_image('MP.255-263.Fond_bas.png','bas');
            $this->placer_image('MP.'.$this->numero.'.Texte.png','bas');
        }
        else {
            include_once($this->getChemin().'/../../MyFonts.Post.class.php');
            if (in_array($this->numero, array(300,303,304))) {
                $this->placer_image('MP.'.$this->numero.'.tranche.png');
                return $this->image;
            }
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte numéro');
            if ($this->numero <= 295) {
                if ($this->numero < 287)
                    list($rouge,$vert,$bleu)=$this->getColorsFromDB();
                else
                    list($rouge,$vert,$bleu)=array(253,83,11);
                $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
                imagefill($this->image,0,0,$fond);

                $this->placer_image('Titre MPG.png', 'haut', array($this->largeur/8, $this->largeur/2), 0.667, 0.667);
                $post=new MyFonts('fontfont/zwo/bold-lf-sc',
                      rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                      rgb2hex($rouge,$vert,$bleu),
                      225,
                      $this->numero);
                $chemin_image=$post->chemin_image;
                list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                $nouvelle_largeur=$this->largeur*($width/$height);
                imagecopyresampled ($this->image, $texte, $this->largeur*0.05, $this->hauteur-0.8*$this->largeur, 0, 0, $nouvelle_largeur - $this->largeur*0.1, $this->largeur/2, $width, $height/2);

            }
            else {
                $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
                $blanc=imagecolorallocate($image2, 255, 255, 255);
                
                list($rouge,$vert,$bleu)=$this->getColorsFromDB();
                $fond=imagecolorallocate($image2,$rouge,$vert,$bleu);
                imagefill($image2,0,0,$fond);

                $texte=imagecolorallocate($image2,$rouge_texte,$vert_texte,$bleu_texte);
                if ($this->numero<=309) {
                    $post=new MyFonts('adobe/frutiger/bold-2',
                                  rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                                  rgb2hex($rouge,$vert,$bleu),
                                  1700,
                                  'MICKEY PARADE G&Eacute;ANT');
                    $chemin_image=$post->chemin_image;
                    list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                    $nouvelle_largeur=$this->largeur*($width/$height);
                    imagecopyresampled ($image2, $texte, $this->hauteur*0.6, $this->largeur*0.28, 0, 0, $nouvelle_largeur*0.8, $this->largeur*0.4, $width, $height/2);

                    $this->image=imagerotate($image2, 90, $blanc);
                    $post=new MyFonts('fontfont/zwo/bold-lf-sc',
                          rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                          rgb2hex($rouge,$vert,$bleu),
                          225,
                          $this->numero);
                    $chemin_image=$post->chemin_image;
                    list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                    $nouvelle_largeur=$this->largeur*($width/$height);
                    imagecopyresampled ($this->image, $texte, $this->largeur*0.05, $this->hauteur-0.8*$this->largeur, 0, 0, $nouvelle_largeur - $this->largeur*0.1, $this->largeur/2, $width, $height/2);

                }
                else {
                    $post=new MyFonts('bitstream/humanist-777/black',
                                  rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                                  rgb2hex($rouge,$vert,$bleu),
                                  1850,
                                  'MICKEY PARADE G&Eacute;ANT');
                    $chemin_image=$post->chemin_image;
                    list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                    $nouvelle_largeur=$this->largeur*($width/$height);
                    imagecopyresampled ($image2, $texte, $this->hauteur*0.58, $this->largeur*0.2, 0, 0, $nouvelle_largeur, $this->largeur*0.5, $width, $height/2);

                    $this->image=imagerotate($image2, 90, $blanc);

                    $post=new MyFonts('bitstream/humanist-777/black',
                          rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                          rgb2hex($rouge,$vert,$bleu),
                          275,
                          $this->numero.'   .');
                    $chemin_image=$post->chemin_image;
                    list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                    $nouvelle_largeur=$this->largeur*($width/$height);
                    imagecopyresampled ($this->image, $texte, $this->largeur*0.05, $this->hauteur-0.8*$this->largeur, 0, 0, $nouvelle_largeur, $this->largeur/2, $width, $height/2);
                }
            }
            
            list($width,$height)= getimagesize($this->getChemin().'/MP.'.$this->numero.'.icone.png');
            $hauteur_icone=$this->largeur*($height/$width);
            $this->placer_image('MP.'.$this->numero.'.icone.png', 'bas', array(0, -$hauteur_icone/2+1.4*$this->largeur));

        }
        return $this->image;
    }

}
    function __toString($val)
{
        return ($val);
}
