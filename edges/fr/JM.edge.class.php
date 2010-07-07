<?php
class JM extends Edge {
    var $pays='fr';
    var $magazine='JM';
    static $numeros_doubles=array('2411-12','2463-64','2479-80','2506-07','2515-16','2531-32','2558-59','2584-85','2610-11','2619-20','2636-37','2662-63','2671-72','2688-89','2715-16','2723-24','2767-68','2819-20','2828-29','2844-45','2871-72','2879-80','2896-97','2923-24','2932-33','2948-49','2975-76','2984-85');
    var $intervalles_validite=array(array('debut'=>500 , 'fin'=>1069),
                                    array('debut'=>1229, 'fin'=>1322),
                                    array('debut'=>1323, 'fin'=>1553),
                                    array('debut'=>1558, 'fin'=>1942),
                                    array('debut'=>1943, 'fin'=>2249),
                                    array('debut'=>2400, 'fin'=>2499),
                                    array('debut'=>2800, 'fin'=>3023,'sauf'=>array('2506-07','2515-16','2531-32','2558-59','2584-85','2610-11','2619-20','2636-37','2662-63','2671-72','2688-89','2715-16','2723-24','2767-68','2819-20','2828-29','2844-45','2871-72','2879-80','2896-97','2923-24','2932-33','2948-49','2975-76','2984-85')));
    static $largeur_defaut=5;
    static $hauteur_defaut=275;

    function JM ($numero) {
        $this->numero=$numero;
        $this->largeur=5;
        if (in_array($this->numero, JM::$numeros_doubles)) {
            $this->hauteur=275;
            $this->largeur=6;
        }
        elseif ($this->numero >=2963) {
            $this->hauteur=275;
        }
        elseif ($this->numero >=2800) {
            $this->hauteur=285;
        }
        elseif ($this->numero >= 2400 && $this->numero < 2500) {
            $this->hauteur=279;
        }
        elseif ($this->numero >=1973 & $this->numero <= 2249) {
            $this->hauteur=270;
        }
        elseif ($this->numero >=1558 & $this->numero <= 1942) {
            $this->hauteur=285;
        }
        elseif ($this->numero >=1323 & $this->numero <= 1553) {
            $this->hauteur=295;
        }
        elseif ($this->numero >=1229 & $this->numero <= 1322) {
            $this->hauteur=298;
        }
        elseif ($this->numero >=500 & $this->numero <= 1069) {
            $this->hauteur=312;
        }
        else {
            $this->hauteur=279;
        }
        $this->hauteur*=Edge::$grossissement;
        $this->largeur*=Edge::$grossissement;
        $this->image=imagecreatetruecolor(intval($this->largeur),intval($this->hauteur));
        if ($this->image===false)
            xdebug_break ();
    }
    function dessiner() {
        if (in_array($this->numero,JM::$numeros_doubles)) {
            $blanc = imagecolorallocate($this->image, 255, 255, 255);
            $noir = imagecolorallocate($this->image, 0,0,0);
            list($rouge,$vert,$bleu)=$this->getColorsFromDB();
            $fond=imagecolorallocate($this->image, $rouge, $vert, $bleu);
            list($rouge_texte,$vert_texte,$bleu_texte)=$this->getColorsFromDB($blanc, 'Texte');

            imagefill($this->image, 0, 0, $fond);
            list($texte,$width,$height)=imagecreatefrompng_getimagesize($this->getChemin().'/JM.'.$this->numero.'.Texte1.png');

            imagealphablending($texte, false);
		    $transparent = imagecolorallocatealpha($texte, 0, 0, 0, 127);
		    imagefill($texte, 0, 0, $transparent);
		    imagesavealpha($texte,true);
		    imagealphablending($texte, true);

			$nouvelle_hauteur=($this->largeur)*($height/$width);
			imagecopyresampled ($this->image, $texte, 0, $this->largeur, 0, 0, $this->largeur, $nouvelle_hauteur, $width, $height);

        }
        else {
            $blanc = imagecolorallocate($this->image, 255, 255, 255);
            $noir = imagecolorallocate($this->image, 0, 0, 0);
            imagefilledrectangle($this->image, 0, 0, $this->largeur, $this->hauteur, $blanc);
            $this->agrafer();
        }
        return $this->image;
    }

}