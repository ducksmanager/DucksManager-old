<?php
class EdgeCreatorg extends CI_Controller {
	static $pays;
	static $magazine;
	
	function logout() {
		$this->load->library('session');
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('pass');
		$this->index();
	}
	
	function index($pays=null,$magazine=null,$etape_ouverture=null,$numero_debut_filtre=null,$numero_fin_filtre=null)
	{
		self::$pays=$pays;
		self::$magazine=$magazine;
		$this->load->helper('url');
		$this->load->database();
		$this->load->library('session');
		$this->load->model('Modele_tranche');
		
		$privilege=$this->Modele_tranche->get_privilege();
		
		global $erreur;
		$erreur = '';
		
		
		$data = array(
				'user'=>$this->session->userdata('user'),
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
		$this->load->view('edgecreatorgview',$data);
		$this->load->view('footerview',$data);
	}	
}
?>