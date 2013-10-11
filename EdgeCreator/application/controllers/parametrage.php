<?php
class Parametrage extends CI_Controller {
	static $pays;
	static $magazine;
	static $ordre;
	static $numero_debut;
	static $numero_fin;
	static $nom_fonction;
	
	function index($pays=null,$magazine=null,$ordre=null,$numero_debut=1,$numero_fin=1,$nom_fonction=null,$parametrage='',$appliquer=false) {
		
		if (in_array(null,array($pays,$magazine,$ordre,$nom_fonction))) {
			echo 'Erreur : Nombre d\'arguments insuffisant';
			exit();
		}
		self::$pays=$pays;
		self::$magazine=$magazine;
		self::$ordre=$ordre;
		self::$numero_debut=$numero_debut;
		self::$numero_fin=$numero_fin;
		self::$nom_fonction=$nom_fonction;
		
		$this->load->library('session');
		$this->load->database();
		$this->db->query('SET NAMES UTF8');
		$this->load->helper('url');
		$this->load->helper('form');
		
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
		$numeros_dispos=$this->Modele_tranche->get_numeros_disponibles(self::$pays,self::$magazine);
		$this->Modele_tranche->setNumerosDisponibles($numeros_dispos);
		if ($appliquer) {
			$parametrage=json_decode($parametrage);
			$parametrage_f=array();
			foreach($parametrage as $ordre_fonction=>$options) {
				list($ordre,$nom_fonction,$numero_debut,$numero_fin)=explode('~',$ordre_fonction);
				$parametrage_f[$ordre_fonction]=array();
				foreach($options as $option_nom_intervalle=>$option_valeur) {
					$parametrage_f[$ordre_fonction][$option_nom_intervalle]=urldecode(str_replace('^','%',$option_valeur));
				}
				$this->Modele_tranche->update_ordre($pays,$magazine,$ordre,$numero_debut,$numero_fin,$nom_fonction,$parametrage_f[$ordre_fonction]);
			}
		}
		else {
			$this->Modele_tranche->setPays(self::$pays);
			$this->Modele_tranche->setMagazine(self::$magazine);
			$this->Modele_tranche->setNumeroDebut(self::$numero_debut);
			$this->Modele_tranche->setNumeroFin(self::$numero_fin);
			//print_r($ordres);
			$fonctions=$this->Modele_tranche->get_fonctions($pays,$magazine,$ordre);
			$fonction=$fonctions[0];
			$fonction->options=$this->Modele_tranche->get_options($pays,$magazine,$ordre,$nom_fonction);
			
			$numeros_dispos=$this->Modele_tranche->get_numeros_disponibles(self::$pays,self::$magazine,$fonction->Numero_debut,$fonction->Numero_fin);
			$this->Modele_tranche->setDropdownNumeros(form_dropdown('', $numeros_dispos));
			$numeros_debut_globaux=$numeros_fin_globaux=array();
			$numeros_debut=explode(';',$fonction->Numero_debut);
			$numeros_fin=explode(';',$fonction->Numero_fin);
			foreach($numeros_debut as $i=>$numero_debut) {
				$numero_fin=$numeros_fin[$i];
				$numeros_debut_globaux[]=$this->Modele_tranche->setDropdownNumerosSelected($numero_debut,$this->Modele_tranche->setDropdownNumerosId('numero_debut'.$i));
				$numeros_fin_globaux[]=$this->Modele_tranche->setDropdownNumerosSelected($numero_fin,$this->Modele_tranche->setDropdownNumerosId('numero_fin'.$i));
			}
			$data = array(
					'fonction'=>$fonction,
					'options'=>$fonction->options,
					'intervalle'=>$fonction->getIntervalle($fonction->Numero_debut, $fonction->Numero_fin),
					'numeros_debut_globaux'=>$numeros_debut_globaux,
					'numeros_fin_globaux'=>$numeros_fin_globaux,
			);

			$this->load->view('parametrageview',$data);
		}
	}
}

?>
