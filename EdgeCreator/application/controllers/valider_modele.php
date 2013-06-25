<?php

class Valider_Modele extends CI_Controller {
	
	function index($pays,$magazine,$numero,$nom_image) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->load->library('email');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));

		$etapes=$this->Modele_tranche->get_ordres($pays,$magazine,$numero,true);
		
		ob_start();
		print_r($etapes);
		$affichage_etapes=ob_get_contents();
		ob_end_clean();
			
		$this->email->from('admin@ducksmanager.net', 'DucksManager - '.$this->session->userdata('user'));
		$this->email->to('admin@ducksmanager.net');
			
		$this->email->subject('Proposition de modele de tranche de '.$this->session->userdata('user'));
		$this->email->message($affichage_etapes);
		$src_image='../edges/'.$pays.'/tmp/'.$nom_image.'.png';
		$this->email->attach($src_image);
		$this->email->send();
		echo $affichage_etapes;
		echo $this->email->print_debugger();
	}
}

?>
