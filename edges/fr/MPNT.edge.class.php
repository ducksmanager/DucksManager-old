<?php
class MPNT extends Edge {
    var $pays='fr';
    var $magazine='MPNT';
    var $intervalles_validite=array(723,735,756,786,807,824,838/*,856,886*/); // 886 = dernier MPNT

    static $largeur_defaut=15;
    static $hauteur_defaut=186;

    function MPNT ($numero) {
        $this->numero=$numero;

        $this->largeur=15*Edge::$grossissement;
        $this->hauteur=186*Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');
        $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
        $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
        $blanc=imagecolorallocate($image2, 255, 255, 255);
        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
        $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);
        list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
        imagefill($image2, 0, 0, $fond);

        $post=new MyFonts('fontfont/ff-schulbuch/nord-fett',
                      rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                      rgb2hex($rouge,$vert,$bleu),
                      3700,
                      'LE JOU   NAL DE MICKEY    .',
                      48);
        $chemin_image=$post->chemin_image;
        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
        $nouvelle_largeur=$this->largeur*($width/$height);
        //$this->placer_image($chemin_image,'haut',array(1.5*$this->largeur,0));
        $dst_x=$this->numero <= 756 ? $this->largeur*1.7 : $this->largeur*2;
        $dst_y=$this->numero == 807 || $this->numero == 824 ? $this->largeur*0.2 : $this->largeur * 0.35;
        imagecopyresampled ($image2, $texte, $dst_x, $dst_y, 0, 0, $nouvelle_largeur*0.37, $this->largeur*0.34, $width/2, $height/2);

        $post=new MyFonts('fontbureau/benton-sans/bold',
                      rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                      rgb2hex($rouge,$vert,$bleu),
                      230,
                      'R',
                      48);
        $chemin_image_r=$post->chemin_image;
        list($texte_r,$width,$height)=imagecreatefromgif_getimagesize($chemin_image_r);
        $nouvelle_largeur=$this->largeur*($width/$height);
        imagecopyresampled ($image2, $texte_r, $dst_x+$this->largeur*1.38, $dst_y, 0, 0, $nouvelle_largeur*0.34, $this->largeur*0.3, $width*0.5, $height*0.5);

        $dst_x=$this->numero <= 756 ? $this->largeur*8.2 : $this->largeur*7.8;
        switch($this->numero) {
            case 735:
                $titre2='DONALD-PA   ADE';
                $pos_r=$dst_x+$this->largeur*1.94;
            break;
            case 756:
                $titre2='PICSOU-PA   ADE';
                $pos_r=$dst_x+$this->largeur*1.825;
            break;
            default:
                $titre2='MICKEY-PA   ADE';
                $pos_r=$dst_x+$this->largeur*1.828;
        }
        $post=new MyFonts('fontfont/ff-schulbuch/nord-fett',
                      rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                      rgb2hex($rouge,$vert,$bleu),
                      2700,
                      $titre2.'   .',
                      48);
        $chemin_image=$post->chemin_image;
        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
        $nouvelle_largeur=$this->largeur*($width/$height);
        imagecopyresampled ($image2, $texte, $dst_x, $dst_y + $this->largeur*0.05, 0, 0, $nouvelle_largeur*0.32, $this->largeur*0.3, $width/2, $height/2);

        list($texte_r,$width,$height)=imagecreatefromgif_getimagesize($chemin_image_r);
        $nouvelle_largeur=$this->largeur*($width/$height);
        imagecopyresampled ($image2, $texte_r, $pos_r, $dst_y + $this->largeur*0.055, 0, 0, $nouvelle_largeur*0.3, $this->largeur*0.26, $width*0.5, $height*0.5);

        if (in_array($this->numero,array(856)))
            $this->image=imagerotate($image2, -90, $blanc);
        else
            $this->image=imagerotate($image2, 90, $blanc);
        $this->placer_image('MPNT.'.$this->numero.'.Etoile.png','bas',array(0,$this->hauteur*0.57));
        
        return $this->image;
    }
}
?>
