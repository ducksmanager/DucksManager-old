<?php
class us_LTSMG extends Edge {
    var $pays='us';
    var $magazine='LTSMG';
    var $intervalles_validite=array('1','2');
    var $en_cours=array();
    static $largeur_defaut=20;
    static $hauteur_defaut=258;


    function us_LTSMG($numero) {
        $this->numero=$numero;
        switch($this->numero) {
            case '1':
                $this->largeur=16*Edge::$grossissement;
            break;
            case '2':
                $this->largeur=12.5*Edge::$grossissement;
        }
        $this->hauteur=258*Edge::$grossissement;
        
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }

    function dessiner() {
        switch($this->numero) {
            case '1':
                $fond=imagecolorallocate($this->image, 247,186,79);
            break;
            case '2':
                $fond=imagecolorallocate($this->image, 223,91,68);

        }
        imagefill($this->image,0,0,$fond);
        $this->placer_image('LTSMG'.$this->numero.'.tranche.png');
        
        return $this->image;
    }
}
?>
