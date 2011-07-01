<?php
class Helper extends CI_Controller {
	
	function index($nom=null) {
		
		if (in_array(null,array($nom))) {
			echo 'Erreur : Nombre d\'arguments insuffisant';
			exit();
		}
		$this->load->library('session');
		$this->load->model('Modele_tranche');
		$this->load->database();
		
		$privilege=$this->Modele_tranche->get_privilege();
		
		if ($privilege == 'Affichage') {
			echo 'Vous n\'avez pas les permissions suffisantes pour r&eacute;aliser cette action !';
			return;
		}
		
		ob_start();
		include_once('helpers/'.$nom);
		$contenu=ob_get_clean();
		
		$data=array('contenu'=>$contenu);
		
		$this->load->view('helperview',$data);
		
	}
}

?>
