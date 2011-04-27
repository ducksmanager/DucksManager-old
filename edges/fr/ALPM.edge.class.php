<?php
class fr_ALPM extends Edge {
    var $pays='fr';
    var $magazine='ALPM';
    var $intervalles_validite=array('AX','B1','B7','B8','B16','B19','B25','B27','B28','B34','B40','B42','B49','B54','B57','B61');

    var $en_cours=array();
    static $largeur_defaut=18;
    static $hauteur_defaut=282;

    var $serie;
    var $numero_serie;

    function fr_ALPM($numero) {
        $this->numero=$numero;
        $this->serie=$numero[0];
        $this->numero_serie=substr($this->numero, strpos($this->numero, ' ')+1, strlen($this->numero));
        switch($this->serie) {
            case 'A':
                $this->largeur=30*Edge::$grossissement;
                $this->hauteur=265*Edge::$grossissement;
            break;
        
            case 'B':
                if ($this->numero_serie<=27) {
                    $this->largeur=18*Edge::$grossissement;
                    $this->hauteur=260*Edge::$grossissement;
                }
                else {
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
        
        return $this->image;
    }
}
?>
