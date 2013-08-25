<?php
class Numerosdispos extends CI_Controller {
	
	function index($pays=null,$magazine=null,$get_tranches_non_pretes=false) {
		if ($pays == 'null') $pays = null;
		if ($magazine == 'null') $magazine = null;
		$get_tranches_non_pretes = $get_tranches_non_pretes === 'true';
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		if ($pays == null) {
			if ($get_tranches_non_pretes) {
				$data=array('mode'=>'get_tranches_non_pretes');
				$data['tranches_non_pretes']=$this->Modele_tranche->get_tranches_non_pretes();
			}
			else {
				$data=array('mode'=>'get_pays');
				$pays=$this->Modele_tranche->get_pays();
				$data['pays']=$pays;
			}			
		}
		else if ($magazine == null) {
			$data=array('mode'=>'get_magazines');
			$magazines=$this->Modele_tranche->get_magazines($pays);
			$data['magazines']=$magazines;
			
		}
		else {
			$data=array('mode'=>'get_numeros');
			list($numeros_dispos,$tranches_pretes)=$this->Modele_tranche->get_numeros_disponibles($pays,$magazine,true);
		
			$nb_etapes=$this->Modele_tranche->get_nb_etapes($pays,$magazine);
	
			list($noms_complets_pays, $noms_complets_magazines) = Inducks::get_noms_complets(array($pays.'/'.$magazine));
						
			$data['numeros_dispos']=$numeros_dispos;
			$data['tranches_pretes']=$tranches_pretes;
			$data['nb_etapes']=$nb_etapes;
			$data['nom_magazine']=$noms_complets_pays[$pays].' ('.$noms_complets_magazines[$pays.'/'.$magazine].')';
	
		}
		$this->load->view('numerosdisposview',$data);
	}
}

?>
