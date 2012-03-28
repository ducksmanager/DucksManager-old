<?php
class Helper extends CI_Controller {
	
	function index($nom=null) {
		
		if (in_array(null,array($nom))) {
			$this->load->view('errorview',array('Erreur'=>'Nombre d\'arguments insuffisant'));
			exit();
		}
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		
		$privilege=$this->Modele_tranche->get_privilege();
		
		//echo '<div style="display:none">'.$this->session->userdata('user').','.$this->session->userdata('pass').'</div>';
		if ($privilege == 'Affichage') {
			echo 'Vous n\'avez pas les permissions suffisantes pour r&eacute;aliser cette action !';
			return;
		}
		
		ob_start();
		include_once('helpers/'.$nom);
		$contenu=ob_get_clean();
		
		$data=array('contenu'=>$contenu);
		
		$this->load->view('helperview',$data);
		
	}
}

?>
