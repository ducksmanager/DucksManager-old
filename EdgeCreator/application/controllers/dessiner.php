<?php
include_once(BASEPATH.'/../application/controllers/viewer.php');

class Dessiner extends CI_Controller {
	
	function index() {
		$arguments = func_get_args();
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		$nom_fonction = $arguments[0];
		if (class_exists($nom_fonction)) {
			Viewer::$zoom=$arguments[1];
			Viewer::$is_debug=$arguments[2] != 0;
			$options=new stdClass();
			$i=3;
			foreach(array_keys(Arc_cercle::$champs) as $nom_champ) {
				$options->$nom_champ = $arguments[$i++];
			}
			//print_r($options);
			switch($arguments[0]) {
				case 'Arc_cercle':
					
					$largeur=$options->Pos_x_centre+$options->Largeur/2;
					$hauteur=$options->Pos_y_centre+$options->Hauteur/2;
					Viewer::$image=imagecreatetruecolor(z($largeur), z($hauteur));
					$transp=imagecolorallocatealpha(Viewer::$image, 255, 255, 255, 127);
					imagefill(Viewer::$image,0,0,$transp);
					imagesavealpha(Viewer::$image, true);
					
					new Arc_cercle($options);
					if (Viewer::$is_debug===false)
						header('Content-type: image/png');
					imagepng(Viewer::$image);
				break;
			}
		}
		else {
			echo 'Erreur : '.$arguments[0]. 'n\'est pas une classe';
		}
	}
	
}
?>