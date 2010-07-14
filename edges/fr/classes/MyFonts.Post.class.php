<?php
@include_once('Post.class.php');
class MyFonts extends Post {
    var $p;
    var $chemin_image;
    static $regex_source_image='#src="([^"]+)"#is';

    function MyFonts($font,$color,$color_bg, $width, $text) {
        $data = array(
            'seed'=>'43',
            'dock'=>'false',
            'size'=>'18',
            'w'=>$width,
            'src'=>'custom',
            'text'=>$text,
            'fg'=>$color,
            'bg'=>$color_bg,
            'goodies'=>'ot.liga',
            'i[0]'=>$font.',,720,144'
        );

        // send a request to example.com (referer = jonasjohn.de)
        $this->p=new Post(
            "http://new.myfonts.com/ajax-server/testdrive.xml",
            "http://www.jonasjohn.de/",
            $data
        );

        $code_image=$this->p->content;
        preg_match(self::$regex_source_image, $code_image, $chemin);
        $this->chemin_image=$chemin[1];
    }
}

?>
