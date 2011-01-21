<?php
class fr_CB extends Edge {
    var $pays='fr';
    var $magazine='CB';
    var $intervalles_validite=array('P81','P111','P113','P132','PN14','PN16','PN18');

    static $largeur_defaut=20;
    static $hauteur_defaut=255;

    function fr_CB ($numero) {
        $this->numero=$numero;
        $this->hauteur=255*Edge::$grossissement;
        $this->largeur=20*Edge::$grossissement;

        $arr=str_split($this->numero);
        if ($arr[0]=='P' && isset($arr[1]) && $arr[1]=='N') {
            $this->hauteur=253*Edge::$grossissement;
            $this->largeur=38*Edge::$grossissement;
        }
        
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
                $deuxieme_lettre=$arr[1];
                switch($deuxieme_lettre) {
                    case 'N':
                        include_once($this->getChemin().'/../../MyFonts.Post.class.php');

                        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(0,0,0));
                        list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
                        
                        $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
                        $fond=imagecolorallocate($image2, $rouge,$vert,$bleu);
                        imagefill($image2, 0, 0, $fond);
                        $post=new MyFonts('efscangraphic/compacta-sh/bold',
                                          rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                                          rgb2hex($rouge,$vert,$bleu),
                                          1000,
                                          'SUPER PICSOU GEANT    .');
                        $chemin_image=$post->chemin_image;
                        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                        $nouvelle_largeur=$this->largeur*($width/$height);
                        imagecopyresampled ($image2, $texte, $this->largeur*0.65, $this->largeur*0.25, 0, 0, $nouvelle_largeur*1.25*1.05, $this->largeur*0.51*1.05, $width, $height*0.51);
                        $this->image=imagerotate($image2, -90, $blanc);
                        
                        $blanc=imagecolorallocate($this->image,255,255,255);
                        $noir=imagecolorallocate($this->image,0,0,0);
                        $texte=imagecolorallocate($this->image, $rouge_texte,$vert_texte,$bleu_texte);
                        imagefilledrectangle($this->image, $this->largeur*0.77, $this->hauteur*0.663, $this->largeur*0.772, $this->hauteur*0.69, $texte);
                        imagearc($this->image, $this->largeur/2, $this->hauteur-$this->largeur*0.6, $this->largeur*0.6, $this->largeur*0.6, 0, 360, $noir);
                        imagefill($this->image,$this->largeur/2,$this->hauteur*0.95,$blanc);
                        switch($this->numero) {
                            case 'PN14':
                                $chiffre=3;
                            break;
                        }
                        $post=new MyFonts('paratype/futura-book/bold',
                                          rgb2hex(0,0,0),
                                          rgb2hex(255,255,255),
                                          85,
                                          $chiffre.'    .');
                        $chemin_image=$post->chemin_image;
                        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                        $nouvelle_largeur=$this->largeur*($width/$height);
                        imagecopyresampled ($this->image, $texte, $this->largeur*0.37, $this->hauteur-$this->largeur*0.75, 0, 0, $nouvelle_largeur*0.35, $this->largeur*0.35, $width, $height);

                    break;
                    default ://chiffre
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
                
            break;
        }
        return $this->image;
    }
}
?>
