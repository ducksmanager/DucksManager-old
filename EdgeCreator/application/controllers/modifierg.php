<?php
class ModifierG extends CI_Controller {
	static $pays;
	static $magazine;
	static $etape;
	static $numeros;
	static $nom_option;
	static $nouvelle_valeur;
	
	function index($pays=null,$magazine=null,$etape=null,$numeros,$nom_option,$nouvelle_valeur, $debut_plage,$fin_plage, $nom_nouvelle_fonction=null, $est_etape_temporaire=false) {
		try {
			$est_etape_temporaire=$est_etape_temporaire === 'true';
			$nouvelle_valeur=$nouvelle_valeur=='null' ? null : str_replace('[pt]','.',urldecode($nouvelle_valeur));
			if (in_array(null,array($pays,$magazine,$etape))) {
				$this->load->view('errorview',array('Erreur'=>'Nombre d\'arguments insuffisant'));
				exit();
			}
			self::$etape=$etape;
			self::$pays=$pays;
			self::$magazine=$magazine;
			self::$numeros=$numeros=explode('~',$numeros);
			self::$nom_option=$nom_option;
			self::$nouvelle_valeur=$nouvelle_valeur;
			$a=self::$nouvelle_valeur;
			
			$this->db->query('SET NAMES UTF8');
			$this->load->helper('url');
			$this->load->helper('form');
			
			$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
			$data=array();
			
			$privilege=$this->Modele_tranche->get_privilege();
			if ($privilege == 'Affichage') {
				$this->load->view('errorview',array('Erreur'=>'droits insuffisants'));
				return;
			}
			$this->Modele_tranche->setUsername($this->session->userdata('user'));
			
			$numeros_dispos=$this->Modele_tranche->get_numeros_disponibles(self::$pays,self::$magazine);
			$this->Modele_tranche->setNumerosDisponibles($numeros_dispos);
			$this->Modele_tranche->setPays(self::$pays);
			$this->Modele_tranche->setMagazine(self::$magazine);
	
			if ($est_etape_temporaire) {
				$this->Modele_tranche->dupliquer_modele_magazine_si_besoin($pays,$magazine);
				$this->Modele_tranche->decaler_etapes_a_partir_de(self::$pays,self::$magazine,self::$etape);
				$this->Modele_tranche->insert_ordre(self::$pays,self::$magazine,self::$etape,self::$numeros[0],self::$numeros[count(self::$numeros)-1],$nom_nouvelle_fonction,array());
			}
			$valeurs=array();
			$fonction=$this->Modele_tranche->get_fonction(self::$pays,self::$magazine,self::$etape);
			if (!is_null($fonction)) {
				$options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,self::$etape,$fonction->Nom_fonction);
	
				if ($nom_option == 'Actif') {
					$intervalles=array();
					$numeros_debut=explode(';',$fonction->Numero_debut);
					$numeros_fin=explode(';',$fonction->Numero_fin);
					foreach($numeros_debut as $i=>$numero_debut)
						$intervalles[]=$numero_debut.'~'.$numeros_fin[$i];
					$intervalles=implode(';',$intervalles);
					$valeurs_preexistantes=array($intervalles=>'on');
				}
				else {
					if (is_array($options->$nom_option))
						$valeurs_preexistantes=$options->$nom_option;
					else {
						if (is_null($options->$nom_option))
							$valeurs_preexistantes=array();
						else
							$valeurs_preexistantes=array('Tous'=>$options->$nom_option);
					}
				}
				
				$prop_valeurs_defaut=new ReflectionProperty($fonction->Nom_fonction, 'valeurs_defaut');
				$valeurs_defaut=$prop_valeurs_defaut->getValue();
				
				foreach($valeurs_preexistantes as $intervalles=>$valeur) {
					$liste_intervalles=explode(';',$intervalles);
					foreach($numeros_dispos as $numero_dispo) {
						if ($numero_dispo == 'Aucun')
							continue;
						foreach($liste_intervalles as $i=>$intervalle) {
							if (est_dans_intervalle($numero_dispo,$intervalle))
								$valeurs[$numero_dispo]=$valeur;
						}
						if (!array_key_exists($numero_dispo, $valeurs) && array_key_exists($nom_option, $valeurs_defaut))
							$valeurs[$numero_dispo]=$valeurs_defaut[$nom_option];
					}
				}
			}
			else {
				$fonction=new stdClass();
				$fonction->Nom_fonction='Dimensions';
			}
	
			
			foreach($numeros as $numero) {
				if ($nom_option == 'Actif' && (is_null(self::$nouvelle_valeur) || empty(self::$nouvelle_valeur))) {
					if (array_key_exists($numero, $valeurs))
						unset ($valeurs[$numero]);
				}
				else
					$valeurs[$numero]=self::$nouvelle_valeur;
			}
			$valeurs_distinctes=array_unique($valeurs);
			$valeurs_distinctes_numeros_groupes=array();
			foreach($valeurs_distinctes as $valeur_distincte) {
				$numeros_associes=array_value_list($valeur_distincte, $valeurs);
				sort($numeros_associes);
				print_r($numeros_associes);
				$valeurs_distinctes_numeros_groupes[$valeur_distincte]=array();
				$numero_debut=null;
				$i=0;
				foreach($numeros_dispos as $numero_dispo) {
					echo $numero_dispo.' - '.$i."\n";
					if (is_null($numero_debut)) {
						if (!array_key_exists($i,$numeros_associes) || $numero_dispo != $numeros_associes[$i])
							continue;
						$numero_debut=$numero_fin=$numeros_associes[$i];
					}
					else {
						if (!array_key_exists($i,$numeros_associes) || $numero_dispo != $numeros_associes[$i]) {
							$valeurs_distinctes_numeros_groupes[$valeur_distincte][]=$numero_debut.'~'.$numero_fin;
							$numero_debut=null;
							continue;
						}
						else
							$numero_fin=$numeros_associes[$i];
					}
					$i++;
				}
				if (!is_null($numero_debut))
					$valeurs_distinctes_numeros_groupes[$valeur_distincte][]=$numero_debut.'~'.$numero_fin;
			}
			
			$this->Modele_tranche->dupliquer_modele_magazine_si_besoin($pays,$magazine);
	
			if (!$est_etape_temporaire)
				$this->Modele_tranche->delete_option(self::$pays,self::$magazine,self::$etape,self::$nom_option);
	
			foreach($valeurs_distinctes_numeros_groupes as $valeur=>$intervalles) {
				$id_valeur=$this->Modele_tranche->get_id_valeur_max()+1;
				foreach($intervalles as $intervalle) {
					list($numero_debut,$numero_fin)=explode('~',$intervalle);
					$this->Modele_tranche->insert_valeur_option(self::$pays,self::$magazine,self::$etape,$fonction->Nom_fonction,self::$nom_option,
																$valeur,$numero_debut,$numero_fin,$id_valeur);
				}
			}
			$this->load->view('parametragegview',$data);
		}
		catch(Exception $e) {
	    	echo 'Exception reçue : ',  $e->getMessage(), "\n";
	    	echo '<pre>';print_r($e->getTrace());echo '</pre>';
		}
	}
}

function array_value_list ($match, $array)
{
	$occurences = array();
	foreach ($array as $key => $value) {
		if ($value == $match)
			$occurences[]=$key;
	}

	return $occurences;
}
?>
