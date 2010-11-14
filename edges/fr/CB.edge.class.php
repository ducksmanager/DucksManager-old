<?php
class fr_CB extends Edge {
    var $pays='fr';
    var $magazine='CB';
    var $intervalles_validite=array('P111','P113');

    static $largeur_defaut=20;
    static $hauteur_defaut=255;

    function fr_CB ($numero) {
        $this->numero=$numero;
        $this->hauteur=255*Edge::$grossissement;
        $this->largeur=20*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        $arr=str_split($this->numero);
        $premiere_lettre=$arr[0];
        switch($premiere_lettre) {
            case 'M':
                
            break;
            case 'P':
                include_once($this->getChemin().'/../../MyFonts.Post.class.php');
                $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
                $blanc=imagecolorallocate($image2,255,255,255);
                list($r,$v,$b)=$this->getColorsFromDB();
                $post=new MyFonts('samuelstype/andrew-samuels/light-italic',
                                  rgb2hex($r_texte,$v_texte,$b_texte),
                                  rgb2hex($r,$v,$b),
                                  1000,
                                  'COLLECTION    .');
                $chemin_image=$post->chemin_image;
                list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                $nouvelle_largeur=$this->largeur*($width/$height);
                imagecopyresampled ($image2, $texte, $this->hauteur-$this->largeur*3, $this->largeur*0.225, 0, 0, $nouvelle_largeur*0.6*1.05, $this->largeur*0.51*0.6, $width, $height*0.51);

                $post->text='BIBLIOTHEQUE';
                $post->build();
                $chemin_image=$post->chemin_image;
                list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                $nouvelle_largeur=$this->largeur*($width/$height);
                imagecopyresampled ($image2, $texte, $this->hauteur-$this->largeur*3.1, $this->largeur*0.525, 0, 0, $nouvelle_largeur*0.6*1.05, $this->largeur*0.51*0.6, $width, $height*0.51);

                $this->image=imagerotate($image2, -90, $blanc);
                
                imagefill($this->image,0,0,imagecolorallocate($this->image,$r,$v,$b));
                list($r_texte,$v_texte,$b_texte)=$this->getColorsFromDB(array(0,0,0),'Texte');
                $texte=imagecolorallocate($this->image,$r_texte,$v_texte,$b_texte);
                $titre=new Texte('SUPER PICSOU',$this->largeur*0.2,$this->largeur * 0.8,
                                 13*Edge::$grossissement,-90,$texte,'Folio Extra Bold BT.ttf');
                $titre->dessiner($this->image);
                
                imagefilledrectangle($this->image, $this->largeur*0.17, $this->hauteur-3.2*$this->largeur, $this->largeur*0.19, $this->hauteur-0.8*$this->largeur, $noir);
                imagefilledrectangle($this->image, $this->largeur*0.19, $this->hauteur-0.8*$this->largeur, $this->largeur*0.81, $this->hauteur-0.82*$this->largeur, $noir);
                imagefilledrectangle($this->image, $this->largeur*0.81, $this->hauteur-0.8*$this->largeur, $this->largeur*0.83, $this->hauteur-3.2*$this->largeur, $noir);
                imagefilledrectangle($this->image, $this->largeur*0.83, $this->hauteur-3.2*$this->largeur, $this->largeur*0.17, $this->hauteur-3.18*$this->largeur, $noir);
            break;
        }
        return $this->image;
    }
}
?>
