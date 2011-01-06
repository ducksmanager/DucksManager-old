<?php
class Image {
    var $nom;
    var $contenu;

    function __construct($nom,$contenu) {
        $this->nom=$nom;
        $this->contenu=$contenu;
        if (!array_key_exists($this->nom, ElementGraphique::$liste_images))
            ElementGraphique::$liste_images[$this->nom]=&$contenu;
        imagecolorallocate($this->contenu, 200, 200, 200);
    }

    function __toString() {
        return "'".$this->nom."'";
    }
}

?>
