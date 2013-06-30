<?php

class Desactiver_Modele extends CI_Controller {
	
	function index($pays,$magazine,$numero) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		$this->Modele_tranche->desactiver_modele($pays,$magazine,$numero);
	}
}

?>
