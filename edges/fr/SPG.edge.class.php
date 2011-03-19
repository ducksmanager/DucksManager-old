<?php

class fr_SPG extends Edge {

    var $pays = 'fr';
    var $magazine = 'SPG';
    var $intervalles_validite = array(array('debut' => 1, 'fin' => 162));
    var $en_cours = array();
    static $largeur_defaut = 20;
    static $hauteur_defaut = 219.7;

    function fr_SPG($numero) {
        $this->numero = $numero;
        if ($this->numero <= 88) {
            $this->largeur = 20 * Edge::$grossissement;
            $this->hauteur = 255 * Edge::$grossissement;
        } else {
            $this->largeur = 13 * Edge::$grossissement;
            $this->hauteur = 275 * Edge::$grossissement;
        }
        $this->image = imagecreatetruecolor(intval($this->largeur), intval($this->hauteur));
        if ($this->image === false)
            xdebug_break ();
    }

    function dessiner() {
        if ($this->numero <= 57) {
            $blanc = imagecolorallocate($this->image, 255, 255, 255);
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->hauteur, $noir);
            imagefilledrectangle($this->image, .5 * Edge::$grossissement, .5 * Edge::$grossissement, $this->largeur - .5 * Edge::$grossissement, $this->hauteur - .5 * Edge::$grossissement, $blanc);
            $titre = new Texte('SUPER PICSOU G&#201;ANT', $this->largeur * 1.5 / 5, $this->largeur / 2,
                            6.2 * Edge::$grossissement, -90, $noir, 'ArialBlack.ttf');
            $this->textes[] = $titre;
            $texte_numero = new Texte($this->numero, $this->numero < 10 ? $this->largeur * 1.7 / 5 : $this->largeur * 1 / 5, $this->hauteur - $this->largeur / 2,
                            7 * Edge::$grossissement, 0, $noir, 'ArialBlack.ttf');
            $this->textes[] = $texte_numero;
        } elseif ($this->numero <= 88) {
            switch ($this->numero) {
                case 59 : case 60: case 62: case 63:
                    $image_texte = $this->getChemin() . '/SPG.' . $this->numero . '.Texte.png';
                    break;
                default:
                    $image_texte = $this->getChemin() . '/Texte_SPG 2.png';
            }
            if (in_array($this->numero, array(69, 71, 72))) {
                include_once($this->getChemin() . '/../../util.php');
                $couleur1 = $this->getColorsFromDB(array(0, 0, 0), 'Couleur 1');
                $couleur2 = $this->getColorsFromDB(array(255, 255, 255), 'Couleur 2');
                $couleurs_inter = getMidColors($couleur1, $couleur2, $this->hauteur);
                foreach ($couleurs_inter as $i => $couleur) {
                    list($rouge, $vert, $bleu) = $couleur;
                    $couleur_allouee = imagecolorallocate($this->image, $rouge, $vert, $bleu);
                    imageline($this->image, 0, $i, $this->largeur, $i, $couleur_allouee);
                }
            } else {
                list($rouge, $vert, $bleu) = $this->getColorsFromDB(array(255, 255, 255));
                $fond = imagecolorallocate($this->image, $rouge, $vert, $bleu);
                imagefill($this->image, 0, 0, $fond);
            }
            $blanc = imagecolorallocate($this->image, 255, 255, 255);
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            list($icone, $width, $height) = imagecreatefrompng_getimagesize($image_texte);
            imagealphablending($icone, false);
            # set the transparent color
            $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
            imagefill($icone, 0, 0, $transparent);
            # set the transparency settings for the picture after adding the transparency
            imagesavealpha($icone, true);
            imagealphablending($icone, true);
            $nouvelle_largeur = $this->largeur / 1.5;
            $nouvelle_hauteur = $nouvelle_largeur * ($height / $width);
            imagecopyresampled($this->image, $icone, $this->largeur / 6, $this->largeur / 2, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $width, $height);

            $icone = imagecreatefrompng($this->getChemin() . '/SPG.' . $this->numero . '.icone.png');
            imagealphablending($icone, false);
            # set the transparent color
            $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
            imagefill($icone, 0, 0, $transparent);
            # set the transparency settings for the picture after adding the transparency
            imagesavealpha($icone, true);
            imagealphablending($icone, true);
            $width = imagesx($icone);
            $height = imagesy($icone);
            $hauteur_icone = $this->largeur * ($height / $width);
            $this->placer_image($icone, 'bas', array(0, 2.1 * $this->largeur - $hauteur_icone / 2));

            $texte_numero_blanc = array(70, 73, 75, 76, 77, array('debut' => 79, 'fin' => 88));
            $intervalle_numeros_blancs = new IntervalleValidite($texte_numero_blanc);
            $texte_numero = new Texte($this->numero, $this->largeur * 7.5 / 10, $this->hauteur - $this->largeur * 4 / 5,
                            7 * Edge::$grossissement, 90, $intervalle_numeros_blancs->estValide($this->numero) ? $blanc : $noir, 'ArialBlack.ttf');

            $texte_numero->pos_x = $this->largeur * 1 / 5;
            $texte_numero->angle = 0;
            $texte_numero->dessiner($this->image);
        } else {
            $epaisseur_bordure = .25 * Edge::$grossissement;
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            $blanc = imagecolorallocate($this->image, 255, 255, 255);
            if ($this->numero <= 142)
                list($rouge, $vert, $bleu) = array(223, 51, 9);
            else {
                list($rouge, $vert, $bleu) = $this->getColorsFromDB();
            }
            list($rouge_texte, $vert_texte, $bleu_texte) = $couleur_texte = $this->getColorsFromDB(array(255, 255, 255), 'Texte');
            $couleur_texte = imagecolorallocate($this->image, $rouge_texte, $vert_texte, $bleu_texte);
            if ($couleur_texte == $blanc)
                $image_texte_spg = 'Texte_SPG.png';
            else
                $image_texte_spg='Texte_SPG_' . $this->numero . '.png';

            $fond = imagecolorallocate($this->image, $rouge, $vert, $bleu);
            imagefill($this->image, 0, 0, $fond);
            list($icone, $width, $height) = imagecreatefrompng_getimagesize($this->getChemin() . '/SPG.' . $this->numero . '.icone.png');
            imagealphablending($icone, false);
            # set the transparent color
            $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
            imagefill($icone, 0, 0, $transparent);
            # set the transparency settings for the picture after adding the transparency
            imagesavealpha($icone, true);
            imagealphablending($icone, true);

            $nouvelle_hauteur = ($this->largeur - $epaisseur_bordure) * ($height / $width);
            if ($this->numero < 100)
                imagecopyresampled($this->image, $icone, $epaisseur_bordure, $this->hauteur - 2.1 * $this->largeur - $nouvelle_hauteur / 2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
            else {
                if ($this->numero == 111)
                    imagecopyresampled($this->image, $icone, $epaisseur_bordure, $this->hauteur - 1.5 * $this->largeur - $nouvelle_hauteur / 2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
                elseif ($this->numero >= 149) {
                    imagecopyresampled($this->image, $icone, $epaisseur_bordure, $this->hauteur - 3 * $this->largeur - $nouvelle_hauteur / 2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
                }
                else
                    imagecopyresampled($this->image, $icone, $epaisseur_bordure, $this->hauteur - 3.5 * $this->largeur - $nouvelle_hauteur / 2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
            }

            list($texte, $width, $height) = imagecreatefrompng_getimagesize($this->getChemin() . '/' . $image_texte_spg);
            imagealphablending($texte, false);
            # set the transparent color
            $transparent = imagecolorallocatealpha($texte, 0, 0, 0, 127);
            imagefill($texte, 0, 0, $transparent);
            # set the transparency settings for the picture after adding the transparency
            imagesavealpha($texte, true);
            imagealphablending($texte, true);
            $nouvelle_largeur = $this->largeur / 1.5;
            $nouvelle_hauteur = $nouvelle_largeur * ($height / $width);
            imagecopyresampled($this->image, $texte, $this->largeur / 6, $this->largeur / 2, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $width, $height);
            if ($this->numero == 111) {
                $texte_numero = new Texte($this->numero, $this->largeur * 7.5 / 10, $this->hauteur - $this->largeur * 2.2,
                                6 * Edge::$grossissement, 90, $couleur_texte, 'ArialBlack.ttf');
            } else {
                $texte_numero = new Texte($this->numero, $this->largeur * 7.5 / 10, $this->hauteur - $this->largeur * 4 / 5,
                                6 * Edge::$grossissement, 90, $couleur_texte, 'ArialBlack.ttf');
            }
            if ($this->numero < 100) {
                $texte_numero->pos_x = $this->largeur * .3 / 10;
                $texte_numero->angle = 0;
            }
            $texte_numero->dessiner($this->image);
        }
        return $this->image;
    }

}