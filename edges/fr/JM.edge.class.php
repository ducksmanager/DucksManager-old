<?php
class JM extends Edge {
    var $pays='fr';
    var $magazine='JM';
    static $numeros_doubles=array('2411-12','2463-64','2479-80','2506-07','2515-16','2531-32','2558-59','2584-85','2610-11','2619-20','2636-37','2662-63','2671-72','2688-89','2715-16','2723-24','2767-68','2819-20','2828-29','2844-45','2871-72','2879-80','2896-97','2923-24','2932-33','2948-49','2975-76','2984-85');
    var $intervalles_validite=array(array('debut'=>2400, 'fin'=>2499),
                                    array('debut'=>2800, 'fin'=>3023,'sauf'=>array('2506-07','2515-16','2531-32','2558-59','2584-85','2610-11','2619-20','2636-37','2662-63','2671-72','2688-89','2715-16','2723-24','2767-68','2819-20','2828-29','2844-45','2871-72','2879-80','2896-97','2923-24','2932-33','2948-49','2975-76','2984-85')));
    static $largeur_defaut=5;
    static $hauteur_defaut=275;

    function JM ($numero) {
        $this->numero=$numero;
        if (in_array($this->numero, JM::$numeros_doubles)) {
            $this->largeur=5*Edge::$grossissement;
            $this->hauteur=275*Edge::$grossissement;
            if ($this->numero=='2454-55')
                $this->largeur=6*Edge::$grossissement;

        }
        elseif ($this->numero >=2963) {
            $this->largeur=5*Edge::$grossissement;
            $this->hauteur=275*Edge::$grossissement;
        }
        elseif ($this->numero >=2800) {
            $this->largeur=5*Edge::$grossissement;
            $this->hauteur=285*Edge::$grossissement;
        }
        elseif ($this->numero >= 2400 && $this->numero < 2500) {
            $this->largeur=5*Edge::$grossissement;
            $this->hauteur=279*Edge::$grossissement;
        }
        else {
            $this->largeur=5*Edge::$grossissement;
            $this->hauteur=279*Edge::$grossissement;
        }
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
            list($texte,$width,$height)=imagecreatefrompng_getimagesize('edges/fr/JM.'.$this->numero.'.Texte1.png');

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