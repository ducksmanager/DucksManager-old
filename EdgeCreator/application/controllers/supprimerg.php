<?php
class SupprimerG extends CI_Controller {
	static $pays;
	static $magazine;
	static $etape;
	
	function index($pays=null,$magazine=null,$etape=null) {
		if (in_array(null,array($pays,$magazine,$etape))) {
			echo 'Erreur : Nombre d\'arguments insuffisant';
			exit();
		}
		self::$pays=$pays;
		self::$magazine=$magazine;
		self::$etape=$etape;

		$this->load->library('session');
		$this->load->database();
		$this->db->query('SET NAMES UTF8');
		$this->load->model('Modele_tranche');
		$privilege=$this->Modele_tranche->get_privilege();
		$data=array();
		
		
		if ($privilege == 'Affichage')
			echo 'Erreur : Droits insuffisants';
		else {
			$this->Modele_tranche->setUsername($this->session->userdata('user'));
			
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
