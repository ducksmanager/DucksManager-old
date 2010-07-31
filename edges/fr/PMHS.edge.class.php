<?php
class PMHS extends Edge {
    var $pays='fr';
    var $magazine='PMHS';
    var $intervalles_validite=array();

    static $largeur_defaut=15;
    static $hauteur_defaut=278;

    function PMHS ($numero) {
        $this->numero=$numero;

        $this->hauteur=278*Edge::$grossissement;
        $this->largeur=15*Edge::$grossissement;

        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {

        include_once($this->getChemin().'/../classes/MyFonts.Post.class.php');
        switch($this->numero[0]) {
            case 'C':
                $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
                $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
                $blanc=imagecolorallocate($image2, 255, 255, 255);
                list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
                $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);
                imagefill($image2, 0, 0, $fond);
                list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
                $couleur_texte=imagecolorallocate($this->image, $rouge_texte, $vert_texte, $bleu_texte);

                $post=new MyFonts('storm/zeppelin/53-bold-italic',
                              rgb2hex($rouge_texte,$vert_texte,$bleu_texte),
                              rgb2hex($rouge,$vert,$bleu),
                              1000,
                              'Les trésors de Picsou',
                              52);
                $chemin_image=$post->chemin_image;
                list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                $nouvelle_largeur=$this->largeur*($width/$height)*0.5;
                imagecopyresampled ($image2, $texte, $this->hauteur*0.2, $this->largeur*0.1, 0, 0, $nouvelle_largeur, $this->largeur*0.8, $width, $height);
                
                $this->image=imagerotate($image2, 90, $blanc);
            break;
        }
        return $this->image;
    }
}
?>
