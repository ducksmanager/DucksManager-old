<?php
class Etendre extends CI_Controller {
	static $pays;
	static $magazine;
	static $numero;
	static $nouveau_numero;
	
	function index($pays=null,$magazine=null,$numero=null,$nouveau_numero=null) {
		
		if (in_array(null,array($pays,$magazine,$numero,$nouveau_numero))) {
			echo 'Erreur : Nombre d\'arguments insuffisant';
			exit();
		}
		self::$pays=$pays;
		self::$magazine=$magazine;
		self::$numero=$numero;
		self::$nouveau_numero=$nouveau_numero;
		
		$this->load->library('session');
		$this->load->database();
		$this->db->query('SET NAMES UTF8');
		$this->load->helper('url');
		
		$this->load->model('Modele_tranche');
		$numeros_dispos=$this->Modele_tranche->get_numeros_disponibles(self::$pays,self::$magazine);
		$this->Modele_tranche->setNumerosDisponibles($numeros_dispos);
		$this->Modele_tranche->etendre_numero($pays,$magazine,$numero,$nouveau_numero);
		
	}
}

?>
