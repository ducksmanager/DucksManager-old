<?php

class Check_Logged_In extends CI_Controller {
	
	function index() {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$user=$this->session->userdata('user');
		echo isset($user) && $user!=='demo' ? 1 : 0;
	}
}

?>
