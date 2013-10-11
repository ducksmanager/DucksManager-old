<?php

class Update_Photo extends CI_Controller {
	
	function index($pays,$magazine,$numero,$nom_fichier_photo_principale) {
		
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$this->Modele_tranche->update_photo_principale($pays,$magazine,$numero,$nom_fichier_photo_principale);
	}
}

?>
