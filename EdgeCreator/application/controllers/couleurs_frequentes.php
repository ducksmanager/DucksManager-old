<?php

class Couleurs_Frequentes extends CI_Controller {
	
	function index($pays,$magazine,$numero) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$id_modele=$this->Modele_tranche->get_id_modele($pays,$magazine,$numero);
		$couleurs=$this->Modele_tranche->get_couleurs_frequentes($id_modele);

		$data = array(
			'couleurs'=>$couleurs
		);
		$this->load->view('couleursfrequentesview',$data);
	}
}

?>
