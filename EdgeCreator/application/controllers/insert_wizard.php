<?php

class Insert_Wizard extends CI_Controller {
	
	function index($pays,$magazine,$numero,$etape,$nom_fonction) {		
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$this->Modele_tranche->insert_etape($pays,$magazine,$numero,$etape ,$nom_fonction);
	}
}

?>
