<?php
class ALPMB extends Edge {
    var $pays='fr';
    var $magazine='ALPMB';
    var $intervalles_validite=array(25,27,49,57);

    var $en_cours=array();
    static $largeur_defaut=18;
    static $hauteur_defaut=282;

    function ALPMB($numero) {
        $this->numero=$numero;
        if ($this->numero<=24) {

        }
        elseif ($this->numero<=27) {
            $this->largeur=18*Edge::$grossissement;
            $this->hauteur=282*Edge::$grossissement;
        }
        elseif ($this->numero <= 57) {
            $this->largeur=18*Edge::$grossissement;
            $this->hauteur=280*Edge::$grossissement;
        }
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        if ($this->numero <= 24) {

        }
        elseif ($this->numero<=27) {
            include_once($this->getChemin().'/../classes/MyFonts.Post.class.php');

            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $blanc=imagecolorallocate($image2, 255, 255, 255);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
            $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);
            imagefill($image2, 0, 0, $fond);
            $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
            $post=new MyFonts('urw/nimbus-sans/l-black-condensed-italic',
                              rgb2hex($rouge_texte, $vert_texte, $bleu_texte),
                              rgb2hex($rouge, $vert, $bleu),
                              800,
                              'ALBUM PICSOU N°'.$this->numero,
                              42);
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height)*0.8;
            imagecopyresampled ($image2, $texte, $this->hauteur*0.2, $this->largeur*0.1, 0, 0, $nouvelle_largeur, $this->largeur*0.8, $width, $height);

            $this->image=imagerotate($image2, 90, $blanc);
            if ($this->numero==25) {
                $this->placer_image ('/ALPMB.'.$this->numero.'.detail.png');
            }
        }
        elseif ($this->numero <= 57) {
            list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
            $fond=imagecolorallocate($this->image, $rouge, $vert, $bleu);
            imagefill($this->image, 0, 0, $fond);
            $this->placer_image('Logo ALPBM.png');
            $this->placer_image('ALPMB.icone.'.$this->numero.'.png', 'bas');
        }
        return $this->image;
    }
}
?>
