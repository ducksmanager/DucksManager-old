<?php
class Helper extends CI_Controller {
	
	function index($nom=null) {
		
		if (in_array(null,array($nom))) {
			echo 'Erreur : Nombre d\'arguments insuffisant';
			exit();
		}
		ob_start();
		include_once('helpers/'.$nom);
		$contenu=ob_get_clean();
		
		$data=array('contenu'=>$contenu);
		
		$this->load->view('helperview',$data);
		
	}
}

?>
