<?php
class it_TES extends Edge {
    var $pays='it';
    var $magazine='TES';
    var $intervalles_validite=array(4);

    static $largeur_defaut=11.5;
    static $hauteur_defaut=239;

    function it_TES ($numero) {
        $this->numero=$numero;
        $this->hauteur=239*Edge::$grossissement;
        $this->largeur=11.5*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');

        $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
        list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
        $fond=imagecolorallocate($image2, $rouge,$vert,$bleu);
        list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(0,0,0),'Texte');
        imagefill($image2,0,0,$fond);

        $post=new MyFonts('efscangraphic/bodoni-no-1-sb/med-con',
                      rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                      rgb2hex($rouge,$vert,$bleu),
                      450,
                      'TESORI   .');
        $chemin_image=$post->chemin_image;
        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
        $nouvelle_largeur=$this->largeur*($width/$height);
        imagecopyresampled ($image2, $texte, $this->largeur*0.5, $this->largeur*0.1, 0, 0, $nouvelle_largeur*2.3, $this->largeur*0.5*1.7, $width, $height/2);

        $post->text='QUATTRO   .';
        $post->width=500;
        $post->build();
        $chemin_image=$post->chemin_image;
        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
        $nouvelle_largeur=$this->largeur*($width/$height);
        imagecopyresampled ($image2, $texte, $this->largeur*4.3, $this->largeur*0.45, 0, 0, $nouvelle_largeur*1.15, $this->largeur*0.25*1.7, $width, $height/2);

        list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(0,0,0),'Texte central');
        $post->color=rgb2hex($rouge_texte,$vert_texte,$bleu_texte);
        $post->text='Zio Paperone nel Klondike   .';
        $post->width=1250;
        $post->build();
        $chemin_image=$post->chemin_image;
        list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
        $nouvelle_largeur=$this->largeur*($width/$height);
        imagecopyresampled ($image2, $texte, $this->largeur*7.3, $this->largeur*0.1, 0, 0, $nouvelle_largeur*2, $this->largeur*0.5*1.7, $width, $height/2);

        $this->image=imagerotate($image2, 90, $blanc);
        $this->placer_image('signature_disney.png', 'haut',array(0,$this->largeur*0.3));
        return $this->image;
    }
}
?>
