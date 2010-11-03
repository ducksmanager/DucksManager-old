<?php
class ALPM extends Edge {
    var $pays='fr';
    var $magazine='ALPM';
    var $intervalles_validite=array('B1','B16','B19','B25','B27','B40','B42','B49','B54','B57');

    var $en_cours=array();
    static $largeur_defaut=18;
    static $hauteur_defaut=282;

    var $serie;
    var $numero_serie;

    function ALPM($numero) {
        $this->numero=$numero;
        $this->serie=$numero[0];
        $this->numero_serie=substr($this->numero, strpos($this->numero, ' ')+1, strlen($this->numero));
        switch($this->serie) {
            case 'A':
                
            break;
        
            case 'B':
                if ($this->numero_serie<=27) {
                    $this->largeur=18*Edge::$grossissement;
                    $this->hauteur=260*Edge::$grossissement;
                }
                elseif ($this->numero_serie <= 57) {
                    $this->largeur=18*Edge::$grossissement;
                    $this->hauteur=273*Edge::$grossissement;
                }
            break;
        }
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        switch($this->serie) {
            case 'A':

            break;

            case 'B':
               if ($this->numero_serie<=27) {
                    include_once($this->getChemin().'/../../MyFonts.Post.class.php');

                    $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
                    $blanc=imagecolorallocate($image2, 255, 255, 255);
                    list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
                    switch($this->numero_serie) {
                        case 16:
                            $couleur1=$this->getColorsFromDB(array(0,0,0),'Degrade 2');
                            $couleur2=$this->getColorsFromDB(array(255,255,255),'Degrade 1');

                            $largeur_degrade=$this->hauteur;

                            include_once($this->getChemin().'/../../util.php');
                            $couleurs_inter=getMidColors($couleur1, $couleur2, $largeur_degrade);
                            foreach($couleurs_inter as $i=>$couleur) {
                                list($rouge_inter,$vert_inter,$bleu_inter)=$couleur;
                                $couleur_allouee=imagecolorallocate($image2, $rouge_inter,$vert_inter,$bleu_inter);
                                imageline($image2, $i, 0, $i, $this->largeur, $couleur_allouee);
                            }
                        break;
                        default:
                            $fond=imagecolorallocate($image2, $rouge, $vert, $bleu);
                            imagefill($image2, 0, 0, $fond);

                        break;    
                    }
                    $this->image=imagecreatetruecolor($this->hauteur, $this->hauteur);
                    list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB(array(255,255,255),'Texte');
                    $post=new MyFonts('urw/nimbus-sans/l-black-condensed-italic',
                                      rgb2hex($rouge_texte, $vert_texte, $bleu_texte),
                                      rgb2hex($rouge, $vert, $bleu),
                                      1200,
                                      'ALBUM PICSOU N°'.$this->numero_serie.'      .',
                                      84);
                    $chemin_image=$post->chemin_image;
                    list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
                    $nouvelle_largeur=$this->largeur*($width/$height)*0.8;
                    imagecopyresampled ($image2, $texte, $this->hauteur*0.2, $this->largeur*0.1, 0, 0, $nouvelle_largeur*2, $this->largeur*0.8, $width, $height*0.5);

                    $this->image=imagerotate($image2, 90, $blanc);
                    if ($this->numero_serie==25) {
                        $this->placer_image ('ALPMB.'.$this->numero_serie.'.detail.png');
                    }
                }
                elseif ($this->numero_serie <= 57) {
                    list($rouge,$vert,$bleu)=$this->getColorsFromDB(array(255,255,255));
                    $fond=imagecolorallocate($this->image, $rouge, $vert, $bleu);
                    imagefill($this->image, 0, 0, $fond);
                    $this->placer_image('Logo ALPBM.png');
                    list($rouge_logo,$vert_logo,$bleu_logo)=$this->getColorsFromDB(array(255,255,255),'Logo');
                    $fond_logo=imagecolorallocate($this->image, $rouge_logo, $vert_logo, $bleu_logo);
                    imagefill($this->image, $this->largeur*0.66, $this->largeur, $fond_logo);
                    $this->placer_image('ALPMB.icone.'.$this->numero_serie.'.png', 'bas');
                }
            break;
        }
        
        return $this->image;
    }
}
?>
