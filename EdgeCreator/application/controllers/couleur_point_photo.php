<?php

class Couleur_Point_Photo extends CI_Controller {
	
	function index($pays,$magazine,$numero,$frac_x,$frac_y) {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		echo $this->Modele_tranche->get_couleur_point_photo($pays,$magazine,$numero, $frac_x, $frac_y);
	}
}

?>
