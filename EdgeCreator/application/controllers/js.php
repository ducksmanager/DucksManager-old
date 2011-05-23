<?php
class JS extends CI_Controller {
	
	function index($nom1='',$nom2='') {
		
		$str=file_get_contents(getcwd().'/helpers/'.$nom1.(empty($nom2)?'':'/'.$nom2));
		$data=array('contenu'=>$str);
		
		$this->load->view('jsview',$data);
		
	}
}

?>
