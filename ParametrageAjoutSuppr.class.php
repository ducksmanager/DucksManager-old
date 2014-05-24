<?php
class ParametreAjoutSuppr {
    var $nomParametrage;
    var $nomParametre;
    var $libelleParametre;

    function __construct($nomParametrage, $nomParametre, $libelleParametre)
    {
        $this->nomParametrage = $nomParametrage;
        $this->nomParametre = $nomParametre;
        $this->libelleParametre = $libelleParametre;
    }

    function __toString() {
        return '<li><a href="javascript:return false;" name="'.$this->nomParametrage.'_'.$this->nomParametre.'" class="'.$this->nomParametre.'">'.$this->libelleParametre.'</a></li>';
    }
}

class ParametrageAjoutSuppr {
    var $nom;
    var $libelle;

    /**
     * @var ParametreAjoutSuppr[]
     */
    static $liste;

    function __construct($nom, $libelle)
    {
        $this->nom = $nom;
        $this->libelle = $libelle;
        self::$liste = array();
    }

    function __toString() {
        $str = '<div class="footer_section">'
                    .'<h2 class="libelle">'
                        .'<label for="'.$this->nom.'">'.$this->libelle.'</label>'
                    .'</h2>'
                    .'<div class="liste">'
                        .'<ul>';
                        foreach(self::$liste as $item) {
                            $str.= $item;
                        }
        $str.=          '</ul>'
                    .'</div>'
                .'</div>';
        return $str;
    }

    function add_to_list(ParametreAjoutSuppr $parametre) {
        self::$liste[$parametre->nomParametre] = $parametre;
    }
} 