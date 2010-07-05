<?php
class PM extends Edge {
    var $pays='fr';
    var $magazine='PM';
    var $intervalles_validite=array(array('debut'=>1, 'fin'=>324));
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
                    list($degrade,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/PM.88.degrade.png');
                    $nouvelle_hauteur=$this->largeur*($height/$width);
                    imagecopyresampled ($this->image, $degrade, 0, $separation_haut_bas, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
                break;

                case 104:
                    list($dessin,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/PM.104.dessin.png');
                    $nouvelle_hauteur=$this->largeur*($height/$width);
                    imagecopyresampled ($this->image, $dessin, 0, $separation_haut_bas+18*Edge::$grossissement, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
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
                    list($dessin,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/PM.155.dessin.png');
                    $nouvelle_hauteur=$this->largeur*($height/$width);
                    imagecopyresampled ($this->image, $dessin, 0, $this->hauteur-$nouvelle_hauteur-20*Edge::$grossissement, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
                break;

                case 158:
                    imagefilledrectangle($this->image, 0, $this->hauteur/3 + 10*Edge::$grossissement, $this->largeur, $this->hauteur/3 + 33*Edge::$grossissement, $blanc);
                break;

                case 160: case 167: case 173: case 174: case 175: case 180: case 181:
                    list($dessin,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/PM.'.$this->numero.'.dessin.png');
                    $transparent = imagecolorallocatealpha($dessin, 0, 0, 0, 127);
                    imagefill($dessin, 0, 0, $transparent);
                    imagefill($dessin, $width-1, 0, $transparent);
                    $nouvelle_hauteur=$this->largeur*($height/$width);
                    imagecopyresampled ($this->image, $dessin, 0, $this->hauteur-$nouvelle_hauteur, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
                    if ($this->numero == 180) {
                       list($dessin,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/PM.180.bulle.png');
                        $transparent = imagecolorallocatealpha($dessin, 0, 0, 0, 127);
                        imagefill($dessin, 0, 0, $transparent);
                        imagefill($dessin, $width-1, 0, $transparent);
                        $nouvelle_hauteur=$this->largeur*($height/$width);
                        imagecopyresampled ($this->image, $dessin, 0, 45.5*Edge::$grossissement, 0, 0, $this->largeur*2/3, $nouvelle_hauteur*2/3, $width, $height);
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
                list($titre,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/Titre PM2 blanc.png');
            else
                list($titre,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/Titre PM2.png');
            $transparent=imagecolortransparent($titre);
            $ecriture_vers_haut=$this->numero<33 || $this->numero == 165;
            if (!$ecriture_vers_haut)
                $titre=imagerotate ($titre, 180, $transparent);
            $nouvelle_hauteur=($this->largeur*3/4)*($height/$width);
            imagecopyresampled ($this->image, $titre, $this->largeur/5, ($ecriture_vers_haut ? 10 : 5)*Edge::$grossissement, 0, 0, $this->largeur*3/4, $nouvelle_hauteur, $width, $height);
            $texte_numero=new Texte($this->numero,$ecriture_vers_haut ? $this->largeur*8/10 : $this->largeur/3, $ecriture_vers_haut ? 10*Edge::$grossissement : $nouvelle_hauteur + 5*Edge::$grossissement,
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
        elseif ($this->numero <=324) {
            $blanc = imagecolorallocate($this->image, 255, 255, 255);
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->hauteur, $blanc);
            $this->agrafer();
        }
        return $this->image;
    }

}