<?php
class TranchesEnCours extends CI_Controller {
	
	function index() {
		$this->db->query('SET NAMES UTF8');
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		$privilege=$this->Modele_tranche->get_privilege();
		if ($privilege == 'Affichage') {
			$this->load->view('errorview',array('Erreur'=>'droits insuffisants'));
			return;
		}
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$resultats = $this->Modele_tranche->get_tranches_en_cours();
		$data = array(
			'tranches'=>$resultats
		);
		$this->load->view('tranchesencoursview',$data);
	}
}

?>
