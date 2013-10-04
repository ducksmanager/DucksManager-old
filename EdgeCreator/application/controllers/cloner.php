<?php
class Cloner extends CI_Controller {
	
	function index($pays=null,$magazine=null,$numero=null,$pos_relative=null,$etape_courante=null) {
		
		if (in_array(null,array($pays,$magazine,$numero,$pos_relative,$etape_courante))) {
			$this->load->view('errorview',array('Erreur'=> 'Nombre d\'arguments insuffisant'));
			exit();
		}
		
		$this->db->query('SET NAMES UTF8');
		$this->load->helper('url');
		$this->load->helper('form');
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));

		$infos_insertion=$this->Modele_tranche->cloner_etape($pays,$magazine,$numero,$pos_relative,$etape_courante);
		
		$data = array(
				'infos_insertion'=>$infos_insertion
		);
		
		$this->load->view('insertview',$data);
		
	}
	
	function est_clonable($pays=null,$magazine=null,$numeros=null) {
		if (in_array(null,array($pays,$magazine,$numeros))) {
			$this->load->view('errorview',array('Erreur'=> 'Nombre d\'arguments insuffisant'));
			exit();
		}
		
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		$numeros_clonables = $this->Modele_tranche->get_numeros_clonables($pays,$magazine,explode(',',$numeros));
		
		$this->load->view('listergview', array(
			'liste'=>$numeros_clonables,
			'format'=>'json'
		));
	}
}

?>
