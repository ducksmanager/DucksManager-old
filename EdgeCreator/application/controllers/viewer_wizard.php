<?php

class Viewer_wizard extends CI_Controller {
	static $image;
	static $largeur;
	static $hauteur;
	static $pays;
	static $magazine;
	static $numero;
	static $random_id;
	static $parametrage;
	static $fond_noir;
	static $zoom;
	static $etapes_actives=array();
	static $is_debug=false;
	static $etape_en_cours;
	
	function index($pays=null,$magazine=null,$numero=null,$zoom=1,$etapes_actives='1',$parametrage='',$save='false',$fond_noir=false,$random_ou_username=null,$debug=false) {
		if ($etapes_actives=='final')
			$etapes_actives='all';
		if ($etapes_actives=='all') {
			preg_match('#^([0-9]+)\.#is',$parametrage,$matches_num_etape_parametrage);
			if (count($matches_num_etape_parametrage) == 0)
				$num_etape_parametrage=null;
			else {
				$num_etape_parametrage=$matches_num_etape_parametrage[1];
				$parametrage=substr($parametrage,strlen($num_etape_parametrage)+1,strlen($parametrage));
			}
		}
		parse_str($parametrage,$parametrage);
		$fond_noir = $fond_noir == 'true';
		if ($save==='save')
			$zoom=1.5;
		self::$is_debug=$debug;
		self::$zoom=$zoom;
		$this->load->library('email');
		$this->load->helper('url');
		$session_id = $this->session->userdata('session_id');
		
		
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		
		$privilege=$this->Modele_tranche->get_privilege();
		
		if (is_null($pays) || is_null($magazine)) {
			$this->load->view('errorview',array('Erreur'=>'Nombre d\'arguments insuffisant'));
			exit();
		}
		else {
			 if (is_null($numero)) {
				 header('Content-type: image/png');
				 self::$image=imagecreatetruecolor(1, 1);
				 imagepng(self::$image);
				 exit();
			 }
			 elseif($numero=='Aucun') {
				 $largeur=20;
				 $hauteur=250;
				 self::$image=imagecreatetruecolor(z($largeur), z($hauteur));
				 $blanc=imagecolorallocate(self::$image, 255,255,255);
				 imagefill(self::$image,0,0,$blanc);
				 $noir=imagecolorallocate(self::$image, 0,0,0);
				 imagettftext(self::$image,z(10),-90,
							  z(5),z(5),
							  $noir,BASEPATH.'fonts/Arial.TTF','Aucun numero selectionne');
				 $dimensions=new stdClass();
				 $dimensions->Dimension_x=$largeur;
				 $dimensions->Dimension_y=$hauteur;
				 new Dessiner_contour($dimensions);
				 
				 header('Content-type: image/png');
				 imagepng(self::$image);
				 exit();
			 }
		}
		self::$pays=$pays;
		self::$magazine=$magazine;
		self::$random_id=$random_ou_username;
		$this->Modele_tranche->setPays(self::$pays);
		$this->Modele_tranche->setMagazine(self::$magazine);
		$this->Modele_tranche->setRandomId($random_ou_username);
		if (strpos($save,'integrate') !== false) {
			$username_modele=substr($save,strlen('integrate_'));
			$this->Modele_tranche->setUsername($username_modele);
		}
		else
			$this->Modele_tranche->setUsername($this->session->userdata('user'));
		self::$numero=$numero;
		self::$parametrage=$parametrage;
		self::$fond_noir=$fond_noir;
		self::$etapes_actives=explode('-', $etapes_actives);
		
		$num_ordres=$this->Modele_tranche->get_ordres($pays,$magazine,$numero);
		//print_r($ordres);
		$dimensions=array();
		self::$etape_en_cours=new stdClass();
		
		
		$num_ordre=-2;
		$fond_noir_fait=false;
		$options_preview=array();
		try {
		foreach($num_ordres as $num_ordre) {
			if ($num_ordre>-1 && $fond_noir && !$fond_noir_fait) {
				$options=new stdClass();
				$options->Pos_x=$options->Pos_y=0;
				$options->Couleur='000000';
				new Remplir($options);
				$fond_noir_fait=true;
			}
				
			if ($num_ordre<0 || in_array($num_ordre,self::$etapes_actives) || self::$etapes_actives==array('all')) {
				$ordres[$num_ordre]=$this->Modele_tranche->get_fonction($pays,$magazine,$num_ordre,$numero);
				self::$etape_en_cours->num_etape=$num_ordre;
				self::$etape_en_cours->nom_fonction=$ordres[$num_ordre]->Nom_fonction;
				$fonction=$ordres[$num_ordre];
				$options2=$this->Modele_tranche->get_options($pays,$magazine,$num_ordre,self::$numero,$fonction->Nom_fonction,false);
				if ($num_ordre==-1)
					$dimensions=$options2;
				if ((self::$etapes_actives==array('all') && ($num_etape_parametrage == $num_ordre || is_null($num_etape_parametrage)))
				 || self::$etapes_actives!=array('all')) {
					foreach(self::$parametrage as $parametre=>$valeur) {
						$options2->$parametre=$valeur;
					}
				}
				new $ordres[$num_ordre]->Nom_fonction(clone $options2);
				$options_preview[$num_ordre]=$options2;
			}
		}
		}
		catch(Exception $e) {
	    	echo 'Exception reçue : ',  $e->getMessage(), "\n";
	    	echo '<pre>';print_r($e->getTrace());echo '</pre>';
		}
		// Nouvelles étapes
		/*foreach(self::$parametrage as $parametres=>$options) {
			list($num_ordre_param_ajout,$nom_fonction_param)=explode('~', $parametres);
			self::$etape_en_cours->num_etape=$num_ordre_param_ajout;
			self::$etape_en_cours->nom_fonction=$nom_fonction_param;
			if ($num_ordre_param_ajout > $num_ordre && is_array($options)) { // Numéro d'étape supérieure à la maximale existante
				foreach($options as $option_nom=>$option_valeur) {
					$ordres[$num_ordre_param_ajout][0]->options->$option_nom=urldecode(str_replace('^','%',
											   str_replace('!amp!','&',
											   str_replace('!slash!','/',
											   str_replace('!sharp!','#',$option_valeur)))));
				}
				if (isset($ordres[$num_ordre_param_ajout][0]->options))
					new $nom_fonction_param($ordres[$num_ordre_param_ajout][0]->options);
			}
		}*/
		new Dessiner_contour($dimensions);
		
		
		if (strpos($save,'integrate') !== false && $privilege == 'Admin' && self::$is_debug!==false) {
			$data = array(
				'pays'=>$pays,
				'magazine'=>$magazine,
				'numero'=>$numero,
				'options'=>$options_preview,
				'username'=>$username_modele
			);
			$this->load->view('integrateview',$data);
		}
		
		if ($save=='save' && $zoom==1.5) {
			if($privilege == 'Edition') {
				ob_start();
				print_r($options_preview);
				$affichage_options=ob_get_contents();
				ob_end_clean();
				
				$this->email->from(get_admin_email(), 'DucksManager - '.$username_modele);
				$this->email->to(get_admin_email());
				
				$this->email->subject('Proposition de modele de tranche de '.$username_modele);
				$this->email->message($affichage_options);
				$this->email->attach($nom_image);
				$this->email->send();
				$this->email->print_debugger();
			}
			else {
				echo 'Vous n\'avez pas les privil&egrave;ges n&eacute;cessaires pour cette op&eacute;ration';
			}
		}
		
		$this->Modele_tranche->rendu_image();
	}
}

?>
