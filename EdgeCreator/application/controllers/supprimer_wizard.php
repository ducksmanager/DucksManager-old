<?php
class Supprimer_Wizard extends CI_Controller {
	static $pays;
	static $magazine;
	static $etape;
	
	function index($pays=null,$magazine=null,$numero=null,$etape=null) {
		if (in_array(null,array($pays,$magazine,$etape,$numero))) {
			$this->load->view('errorview',array('Erreur'=>'Nombre d\'arguments insuffisant'));
			exit();
		}
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$this->Modele_tranche->supprimer_etape($pays,$magazine,$numero,$etape);
	}
}
?>
