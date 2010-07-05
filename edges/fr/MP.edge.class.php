<?php
class MP extends Edge {
    var $pays='fr';
    var $magazine='MP';
    var $intervalles_validite=array(array('debut'=>1,'fin'=>2),
                                    array('debut'=>9,'fin'=>10),13,15,48,49,55,57,60,61,63,
                                    array('debut'=>66,'fin'=>69),73,74,75,76,77,79,80,81,88,89,91,92,95,113,124,
                                    142,147,149,152,154,160,162,164,171,176,181,182,191,
                                    array('debut'=>205, 'fin'=>216),
                                    226,228,
                                    array('debut'=>229,'fin'=>234),
                                    237,247,
                                    265,266,267,268,270);
    var $en_cours=array(21,29,32,33,34,36,42,43,44,53);
    static $largeur_defaut=20;
    static $hauteur_defaut=219.7;

    function MP($numero) {
        $this->numero=$numero;
        if($this->numero <=86) {
            $this->largeur=14*Edge::$grossissement;
            $this->hauteur=186*Edge::$grossissement;
        }
        elseif ($this->numero <= 132) {
            $this->largeur=11.5*Edge::$grossissement;
            $this->hauteur=186*Edge::$grossissement;
        }
        elseif($this->numero<=234) {
            $this->largeur=11.5*Edge::$grossissement;
            $this->hauteur=210*Edge::$grossissement;
        }
        elseif($this->numero<=253) {
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
        if ($this->numero <= 81) {
            list($rouge,$vert,$bleu)=$this->getColorsFromDB();

            $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
            $noir=imagecolorallocate($this->image,0,0,0);
            imagefilledrectangle($this->image, .5*Edge::$grossissement, .5*Edge::$grossissement, $this->largeur-.5*Edge::$grossissement, $this->hauteur-.5*Edge::$grossissement, $fond);
            $sous_image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
            $fond2=imagecolorallocate($sous_image,$rouge,$vert,$bleu);
            $noir2=imagecolorallocate($sous_image,0,0,0);
            imagefilledrectangle($sous_image, 0, 0, $this->largeur, $this->hauteur, $fond2);
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(0,0,0),'Texte');
            $couleur_texte=imagecolorallocate($this->image,$rouge_texte,$vert_texte,$bleu_texte);
            $r=imagettftext($sous_image,5*Edge::$grossissement,-90, $this->largeur/5, 0, $couleur_texte,'edges/Square721.ttf',strtoupper('Mickey'));
            imagettftext($sous_image,5*Edge::$grossissement,-90, $this->largeur/5, $r[3] + 4*Edge::$grossissement, $couleur_texte,'edges/Square721.ttf',strtoupper('Parade'));
            imagecopyresampled ($this->image, $sous_image, $this->largeur*0.7/5, $this->hauteur*1.2/5, 0, 0, $this->largeur, $this->hauteur*4.7/5, $this->largeur, $this->hauteur);
            $cote_carre=1*Edge::$grossissement;

            imagefilledrectangle($this->image, $this->largeur*2.2/5, $this->hauteur/2 - 6*Edge::$grossissement,
                                               $this->largeur*2.2/5 +$cote_carre, $this->hauteur/2 - 6*Edge::$grossissement + $cote_carre,
                                 $couleur_texte);
            imagerectangle($this->image, 0, 0, $this->largeur-1, $this->hauteur-1, $noir);
        }
        elseif ($this->numero <=133) {
            $blanc = imagecolorallocate($this->image, 255,255,255);
            $noir = imagecolorallocate($this->image, 0,0,0);
            $gris = imagecolorallocate($this->image, 192,192,192);
            
            list($logo,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/logo MP.png');
            $hauteur_logo=($this->largeur-Edge::$grossissement)*($height/$width);

            imagecopyresampled ($this->image, $logo, .5*Edge::$grossissement, 0, 0, 0, $this->largeur, $hauteur_logo, $width, $height);

            list($sous_image,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.'.$this->numero.'.dessin.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagecopyresampled ($this->image, $sous_image, .5*Edge::$grossissement, $hauteur_logo, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
            imagefilledrectangle($this->image, .5*Edge::$grossissement, $hauteur_logo+$nouvelle_hauteur, $this->largeur-.5*Edge::$grossissement, $this->hauteur-.5*Edge::$grossissement, $blanc);
            imagearc($this->image, $this->largeur/2, $this->hauteur-$this->largeur*2/3, $this->largeur*2/3, $this->largeur*2/3, 0,180,$gris);
            imagearc($this->image, $this->largeur/2, $this->hauteur-$this->largeur*2/3, $this->largeur*2/3, $this->largeur*2/3, 180,360,$gris);

            $numero_dans_serie=$this->numero-86 - 12*floor(($this->numero-86)/12);
            if ($numero_dans_serie >= 10) {
                list($numero1,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.numero.1.png');
                $nouvelle_hauteur=($this->largeur/5)*($height/$width)*3/5;

                list($numero2,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.numero.'.($numero_dans_serie-10).'.png');
                $nouvelle_hauteur=($this->largeur/5)*($height/$width)*3/5;

                imagecopyresampled ($this->image, $numero, $this->largeur*5/14, $this->hauteur-$this->largeur*4/5, 0, 0, $this->largeur/5, $nouvelle_hauteur, $width, $height);
                imagecopyresampled ($this->image, $numero2, $this->largeur*6/14, $this->hauteur-$this->largeur*4/5, 0, 0, $this->largeur/5, $nouvelle_hauteur, $width, $height);
            }
            else {
                list($numero,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.numero.'.$numero_dans_serie.'.png');
                $nouvelle_hauteur=($this->largeur/5)*($height/$width);

                imagecopyresampled ($this->image, $numero, $this->largeur*5.5/14, $this->hauteur-$this->largeur*4/5, 0, 0, $this->largeur/5, $nouvelle_hauteur, $width, $height);
            }
            
        }
        elseif ($this->numero >=140 && $this->numero <= 191) {
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(0,0,0),'Texte');
            $couleur_texte=imagecolorallocate($this->image,$rouge_texte,$vert_texte,$bleu_texte);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB();
            $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
            imagefill($this->image,0,0,$fond);           
            
            list($tranche,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/Titre MP2.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagefilledrectangle($this->image,$this->largeur/8, $this->hauteur/3, $this->largeur/8+$this->largeur*2/3-Edge::$grossissement/2, $this->hauteur/3+$nouvelle_hauteur*2/3-Edge::$grossissement/3,$couleur_texte);
            imagecopyresampled ($this->image, $tranche, $this->largeur/8, $this->hauteur/3, 0, 0, $this->largeur*2/3, $nouvelle_hauteur*2/3, $width, $height);

            list($texte_numero,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.texte_numero.140-191.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagefilledrectangle($this->image,$this->largeur/8, $this->hauteur-10*Edge::$grossissement, $this->largeur/8+$this->largeur*2/3-Edge::$grossissement/2, $this->hauteur-10*Edge::$grossissement+$nouvelle_hauteur*2/3-Edge::$grossissement/2,$couleur_texte);

            imagecopyresampled ($this->image, $texte_numero, $this->largeur/8, $this->hauteur-10*Edge::$grossissement, 0, 0, $this->largeur*2/3, $nouvelle_hauteur*2/3, $width, $height);

            $hauteur_precedents=14*Edge::$grossissement;
            for ($i=0;$i<strlen($this->numero);$i++) {
                list($numero,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.numero.140-191.'.$this->numero[$i].'.png');
                $nouvelle_hauteur=($this->largeur)*($height/$width);
                imagefilledrectangle($this->image,$this->largeur/8, $this->hauteur-$hauteur_precedents, $this->largeur/8+$this->largeur*2/3-Edge::$grossissement/2, $this->hauteur-$hauteur_precedents+$nouvelle_hauteur*2/3-Edge::$grossissement/2,$couleur_texte);

                imagecopyresampled ($this->image, $numero, $this->largeur/8, $this->hauteur-$hauteur_precedents, 0, 0, $this->largeur*2/3, $nouvelle_hauteur*2/3, $width, $height);
                $hauteur_precedents+=$this->largeur*2/5;
            }

            imagetruecolortopalette($this->image, false, 255);
            imagecolorset($this->image, imagecolorclosest($this->image,0,0,0), $rouge,$vert,$bleu);

            imagepalettetotruecolor($this->image);
            list($tete,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.tete.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagecopyresampled ($this->image, $tete, 0, $this->largeur/2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
        }
        elseif ($this->numero >= 205 && $this->numero<=216) {
            list($tranche,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.'.$this->numero.'.tranche.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagecopyresampled ($this->image, $tranche, 0, 0, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
        }
        elseif ($this->numero >= 217 && $this->numero<=235) {
            $blanc=imagecolorallocate($this->image, 255, 255, 255);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB();
            $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
            imagefill($this->image,0,0,$fond);
            if ($this->numero == 227 ||$this->numero == 230) {
                list($logo,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/Titre MP 217-234_rouge.png');
            }
            else {
                list($logo,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/Titre MP 217-234.png');
            }
            $hauteur_logo=($this->largeur)*($height/$width);
            imagecopyresampled ($this->image, $logo, 0, 0, 0, 0, $this->largeur, $hauteur_logo, $width, $height);

            if ($this->numero <= 228)
                $texte=$this->numero;
            else
                $texte=html_entity_decode ("NUM&Eacute;RO $this->numero",ENT_NOQUOTES,'utf-8');
            $texte_numero=new Texte($texte,$this->largeur*7/10,$this->hauteur-$this->largeur*4/5,
                                    5.5*Edge::$grossissement,90,$blanc,'Kabel Demi.ttf');
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
                    list($tranche,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.'.$this->numero.'.tranche.png');
                    $nouvelle_hauteur=($this->largeur)*($height/$width);
                    imagecopyresampled ($this->image, $tranche, 0, $hauteur_logo, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
                break;
            }
            if (isset($texte_central)) {
                list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
                $couleur_texte=imagecolorallocate($this->image,$rouge_texte,$vert_texte,$bleu_texte);
                $texte=new Texte($texte_central,$this->largeur*7/10,$pos_texte_central,
                                    5.5*Edge::$grossissement,90,$couleur_texte,'Kabel Demi.ttf');
                $texte->dessiner($this->image);
            }
        }
        elseif ($this->numero >=236 && $this->numero <= 253) {
            $fond=imagecolorallocate($this->image,255,208,18);
            imagefill($this->image,0,0,$fond);

            list($tranche,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.'.$this->numero.'.tranche.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagecopyresampled ($this->image, $tranche, 0, $this->largeur, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);

            list($titre,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/Titre MP 236-253.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagecopyresampled ($this->image, $titre, 0, $this->hauteur-$this->largeur-$nouvelle_hauteur, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
        }
        elseif ($this->numero >=265) {
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(0,0,0),'Texte numéro');
            $couleur_texte=imagecolorallocate($this->image,$rouge_texte,$vert_texte,$bleu_texte);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB();
            $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
            imagefill($this->image,0,0,$fond);

            for ($i=0;$i<strlen($this->numero);$i++) {
                list($texte_numero,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.MPG.numero.'.$this->numero[$i].'.png');
                $nouvelle_hauteur=($this->largeur)*($height/$width);
                imagecopyresampled ($this->image, $texte_numero, $i*($this->largeur/strlen($this->numero)), $this->hauteur-$nouvelle_hauteur/2, 0, 0, $this->largeur/strlen($this->numero), $nouvelle_hauteur/strlen($this->numero), $width, $height);
            }
            imagetruecolortopalette($this->image, false, 255);
            imagecolorset($this->image, imagecolorclosest($this->image,0,0,0), $rouge_texte,$vert_texte,$bleu_texte);

            imagepalettetotruecolor($this->image);

            list($titre,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/Titre MPG.png');
            $nouvelle_hauteur=($this->largeur)*($height/$width);
            imagecopyresampled ($this->image, $titre, $this->largeur/8, $this->largeur/2, 0, 0, $this->largeur*2/3, $nouvelle_hauteur*2/3, $width, $height);

            list($icone,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/MP.'.$this->numero.'.icone.png');
			$nouvelle_hauteur=($this->largeur)*($height/$width);
			imagecopyresampled ($this->image, $icone, 0, $this->hauteur-1.4*$this->largeur-$nouvelle_hauteur/2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);

        }
        return $this->image;
    }

}

function imagepalettetotruecolor(&$img)
    {
        if (!imageistruecolor($img))
        {
            $w = imagesx($img);
            $h = imagesy($img);
            $img1 = imagecreatetruecolor($w,$h);
            imagecopy($img1,$img,0,0,0,0,$w,$h);
            $img = $img1;
        }
    }