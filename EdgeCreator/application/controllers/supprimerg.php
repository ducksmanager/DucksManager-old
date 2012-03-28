<?php
class SupprimerG extends CI_Controller {
	static $pays;
	static $magazine;
	static $etape;
	
	function index($pays=null,$magazine=null,$etape=null) {
		if (in_array(null,array($pays,$magazine,$etape))) {
			$this->load->view('errorview',array('Erreur'=>'Nombre d\'arguments insuffisant'));
			exit();
		}
		self::$pays=$pays;
		self::$magazine=$magazine;
		self::$etape=$etape;

		
		$this->db->query('SET NAMES UTF8');
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		$privilege=$this->Modele_tranche->get_privilege();
		$data=array();
		
		
		if ($privilege == 'Affichage')
			$this->load->view('errorview',array('Erreur'=>'droits insuffisants'));
		else {
			$this->Modele_tranche->setUsername($this->session->userdata('user'));
			
			$this->Modele_tranche->dupliquer_modele_magazine_si_besoin(self::$pays,self::$magazine);
			
			$this->Modele_tranche->delete_ordre(self::$pays,self::$magazine,self::$etape,null,null,null);
			$this->load->view('parametragegview',$data);
		}
	}
}

function array_value_list ($match, $array)
{
	$occurences = array();
	foreach ($array as $key => $value) {
		if ($value == $match)
			$occurences[]=$key;
	}

	return $occurences;
}
?>
