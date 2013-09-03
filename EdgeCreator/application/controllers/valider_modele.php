<?php

class Valider_Modele extends CI_Controller {
	
	function index($pays,$magazine,$numero,$nom_image,$createurs,$photographes) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->load->library('email');
		$this->load->helper('email');
		$username=$this->session->userdata('user');
		$this->Modele_tranche->setUsername($username);
		
		$message=" INSERT INTO `tranches_pretes` VALUES ('$pays/$magazine', '$numero', '$photographes', '$createurs', NULL);";
		
		$message.="\n\n";
			
		$this->email->from(get_admin_email(), 'DucksManager - '.$username);
		$this->email->to(get_admin_email());
			
		$this->email->subject('Proposition de modele de tranche de '.$username);
		$this->email->message($message);
		$src_image='../edges/'.$pays.'/tmp/'.$nom_image.'.png';
		$this->email->attach($src_image);
		$this->email->send();
		
		$this->Modele_tranche->desactiver_modele($pays,$magazine,$numero);
	}
}

?>
