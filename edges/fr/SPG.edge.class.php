<?php
class SPG extends Edge {
	var $pays='fr';
	var $magazine='SPG';
	var $intervalles_validite=array(array('debut'=>1,  'fin'=>57),
                                        array('debut'=>81, 'fin'=>149));
        static $largeur_defaut=20;
        static $hauteur_defaut=219.7;
        
        function SPG($numero) {
            $this->numero=$numero;
            if ($this->numero<=88) {
                $this->largeur=20*Edge::$grossissement;
                $this->hauteur=255*Edge::$grossissement;
            }
            else {
                $this->largeur=13*Edge::$grossissement;
                $this->hauteur=275*Edge::$grossissement;
            }
            $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
            if ($this->image===false)
                xdebug_break ();
	}
	function dessiner() {
		if ($this->numero<=57) {
			$blanc=imagecolorallocate($this->image,255,255,255);
			$noir = imagecolorallocate($this->image, 0, 0, 0);
			imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->hauteur, $noir);
			imagefilledrectangle($this->image, .5*Edge::$grossissement, .5*Edge::$grossissement, $this->largeur-.5*Edge::$grossissement, $this->hauteur-.5*Edge::$grossissement, $blanc);
			$titre=new Texte(mb_strtoupper('Super Picsou Geant','UTF-8'),$this->largeur*1.5/5,$this->largeur/2,
							 6.2*Edge::$grossissement,-90,$noir,'ArialBlack.ttf');
			$this->textes[]=$titre;
			$texte_numero=new Texte($this->numero,$this->numero < 10 ?$this->largeur*1.7/5 : $this->largeur*1/5,$this->hauteur-$this->largeur/2,
									7*Edge::$grossissement,0,$noir,'ArialBlack.ttf');
			$this->textes[]=$texte_numero;
		}
		elseif ($this->numero<=88) {
            list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
            $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
            imagefilledrectangle($this->image,0,0,$this->largeur, $this->hauteur,$fond);
			$blanc=imagecolorallocate($this->image,255,255,255);
			$noir=imagecolorallocate($this->image,0,0,0);
			$icone=imagecreatefrompng('edges/fr/Texte_SPG 2.png');
            imagealphablending($icone, false);
		    # set the transparent color
		    $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
		    imagefill($icone, 0, 0, $transparent);
		    # set the transparency settings for the picture after adding the transparency
		    imagesavealpha($icone,true);
		    imagealphablending($icone, true);
			list($width, $height) = getimagesize('edges/fr/Texte_SPG 2.png');
			$nouvelle_largeur=$this->largeur/1.5;
			$nouvelle_hauteur=$nouvelle_largeur*($height/$width);
            imagecopyresampled ($this->image, $icone, $this->largeur/6, $this->largeur/2, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $width, $height);
		    imagefill($this->image, 11.75*Edge::$grossissement, 40.25*Edge::$grossissement, $fond);
		    $icone=imagecreatefrompng('edges/fr/SPG.'.$this->numero.'.icone.png');
            imagealphablending($icone, false);
		    # set the transparent color
		    $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
		    imagefill($icone, 0, 0, $transparent);
		    # set the transparency settings for the picture after adding the transparency
		    imagesavealpha($icone,true);
		    imagealphablending($icone, true);

			list($width, $height) = getimagesize('edges/fr/SPG.'.$this->numero.'.icone.png');
			$nouvelle_hauteur=($this->largeur)*($height/$width);
			imagecopyresampled ($this->image, $icone, 0, $this->hauteur-2.1*$this->largeur-$nouvelle_hauteur/2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
			imagefill($this->image, $this->largeur-1, $this->hauteur-2.1*$this->largeur-$nouvelle_hauteur/2+1, $fond);
            $texte_numero=new Texte($this->numero,$this->largeur*7.5/10,$this->hauteur-$this->largeur*4/5,
                                        7*Edge::$grossissement,90,$blanc,'ArialBlack.ttf');
            
			$texte_numero->pos_x=$this->largeur*1/5;
			$texte_numero->angle=0;
			$texte_numero->dessiner($this->image);

		}
		else {
            $epaisseur_bordure=.25*Edge::$grossissement;
            $contenu_couleur='';
            if ($this->numero<=141)
                list($rouge,$vert,$bleu)=array(223,51,9);
            else {
                list($rouge,$vert,$bleu)=$this->getColorsFromDB();
            }
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            $blanc = imagecolorallocate($this->image, 255,255,255);
            $fond=imagecolorallocate($this->image,$rouge,$vert,$bleu);
            imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->hauteur, $noir);
            imagefilledrectangle($this->image, $epaisseur_bordure, $epaisseur_bordure, $this->largeur-$epaisseur_bordure, $this->hauteur-$epaisseur_bordure, $fond);

            $icone=imagecreatefrompng('edges/fr/SPG.'.$this->numero.'.icone.png');
            imagealphablending($icone, false);
		    # set the transparent color
		    $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
		    imagefill($icone, 0, 0, $transparent);
		    # set the transparency settings for the picture after adding the transparency
		    imagesavealpha($icone,true);
		    imagealphablending($icone, true);
		    
			list($width, $height) = getimagesize('edges/fr/SPG.'.$this->numero.'.icone.png');
			$nouvelle_hauteur=($this->largeur-$epaisseur_bordure)*($height/$width);
			if ($this->numero<100)
				imagecopyresampled ($this->image, $icone, $epaisseur_bordure, $this->hauteur-2.1*$this->largeur-$nouvelle_hauteur/2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
			else {
                if ($this->numero==111)
                    imagecopyresampled ($this->image, $icone, $epaisseur_bordure, $this->hauteur-1.5*$this->largeur-$nouvelle_hauteur/2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
                else
                    imagecopyresampled ($this->image, $icone, $epaisseur_bordure, $this->hauteur-3.5*$this->largeur-$nouvelle_hauteur/2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
            }
            
			$icone=imagecreatefrompng('edges/fr/Texte_SPG.png');
			imagealphablending($icone, false);
		    # set the transparent color
		    $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
		    imagefill($icone, 0, 0, $transparent);
		    # set the transparency settings for the picture after adding the transparency
		    imagesavealpha($icone,true);
		    imagealphablending($icone, true);
			list($width, $height) = getimagesize('edges/fr/Texte_SPG.png');
			$nouvelle_largeur=$this->largeur/1.5;
			$nouvelle_hauteur=$nouvelle_largeur*($height/$width);
            imagecopyresampled ($this->image, $icone, $this->largeur/6, $this->largeur/2, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $width, $height);
			if ($this->numero == 111) {
                $texte_numero=new Texte($this->numero,$this->largeur*7.5/10,$this->hauteur-$this->largeur*2.2,
                                        6*Edge::$grossissement,90,$blanc,'ArialBlack.ttf');
            }
            else {
                $texte_numero=new Texte($this->numero,$this->largeur*7.5/10,$this->hauteur-$this->largeur*4/5,
                                        6*Edge::$grossissement,90,$blanc,'ArialBlack.ttf');
            }
			if ($this->numero < 100) {
				$texte_numero->pos_x=$this->largeur*.3/10;
				$texte_numero->angle=0;
			}
			$texte_numero->dessiner($this->image);
		}
		return $this->image;
	}

}