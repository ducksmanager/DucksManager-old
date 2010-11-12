<?php
class AJM extends Edge {
    var $pays='fr';
    var $magazine='AJM';
    var $intervalles_validite=array(65,68,78,79,80,82,83,84,85,86,87);
    static $largeur_defaut=10;
    static $hauteur_defaut=254;

    function AJM ($numero) {
        $this->numero=$numero;
        if ($this->numero<=64) {
            
        }
        elseif($this->numero<=68) {
            $this->largeur=10.5*Edge::$grossissement;
            $this->hauteur=238*Edge::$grossissement;
        }
        elseif($this->numero<=84){
            if ($this->numero==83) {
                $this->largeur=8*Edge::$grossissement;
                $this->hauteur=238*Edge::$grossissement;
            }
            else {
                $this->largeur=9.5*Edge::$grossissement;
                $this->hauteur=238*Edge::$grossissement;
            }
        }
        else {
            $this->hauteur=271*Edge::$grossissement;
            switch($this->numero) {
                case 85:
                    $this->largeur=18*Edge::$grossissement;
                break;
                case 86:
                    $this->largeur=15*Edge::$grossissement;
                break;
                case 87:
                    $this->largeur=12*Edge::$grossissement;
                break;
            }
        }
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    
    function dessiner() {
        include_once($this->getChemin().'/../../MyFonts.Post.class.php');

        if ($this->numero<=64) {
            
        }
        elseif($this->numero<=68) {            
            $image2=imagecreatetruecolor($this->hauteur, $this->largeur);
            $fond=imagecolorallocate($image2,255,255,255);
            
            imagefill($image2,0,0,$fond);
            
            $post=new MyFonts('redrooster/block-gothic-rr/demi-extra-condensed',
                              rgb2hex(0,0,0),
                              rgb2hex(255,255,255),
                              700,
                              'D U   J O U R N A L    D E      .');
            $chemin_image=$post->chemin_image;
            list($texte,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($image2, $texte, $this->largeur*10, $this->largeur*0.1, 0, 0, $nouvelle_largeur*2, $this->largeur, $width, $height/2);
            
            $this->image=imagerotate($image2, 90, $fond);
            
            $image_almanach=imagecreatetruecolor($this->hauteur*0.25, $this->largeur);
            $ecriture_almanach=imagecolorallocate($image_almanach,216,1,77);
            $fond_almanach=imagecolorallocate($image_almanach, 255, 255, 255);
            imagefill($image_almanach,0,0,$fond_almanach);
            $texte_almanach=new Texte('ALMANACH',0,100,
                                7*Edge::$grossissement,0,$ecriture_almanach,'Gill Sans Bold.ttf');
            $texte_almanach->dessiner($image_almanach);
            imagepng($image_almanach, 'tmp.png');
            $image_almanach=imagerotate($image_almanach, 90, $fond_almanach);
            
            list($width,$height)=array(imagesx($image_almanach),imagesy($image_almanach));
            imagecopyresampled ($this->image, $image_almanach, -$this->largeur*0.2, $this->hauteur-$this->largeur*9.5, 0, 0, $width*1.05, $height*0.85, $width, $height);

            
            $image_mickey=imagecreatetruecolor($this->hauteur*0.25, $this->largeur);
            $ecriture_mickey=imagecolorallocate($image_mickey,216,1,77);
            $fond_mickey=imagecolorallocate($image_mickey, 255, 255, 255);
            imagefill($image_mickey,0,0,$fond_mickey);
            $texte_mickey=new Texte('MICKEY',0,100,
                                7*Edge::$grossissement,0,$ecriture_mickey,'Gill Sans Bold.ttf');
            $texte_mickey->dessiner($image_mickey);
            $image_mickey=imagerotate($image_mickey, 90, $fond_mickey);
            list($width,$height)=array(imagesx($image_mickey),imagesy($image_mickey));
            imagecopyresampled ($this->image, $image_mickey, -$this->largeur*0.2, $this->largeur*2, 0, 0, $width*1.05, $height*0.85, $width, $height);

            $post->text='19'.$this->numero.'   .';
            $post->width=200;
            $post->build();
            $chemin_image=$post->chemin_image;
            list($date,$width,$height)=imagecreatefromgif_getimagesize($chemin_image);
            $nouvelle_largeur=$this->largeur*($width/$height);
            imagecopyresampled ($this->image, $date, $this->largeur*0.1, $this->largeur, 0, 0, $nouvelle_largeur*1.255, $this->largeur*0.625, $width, $height/2);
            imagecopyresampled ($this->image, $date, $this->largeur*0.1, $this->hauteur-$this->largeur*2, 0, 0, $nouvelle_largeur*1.255, $this->largeur*0.625, $width, $height/2);
            
            
        }
        else if ($this->numero<=84) {
            list($r,$g,$b)=$this->getColorsFromDB(array(255,255,255));
            $fond=imagecolorallocate($this->image, $r,$g,$b);
            imagefill($this->image, 0, 0, $fond);
            $noir=imagecolorallocate($this->image, 0, 0, 0);
            switch($this->numero) {
                case '78':
                    $texte_numero=new Texte('DE LA LECTURE     CONSEILS PRATIQUES     BRICOLAGES     JEUX...',$this->largeur*0.7,$this->hauteur-$this->largeur,
                                            3.5*Edge::$grossissement,90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('ALMANACH MICKEY 1978',$this->largeur*0.7,$this->hauteur*0.26,
                                            3.5*Edge::$grossissement,90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('*',$this->largeur*0.3,0.405*$this->hauteur,
                                            5.5*Edge::$grossissement,0,$noir,'Gill Sans Bold.ttf');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('*',$this->largeur*0.3,0.56*$this->hauteur,
                                            5.5*Edge::$grossissement,0,$noir,'Gill Sans Bold.ttf');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('*',$this->largeur*0.3,0.805*$this->hauteur,
                                            5.5*Edge::$grossissement,0,$noir,'Gill Sans Bold.ttf');
                    $texte_numero->dessiner($this->image);
                break;
                case '79':
                    $texte_numero=new Texte('JEUX    BRICOLAGES    BANDES DESSINÉES',$this->largeur*0.3,$this->largeur*0.75,
                                            3.5*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('ALMANACH MICKEY 1979',$this->largeur*0.3,$this->hauteur*0.74,
                                            3.5*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                    $this->placer_image('AJM.80.Etoile.png','haut',array(0,2.05*$this->largeur));
                    $this->placer_image('AJM.80.Etoile.png','haut',array(0,5.85*$this->largeur));
                break;
                case '80':
                    $texte_numero=new Texte('JEUX  BRICOLAGES  BANDES DESSINÉES',$this->largeur*0.3,$this->largeur*0.65,
                                            3.5*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('ALMANACH MICKEY 1980',$this->largeur*0.3,$this->hauteur*0.75,
                                            3.5*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                    $this->placer_image('AJM.80.Etoile.png','haut',array(0,1.95*$this->largeur));
                    $this->placer_image('AJM.80.Etoile.png','haut',array(0,5.35*$this->largeur));
                break;
                case '82':
                    $texte_numero=new Texte('BANDES DESSINÉES     JEUX     HUMOUR',$this->largeur*0.3,$this->largeur*0.25,
                                            3.5*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('*',$this->largeur*0.3,5.9*$this->largeur,
                                            5.5*Edge::$grossissement,0,$noir,'Gill Sans Bold.ttf');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('*',$this->largeur*0.3,7.85*$this->largeur,
                                            5.5*Edge::$grossissement,0,$noir,'Gill Sans Bold.ttf');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('ALMANACH MICKEY 1982',$this->largeur*0.3,$this->hauteur*0.74,
                                            3.5*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                break;
                case '83':
                    $texte_numero=new Texte('BANDES DESSINÉES.JEUX.HUMOUR',$this->largeur*0.2,$this->largeur*1.5,
                                            4*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('ALMANACH MICKEY 1983',$this->largeur*0.2,$this->hauteur*0.7,
                                            4*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                break;
                case '84':
                    $texte_numero=new Texte('BANDES DESSINÉES. JEUX. HUMOUR',$this->largeur*0.3,$this->largeur*1.1,
                                            4.5*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                    $texte_numero=new Texte('ALMANACH MICKEY 1984',$this->largeur*0.3,$this->hauteur*0.66,
                                            4.5*Edge::$grossissement,-90,$noir,'ARIAL.TTF');
                    $texte_numero->dessiner($this->image);
                break;
            }
        }
        else {
            $fond=imagecolorallocate($this->image,225,225,225);
            imagefill($this->image,0,0,$fond);
            $couleur_texte=imagecolorallocate($this->image, 215,0,0);
            $texte=new Texte('ALMANACH DU JOURNAL DE MICKEY',$this->largeur*3/10,$this->hauteur*0.05,
                            6*Edge::$grossissement,-90,$couleur_texte,'Swiss 721 Black BT.ttf');
            $texte->dessiner($this->image);
            $this->placer_image('AJM.85-87.Tete.png','haut',array(0,$this->hauteur*0.75+$this->largeur*0.25));
            foreach(str_split('19'.$this->numero) as $i=>$chiffre) {
                $texte=new Texte($chiffre,$this->largeur*3/10,$this->hauteur*(0.88+0.025*$i),
                            6*Edge::$grossissement,0,$couleur_texte,'Swiss 721 Black BT.ttf');
                $texte->dessiner($this->image);
            }
        }
        return $this->image;
    }

}