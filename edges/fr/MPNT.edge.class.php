<?php
class fr_MPNT extends Edge {
    var $pays='fr';
    var $magazine='MPNT';
    var $intervalles_validite=array(723,735,756,772,786,807,824,838,856,873,886); // 886 = dernier MPNT

    static $largeur_defaut=15;
    static $hauteur_defaut=186;

    function fr_MPNT ($numero) {
        $this->numero=$numero;

        switch($this->numero) {
            case 824: case 856: case 873: case 886 :
                $this->largeur=13*Edge::$grossissement;
            break;
            case 838:
                $this->largeur=14*Edge::$grossissement;
            default:
                $this->largeur=15*Edge::$grossissement;
            break;
        }
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
        switch($this->numero) {
            case 723: case 735 : case 756 :
                $dst_x=$this->largeur*1.7;
            break;
            default:
                $dst_x=$this->largeur*2;
            break;
        }

        switch($this->numero) {
            case 807: 
                $dst_y=$this->largeur*0.2;
            break;
            case 824 : case 856: case 873:
                $dst_y=$this->largeur*0.42;
            break;
            default:
                $dst_y=$this->largeur * 0.35;
            break;
        }
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

        switch($this->numero) {
            case 723: case 735 : case 756 :
                $dst_x=$this->largeur*8.2;
            break;
            case 824: case 856 : case 873:
                $dst_x=$this->largeur*7.2;
            break;
            default:
                $dst_x=$this->largeur*7.8;
             break;
        }
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

        if (in_array($this->numero,array(824,856,873)))
            $this->image=imagerotate($image2, -90, $blanc);
        else
            $this->image=imagerotate($image2, 90, $blanc);
        switch($this->numero) {
            case 824: case 856 : case 873:
                $position_etoile=$this->hauteur*0.51;
            break;
            default:
                $position_etoile=$this->hauteur*0.57;

        }
        if ($this->numero!=847)
            $this->placer_image('MPNT.'.$this->numero.'.Etoile.png','bas',array(0,$position_etoile));
        
        return $this->image;
    }
}
?>
