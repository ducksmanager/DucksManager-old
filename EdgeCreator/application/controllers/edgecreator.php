<?php
class EdgeCreator extends CI_Controller {
	static $pays;
	static $magazine;
	
	function index($pays=null,$magazine=null)
	{
		self::$pays=$pays;
		self::$magazine=$magazine;
		$this->load->library('session');
		$this->load->database();
		$this->db->query('SET NAMES UTF8');
		$this->load->helper('form');
		$this->load->helper('url');

		if (!is_null($pays) && !is_null($magazine)) {
			$this->session->set_userdata(array('pays'=>self::$pays));
			$this->session->set_userdata(array('magazine'=>self::$magazine));
		}
		if ($this->session->userdata('pays')!==false && $this->session->userdata('magazine')!==false) {
			self::$pays=$this->session->userdata('pays');
			self::$magazine=$this->session->userdata('magazine');
		}

		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		$this->Modele_tranche->setPays(self::$pays);
		$this->Modele_tranche->setMagazine(self::$magazine);
		$num_ordres=$this->Modele_tranche->get_ordres(self::$pays,self::$magazine);
		//print_r($etapes);
		$etapes=array();
		foreach($num_ordres as $num_ordre) {
			$etapes[$num_ordre]=$this->Modele_tranche->get_fonctions(self::$pays,self::$magazine,$num_ordre);
			foreach($etapes[$num_ordre] as &$fonction) {
				$fonction->options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,$num_ordre,$fonction->Nom_fonction);
			}
		}

		$numeros_dispos=$this->Modele_tranche->get_numeros_disponibles(self::$pays,self::$magazine);
		$liste_fonctions=array('Dimensions','Remplir','Agrafer','TexteTTF','TexteMyFonts','Image','Polygone','Degrade','DegradeTrancheAgrafee','Rectangle','Arc_cercle');
		sort($liste_fonctions);
		$data = array(
				'title' => 'EdgeCreator',
				'pays' => self::$pays,
				'magazine'=>self::$magazine,
				'etapes'=>$etapes,
				'texte'=>'',
				'liste_fonctions'=>form_dropdown('nouvelle_fonction',$liste_fonctions,count($etapes) == 0 ? array_search('Dimensions', $liste_fonctions)  : null,'id="nouvelle_fonction"')
		);
		$this->session->set_userdata(array('zoom'=>1));

		if (false!==$this->input->post('zoom')) {
			$this->session->set_userdata(array('zoom'=>$this->input->post('zoom')));
		}
		$this->session->set_userdata(array('preview'=>'Aucun'));

		if (false!==$this->input->post('preview_issue')) {
			$this->session->set_userdata(array('preview_issue'=>$this->input->post('preview_issue')));
		}
		$data['numeros_preview']=form_dropdown('preview_issue', $numeros_dispos, '[Aucun]','id="preview_issue"');
		$data['numeros_debut_gen']=form_dropdown('preview_issue', $numeros_dispos, '[Aucun]','id="first_issue"');
		$data['numeros_fin_gen']=form_dropdown('preview_issue', $numeros_dispos, '[Aucun]','id="last_issue"');
		
		$data['numeros_visualisables1_select']=form_dropdown('viewable_issue1', $numeros_dispos, null);
		$data['numeros_visualisables2_select']=form_dropdown('viewable_issue2', $numeros_dispos, null);

		$data['numeros_extension1_select']=form_dropdown('extension1', $numeros_dispos, end($numeros_dispos),'id="extension1"');
		$data['numeros_extension2_select']=form_dropdown('extension2', $numeros_dispos, end($numeros_dispos),'id="extension2"');
		
		$num_etapes=array();
		foreach(array_keys($etapes) as $num_etape) {
			if ($num_etape != -1)
				$num_etapes[$num_etape]=$num_etape;
			$derniere_etape=$num_etape;
		}
		$data['etapes_clonables']=count($num_etapes) == 0 ? '' : form_dropdown('etapes_clonables', $num_etapes, $num_etape);
		
		$data['zoom']=form_dropdown('zoom', array('1'=>1,'1.5'=>1.5,'2'=>2,'4'=>4,'8'=>8), 1.5,'id="zoom"')
							 .'<br /><br />';
		$data['preview_form']='Num&eacute;ro de pr&eacute;visualisation : '
							 .$data['numeros_preview']
							 .'<br />'
							 .'<button onClick="reload_preview()">OK</button>';
		$data['gen_form']='D&eacute;but : '
						 .$data['numeros_debut_gen']
						 .'<br />'
						 .'Fin : '
						 .$data['numeros_fin_gen']
						 .'<br />'
						 .'<button onClick="reload_gen()">OK</button>';
		/*
		$data['texte'].=form_open('edgecreator');
		$input = array(
		  'name'		=> 'dimension_w',
		  'id'		  => 'dimension_w',
		  'value'	   => $this->session->userdata('dimension_w'),
		  'maxlength'   => '2',
		  'size'		=> '2'
		);
		$data['texte'].=form_input($input).'mm x ';
		$input = array(
		  'name'		=> 'dimension_h',
		  'id'		  => 'dimension_h',
		  'value'	   => $this->session->userdata('dimension_h'),
		  'maxlength'   => '3',
		  'size'		=> '3'
		);
		$data['texte'].=form_input($input).'mm';
		$data['texte'].=form_submit('', 'OK');*/

		$this->load->view('headerview',$data);
		$this->load->view('preview',$data);
		$this->load->view('edgecreatorview',$data);
		$this->load->view('footerview',$data);
	}

	function _private() {

	}
}
?>