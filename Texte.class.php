<?php
class Texte {
	var $texte;
	var $pos_x;
	var $pos_y;
	var $taille;
	var $angle;
	var $couleur;
	var $police;
	function Texte($texte,$pos_x,$pos_y,$taille,$angle,$couleur,$police) {
		$this->texte=$texte;
		$this->pos_x=$pos_x;$this->pos_y=$pos_y;
		$this->taille=$taille;
		$this->angle=$angle;
		$this->couleur=$couleur;
		$this->police='edges/'.$police;
	}

    function dessiner(&$image, $compression=array(1,1),$couleur_fond=array(0,0,0)) {
        if ($compression !== array(1,1)) {
            $image_factice=imagecreatetruecolor(imagesx($image),imagesy($image));
            imagefill($image_factice, 0, 0, imagecolorallocate($image_factice, $couleur_fond[0],$couleur_fond[1],$couleur_fond[2]));
            $points=imagettftext($image_factice,$this->taille,$this->angle,$this->pos_x,$this->pos_y,$this->couleur,$this->police,$this->texte);
            $largeur_coupee=$points[2]-$points[0];
            $hauteur_coupee=$points[3]-$points[5];
            $largeur_compressee=intval($largeur_coupee*$compression[0]);
            $hauteur_compressee=intval($hauteur_coupee*$compression[1]);
            $image_texte=imagecreatetruecolor($largeur_compressee, $hauteur_compressee);
            imagecopyresampled($image_texte, $image_factice, 0, 0, $points[6], $points[7], $largeur_compressee, $hauteur_compressee, $largeur_coupee, $hauteur_coupee);
            $placement_texte=array($points[6]+($largeur_coupee-$largeur_compressee)/2,
                                   $points[7]+($hauteur_coupee-$hauteur_compressee)/2);
            imagecopyresampled($image, $image_texte, $placement_texte[0], $placement_texte[1], 0, 0, $largeur_compressee, $hauteur_compressee, $largeur_compressee, $hauteur_compressee);

        }
        else
            return imagettftext($image,$this->taille,$this->angle,$this->pos_x,$this->pos_y,$this->couleur,$this->police,$this->texte);
    }

    function dessiner_centre($image, $compression=array(1,1),$couleur_fond=array(0,0,0)) {
        // Test pour détecter la largeur de la lettre
        $this->pos_x=0;
        $image_factice=imagecreate(imagesx($image),imagesy($image));
        $points=$this->dessiner($image_factice);
        $largeur_texte=$points[2]-$points[0];
        $this->pos_x=intval(imagesx($image)/2) - $largeur_texte/2;
        imagedestroy($image_factice);
        $this->dessiner($image, $compression,$couleur_fond);
    }
}