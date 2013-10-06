<?php
include_once(BASEPATH.'/../application/controllers/viewer_wizard.php');

class Viewer_myfonts extends Viewer_wizard {
	
	function index($url,$couleur_texte,$couleur_fond,$largeur,$chaine,$demi_hauteur,$rotation,$largeur_tranche,$debug=false) {
		self::$is_debug = $debug === 'true';
		
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
				
		$options=new stdClass();
		$options->URL=$url;
		$options->Couleur_texte=$couleur_texte;
		$options->Couleur_fond=$couleur_fond;
		$options->Largeur=$largeur;
		$options->Chaine=$chaine;
		$options->Demi_hauteur=$demi_hauteur;
		$options->Rotation=$rotation=='null' ? null : $rotation;
		
		Viewer_wizard::$largeur = intval($largeur_tranche*1.5);
		
		new TexteMyFonts($options,true,false,true,false);
		
		if (self::$is_debug===false) {
			header('Content-type: image/png');
			imagepng(Viewer_wizard::$image);
		}
	}
}

?>
