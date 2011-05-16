<?php
class ListerG extends CI_Controller {
	
	function index($nom_option) {
		if (in_array(null,array($nom_option))) {
			echo 'Erreur : Nombre d\'arguments insuffisant';
			exit();
		}
		$this->load->library('session');
		$this->load->model('Modele_tranche');

		$liste=get_liste(null, $nom_option);

			$data = array(
					'liste'=>$liste
			);

			$this->load->view('listergview',$data);
	}
}
?>
