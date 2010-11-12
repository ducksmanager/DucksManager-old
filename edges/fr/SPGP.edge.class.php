<?php
class fr_SPGP extends Edge {
	var $pays='fr';
	var $magazine='SPGP';
	var $intervalles_validite=array(65,70,73,77,81,85,88,90,93,97,100,102,105,108,111,113,117,120,123,125,129,132);
        static $largeur_defaut=20;
        static $hauteur_defaut=255;
        
        function fr_SPGP($numero) {
            $this->numero=$numero;
               
            if ($this->numero <= 73)
                $this->largeur=16*Edge::$grossissement;
            else
                $this->largeur=20*Edge::$grossissement;
            $this->hauteur=255*Edge::$grossissement;
            
            $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
            if ($this->image===false)
                xdebug_break ();
	}
	function dessiner() {
            switch($this->numero) {
                case 65:
                    include_once($this->getChemin().'/../../MyFonts.Post.class.php');
                    $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
                    $blanc=imagecolorallocate($image2,255,255,255);
                    list($rouge, $vert, $bleu)=array(0,0,91);
                    $fond=imagecolorallocate($image2,$rouge, $vert, $bleu);
                    list($rouge_texte, $vert_texte, $bleu_texte)=array(255,199,28);
                    imagefill($image2,0,0,$fond);
                    $post=new MyFonts('larabie/coolvetica/bold',
                                  rgb2hex($rouge_texte, $vert_texte, $bleu_texte),
                                  rgb2hex($rouge, $vert, $bleu),
                                  1400,
                                  'SUPER PICSOU G&Eacute;ANT   .');
                    $chemin_image=$post->chemin_image;
                    list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                    $nouvelle_largeur=$this->largeur*($width/$height);
                    imagecopyresampled ($image2, $texte, $this->largeur*0.5, $this->largeur*0.35, 0, 0, $nouvelle_largeur*0.6*1.05, $this->largeur*0.51*0.6, $width, $height*0.51);

                    $this->image=imagerotate($image2, -90, $blanc);
                break;

            default:
                switch($this->numero) {
                    case 70 : case 93:
                        $fond=imagecolorallocate($this->image,255,214,0);
                    break;
                    case 73:
                        $fond=imagecolorallocate($this->image,248,225,199);
                    break;
                    default:
                        $fond=imagecolorallocate($this->image,255,255,255);
                }
                $rouge=imagecolorallocate($this->image,255,0,0);
                $noir = imagecolorallocate($this->image, 0, 0, 0);
                imagefill($this->image,0,0,$fond);
                $titre=new Texte('SUPER PICSOU G&#201;ANT',$this->largeur*1.5/5,$this->numero <= 73 ? $this->largeur*0.5 : $this->largeur * 0.8,
                                 5.7*Edge::$grossissement,-90,$this->numero == 70 ? $rouge : $noir,'ArialBlack.ttf');
                $titre->dessiner($this->image);

                if ($this->numero == 70) {
                    $this->placer_image('SPGP.Numéro.2.png','haut',array(0,$this->hauteur*0.57));
                    list($icone,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/SPGP.signature_disney_rouge.png');
                }
                else
                    list($icone,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/SPGP.signature_disney.png');
                
                $nouvelle_largeur=$this->largeur/1.5;
                $nouvelle_hauteur=$nouvelle_largeur*($height/$width);
                imagecopyresampled ($this->image, $icone, $this->largeur/6, $this->hauteur-3*$this->largeur, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $width, $height);
            break;
        }
	return $this->image;
     }
}