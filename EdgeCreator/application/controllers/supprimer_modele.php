<?php

class Supprimer_Modele extends CI_Controller {
	
	function index($pays,$magazine,$numero) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		$this->Modele_tranche->supprimer_modele($pays,$magazine,$numero);
	}
}

?>
