<?php

class Upload_Wizard extends CI_Controller {
	
	function index($pays,$magazine,$numero) {
		@mkdir('../edges/'.$pays.'/photos',0777,true);
		echo file_exists('../edges/'.$pays.'/photos/'.$magazine.'.'.$numero.'.jpg') ? 'true' : 'false';
	}
}

?>
