<?php
class SPGP extends Edge {
	var $pays='fr';
	var $magazine='SPGP';
	var $intervalles_validite=array(65,70,73,77,81,85,88,90,93,97,100,102,105,108,111,113,117,120,123,125,129,132);
        static $largeur_defaut=20;
        static $hauteur_defaut=255;
        
        function SPGP($numero) {
            $this->numero=$numero;
            $this->largeur=20*Edge::$grossissement;
            $this->hauteur=255*Edge::$grossissement;
            
            $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
            if ($this->image===false)
                xdebug_break ();
	}
	function dessiner() {
        $jaune=imagecolorallocate($this->image,255,214,0);
        $blanc=imagecolorallocate($this->image,255,255,255);
        $noir = imagecolorallocate($this->image, 0, 0, 0);
        imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->hauteur, $noir);
        imagefilledrectangle($this->image, .5*Edge::$grossissement, .5*Edge::$grossissement, $this->largeur-.5*Edge::$grossissement, $this->hauteur-.5*Edge::$grossissement, $this->numero==93?$jaune:$blanc);
        $titre=new Texte('SUPER PICSOU G&#201;ANT',$this->largeur*1.5/5,$this->largeur/2,
                         6.2*Edge::$grossissement,-90,$noir,'ArialBlack.ttf');
        $this->textes[]=$titre;

        list($icone,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/SPGP.signature_disney.png');
        imagealphablending($icone, false);
        # set the transparent color
        $transparent = imagecolorallocatealpha($icone, 0, 0, 0, 127);
        imagefill($icone, 0, 0, $transparent);
        # set the transparency settings for the picture after adding the transparency
        imagesavealpha($icone,true);
        imagealphablending($icone, true);

        $nouvelle_largeur=$this->largeur/1.5;
        $nouvelle_hauteur=$nouvelle_largeur*($height/$width);
        imagecopyresampled ($this->image, $icone, $this->largeur/6, $this->hauteur-3*$this->largeur, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $width, $height);

		return $this->image;
	}

}