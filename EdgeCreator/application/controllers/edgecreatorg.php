<?php
class EdgeCreatorg extends CI_Controller {
	static $pays;
	static $magazine;
	
	function login() {
		$this->load->library('input');
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		$this->logout();
		
		global $erreur;$erreur='';
		
		$privilege=$this->Modele_tranche->get_privilege();
		if (is_null($privilege))
			echo 'Erreur - '.$erreur;
		else
			echo $privilege;
	}
	
	function logout() {
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('pass');
		$this->session->unset_userdata('mode_expert');
	}
	
	function index($pays=null,$magazine=null,$etape_ouverture=null,$numero_debut_filtre=null,$numero_fin_filtre=null)
	{
		self::$pays=$pays;
		self::$magazine=$magazine;
		$this->load->helper('url');
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		$privilege=$this->Modele_tranche->get_privilege();
		
		global $erreur;
		$erreur = '';
		
		
		$data = array(
				'user'=>$this->session->userdata('user'),
				'mode_expert'=>$this->session->userdata('mode_expert'),
				'just_connected'=>$this->Modele_tranche->get_just_connected(),
				'privilege' => $privilege,
				'erreur' => $erreur,
				'title' => 'EdgeCreator',
				'pays' => self::$pays,
				'magazine'=>self::$magazine,
				'etape_ouverture'=>$etape_ouverture,
				'numero_debut_filtre'=>$numero_debut_filtre,
				'numero_fin_filtre'=>$numero_fin_filtre
		);
		$this->load->view('headergview',$data);
		$this->load->view('wizarddialogsview',$data);
		$this->load->view('edgecreatorgview',$data);
		$this->load->view('footerview',$data);
	}	
}
?>