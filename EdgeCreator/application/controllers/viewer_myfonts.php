<?php
include_once(BASEPATH.'/../application/controllers/viewer.php');

class Viewer_myfonts extends Viewer {
	
	function index($url,$couleur_texte,$couleur_fond,$largeur,$chaine,$demi_hauteur,$debug=false) {
		self::$is_debug = $debug === 'true';
		
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
				
		$options=new stdClass();
		$options->URL=$url;
		$options->Couleur_texte=$couleur_texte;
		$options->Couleur_fond=$couleur_fond;
		$options->Largeur=$largeur;
		$options->Chaine=$chaine;
		$options->Demi_hauteur=($demi_hauteur == 1);
		
		new TexteMyFonts($options,true,false,true,false);
		
		if (self::$is_debug===false) {
			header('Content-type: image/png');
			imagepng(Viewer::$image);
		}
	}
}

?>
