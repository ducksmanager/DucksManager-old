<?php
class WDT extends Edge {
    var $pays='us';
    var $magazine='WDT';
    var $intervalles_validite=array('2');
    var $en_cours=array();
    static $largeur_defaut=9;
    static $hauteur_defaut=258;


    function WDT($numero) {
        $this->numero=$numero;
        $this->largeur=9*Edge::$grossissement;
        $this->hauteur=258*Edge::$grossissement;
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');

        $noir=imagecolorallocate($this->image, 0, 0, 0);
        $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
        $blanc=imagecolorallocate($image2, 255, 255, 255);
        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
        $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);

        $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
        $couleur_texte=imagecolorallocate($image2, 0, 0, 0);
        imagefill($image2, 0, 0, $fond);
        
        $post=new MyFonts('agfa/itc-kabel/itc-book',
                          rgb2hex(0,0,0),
                          rgb2hex($rouge, $vert, $bleu),
                          4600,
                          'WALT DISNEY TREASURES     UNCLE SCROOGE: A LITTLE SOMETHING SPECIAL     .',
                          84);
        $chemin_image=$post->chemin_image;
        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
        $nouvelle_largeur=$this->largeur*($width/$height)*0.8;
        imagecopyresampled ($image2, $texte, $this->largeur, $this->largeur*0.3, 0, 0, $nouvelle_largeur*1.7, $this->largeur*0.5, $width, $height*0.5);

        $this->image=imagerotate($image2, 90, $blanc);
        
        $texte=new Texte('GEMSTONE',$this->largeur*7/10,$this->largeur*3.7,
                            3*Edge::$grossissement,90,$couleur_texte,'Gill Sans Bold.ttf');
        $texte->dessiner($this->image);
        
        return $this->image;
    }
}
?>
