<?php
include_once('viewer.php');
class Sv_doublons extends CI_Controller {
	static $pays;
	static $magazine;
	static $etape_courante;
	static $etape;
	
	function index($pays=null,$magazine=null) {
		
		if (in_array(null,array($pays,$magazine))) {
			$this->load->view('errorview',array('Erreur'=>'Nombre d\'arguments insuffisant'));
			exit();
		}
		self::$pays=$pays;
		self::$magazine=$magazine;
		
		if ($this->session->userdata('user') == false) {
			echo 'Aucun utilisateur connecte';
			return;
		}
		
		$this->db->query('SET NAMES UTF8');
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$this->Modele_tranche->sv_doublons($pays,$magazine);
		
	}
}

?>
