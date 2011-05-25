<?php
class ListerG extends CI_Controller {
	
	function index($nom_option,$arg=null,$format='json') {
		if (in_array(null,array($nom_option))) {
			echo 'Erreur : Nombre d\'arguments insuffisant';
			exit();
		}
		$this->load->library('session');
		$this->load->model('Modele_tranche');

		$liste=get_liste(null, $nom_option,$arg);

			$data = array(
					'liste'=>$liste,
					'format'=>$format
			);

			$this->load->view('listergview',$data);
	}
}
?>
