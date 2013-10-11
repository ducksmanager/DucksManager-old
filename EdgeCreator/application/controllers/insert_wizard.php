<?php

class Insert_Wizard extends CI_Controller {
	
	function index($pays,$magazine,$numero,$pos,$etape,$nom_fonction) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		if ($nom_fonction == 'Dimensions') { // Créer aussi le modèle
			$this->Modele_tranche->creer_modele($pays,$magazine,$numero);
		}
		$infos_insertion=$this->Modele_tranche->insert_etape($pays,$magazine,$numero,$pos,$etape ,$nom_fonction);

		$data = array(
			'infos_insertion'=>$infos_insertion
		);
		
		$this->load->view('insertview',$data);
	}
}

?>
