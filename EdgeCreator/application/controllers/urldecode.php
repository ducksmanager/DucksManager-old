<?php
class UrlDecode extends CI_Controller {
	function index() {
		
		$this->db->query('SET NAMES UTF8');
		$this->load->helper('url');
		
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		$privilege=$this->Modele_tranche->urldecode();
		
	}
}
?>