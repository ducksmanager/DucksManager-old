<?php
include_once('viewer.php');
class Sv_doublons extends CI_Controller {
	static $pays;
	static $magazine;
	static $etape_courante;
	static $etape;
	
	function index($pays=null,$magazine=null) {
		
		if (in_array(null,array($pays,$magazine))) {
			echo 'Erreur : Nombre d\'arguments insuffisant';
			exit();
		}
		self::$pays=$pays;
		self::$magazine=$magazine;
		
		$this->load->library('session');
		if ($this->session->userdata('user') == false) {
			echo 'Aucun utilisateur connecte';
			return;
		}
		$this->load->database();
		$this->db->query('SET NAMES UTF8');
		
		$this->load->model('Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		$this->Modele_tranche->sv_doublons($pays,$magazine);
		
	}
}

?>
