<?php
@include_once('Post.class.php');
@include_once('Database.class.php');
class MyFonts extends Post {
    var $p;
    var $chemin_image;
    static $regex_source_image='#src="([^"]+)"#is';
    var $font;
    var $color;
    var $color_bg;
    var $width;
    var $text;
    var $precision;

    var $data;

    function MyFonts($font,$color,$color_bg, $width, $text,$precision=18) {
        
        $this->font=$font;
        $this->color=$color;
        $this->color_bg=$color_bg;
        $this->width=$width;
        $this->text=$text;
        $this->precision=$precision;

        $this->build();
    }
    
    function build() {
        $this->data = array(
            'seed'=>'43',
            'dock'=>'false',
            'size'=>$this->precision,
            'w'=>$this->width,
            'src'=>'custom',
            'text'=>urlencode(utf8_encode($this->text)),
            'fg'=>$this->color,
            'bg'=>$this->color_bg,
            'goodies'=>'ot.liga',
            urlencode('i[0]')=>urlencode($this->font.',,720,144')
        );
        $requete_image_existe='SELECT ID FROM images_myfonts '
                             .'WHERE Font LIKE \''.$this->font.'\' AND Color LIKE \''.$this->color.'\' AND ColorBG LIKE \''.$this->color_bg.'\''
                             .' AND Width LIKE \''.$this->width.'\' AND Texte LIKE \''.$this->text.'\' AND Precision_ LIKE \''.$this->precision.'\'';
        $requete_image_existe_resultat=DM_Core::$d->requete_select($requete_image_existe);
        $image_existe=count($requete_image_existe_resultat) != 0;
        if ($image_existe && !isset($_GET['force_post'])) {
            $id_image=$requete_image_existe_resultat[0]['ID'];
            $this->chemin_image='edges/images_myfonts/'.$id_image.'.gif';
        }
        else {
            $this->p=new Post(
                "http://new.myfonts.com/ajax-server/testdrive.xml",
                "http://www.jonasjohn.de/",
                $this->data,
                'GET'
            );

            $code_image=$this->p->content;
            preg_match(self::$regex_source_image, $code_image, $chemin);
            $this->chemin_image=$chemin[1];

            $requete_get_id='SELECT Max(ID) AS id_max FROM images_myfonts';
            $resultat_get_id=DM_Core::$d->requete_select($requete_get_id);
            $id=$resultat_get_id[0]['id_max']+1;
            $requete='INSERT INTO images_myfonts(ID,Font,Color,ColorBG,Width,Texte,Precision_) '
                    .'VALUES('.$id.',\''.$this->font.'\',\''.$this->color.'\',\''.$this->color_bg.'\','
                    .'\''.$this->width.'\',\''.$this->text.'\',\''.$this->precision.'\')';
            DM_Core::$d->requete($requete);
            
            $im=imagecreatefromgif($this->chemin_image);
            imagegif($im,'edges/images_myfonts/'.$id.'.gif');
        }
    }
}

?>
