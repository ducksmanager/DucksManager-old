<?php

class Valider_Modele extends CI_Controller {
	
	function index($pays,$magazine,$numero,$nom_image,$createurs,$photographes) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->load->library('email');
		$username=$this->session->userdata('user');
		$this->Modele_tranche->setUsername($username);

		$etapes=$this->Modele_tranche->get_ordres($pays,$magazine,$numero,true);
		
		$message=" INSERT INTO `tranches_pretes` (`publicationcode`, `issuenumber`, `photographes`, `createurs`, `dateajout`)"
				." VALUES ('$pays/$magazine', '$numero', '$photographes,', '$createurs');";
		
		$message.="\n\n";
		
		ob_start();
		print_r($etapes);
		$message.=ob_get_contents();
		ob_end_clean();
			
		$this->email->from('admin@ducksmanager.net', 'DucksManager - '.$username);
		$this->email->to('admin@ducksmanager.net');
			
		$this->email->subject('Proposition de modele de tranche de '.$username);
		$this->email->message($message);
		$src_image='../edges/'.$pays.'/tmp/'.$nom_image.'.png';
		$this->email->attach($src_image);
		$this->email->send();
		
		$this->Modele_tranche->desactiver_modele($pays,$magazine,$numero);
		
		echo $affichage_etapes;
		echo $this->email->print_debugger();
	}
}

?>
