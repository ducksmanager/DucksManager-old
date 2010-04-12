<?php
class SPG extends Edge {
	function SPG($numero) {
		$this->numero=$numero;
	}
	function dessiner() {
		if ($this->numero<=57) {
			$this->largeur=20*Edge::$grossissement;
			$this->hauteur=255*Edge::$grossissement;
			$image=imagecreatetruecolor($this->largeur,$this->hauteur);
			$blanc=imagecolorallocate($image,255,255,255);
			$noir = imagecolorallocate($image, 0, 0, 0);
			imagefilledrectangle($image, 0, 0, $this->largeur, $this->hauteur, $noir);
			imagefilledrectangle($image, .5*Edge::$grossissement, .5*Edge::$grossissement, $this->largeur-.5*Edge::$grossissement, $this->hauteur-.5*Edge::$grossissement, $blanc);
			$titre=new Texte(mb_strtoupper('Super Picsou Geant','UTF-8'),$this->largeur*2/5,$this->largeur/2,
							 6.2*Edge::$grossissement,-90,$noir,'ArialBlack.ttf');
			$this->textes[]=$titre;
			$texte_numero=new Texte($this->numero,$this->largeur*1/5,$this->hauteur-$this->largeur/2,
									7*Edge::$grossissement,0,$noir,'ArialBlack.ttf');
			$this->textes[]=$texte_numero;
		}
		elseif ($this->numero<=88) {
			
		}
		else {
			$this->largeur=13*Edge::$grossissement;
			$this->hauteur=275*Edge::$grossissement;
			$epaisseur_bordure=.25*Edge::$grossissement;
			$image=imagecreatetruecolor($this->largeur,$this->hauteur);
			$contenu_couleur='';
			if ($this->numero<141)
				list($rouge,$vert,$bleu)=array(223,51,9);
			else {
				
				$inF = fopen('fr/SPG.'.$this->numero.'.fond.txt',"r");
				while (!feof($inF)) {
				   $contenu_couleur.= fgets($inF, 4096);
				}
				list($rouge,$vert,$bleu)=explode(',',$contenu_couleur);
			}
			$fond=imagecolorallocate($image,$rouge,$vert,$bleu);
			imagefilledrectangle($image, 0, 0, $this->largeur, $this->hauteur, $noir);
			imagefilledrectangle($image, $epaisseur_bordure, $epaisseur_bordure, $this->largeur-$epaisseur_bordure, $this->hauteur-$epaisseur_bordure, $fond);
			$noir = imagecolorallocate($image, 0, 0, 0);
			$blanc = imagecolorallocate($image, 255,255,255);
			
			$icone=imagecreatefrompng('fr/SPG.'.$this->numero.'.icone.png');
			imagealphablending($icone, false);
		    # set the transparent color
		    $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
		    imagefill($icone, 0, 0, $transparent);
		    # set the transparency settings for the picture after adding the transparency
		    imagesavealpha($icone,true);
		    imagealphablending($icone, true);
		    
			list($width, $height) = getimagesize('fr/SPG.'.$this->numero.'.icone.png');
			$nouvelle_hauteur=($this->largeur-$epaisseur_bordure)*($height/$width);
			if ($this->numero<100)
				imagecopyresampled ($image, $icone, $epaisseur_bordure, $this->hauteur-2.1*$this->largeur-$nouvelle_hauteur/2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
			else
				imagecopyresampled ($image, $icone, $epaisseur_bordure, $this->hauteur-3.5*$this->largeur-$nouvelle_hauteur/2, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);
			
			$icone=imagecreatefrompng('fr/Texte_SPG.png');
			imagealphablending($icone, false);
		    # set the transparent color
		    $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
		    imagefill($icone, 0, 0, $transparent);
		    # set the transparency settings for the picture after adding the transparency
		    imagesavealpha($icone,true);
		    imagealphablending($icone, true);
			list($width, $height) = getimagesize('fr/Texte_SPG.png');
			$nouvelle_largeur=$this->largeur/1.5;
			$nouvelle_hauteur=$nouvelle_largeur*($height/$width);
			imagecopyresampled ($image, $icone, $this->largeur/6, $this->largeur/2, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $width, $height);
			$texte_numero=new Texte($this->numero,$this->largeur*3/10,$this->hauteur-$this->largeur*4/5,
									7*Edge::$grossissement,90,$blanc,'ArialBlack.ttf');
			if ($this->numero < 100) {
				$texte_numero->pos_x=$this->largeur*.75/10;
				$texte_numero->angle=0;
			}
			$this->textes[]=$texte_numero;
			imagerectangle($image, 0, 0, $this->largeur, $this->hauteur, $noir);
		}
		return $image;
	}
}