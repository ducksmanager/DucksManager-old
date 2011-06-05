<?php
class Numerosdispos extends CI_Controller {
	
	function index($pays=null,$magazine=null) {
		
		$this->load->database();
		$this->load->library('session');
		$this->load->model('Modele_tranche');
		
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		if ($pays == null) {
			$data=array('mode'=>'get_pays');
			$pays=$this->Modele_tranche->get_pays();
			$data['pays']=$pays;
		}
		else if ($magazine == null) {
			$data=array('mode'=>'get_magazines');
			$magazines=$this->Modele_tranche->get_magazines($pays);
			$data['magazines']=array_merge(array(''=>''),$magazines);
			
		}
		else {
			$data=array('mode'=>'get_numeros');
			list($numeros_dispos,$tranches_pretes)=$this->Modele_tranche->get_numeros_disponibles($pays,$magazine,true);
		
			$nb_etapes=$this->Modele_tranche->get_nb_etapes($pays,$magazine);
	
			$requete_nom_magazine='SELECT NomComplet FROM magazines WHERE PaysAbrege LIKE \''.$pays.'\' AND (NomAbrege LIKE \''.$magazine.'\' OR RedirigeDepuis LIKE \''.$magazine.'\')';
			$resultat_nom_magazine=$this->Modele_tranche->db->query($requete_nom_magazine)->result();
			$requete_nom_pays='SELECT NomComplet FROM pays WHERE NomAbrege LIKE \''.$pays.'\' AND L10n LIKE \''.$_SESSION['lang'].'\'';
			$resultat_nom_pays=$this->Modele_tranche->db->query($requete_nom_pays)->result();
	
			
			$data['numeros_dispos']=$numeros_dispos;
			$data['tranches_pretes']=$tranches_pretes;
			$data['nb_etapes']=$nb_etapes;
			$data['nom_magazine']=$resultat_nom_magazine[0]->NomComplet.' ('.$resultat_nom_pays[0]->NomComplet.')';
	
		}
		$this->load->view('numerosdisposview',$data);
	}
}

?>
