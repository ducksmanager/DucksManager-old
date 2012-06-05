<?php
class ListerG extends CI_Controller {
	
	function index($nom_option,$pays=null,$magazine=null,$format='json') {
		if (in_array(null,array($nom_option))) {
			$this->load->view('errorview',array('Erreur'=>'Nombre d\'arguments insuffisant'));
			exit();
		}
		
		$this->db->query('SET NAMES UTF8');
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');

		$liste=$this->Modele_tranche->get_liste(null, $nom_option,$pays,$magazine);

			$data = array(
					'liste'=>$liste,
					'format'=>$format
			);

			$this->load->view('listergview',$data);
	}
}
?>
