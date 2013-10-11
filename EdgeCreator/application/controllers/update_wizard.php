<?php

class Update_Wizard extends CI_Controller {
	
	function index($pays,$magazine,$numero,$etape,$parametrage) {
		parse_str($parametrage,$parametrage);
		
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$this->Modele_tranche->update_etape($pays,$magazine,$numero,$etape,$parametrage);
	}
}

?>
