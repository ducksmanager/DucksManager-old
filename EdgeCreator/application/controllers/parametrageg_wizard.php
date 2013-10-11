<?php
class ParametrageG_wizard extends CI_Controller {
	static $pays;
	static $magazine;
	static $numero;
	static $etape;
	
	function index($pays=null,$magazine=null,$numero,$etape=null,$nom_fonction='null', $nom_option_sel='null') {
		
		if (in_array(null,array($pays,$magazine))) {
			$this->load->view('errorview',array('Erreur'=> 'Nombre d\'arguments insuffisant'));
			exit();
		}
		self::$pays=$pays;
		self::$magazine=$magazine;
		self::$numero=$numero;
		self::$etape=$etape=='null'?null:$etape;
		$nom_fonction=$nom_fonction=='null' ? null : $nom_fonction;
		$nom_option=$nom_option_sel=='null' ? null : $nom_option_sel;
		
		
		$this->db->query('SET NAMES UTF8');
		$this->load->helper('url');
		$this->load->helper('form');
		
		$mode_expert=$this->session->userdata('mode_expert') === true;
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		$this->Modele_tranche->setPays(self::$pays);
		$this->Modele_tranche->setMagazine(self::$magazine);
		if (is_null(self::$etape)) { // Liste des étapes
			$etapes=$this->Modele_tranche->get_etapes_simple(self::$pays,self::$magazine,self::$numero);
			if (count($etapes) == 0) {
				$fonction_dimension=new stdClass();
				$fonction_dimension->Ordre=-1;
				$fonction_dimension->Nom_fonction='Dimensions';
				$etapes[]=$fonction_dimension;
			}
			$data=array('etapes'=>$etapes);
		}
		else {
			$fonction=$this->Modele_tranche->get_fonction(self::$pays,self::$magazine,self::$etape,self::$numero);
			if (is_null($fonction)) {// Etape temporaire ou dimensions
				if (self::$etape == -1) {
					$fonction=new stdClass();
					$fonction->Nom_fonction='Dimensions';
				}
				else
					$options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,self::$etape, self::$numero, false, true, true, $nom_option);
			}
			else {
				
				if ($this->Modele_tranche->has_no_option(self::$pays,self::$magazine,self::$etape)) {
					$options=$this->Modele_tranche->get_noms_champs($fonction->Nom_fonction);
				}
				else {
					$options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,self::$etape, self::$numero, false, true, false, $nom_option);
				}
			}
			
			$data = array(
				'options'=>$options
			);
		}
		$this->load->view('parametragegview',$data);
	}
}

?>