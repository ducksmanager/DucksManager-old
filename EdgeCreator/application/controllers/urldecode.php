<?php
class UrlDecode extends CI_Controller {
	function index() {
		$this->load->library('session');
		$this->load->database();
		$this->db->query('SET NAMES UTF8');
		$this->load->helper('url');
		
		
		$this->load->model('Modele_tranche');
		
		$privilege=$this->Modele_tranche->urldecode();
		
	}
}
?>