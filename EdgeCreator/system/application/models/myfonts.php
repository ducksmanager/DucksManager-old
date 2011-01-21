<?php
class MyFonts extends Model {
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

    function MyFonts($font=null,$color=null,$color_bg=null, $width=null, $text=null,$precision=18) {
        
        parent::Model();
        if (is_null($font))
            return;
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
            'text'=>urlencode(($this->text)),
            'fg'=>$this->color,
            'bg'=>$this->color_bg,
            'goodies'=>'ot.liga',
            urlencode('i[0]')=>urlencode($this->font.',,720,144')
        );
        /*
        $requete_image_existe='SELECT ID FROM images_myfonts '
                             .'WHERE Font LIKE \''.$this->font.'\' AND Color LIKE \''.$this->color.'\' AND ColorBG LIKE \''.$this->color_bg.'\''
                             .' AND Width LIKE \''.$this->width.'\' AND Texte LIKE \''.$this->text.'\' AND Precision_ LIKE \''.$this->precision.'\'';
        $requete_image_existe_resultat=$this->db->query($requete_image_existe)->result();
        $image_existe=count($requete_image_existe_resultat) != 0;
        if ($image_existe && !isset($_GET['force_post'])) {
            $id_image=$requete_image_existe_resultat[0]->ID;
            $this->chemin_image='edges/images_myfonts/'.$id_image.'.gif';
        }
        else {*/
            $this->p=new Post(
                "http://new.myfonts.com/ajax-server/testdrive.xml",
                "http://www.jonasjohn.de/",
                $this->data,
                'GET'
            );

            $code_image=$this->p->content;
            preg_match(self::$regex_source_image, $code_image, $chemin);
            $this->chemin_image=$chemin[1];
            /*
            $requete_get_id='SELECT Max(ID) AS id_max FROM images_myfonts';
            $resultat_get_id=$this->db->query($requete_get_id)->result();
            $id=$resultat_get_id[0]->id_max+1;
            $requete='INSERT INTO images_myfonts(ID,Font,Color,ColorBG,Width,Texte,Precision_) '
                    .'VALUES('.$id.',\''.$this->font.'\',\''.$this->color.'\',\''.$this->color_bg.'\','
                    .'\''.$this->width.'\',\''.$this->text.'\',\''.$this->precision.'\')';
            $this->db->query($requete);
            
            $im=imagecreatefromgif($this->chemin_image);
            imagegif($im,'edges/images_myfonts/'.$id.'.gif');
        }*/
    }
}

class Post extends Model {
    var $header;
    var $content;
    
    function Post($url, $referer, $_data,$type='POST',$cookie='',$easyget=true) {
        // convert variables array to string:
        $data = array();
        while(list($n,$v) = each($_data)){
            $data[] = ($n).'='.($v);
        }
        $data = implode('&', $data);
        // format --> test1=a&test2=b etc.

        $url=$url.'?'.$data;
        $this->content = Util::get_page($url);
        
        return;
        
        // parse the given URL
        $url = parse_url($url);
        if ($url['scheme'] != 'http') {
            die('Only HTTP request are supported !');
        }
        // extract host and path:
        $host = $url['host'];
        $path = $url['path'];

        // open a socket connection on port 80
        $fp = fsockopen($host, 80);

        // send the request headers:
        fputs($fp, "$type $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Cookie: $cookie\r\n");
        fputs($fp, "Referer: http://coa.inducks.org\r\n");
        fputs($fp, "Content-length: ". strlen($data) ."\r\n");
        fputs($fp, "Connection: keep-alive\r\n\r\n");
        fputs($fp, $data);
        
        $result = '';
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 128);
        }

        // close the socket connection:
        fclose($fp);

        // split the result header from the content
        $result = explode("\r\n\r\n", $result, 2);

        $header = isset($result[0]) ? $result[0] : '';
        $content = isset($result[1]) ? $result[1] : '';

        // return as array:
        $this->header=$header;
        $this->content=$content;
    }
}

class Util {
    static function get_page($url) {
        if (extension_loaded('curl')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_NOBODY, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_ENCODING, "gzip");
            $page = curl_exec($ch);
            curl_close($ch);
            return $page;
        }
        else {
            $handle = @fopen($url, "r");
            if ($handle) {
                $buffer="";
                while (!feof($handle)) {
                    $buffer.= fgets($handle, 4096);
                }
                fclose($handle);
                return $buffer;
            }
            else return ERREUR_CONNEXION_INDUCKS;
        }
    }
}
?>