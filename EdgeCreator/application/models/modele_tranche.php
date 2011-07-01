<?php
include_once(BASEPATH.'/../../Inducks.class.php');
Inducks::$use_db=true;
Inducks::$use_local_db=strpos($_SERVER['SERVER_ADDR'],'localhost') === false;
		
class Modele_tranche extends CI_Model {
	static $id_session;
	static $pays;
	static $magazine;
	static $username;
	static $numero_debut;
	static $numero_fin;
	static $numeros_dispos;
	static $dropdown_numeros;
	static $fields;
	static $user_possede_modele=null;

	function Modele_tranche($tab=array())
	{
		foreach($tab as $arg_name=>$arg_value)
			$this->$arg_name=$arg_value;
		parent::__construct();
		$_SESSION['lang']='fr';
	}
	
	function get_privilege() {
		$privilege=null;
		if (isset($_POST['user'])) {
			if (!is_null($privilege = $this->user_connects($_POST['user'],$_POST['pass'])))
				$this->creer_id_session($_POST['user'],md5($_POST['pass']));
		}
		else {
			if ($this->session->userdata('user') !== false && $this->session->userdata('pass') !== false) {
				$privilege = $this->user_connects($this->session->userdata('user'),$this->session->userdata('pass'));
				if ($privilege == null) {
					$this->creer_id_session($this->session->userdata('user'),$this->session->userdata('pass'));
				}
			}
		}
		return $privilege;
	}
	
	function user_connects($user,$pass) {
		global $erreur;
		if (!$this->user_exists($user)) {
			$erreur = 'Cet utilisateur n\'existe pas';
			return false;
		}
		else {
			$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\') AND (password LIKE \''.$pass.'\' OR md5(password) LIKE \''.$pass.'\')';
			$resultat=$this->db->query($requete);
			if ($resultat->num_rows==0) {
				$erreur = 'Identifiants invalides !';
				return null;
			}
			else {
				$requete='SELECT privilege FROM edgecreator_droits WHERE username LIKE(\''.$user.'\')';
				$resultat= $this->db->query($requete);
				if ($resultat->row()==null) {
					return 'Affichage';
				}
				return $resultat->row()->privilege;
			}
		}
	}
	
	function username_to_id($username) {
		$requete='SELECT ID FROM users WHERE username LIKE \''.$username.'\'';
		$resultat=$this->db->query($requete);
		return $resultat->row()->ID;
	}

	function user_exists($user) {
		$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\')';
		return ($this->db->query($requete)->num_rows > 0);
	}
	
	
	function creer_id_session($user,$pass) {
		
		$this->session->set_userdata(array('user' => $user, 'pass' => $pass));
	}
	
	function user_possede_modele($pays=null,$magazine=null,$username=null) {
		if (is_null($pays)) $pays=self::$pays;
		if (is_null($magazine)) $magazine=self::$magazine;
		if (is_null($username)) $username=self::$username;
		if (is_null(self::$user_possede_modele)) {
			$requete_modele_magazine_existe='SELECT Count(1) AS cpt FROM edgecreator_modeles2 '
										   .'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
										   .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
										   .'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND username LIKE \''.$username.'\'';
			$user_possede_modele = $this->db->query($requete_modele_magazine_existe)->first_row()->cpt > 0;
		}
		return $user_possede_modele;
	}

	function dupliquer_modele_magazine_si_besoin($pays,$magazine) {
		if (!$this->user_possede_modele($pays,$magazine,self::$username)) {
			$options=$this->get_modeles_magazine($pays,$magazine);
			$ordre_courant=null;
			$nom_option_courant=null;
			$valeur_option_courante=null;
			foreach($options as $option) {
				$this->insert($option->Pays,$option->Magazine,$option->Ordre,$option->Nom_fonction,$option->Option_nom,$option->Option_valeur,$option->Numero_debut,$option->Numero_fin,self::$username,null);
				
			}
		}
	}
	
	function get_modeles_magazine($pays,$magazine,$ordre=null)
	{
		$resultats_o=array();
		$requete='SELECT '.implode(', ', self::$fields).' '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
				.'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
				.'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' '
				.'AND username LIKE \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\' ';
		if (!is_null($ordre))
			$requete.='AND Ordre='.$ordre.' ';
		$requete.='ORDER BY Ordre';
		$query = $this->db->query($requete);
		$resultats=$query->result();
		foreach($resultats as $resultat)
			$resultats_o[]=new Modele_tranche ($resultat);
		return $resultats_o;
	}

	function get_ordres($pays,$magazine,$numero=null) {
		$resultats_ordres=array();
		$requete='SELECT DISTINCT Ordre, Numero_debut, Numero_fin '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
			    .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
			    .'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' '
				.'AND username LIKE \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\' '
				.'ORDER BY Ordre';
		$query = $this->db->query($requete);
		$resultats=$query->result();
		foreach($resultats as $resultat) {
			if (!is_null($numero)) {
				$numeros_debut=explode(';',$resultat->Numero_debut);
				$numeros_fin=explode(';',$resultat->Numero_fin);
				foreach($numeros_debut as $i=>$numero_debut) {
					$numero_fin=$numeros_fin[$i];
					$intervalle=$this->getIntervalleShort($this->getIntervalle($numero_debut, $numero_fin));
					if (!est_dans_intervalle($numero, $intervalle))
						continue;
				}

			}
			$resultats_ordres[]=$resultat->Ordre;
		}
		$resultats_ordres=array_unique($resultats_ordres);
		return $resultats_ordres;
	}

	function get_nb_etapes($pays,$magazine) {
		$resultats_etapes=array();
		$requete='SELECT Count(Nom_fonction) AS cpt '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
			    .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
			    .'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Option_nom IS NULL '
				.'AND username LIKE \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\'';
		$query = $this->db->query($requete);
		$resultats=$query->result();
			
		foreach($resultats as $resultat) {
			return $resultat->cpt;
		}
	}

	function get_etapes_simple($pays,$magazine,$num_etape=null) {
		$resultats_etapes=array();
		$username=($this->user_possede_modele() ? self::$username : 'brunoperel');
		$requete='SELECT DISTINCT Ordre, Nom_fonction, edgecreator_valeurs.ID AS ID_Valeur '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
			    .'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Option_nom IS NULL '
				.'AND EXISTS (SELECT 1 FROM edgecreator_intervalles WHERE edgecreator_intervalles.ID_Valeur = edgecreator_valeurs.ID AND username LIKE \''.$username.'\') '
				.'GROUP BY Ordre ';
		if (!is_null($num_etape))
			$requete.='AND Ordre='.$num_etape.' ';
		$requete.=' ORDER BY Ordre';
		echo $requete;
		$resultats = $this->db->query($requete)->result();
		foreach($resultats as $resultat) {
			$resultat->Numero_debut=array();
			$resultat->Numero_fin=array();
			$requete_intervalles='SELECT Numero_debut, Numero_fin '
								.'FROM edgecreator_modeles2 '
								.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
								.'INNER JOIN edgecreator_intervalles ON edgecreator_intervalles.ID_Valeur = edgecreator_valeurs.ID '
			    				.'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Ordre='.$resultat->Ordre.' AND Option_nom IS NULL ';
			$resultats_intervalles = $this->db->query($requete_intervalles)->result();
			foreach($resultats_intervalles as $intervalle) {
				$resultat->Numero_debut[]=$intervalle->Numero_debut;
				$resultat->Numero_fin[]=$intervalle->Numero_fin;
			}
			$resultat->Numero_debut=implode(';',$resultat->Numero_debut);
			$resultat->Numero_fin=implode(';',$resultat->Numero_fin);
			$resultats_etapes[]=$resultat;
		}
		return $resultats_etapes;
	}

	function get_fonction($pays,$magazine,$ordre,$numero=null) {
		$resultats_fonctions=array();
		$requete='SELECT '.implode(', ', self::$fields).' '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
			    .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
				.'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Ordre='.$ordre.' AND Option_nom IS NULL '
				.'AND username LIKE \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\'';
		$query = $this->db->query($requete);
		$resultats=$query->result();
		if (count($resultats) == 0) {
			return null;
		}
		$numeros_debut=array();
		$numeros_fin=array();
		foreach($resultats as $resultat) {
			if (!is_null($numero)) {
				$numeros_debut[]=$resultat->Numero_debut;
				$numeros_fin[]=$resultat->Numero_fin;
				$intervalle=$this->getIntervalleShort($this->getIntervalle($resultat->Numero_debut, $resultat->Numero_fin));
				if (!est_dans_intervalle($numero, $intervalle))
					continue;
			}
		}
		$resultat_tous_intervalles=$resultat;
		$resultat_tous_intervalles->Numero_debut=implode(';',$numeros_debut);
		$resultat_tous_intervalles->Numero_fin=implode(';',$numeros_fin);
		
		return new Fonction($resultat_tous_intervalles);
	}

	function get_options($pays,$magazine,$ordre,$nom_fonction,$numero=null,$creation=false,$inclure_infos_options=false, $nouvelle_etape=false) {
		$creation=false;
		$resultats_options=new stdClass();
		$requete='SELECT '.implode(', ', self::$fields).' '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
			    .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
				.'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Ordre='.$ordre.' AND Option_nom IS NOT NULL '
				.'AND username LIKE \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\' ';
		if (!is_null($nom_fonction))
			$requete.='AND Nom_fonction LIKE \''.$nom_fonction.'\' ';
		$requete.='ORDER BY Option_nom ASC';
		
		$resultats=$this->db->query($requete)->result();
		$option_nom='';
		foreach($resultats as $resultat) {
			if ($option_nom!=$resultat->Option_nom) {
				$option_courante=array();
				if (!empty($option_nom) && is_null($numero))
					uksort($resultats_options->$option_nom,'trier_intervalles');
			}
			$nom_fonction=$resultat->Nom_fonction;
			$option_nom=$resultat->Option_nom;
			$numeros_debut=explode(';',$resultat->Numero_debut);
			$numeros_fin=explode(';',$resultat->Numero_fin);
			foreach($numeros_debut as $i=>$numero_debut) {
				$numero_fin=$numeros_fin[$i];
				$intervalle=$this->getIntervalleShort($this->getIntervalle($numero_debut, $numero_fin));
				if (est_dans_intervalle($numero, $intervalle)) {
					if (is_null($numero))
						$option_courante[$intervalle]=$resultat->Option_valeur;
					else
						$option_courante=$resultat->Option_valeur;
					continue;
				}
			}
			$resultats_options->$option_nom=$option_courante;
		}
		if (is_null($numero))
			if (isset($resultats_options->$option_nom))
				uksort($resultats_options->$option_nom,'trier_intervalles');
			
		$f=new $nom_fonction($resultats_options,false,$creation,!$nouvelle_etape); // Ajout des champs avec valeurs par défaut
		if ($inclure_infos_options) {
			$prop_champs=new ReflectionProperty(get_class($f), 'champs');
			$champs=$prop_champs->getValue();
			$prop_valeurs_defaut=new ReflectionProperty(get_class($f), 'valeurs_defaut');
			$valeurs_defaut=$prop_valeurs_defaut->getValue();
			$prop_descriptions=new ReflectionProperty(get_class($f), 'descriptions');
			$descriptions=$prop_descriptions->getValue();
			foreach($f->options as $nom_option=>$val) {
				$intervalles_option=$f->options->$nom_option;
				if (!is_array($intervalles_option))
					$intervalles_option=array(null=>$intervalles_option);
				$intervalles_option['type']=$champs[$nom_option];
				$intervalles_option['description']=isset($descriptions[$nom_option]) ? $descriptions[$nom_option] : '';
				if (array_key_exists($nom_option, $valeurs_defaut))
					$intervalles_option['valeur_defaut']=$valeurs_defaut[$nom_option];
				$f->options->$nom_option=$intervalles_option;
			}
		}
		return $f->options;
	}

	function get_noms_champs($nom_fonction) {
		$f=new $nom_fonction(null,false,false,false);
		$prop_champs=new ReflectionProperty(get_class($f), 'champs');
		$champs=$prop_champs->getValue();
		$prop_valeurs_defaut=new ReflectionProperty(get_class($f), 'valeurs_defaut');
		$valeurs_defaut=$prop_valeurs_defaut->getValue();
		$prop_descriptions=new ReflectionProperty(get_class($f), 'descriptions');
		$descriptions=$prop_descriptions->getValue();
		
		foreach($f->options as $nom_option=>$val) {
			$intervalles_option=$f->options->$nom_option;
			$intervalles_option['type']=$champs[$nom_option];
			$intervalles_option['description']=isset($descriptions[$nom_option]) ? $descriptions[$nom_option] : '';
			if (array_key_exists($nom_option, $valeurs_defaut))
				$intervalles_option['valeur_defaut']=$valeurs_defaut[$nom_option];
			$f->options->$nom_option=$intervalles_option;
		}
		return $f->options;
	}

	function has_no_option($pays,$magazine,$etape) {
		$requete='SELECT Option_nom '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
			    .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
				.'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Option_nom IS NOT NULL '
				.'AND username LIKE \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\'';
		return $this->db->query($requete)->num_rows() == 0;
	}

	function decaler_etapes_a_partir_de($pays,$magazine,$etape_debut) {
		$requete='SELECT Max(Ordre) AS max_ordre FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
			    .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
				.'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Ordre>='.$etape_debut.' AND username LIKE \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\' ';
		$resultat=$this->db->query($requete)->row(0);
		
		if (!is_null($resultat)) {
			$etape=$resultat->max_ordre;
			echo 'Decalage des etapes '.$etape_debut.' a '.$etape."\n";
			for ($i=$etape;$i>=$etape_debut;$i--) {
				$requete='UPDATE edgecreator_modeles2 '
						.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
					    .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
						.'SET Ordre='.($i+1).' '
						.'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Ordre='.$i.' AND username LIKE \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\'';
				$this->db->query($requete);
			}
		}
		else
			echo 'Pas de decalage'."\n";
		
	}
	
	function get_preview_existe($options_json) {
		$requete='SELECT ID_Preview FROM tranches_previews WHERE Options LIKE \''.$options_json.'\' AND ID_Session LIKE \''.self::$id_session.'\'';
		$resultat=$this->db->query($requete)->result();
		return count($resultat) > 0;
	}

	function ajouter_preview($options_json) {
		$requete='INSERT INTO tranches_previews(ID_Session,Options) VALUES (\''.self::$id_session.'\',\''.$options_json.'\')';
		$this->db->query($requete);
		$requete_get_id_preview='SELECT Max(ID_Preview) AS ID FROM tranches_previews';
		$resultats_get_id_preview=$this->db->query($requete_get_id_preview)->result();
		foreach($resultats_get_id_preview as $resultat)
			return $resultat->ID;
		return 1;
	}
	
	function sv_doublons($pays,$magazine) {
		$requete_suppression_existants='DELETE FROM tranches_doublons WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND username LIKE \''.self::$username.'\'';
		$this->db->query($requete_suppression_existants);
		self::$numeros_dispos=$this->get_numeros_disponibles($pays, $magazine);
		$numeros_disponibles=self::$numeros_dispos;
		unset ($numeros_disponibles['Aucun']);
		$etape=-1;
		$requete_get_etape_max='SELECT MAX(Ordre) AS max '
							  .'FROM edgecreator_modeles2 '
							  .'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
						      .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
						      .'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND username LIKE \''.self::$username.'\'';
		$resultat_get_etape_max=$this->db->query($requete_get_etape_max)->result();
		for($etape=-1;$etape<=$resultat_get_etape_max[0]->max;$etape++) {
			$requete_get_options='SELECT Numero_debut, Numero_fin, Option_nom, Option_valeur '
								.'FROM edgecreator_modeles2 '
								.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
							    .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
							    .'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Ordre='.$etape.' AND username LIKE \''.self::$username.'\'';
			$resultat_get_options=$this->db->query($requete_get_options)->result();
			foreach($resultat_get_options as $option) {
				foreach(array_keys($numeros_disponibles) as $numero) {
					$intervalle=$option->Numero_debut.'~'.$option->Numero_fin;
					if (est_dans_intervalle($numero,$intervalle)) {
						if (is_string($numeros_disponibles[$numero]))
							$numeros_disponibles[$numero]=array();
						$a=$numeros_disponibles[$numero];
						if (!array_key_exists($etape,$numeros_disponibles[$numero]))
							$numeros_disponibles[$numero][$etape]=array();
						$valeur=is_null($option->Option_valeur)?'null':$option->Option_valeur;
						//$valeur=Fonction_executable::toTemplatedString($valeur,false);
						$numeros_disponibles[$numero][$etape][is_null($option->Option_nom)?'null':$option->Option_nom]=$valeur;
					}
				}
			}
		}
		$groupes_numeros=array();
		foreach(array_keys($numeros_disponibles) as $numero) {
			$numeros_disponibles[$numero]=serialize($numeros_disponibles[$numero]);
		}
		foreach($numeros_disponibles as $numero=>$etapes_serialized) {
			if (!array_key_exists($etapes_serialized, $groupes_numeros))
				$groupes_numeros[$etapes_serialized]=array();
			$groupes_numeros[$etapes_serialized][]=$numero;
		}
		foreach($groupes_numeros as $groupe) {
			if (count($groupe) > 1) {
				$numero_reference=$groupe[0];
				for ($i=1;$i<count($groupe);$i++) {
					$numero=$groupe[$i];
					$requete='INSERT INTO tranches_doublons(Pays,Magazine,Numero,NumeroReference) '
							.'VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$numero.'\',\''.$numero_reference.'\')';
					$this->db->query($requete);
				}
			}
		}
		echo '<pre>';print_r($groupes_numeros);echo '</pre>';
	}
	
	function get_pays() {
		return Inducks::get_pays();
	}
	
	function get_magazines($pays) {
		return Inducks::get_liste_magazines($pays);
	}
	
	function get_numeros_disponibles($pays,$magazine,$get_prets=false) {
		$numeros_affiches=array('Aucun'=>'Aucun');
		if ($get_prets)
			$tranches_pretes=array();
		$numeros_soustitres=Inducks::get_numeros($pays, $magazine,false,true);
		$id_user=$this->username_to_id(self::$username);
		foreach($numeros_soustitres[0] as $i=>$numero) {
			$numero_affiche=str_replace("\n",'',str_replace('+','',$numero));
			$numeros_affiches[$numero_affiche]=$numero_affiche;

			if ($get_prets) {
				$requete_get_prets='SELECT issuenumber, createurs FROM tranches_pretes '
						.'WHERE publicationcode LIKE \''.$pays.'/'.$magazine.'\' AND replace(issuenumber,\' \',\'\') LIKE \''.$numero_affiche.'\'';
				$resultat_get_prets=$this->db->query($requete_get_prets)->result();
				if (count($resultat_get_prets) > 0) {
					$createurs=explode(';',$this->db->query($requete_get_prets)->row()->createurs);
					$tranches_pretes[$numero_affiche]=in_array($id_user,$createurs) ? 'par_moi' : 'global';
				}

			}
		}
		if ($get_prets) {
			return array($numeros_affiches, $tranches_pretes);
		}
		return $numeros_affiches;
	}
	
	function valeur_existe($id_valeur) {
		$requete='SELECT ID FROM edgecreator_valeurs WHERE ID='.$id_valeur;
		return $this->db->query($requete)->num_rows() > 0;
	}
	
	function insert($pays,$magazine,$etape,$nom_fonction,$option_nom,$option_valeur,$numero_debut,$numero_fin,$username,$id_valeur=null) {
		$option_nom=is_null($option_nom) ? 'NULL' : '\''.$option_nom.'\'';
		$option_valeur=is_null($option_valeur) ? 'NULL' : '\''.$option_valeur.'\'';
		
		$requete='INSERT INTO edgecreator_modeles2 (Pays,Magazine,Ordre,Nom_fonction,Option_nom) VALUES '
				.'(\''.$pays.'\',\''.$magazine.'\',\''.$etape.'\',\''.$nom_fonction.'\','.$option_nom.') ';
		echo $requete."\n";
		$this->db->query($requete);
		$id_option = $this->db->insert_id();
		
		if (is_null($id_valeur) || !$this->valeur_existe($id_valeur)) {
			if (is_null($id_valeur))
				$requete='INSERT INTO edgecreator_valeurs (Option_valeur,ID_Option) VALUES ('.$option_valeur.','.$id_option.')';
			else
				$requete='INSERT INTO edgecreator_valeurs (ID,Option_valeur,ID_Option) VALUES ('.$id_valeur.','.$option_valeur.','.$id_option.')';
				
			echo $requete."\n";
			$this->db->query($requete);
			$id_valeur = $this->db->insert_id();
		}
		$requete='INSERT INTO edgecreator_intervalles (ID_Valeur,Numero_debut,Numero_fin,username) VALUES ('.$id_valeur.',\''.$numero_debut.'\',\''.$numero_fin.'\',\''.self::$username.'\')';
		echo $requete."\n";
		$this->db->query($requete);
			
	}

	function update_ordre($pays,$magazine,$ordre,$numero_debut,$numero_fin,$nom_fonction,$parametrage) {
		$requete_suppr='DELETE modeles, valeurs, intervalles FROM edgecreator_modeles2 AS modeles '
					  .'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
				      .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
					  .'WHERE (Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Ordre LIKE \''.$ordre.'\' AND Nom_Fonction LIKE \''.$nom_fonction.'\' AND username LIKE \''.self::$username.'\')';
		$this->db->query($requete_suppr);
		echo $requete_suppr."\n";
		$this->insert($pays,$magazine,$ordre,$nom_fonction,null,null,$numero_debut,$numero_fin,self::$username);
		
		foreach($parametrage as $option_nom_intervalle=>$option_valeur) {
			$option_valeur=str_replace("'","\'",$option_valeur);
			list($option_nom,$intervalle)=explode('.',$option_nom_intervalle);
			list($numero_debut,$numero_fin)=explode('~',$intervalle);
			
			$this->insert($pays,$magazine,$ordre,$nom_fonction,$option_nom,$option_valeur,$numero_debut,$numero_fin,self::$username);
		}
	}

	function cloner_etape($pays,$magazine,$etape_courante,$etape) {
		$requete='SELECT '.implode(', ', self::$fields).' '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs AS valeurs ON edgecreator_modeles2.ID = valeurs.ID_Option '
			    .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
				.'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Ordre='.$etape_courante.' AND username LIKE \''.self::$username.'\'';
		$resultats=$this->db->query($requete)->result();
		foreach($resultats as $resultat) {
			$resultat->Ordre=$etape;
		}
		$this->db->insert_batch('edgecreator_modeles2',$resultats);
	}

	function insert_ordre($pays,$magazine,$ordre,$numero_debut,$numero_fin,$nom_fonction,$parametrage) {
		$ordre_existe=count($this->get_etapes_simple($pays, $magazine, $ordre)) > 0;
		if ($ordre_existe) {
			return;
		}
		$this->insert($pays,$magazine,$ordre,$nom_fonction,null,null,$numero_debut,$numero_fin,self::$username);
		foreach($parametrage as $option_nom_intervalle=>$option_valeur) {
			$option_valeur=str_replace("'","\'",$option_valeur);
			list($option_nom,$intervalle)=explode('.',$option_nom_intervalle);
			list($numero_debut,$numero_fin)=explode('~',$intervalle);
				
			$this->insert($pays,$magazine,$ordre,$nom_fonction,$option_nom,$option_valeur,$numero_debut,$numero_fin,self::$username);
			
		}
	}

	function delete_ordre($pays,$magazine,$ordre,$numero_debut,$numero_fin,$nom_fonction) {
		$requete_suppr='DELETE modeles, valeurs, intervalles FROM edgecreator_modeles2 modeles '
					  .'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
				      .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
					  .'WHERE (Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Ordre LIKE \''.$ordre.'\' AND username LIKE \''.self::$username.'\'';
		if ($numero_debut!=null)
			$requete_suppr.=' AND Nom_Fonction LIKE \''.$nom_fonction.'\' AND Numero_debut LIKE \''.$numero_debut.'\' AND Numero_fin LIKE \''.$numero_fin.'\'';
		$requete_suppr.=')';
		$this->db->query($requete_suppr);
		echo $requete_suppr."\n";
	}

	function delete_option($pays,$magazine,$etape,$nom_option) {
		if ($nom_option=='Actif')
			$requete_suppr_option='DELETE modeles, valeurs, intervalles FROM edgecreator_modeles2 modeles '
								  .'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
							      .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
							      .'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' '
								  .'AND Ordre='.$etape.' AND username LIKE \''.self::$username.'\'';
		else
			$requete_suppr_option='DELETE modeles, valeurs, intervalles FROM edgecreator_modeles2 modeles '
								  .'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
							      .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
							      .'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' '
								  .'AND Ordre='.$etape.' AND Option_nom LIKE \''.$nom_option.'\' AND username LIKE \''.self::$username.'\'';
		$this->db->query($requete_suppr_option);
		echo $requete_suppr_option."\n";
	}

	function insert_valeur_option($pays,$magazine,$etape,$nom_fonction,$option_nom,$valeur,$numero_debut,$numero_fin,$id_valeur=null) {
		$valeur=mysql_real_escape_string($valeur);
		if ($option_nom=='Actif') {
			$this->insert($pays,$magazine,$etape,$nom_fonction,null,null,$numero_debut,$numero_fin,self::$username,$id_valeur);
			
		}
		else
			$this->insert($pays,$magazine,$etape,$nom_fonction,$option_nom,$valeur,$numero_debut,$numero_fin,self::$username,$id_valeur);
	}
	
	function get_id_valeur_max() {
		$requete='SELECT MAX(ID) AS Max FROM edgecreator_valeurs';
		return $this->db->query($requete)->first_row()->Max;
		
	}

	function etendre_numero ($pays,$magazine,$numero,$nouveau_numero) {
		$requete_get_options='SELECT '.implode(', ', self::$fields).',username '
						    .'FROM edgecreator_modeles2 AS modeles '
							.'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
					        .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
							.'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' '
							.'ORDER BY Ordre';
		echo $requete_get_options."\n";
		$resultats=$this->db->query($requete_get_options)->result();
		foreach($resultats as $resultat) {
			$modifs=array();
			$modifs_requete=array();
			$option_nom=is_null($resultat->Option_nom) ? 'NULL' : ('\''.$resultat->Option_nom.'\'');
			$option_valeur=is_null($resultat->Option_valeur) ? 'NULL' : ('\''.$resultat->Option_valeur.'\'');
			$intervalle=$this->getIntervalleShort($this->getIntervalle($resultat->Numero_debut, $resultat->Numero_fin));
			if (est_dans_intervalle($numero,$intervalle)) {
				if (!est_dans_intervalle($nouveau_numero,$intervalle)) {
					$intervalle=$this->ajouterNumeroAIntervalle($intervalle, $nouveau_numero);
					
					$condition_option_nom=is_null($resultat->Option_nom) ? 'IS NULL' : 'LIKE '.$option_nom;
					$condition_option_valeur=is_null($resultat->Option_nom) ? 'IS NULL' : 'LIKE '.$option_valeur;
					$requete_id_valeur='SELECT edgecreator_valeurs.ID AS ID '
									  .'FROM edgecreator_valeurs '
									  .'INNER JOIN edgecreator_modeles2 ON edgecreator_valeurs.ID_Option = edgecreator_modeles2.ID '	
									  .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '		   
									  .'WHERE Pays LIKE \''.$resultat->Pays.'\' AND Magazine LIKE \''.$resultat->Magazine.'\' '
									  .'AND Ordre LIKE \''.$resultat->Ordre.'\' AND Nom_fonction LIKE \''.$resultat->Nom_fonction.'\' '
									  .'AND Option_nom '.$condition_option_nom.' AND Option_valeur '.$condition_option_valeur.' '
									  .'AND Numero_debut LIKE \''.$resultat->Numero_debut.'\' AND Numero_fin LIKE \''.$resultat->Numero_fin.'\' '
									  .'AND username LIKE \''.$resultat->username.'\'';
					echo $requete_id_valeur."\n";
					$id_valeur = $this->db->query($requete_id_valeur)->first_row()->ID;
					
					
					$req_suppression_existantes='DELETE FROM edgecreator_intervalles '
											   .'WHERE ID_Valeur='.$id_valeur.' AND Numero_debut LIKE \''.$resultat->Numero_debut.'\' AND Numero_fin LIKE \''.$resultat->Numero_fin.'\' '
											   .'AND username LIKE \''.$resultat->username.'\'';
					echo $req_suppression_existantes."\n";
					$this->db->query($req_suppression_existantes);
											
					$intervalles=explode(';',$intervalle);
					foreach($intervalles as $intervalle) {
						list($numero_debut,$numero_fin)=explode('~',$intervalle);
						$req_ajout_nouvel_intervalle='INSERT INTO edgecreator_intervalles (ID_Valeur,Numero_debut,Numero_fin,username) '
										    .'VALUES ('.$id_valeur.',\''.$numero_debut.'\',\''.$numero_fin.'\',\''.$resultat->username.'\')';
						echo $req_ajout_nouvel_intervalle."\n";
						$this->db->query($req_ajout_nouvel_intervalle);
							
		
					}
				}
			}
		}
	}

	function getFields() {
		$fields=array();
		foreach(self::$fields as $field)
			$fields[]=$this->$field;
		return $fields;
	}

	function getRGB($pays,$magazine,$numero,$couleurs,$couleur_defaut=array(255,255,255)) {
		if (is_array($couleurs))
			return $couleurs;
		else {
			if (strpos($couleurs, ','))
				return explode(',',$couleurs);
			else
				return hex2rgb($couleurs);
		}
	}

	function getValeur($option_nom,$option_valeur) {
		$texte='';
		if (!is_array($option_valeur)) // Valeur par défaut
			$option_valeur=array('Tous'=>$option_valeur);
		asort($option_valeur);
		$option_valeur_groupe=array();
		foreach($option_valeur as $intervalle=>$valeur) {
			if (!array_key_exists($valeur, $option_valeur_groupe))
				$option_valeur_groupe[$valeur]=array();
			$option_valeur_groupe[$valeur][]=$intervalle;
		}
		uasort($option_valeur_groupe,'trier_intervalles');
		foreach($option_valeur_groupe as $valeur=>$intervalles) {
			usort($intervalles,'trier_intervalles');
			$contient_template= Fonction_executable::toTemplatedString($valeur,false);
			$propriete_champs=new ReflectionProperty($this->Nom_fonction, 'champs');
			$champs=$propriete_champs->getValue();
			$type_donnee=$champs[$option_nom];
			switch($type_donnee) {
				case 'couleur':
					if (strpos($valeur, ',')===false)
						$valeur=implode(',',hex2rgb ($valeur));
					$texte.='<span style="border:1px solid black;background-color:rgb('.$valeur.')">&nbsp;&nbsp;&nbsp;</span>';

				break;
				case 'fichier_ou_texte':
					$texte.=($contient_template?'':'<img src="'.Image::get_chemin_relatif($valeur).'" width="25" />').'&nbsp;'.$valeur;
				break;
				default:
					if ($option_nom=='Chaine')
						$texte.= '<div class="valeur">';
					$texte.=str_replace(' ','&nbsp;',  urldecode($valeur));
					switch($option_nom) {
						case 'Pos_x':case 'Pos_y':case 'Dimension_x':case 'Dimension_y':
							$texte.='&nbsp;mm';
						break;
					}
					if ($option_nom=='Chaine')
						$texte.= '</div>';
				break;
			}
			$texte.='<span style="font-size:12px;"> ('.implode(' ; ',$intervalles).')</span>&nbsp;<br />';
		}
		return $texte;
	}

	function getValeurModifiable($option_nom,$option_valeur,$modif=true) {
		if (!is_array($option_valeur)) // Valeur par défaut
			$option_valeur=array('Tous'=>$option_valeur);
		asort($option_valeur);
		$option_valeur_groupe=array();
		foreach($option_valeur as $intervalle=>$valeur) {
			if (!array_key_exists($valeur, $option_valeur_groupe))
				$option_valeur_groupe[$valeur]=array();
			$option_valeur_groupe[$valeur][]=$intervalle;
		}
		uasort($option_valeur_groupe,'trier_intervalles');
		ob_start();
		foreach($option_valeur_groupe as $valeur=>$intervalles) {
			usort($intervalles,'trier_intervalles');
			$intervalles_short=$this->getIntervalleShort(implode(';',$intervalles));
			$contient_template= Fonction_executable::toTemplatedString($valeur,false);
			$id=$option_nom.'.'.$intervalles_short;
			$propriete_champs=new ReflectionProperty($this->Nom_fonction, 'champs');
			$champs=$propriete_champs->getValue();
			$type_donnee=$champs[$option_nom];
			?><div id="ligne_<?=$id?>-" name="<?=$option_nom?>" class="ligne_option_intervalle"><table border="0"><tr><td style="width:30px"></td><td class="cellule_valeur largeur_standard"><?php
			switch($type_donnee) {
				case 'couleur':
					list($r,$g,$b)=$this->getRGB(null,null,null, $valeur);
					?><input id="<?=$id?>-" class="parametre color" value="<?=rgb2hex($r,$g,$b)?>" /><?php
				break;
				case 'fichier_ou_texte':
					?><span id="<?=$id?>-alt-affichage1" class="<?=($contient_template?'cache':'montre')?>">
						<table cellspacing="0"><tr><td style="width:29px;padding-right:0px">
						<img id="<?=$id?>-image" src="<?=Image::get_chemin_relatif($valeur)?>" width="25" />&nbsp;
						</td><td>
						<select class="parametre liste image alt" id="<?=$id?>-"><?php
						$options=get_liste($this->Nom_fonction,$option_nom);
						foreach($options as $option) {
							?><option value="<?=$option?>" <?=(($option==$valeur) ? 'selected="selected"' :'')?>><?=$option?></option><?
						}
						?></select>
						</td></tr></table>
					</span>
					<span class="<?=($contient_template?'montre':'cache')?>" id="<?=$id?>-alt-affichage2">
						<input class="parametre modifiable alt" id="<?=$id?>-" type="text" value="<?=($valeur)?>" />
					<br /></span> ou <a href="javascript:void(0)" id="<?=$id?>-alt" onclick="alterner_champ(this)">
						<span class="<?=($contient_template?'cache':'montre')?>" id="<?=$id?>-alt1">texte libre</span>
						<span class="<?=($contient_template?'montre':'cache')?>" id="<?=$id?>-alt2">fichier pr&eacute;d&eacute;fini</span>
					</a><?php
				break;
				case 'quantite':
					?><input class="parametre modifiable quantite" id="<?=$id?>-" type="text" value="<?=$valeur?>" /><?php
				break;
				case 'liste':
					?><select class="parametre liste" id="<?=$id?>-"><?php
					$options=get_liste($this->Nom_fonction,$option_nom);
					foreach($options as $option) {
						?><option value="<?=$option?>" <?=($option==$valeur ?'selected="selected"':'')?>><?=$option?></option><?php
					}
					?></select><?php
				break;
				default:
					?><input class="parametre modifiable" id="<?=$id?>-" type="text" value="<?=($valeur)?>" /><?php
			}
			?>
			</td><td style="width:30px"></td><td class="cellule_intervalle_validite"><div class="intervalle_validite" name="<?=$option_nom?>"><?=$this->getIntervalleListesDeroulantes($option_nom,$intervalles_short,$modif)?></div>
			</td><td><a class="cloner" href="javascript:void(0)" onclick="cloner(this)">Cl</a>
			</td><td>|</td><td><a class="supprimer" href="javascript:void(0)" onclick="supprimer(this)">X</a></td>
			</tr></table></div><?php
		}?>
		<a href="javascript:void(0)" onclick="par_defaut('<?=$option_nom?>')">Renseigner la valeur par d&eacute;faut...</a><?php
		$texte=ob_get_clean();
		return $texte;
	}

	function  __toString() {
		$texte_intervalle=str_replace(';N',' ; N',$this->getIntervalle($this->Numero_debut,$this->Numero_fin));
		if (!isset($this->Option_nom) || is_null($this->Option_nom)) {
			return $this->Nom_fonction.' ('.$texte_intervalle.')';
		}
		else {
			return '<tr><td>'.$this->Option_nom.'</td>'
					  .'<td>'.$this->getValeur().'</td>'
					  .'<td>'.$texte_intervalle.'</td></tr>';
		}
	}

	function ajouterNumeroAIntervalle($intervalle,$numero,$forcer_ajout=false) {
		$intervalles=explode(';',$intervalle);
		$numero_ajoute=false;
		foreach ($intervalles as $i=>$intervalle) {
			if (strpos($intervalle,'~') === false)
				list($numero_debut,$numero_fin)=array($intervalle,$intervalle);
			else
				list($numero_debut,$numero_fin)=explode('~',$intervalle);
			if (is_null($numero_fin))
				$numero_fin=$numero_debut;
			list($nouveau_numero_est_apres_debut,$nouveau_numero_est_adjacent_debut)=$this->getPositionRelativeNumero($numero,$numero_debut);
			list($nouveau_numero_est_apres_fin,$nouveau_numero_est_adjacent_fin)=$this->getPositionRelativeNumero($numero,$numero_fin);
			if ($forcer_ajout) {
				$intervalles[]=$numero.'~'.$numero;
				$numero_ajoute=true;
				break;
			}
			if (!$nouveau_numero_est_apres_debut && $nouveau_numero_est_adjacent_debut) {
				$numero_debut=$numero;
				$numero_ajoute=true;
			}
			elseif ($nouveau_numero_est_apres_fin && $nouveau_numero_est_adjacent_fin) {
				$numero_fin=$numero;
				$numero_ajoute=true;
			}
			$intervalles[$i]=$numero_debut.'~'.$numero_fin;
		}
		$intervalles=implode(';',$intervalles);
		if (!$numero_ajoute)
			$intervalles=$this->ajouterNumeroAIntervalle($intervalles,$numero,true);
		return $intervalles;
	}

	function getPositionRelativeNumero($nouveau_numero,$numero) {
		$nouveau_numero_est_apres=null;
		$nouveau_numero_est_adjacent=null;
		$index_numero_trouve=-1;
		$index_nouveau_numero_trouve=-1;
		$i=0;
		foreach(self::$numeros_dispos as $numero_disponible) {
			if ($numero_disponible==$numero) {
				$index_numero_trouve=$i;
				$nouveau_numero_est_apres= $index_nouveau_numero_trouve == -1;
			}
			if ($numero_disponible==$nouveau_numero) {
				$index_nouveau_numero_trouve=$i;
			}
			if ($index_nouveau_numero_trouve != -1 && $index_numero_trouve !=-1) {
				$nouveau_numero_est_adjacent=abs($index_numero_trouve - $i) == 1;
				break;
			}
			$i++;
		}
		return array($nouveau_numero_est_apres,$nouveau_numero_est_adjacent);
	}

	function getIntervalleShort($intervalle) {
		if (strpos($intervalle, 'Tous')!==false)
			return 'Tous~Tous';
		return str_replace('Num&eacute;ros ', '', str_replace('Num&eacute;ro ', '', str_replace(' &agrave; ', '~', $intervalle)));
	}

	function getIntervalle($numero_debut,$numero_fin) {
		$intervalles='';
		$numeros_debut=explode(';',$numero_debut);
		$numeros_fin=explode(';',$numero_fin);
		foreach($numeros_debut as $i=>$numero_debut) {
			$numero_fin=$numeros_fin[$i];
			if ($numero_debut=='Tous')
				$intervalles[]= 'Tous num&eacute;ros';
			elseif ($numero_debut===$numero_fin)
				$intervalles[]= 'Num&eacute;ro '.$numero_debut;
			else
				$intervalles[]= 'Num&eacute;ros '.$numero_debut.' &agrave; '.$numero_fin;
		}
		return implode(';', $intervalles);
	}

	function getIntervalleListesDeroulantes($option_nom,$intervalle=null,$modif=true) {
		$ci = get_instance();
		$ci->load->helper('form');
		if (strpos($intervalle,'&agrave;')!==false)
			$intervalle=$this->getIntervalleShort($intervalle);
		if ($modif) {
			$intervalles=explode(';',$intervalle);
			foreach($intervalles as $i=>$sous_intervalle)
				if (strpos($sous_intervalle, '~') === false)
					$intervalles[$i].='~'.$intervalles[$i];
			$intervalle=implode(';',$intervalles);
		}
		list($numero_debut_intervalle,$numero_fin_intervalle)=getNumerosDebutFinShort($intervalle,self::$numero_debut,self::$numero_fin);
		if ($modif) {
			$numeros_debut=explode(';',$numero_debut_intervalle);
			$numeros_fin=explode(';',$numero_fin_intervalle);
		}
		else {
			$numeros_debut=explode(';',self::$numero_debut);
			$numeros_fin=explode(';',self::$numero_fin);
		}
		$numeros_debut2=array();
		$numeros_fin2=array();
		foreach($numeros_debut as $i=>$numero_debut) {
			if (strpos($numero_debut,'~')!==false) {
				list($numeros_debut2[],$numeros_fin2[])=explode('~',$numero_debut);
			}
			else
				list($numeros_debut2[],$numeros_fin2[])=array($numero_debut,$numeros_fin[$i]);
		}
		$numeros_debut=$numeros_debut2;
		$numeros_fin=$numeros_fin2;
		$texte='';
		foreach($numeros_debut as $i=>$numero_debut) {
			$numero_fin=$numeros_fin[$i];
			$id_debut=$option_nom.'.'.$this->getIntervalleShort($intervalle).'-numero-debut-intervalle'.$i.'-';
			$id_fin=$option_nom.'.'.$this->getIntervalleShort($intervalle).'-numero-fin-intervalle'.$i.'-';

			$texte.='<div><a href="javascript:void(0)" onclick="ajouter_intervalle(this)">Cl</a>|<a href="javascript:void(0)" onclick="supprimer_intervalle(this)">X</a>&nbsp;'
				  .'Num&eacute;ros '.form_dropdown('', self::$numeros_dispos, $numero_debut,'id="'.$id_debut.'" class="debut"')
				  .'&nbsp;&agrave;&nbsp; '.form_dropdown('', self::$numeros_dispos, $numero_fin,'id="'.$id_fin.'" class="fin"').'</div>';
		}
		return $texte;
	}

	function getNumerosDebutFin($intervalle=null) {
		if (is_null($intervalle))
			return array(self::$numero_debut,self::$numero_fin);
		$regex_numeros_debut_fin='#Num&eacute;ros? ([^ ]+) (?:&agrave; ([^ ]+))?#is';
		preg_match($regex_numeros_debut_fin, $intervalle.' ', $numeros_debut_fin);
		if (isset($numeros_debut_fin[2]))
			return array($numeros_debut_fin[1],$numeros_debut_fin[2]);
		else {
			if (isset($numeros_debut_fin[1]))
				return array($numeros_debut_fin[1],$numeros_debut_fin[1]);
			else
				return array(self::$numero_debut,self::$numero_fin);
		}
	}

	function setSessionID($id_session) {
		self::$id_session=$id_session;
	}
	
	function setPays($pays) {
		self::$pays=$pays;
	}

	function setMagazine($magazine) {
		self::$magazine=$magazine;
	}

	function setUsername($username) {
		self::$username=$username;
	}

	function setNumeroDebut($numero_debut) {
		self::$numero_debut=$numero_debut;
	}

	function setNumeroFin($numero_fin) {
		self::$numero_fin=$numero_fin;
	}

	function setNumerosDisponibles($numeros_disponibles) {
		self::$numeros_dispos=$numeros_disponibles;
	}
	
	function setDropdownNumeros($numeros_disponibles) {
		self::$dropdown_numeros=$numeros_disponibles;
	}
	
	function setDropdownNumerosId($id,$dropdown='static') {
		if ($dropdown=='static')
			$dropdown=self::$dropdown_numeros;
		return str_replace('<select', '<select id="'.$id.'" ', $dropdown);
	}
	
	function setDropdownNumerosName($name,$dropdown='static') {
		if ($dropdown=='static')
			$dropdown=self::$dropdown_numeros;
		return preg_replace('#name="[^"]+"', 'name="'.$name.'"', $dropdown);
	}
	
	function setDropdownNumerosSelected($value,$dropdown='static') {
		if ($dropdown=='static')
			$dropdown=self::$dropdown_numeros;
		return str_replace('<option value="'.$value.'"', '<option value="'.$value.'" selected="selected" ', $dropdown);
	}
	
	function getDropdownNumeros() {
		return self::$dropdown_numeros;
	}
}
Modele_tranche::$fields=array('Pays', 'Magazine', 'Ordre', 'Nom_fonction', 'Option_nom', 'Option_valeur', 'Numero_debut', 'Numero_fin');
Fonction::$valeurs_defaut=array('Remplir'=>array('Pos_x'=>0,'Pos_y'=>0));

class Fonction extends Modele_tranche {
	public $options;
	static $valeurs_defaut=array();

	function option($nom) {
		if (isset($this->options->$nom)) {
			return $this->options->$nom;
		}
		else {
			if (isset(self::$valeurs_defaut[$fonction->Nom_fonction][$nom]))
				return self::$valeurs_defaut[$fonction->Nom_fonction][$nom];
			else {
				echo 'Aucune valeur dans la BD pour '.$nom."\n";
				exit(0);
			}
		}
	}
}

class Fonction_executable extends Fonction {

	static $descriptions=array();
	
	function Fonction_executable($options,$creation=false,$get_options_defaut=true) {
		$this->options=$options;
		$classe=get_class($this);
		if ($creation) {
			$propriete_valeurs_nouveau=new ReflectionProperty($classe, 'valeurs_nouveau');
			$valeurs_nouveau=$propriete_valeurs_nouveau->getValue();
			foreach($valeurs_nouveau as $nom=>$valeur) {
				if (!isset($this->options->$nom)) {
					$this->options->$nom=$valeur;
				}
			}
		}
		else if ($get_options_defaut){
			$propriete_valeurs_defaut=new ReflectionProperty($classe, 'valeurs_defaut');
			$valeurs_defaut=$propriete_valeurs_defaut->getValue();
			foreach($valeurs_defaut as $nom=>$valeur) {
				if (!isset($this->options->$nom) || $this->options->$nom == array()) {
					$this->options->$nom=$valeur;
				}
			}
			$propriete_champs=new ReflectionProperty($classe, 'champs');
			$champs=$propriete_champs->getValue();
			foreach(array_keys($champs) as $nom) {
				if (!isset($this->options->$nom))
					$this->options->$nom=null;
			}
			return;
		}
		else {
			$propriete_champs=new ReflectionProperty($classe, 'champs');
			$valeurs_champs=$propriete_champs->getValue();
			foreach($valeurs_champs as $nom=>$valeur) {
				if (!isset($this->options->$nom))
					$this->options->$nom=null;
			}

			return;
		}

		if ($creation) {
			$propriete_champs=new ReflectionProperty($classe, 'champs');
			$champs=$propriete_champs->getValue();
			foreach(array_keys($champs) as $nom) {
				if (!isset($this->options->$nom) || (strpos('Couleur', $nom)!==false && $this->options->$nom==array())) {
					self::erreur('Le champ "'.$nom.'" est indéfini !');
				}
			}
		}
	}
		
	function afficher_si_existant() {
		$ci =& get_instance();
		$ci->load->model('Modele_tranche','Modele_tranche',true);
		$modele_tranche=new Modele_tranche();
		if ($modele_tranche->get_preview_existe($this->getJSONOptions())) {
			$session_id=$modele_tranche->id_session;
			header('Content-type: image/png');
			imagepng(imagecreatefrompng('../edges/tmp_previews/'.$session_id.'/'.Viewer::$pays.'_'.Viewer::$magazine.'_'.Viewer::$numero.'_'.Viewer::$etape_en_cours->num_etape.'_'.z(1).'.png'));
			exit(0);
		}
	}

	function getJSONOptions() {
		return json_encode($this->options);
	}
	static function erreur($erreur) {
		if (!is_resource(Viewer::$image)) {
			Viewer::$largeur=z(20);
			Viewer::$hauteur=z(220);
			Viewer::$image=imagecreatetruecolor(Viewer::$largeur, Viewer::$hauteur);
		}
		imagefilledrectangle(Viewer::$image, 0, 0, Viewer::$largeur, Viewer::$hauteur, imagecolorallocate(Viewer::$image, 255, 255, 255));
		$noir=imagecolorallocate(Viewer::$image,0,0,0);
		$lignes_erreur=explode(';', $erreur);
		foreach($lignes_erreur as $i=>$ligne) {
			if ($i==0)
				$texte_erreur='Erreur etape '.Viewer::$etape_en_cours->num_etape.' (Fonction '.Viewer::$etape_en_cours->nom_fonction.') : '.$ligne;
			else
				$texte_erreur=$ligne;
			imagettftext(Viewer::$image,z(3),90,
						 ($i+1)*Viewer::$largeur/3,Viewer::$hauteur,
						 $noir,BASEPATH.'fonts/Arial.ttf',$texte_erreur);
		}
		if (Viewer::$is_debug===false)
			header('Content-type: image/png');
		imagepng(Viewer::$image);
		exit();
	}
	
	static function getCheminElements($pays=null) {
		if (is_null($pays))
			$pays=self::$pays;
		return BASEPATH.'../../edges/'.$pays.'/elements';
	}

	static function toTemplatedString($str,$actif=true) {
		$tab=array('numero'=>'#\[Numero\]#is',
				   'numero[]'=>'#\[Numero\[([0-9]+)\]\]#is',
				   'largeur'=>'#\[Largeur\]#is',
				   'hauteur'=>'#\[Hauteur\]#is',
				   'caracteres_speciaux'=>'#\Â°#is');
		if ($str==array())
			$str='';
		foreach($tab as $nom=>$regex) {
			if (is_array($str)) {
			   $a=1;
			}
			if (0 !== preg_match($regex, $str, $matches)) {
				if (!$actif) return true;
				switch($nom) {
					case 'numero':
						$str=preg_replace($regex, Viewer::$numero, $str);
					break;
					case 'numero[]':
						$spl=str_split(Viewer::$numero);
						if (0!=preg_match_all($regex, $str, $matches)) {
							foreach($matches[1] as $i=>$num_caractere) {
								if (!array_key_exists($num_caractere, $spl))
									$str=str_replace($matches[0][$i],'',$str);
								else
									$str=str_replace($matches[0][$i],preg_replace($regex, $spl[$num_caractere],$matches[0][$i]),$str);
							}
						}
					break;
					case 'largeur':
						$str=preg_replace($regex, Viewer::$largeur, $str);
						eval("\$str=".$str.";");
						$str/=z(1);
					break;
					case 'hauteur':
						$str=preg_replace($regex, Viewer::$hauteur, $str);
						eval("\$str=".$str.";");
						$str/=z(1);
					break;
					case 'caracteres_speciaux':
						$str=str_replace('Â°','°',$str);
					break;

				}
			}
		}
		if (!$actif) return true;
		return $str;
	}

}

class Dimensions extends Fonction_executable {
	static $champs=array('Dimension_x'=>'quantite','Dimension_y'=>'quantite');
	static $valeurs_nouveau=array('Dimension_x'=>15,'Dimension_y'=>200);
	static $valeurs_defaut=array();
	static $descriptions=array('Dimension_x'=>'Largeur de la tranche', 
							   'Dimension_y'=>'Hauteur de la tranche');
	
	function Dimensions($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;
		$this->verifier_erreurs();
		Viewer::$image=imagecreatetruecolor(z($this->options->Dimension_x), z($this->options->Dimension_y));
		//imageantialias(Viewer::$image, true);
		Viewer::$largeur=z($this->options->Dimension_x);
		Viewer::$hauteur=z($this->options->Dimension_y);
		imagefill(Viewer::$image,0,0,  imagecolorallocate(Viewer::$image, 255, 255, 255));
	}
	
	function verifier_erreurs() {
		if ($this->options->Dimension_x < 0 || $this->options->Dimension_y < 0 ) {
			self::erreur('Dimensions négatives');
		}
		if ($this->options->Dimension_x == array() || $this->options->Dimension_y == array() ) {
			self::erreur('Dimensions nulles');
		}
	}
}

class Remplir extends Fonction_executable {
	static $champs=array('Pos_x'=>'quantite','Pos_y'=>'quantite','Couleur'=>'couleur');
	static $valeurs_nouveau=array('Pos_x'=>0,'Pos_y'=>0,'Couleur'=>'AAAAAA');
	static $valeurs_defaut=array('Pos_x'=>0,'Pos_y'=>0);
	static $descriptions=array('Pos_x'=>'Abscisse du point de d&eacute;part du remplissage', 
							   'Pos_y'=>'Ordonn&eacute;e du point de d&eacute;part du remplissage',
							   'Couleur'=>'Couleur de remplissage');
	
	function Remplir($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;
		$this->options->Pos_x=z(self::toTemplatedString($this->options->Pos_x));
		$this->options->Pos_y=z(self::toTemplatedString($this->options->Pos_y));
		$this->verifier_erreurs();
		$this->afficher_si_existant();
		list($r,$g,$b)=$this->getRGB(Viewer::$pays,Viewer::$magazine,Viewer::$numero,$this->options->Couleur);
		$couleur=imagecolorallocate(Viewer::$image, $r,$g,$b);
		imagefill(Viewer::$image, $this->options->Pos_x, $this->options->Pos_y, $couleur);
		//imageline(Viewer::$image, $this->options->Pos_x, ($this->options->Pos_y-5), ($this->options->Pos_x+5), ($this->options->Pos_y+5), $couleur);
	}
	
	function verifier_erreurs() {
		if ($this->options->Pos_x >= Viewer::$largeur || $this->options->Pos_y >= Viewer::$hauteur
		 || $this->options->Pos_x < 0 || $this->options->Pos_y < 0) {
			self::erreur('Point de remplissage hors de l\'image : ('.$this->options->Pos_x.','.$this->options->Pos_y.') vers ('.Viewer::$largeur.','.Viewer::$hauteur.')');
		}
	}
}

class Image extends Fonction_executable {
	static $champs=array('Source'=>'fichier_ou_texte','Decalage_x'=>'quantite','Decalage_y'=>'quantite','Compression_x'=>'quantite','Compression_y'=>'quantite','Position'=>'liste');
	static $valeurs_nouveau=array('Source'=>'Tete PM.png','Decalage_x'=>'5','Decalage_y'=>'5','Compression_x'=>'0.6','Compression_y'=>'0.6','Position'=>'haut');
	static $valeurs_defaut=array('Decalage_x'=>0,'Decalage_y'=>0,'Compression_x'=>1,'Compression_y'=>1,'Position'=>'haut');
	
	static $descriptions=array('Source'=>'Nom de l\'image', 
							   'Decalage_x'=>'Marge gauche de l\'image', 
							   'Decalage_y'=>'Marge haute de l\'image<br />(Par rapport au haut de l\'image si Position=haut, sinon par rapport au bas)',
							   'Compression_x'=>'Compression de la largeur de l\'image',
							   'Compression_y'=>'Compression de la hauteur de l\'image',
							   'Position'=>'Position de l\'image par rapport &agrave; la tranche : Haut ou Bas');
	
	function Image($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;
		$this->options->Decalage_x=self::toTemplatedString($this->options->Decalage_x);
		$this->options->Decalage_y=self::toTemplatedString($this->options->Decalage_y);
		$this->options->Source=self::toTemplatedString($this->options->Source);
		$this->verifier_erreurs();
		$extension_image=strtolower(substr($this->options->Source, strrpos($this->options->Source, '.')+1,strlen($this->options->Source)-strrpos($this->options->Source, '.')-1));
		$fonction_creation_image='imagecreatefrom'.$extension_image;

		$chemin_reel=Image::get_chemin_reel($this->options->Source);
		$sous_image=call_user_func($fonction_creation_image,$chemin_reel);
		list($width,$height)=array(imagesx($sous_image),imagesy($sous_image));
		$hauteur_sous_image=Viewer::$largeur*($height/$width);
		if ($this->options->Position=='bas') {
			$this->options->Decalage_y=Viewer::$hauteur-$hauteur_sous_image-z($this->options->Decalage_y);
		}
		else
			$this->options->Decalage_y=z($this->options->Decalage_y);
		imagecopyresampled (Viewer::$image, $sous_image, z($this->options->Decalage_x), $this->options->Decalage_y, 0, 0, Viewer::$largeur*$this->options->Compression_x, $hauteur_sous_image*$this->options->Compression_y, $width, $height);
	}

	static function get_chemin_reel($source) {
		return (strpos($source, 'images_myfonts')!==false) ?
				 $source
			   : self::getCheminElements().'/'.$source;
	}

	static function get_chemin_relatif($source) {
		return base_url().'../edges/'.self::$pays.'/elements/'.$source;
	}
	
	function verifier_erreurs() {
		$chemin_reel=Image::get_chemin_reel($this->options->Source);
		if (!is_file($chemin_reel)) {
			self::erreur('Le fichier '.$this->options->Source.' n\'existe pas');
		}
	}
}

class TexteMyFonts extends Fonction_executable {
	static $champs=array('URL'=>'texte','Couleur_texte'=>'couleur','Couleur_fond'=>'couleur','Largeur'=>'quantite','Chaine'=>'texte','Pos_x'=>'quantite','Pos_y'=>'quantite','Compression_x'=>'quantite','Compression_y'=>'quantite','Rotation'=>'quantite','Demi_hauteur'=>'liste','Mesure_depuis_haut'=>'liste');
	static $valeurs_nouveau=array('URL'=>'redrooster.block-gothic-rr.demi-extra-condensed','Couleur_texte'=>'000000','Couleur_fond'=>'ffffff','Largeur'=>'700','Chaine'=>'Le journal de Mickey','Pos_x'=>'0','Pos_y'=>'5','Compression_x'=>'0.3','Compression_y'=>'0.3','Rotation'=>'90','Demi_hauteur'=>'Oui','Mesure_depuis_haut'=>'Oui');
	static $valeurs_defaut=array('Rotation'=>0,'Compression_x'=>'1','Compression_y'=>'1','Mesure_depuis_haut'=>'Oui');
	
	static $descriptions=array('URL'=>'Nom de la police', 
							   'Couleur_texte'=>'Couleur du texte',
							   'Couleur_fond'=>'Couleur de l\'arri&egrave;re-plan du texte',
							   'Largeur'=>'Largeur occup&eacute; par le texte',
							   'Chaine'=>'Cha&icirc;ne de caract&egrave;res du texte',
							   'Pos_x'=>'Marge de l\'image depuis la gauche de la tranche',
							   'Pos_y'=>'Marge de l\'image depuis le haut de la tranche',
							   'Compression_x'=>'Compression de la largeur du texte<br />(1 = Pas de compression)',
							   'Compression_y'=>'Compression de la hauteur du texte<br />(1 = Pas de compression)',
							   'Rotation'=>'Rotation du texte<br />(0 = Pas de rotation)',
							   'Demi_hauteur'=>'S&eacute;lectionnez "Oui" si jamais vous ne voyez le texte que sur la moiti&eacute; de sa hauteur',
							   'Mesure_depuis_haut'=>'"Oui" si Pos_y doit repr&eacute;senter la marge jusqu\'au haut du texte, "Non" s\'il s\'agit de la marge jusqu\'au bas du texte');
	
	function TexteMyFonts($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;

		$this->options->Chaine=self::toTemplatedString($this->options->Chaine);
		if ($this->options->Chaine==' ')
			return;
		$this->options->URL=str_replace('.','/',$this->options->URL);
		$this->options->Pos_x=self::toTemplatedString($this->options->Pos_x);
		$this->options->Pos_y=self::toTemplatedString($this->options->Pos_y);
		$this->verifier_erreurs();
		list($r,$g,$b)=$this->getRGB(null, null, null, $this->options->Couleur_fond);
		list($r_texte,$g_texte,$b_texte)=$this->getRGB(null, null, null, $this->options->Couleur_texte);

		$this->options->Couleur_fond=rgb2hex($r, $g, $b);
		$this->options->Couleur_texte=rgb2hex($r_texte,$g_texte,$b_texte);

		$ci =& get_instance();
		$ci->load->model('MyFonts','MyFonts',true);

		$post=new MyFonts($this->options->URL,
						  $this->options->Couleur_texte,
						  $this->options->Couleur_fond,
						  $this->options->Largeur,
						  $this->options->Chaine.'                                    .');
		$chemin_image=$post->chemin_image;
		$texte=imagecreatefromgif($chemin_image);
		if ($this->options->Demi_hauteur == 'Oui') {
			$width=imagesx($texte);
			$height=imagesy($texte);
			$texte2=imagecreatetruecolor ($width, $height/2);
			imagecopyresampled($texte2, $texte, 0, 0, 0, 0, $width, $height/2, $width, $height/2);
			$texte=$texte2;
		}
		$width=imagesx($texte);
		$height=imagesy($texte);

		$debut=microtime(true);
		$espace=imagecreatetruecolor(2*$height, $height);
		imagefill($espace, 0, 0, imagecolorallocate($espace,$r, $g, $b));
		for ($i=0;$i<$width;$i+=2*$height) {
			$image_decoupee=imagecreatetruecolor(2*$height, $height);
			imagecopyresampled($image_decoupee, $texte, 0, 0, $i, 0, 2*$height, $height, 2*$height, $height);
			imagetruecolortopalette($image_decoupee, false, 255);
			if (imagecolorstotal($image_decoupee) == 1) { // Image remplie uniformément => découpage
				$texte2=imagecreatetruecolor($i, $height);
				imagecopy($texte2, $texte, 0, 0, 0, 0, $i, $height);
				$texte=$texte2;
				break;
			}
		}
		$fin=microtime(true);
		//echo ($fin-$debut).'<br />';
		$fond=imagecolorallocatealpha($texte, $r, $g, $b, 127);
		imagefill($texte,0,0,$fond);
		$texte=imagerotate($texte, $this->options->Rotation, $fond);
		$width=imagesx($texte);
		$height=imagesy($texte);
		$nouvelle_largeur=Viewer::$largeur*$this->options->Compression_x;
		$nouvelle_hauteur=Viewer::$largeur*($height/$width)*$this->options->Compression_y;
		if ($this->options->Mesure_depuis_haut=='Non')
			$this->options->Pos_y-=$nouvelle_hauteur/z(1);
		imagecopyresampled (Viewer::$image, $texte, z($this->options->Pos_x), z($this->options->Pos_y), 0, 0, $nouvelle_largeur, $nouvelle_hauteur, $width, $height);

	}
	
	function verifier_erreurs() {
		if (is_array($this->options->Couleur_fond) && count($this->options->Couleur_fond) ==0)
			self::erreur('Couleur de fond indéfinie');
		if (is_array($this->options->Couleur_texte) && count($this->options->Couleur_texte) ==0)
			self::erreur('Couleur de texte indéfinie');
	}
}

class TexteTTF extends Fonction_executable {
	static $champs=array('Pos_x'=>'quantite','Pos_y'=>'quantite','Rotation'=>'quantite','Taille'=>'quantite','Couleur'=>'couleur','Chaine'=>'texte','Police'=>'liste','Compression_x'=>'quantite','Compression_y'=>'quantite');
	static $valeurs_nouveau=array('Pos_x'=>'3','Pos_y'=>'5','Rotation'=>'-90','Taille'=>'3.5','Couleur'=>'F50D05','Chaine'=>'Texte du num&eacute;ro [Numero]','Police'=>'Arial','Compression_x'=>'1','Compression_y'=>'1');
	static $valeurs_defaut=array('Pos_x'=>0,'Pos_y'=>0,'Rotation'=>0,'Compression_x'=>'1','Compression_y'=>'1');
	
	
	static $descriptions=array('Pos_x'=>'Marge du texte depuis la gauche de la tranche', 
							   'Pos_y'=>'Marge du texte depuis le haut de la tranche',
							   'Rotation'=>'Rotation du texte<br />(0 = Pas de rotation)',
							   'Taille'=>'Taille du texte, en pt',
							   'Couleur'=>'Couleur du texte',
							   'Chaine'=>'Cha&icirc;ne de caract&egrave;res du texte',
							   'Police'=>'Nom de la police de caract&egrave;res',
							   'Compression_x'=>'Compression de la largeur de l\'image<br />(1 = Pas de compression)',
							   'Compression_y'=>'Compression de la hauteur de l\'image<br />(1 = Pas de compression)');
	
	function TexteTTF($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;
		$this->options->Chaine=self::toTemplatedString($this->options->Chaine);
		list($r,$g,$b)=$this->getRGB(Viewer::$pays,Viewer::$magazine,Viewer::$numero,$this->options->Couleur);
		$couleur_texte=imagecolorallocate(Viewer::$image, $r,$g,$b);
		
		$centrage_auto_x=$this->options->Pos_x == -1;
		$centrage_auto_y=$this->options->Pos_y == -1;
		$p=calculateTextBox($this->options->Chaine, BASEPATH.'fonts/'.$this->options->Police.'.ttf', z($this->options->Taille), $this->options->Rotation);
		if ($centrage_auto_x || $centrage_auto_y) {
			if ($centrage_auto_x)
				$this->options->Pos_x=(Viewer::$largeur-$p['width']*$this->options->Compression_x)/z(2);
			if ($centrage_auto_y)
				$this->options->Pos_y=(Viewer::$hauteur-$p['height']*$this->options->Compression_y)/z(2);
		}
		if ($this->options->Compression_x != 1 || $this->options->Compression_y != 1) {
			$largeur_tmp=max($p['width'],Viewer::$largeur)+z(1);
			$hauteur_tmp=max($p['height'],Viewer::$hauteur)+z(1);
			$image2=imagecreatetruecolor($largeur_tmp,$hauteur_tmp);
			imagefill($image2, 0,0, imagecolorallocatealpha($image2, 255, 255, 255, 127));
			if ($this->options->Rotation > 45 && $this->options->Rotation <135) {
				$pos_x_tmp=$p['left'];
				$pos_y_tmp=$p['top'];
			}
			
			imagettftext($image2,z($this->options->Taille),$this->options->Rotation,
						 $pos_x_tmp,$pos_y_tmp,
						 $couleur_texte,BASEPATH.'fonts/'.$this->options->Police.'.ttf',$this->options->Chaine);
			imagepng($image2, BASEPATH.'../../edges/tmp/ttfcomp.png');
			
			imagecopyresampled(Viewer::$image, $image2, z($this->options->Pos_x)*(Viewer::$largeur/$largeur_tmp), z($this->options->Pos_y)*(Viewer::$hauteur/$hauteur_tmp), 0,0, Viewer::$largeur*$this->options->Compression_x, Viewer::$hauteur*$this->options->Compression_y, $largeur_tmp, $hauteur_tmp);

		}
		else {
			imagettftext(Viewer::$image,z($this->options->Taille),$this->options->Rotation,
						 z($this->options->Pos_x),z($this->options->Pos_y),
						 $couleur_texte,BASEPATH.'fonts/'.$this->options->Police.'.ttf',$this->options->Chaine);
		}
	}
}

class Polygone extends Fonction_executable {
	static $champs=array('X'=>'texte','Y'=>'texte','Couleur'=>'couleur');
	static $valeurs_nouveau=array('X'=>'1,4,7,14','Y'=>'5,25,14,12','Couleur'=>'000000');
	static $valeurs_defaut=array();
	
	static $descriptions=array('X'=>'Liste des abscisses des points, s&eacute;par&eacute;es par virgules', 
							   'Y'=>'Liste des ordonn&eacute;es des points, s&eacute;par&eacute;es par virgules', 
							   'Couleur'=>'Couleur du polygone');
	
	function Polygone($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;
		if (is_array($this->options->X)) {
			$a=1;
		}
		$this->options->X=explode(',',str_replace(' ','',$this->options->X));
		$this->options->Y=explode(',',str_replace(' ','',$this->options->Y));
		$args=array(Viewer::$image);
		$coord=array();
		foreach(array_keys($this->options->X) as $i) {
			$this->options->X[$i]=self::toTemplatedString($this->options->X[$i]);
			$this->options->Y[$i]=self::toTemplatedString($this->options->Y[$i]);
			$coord[]=z($this->options->X[$i]);
			$coord[]=z($this->options->Y[$i]);
		}
		$args[]=$coord;
		$args[]=count($this->options->X);
		list($r,$g,$b)=$this->getRGB(null, null, null, $this->options->Couleur);
		$args[]=imagecolorallocate(Viewer::$image, $r,$g,$b);
		call_user_func_array('imagefilledpolygon', $args);

	}
}

class Agrafer extends Fonction_executable {
	static $champs=array('Y1'=>'quantite','Y2'=>'quantite','Taille_agrafe'=>'quantite');
	static $valeurs_nouveau=array('Y1'=>'[Hauteur]*0.2','Y2'=>'[Hauteur]*0.8','Taille_agrafe'=>'[Hauteur]*0.05');
	static $valeurs_defaut=array('Y1'=>'[Hauteur]*0.2','Y2'=>'[Hauteur]*0.8','Taille_agrafe'=>'[Hauteur]*0.05');
	
	static $descriptions=array('Y1'=>'Marge de la 1&egrave;re agrafe par rapport au haut de la tranche', 
							   'Y1'=>'Marge de la 2&egrave;me agrafe par rapport au haut de la tranche', 
							   'Taille_agrafe'=>'Hauteur de chaque agrafe');
	
	function Agrafer($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;
		$this->options->Y1=self::toTemplatedString($this->options->Y1);
		$this->options->Y2=self::toTemplatedString($this->options->Y2);
		$this->options->Taille_agrafe=self::toTemplatedString($this->options->Taille_agrafe);
		$noir=imagecolorallocate(Viewer::$image, 0, 0, 0);
		imagefilledrectangle(Viewer::$image, Viewer::$largeur/2 -z(.25), z($this->options->Y1), Viewer::$largeur/2 +z(.25), z($this->options->Y1+$this->options->Taille_agrafe), $noir);
		imagefilledrectangle(Viewer::$image, Viewer::$largeur/2 -z(.25), z($this->options->Y2), Viewer::$largeur/2 +z(.25), z($this->options->Y2+$this->options->Taille_agrafe), $noir);
	}
}

class Degrade extends Fonction_executable {
	static $champs=array('Couleur_debut'=>'couleur','Couleur_fin'=>'couleur','Sens'=>'liste','Pos_x_debut'=>'quantite','Pos_x_fin'=>'quantite','Pos_y_debut'=>'quantite','Pos_y_fin'=>'quantite');
	static $valeurs_nouveau=array('Couleur_debut'=>'D01721','Couleur_fin'=>'0000FF','Sens'=>'Vertical','Pos_x_debut'=>'3','Pos_x_fin'=>'[Largeur]-3','Pos_y_debut'=>'3','Pos_y_fin'=>'[Hauteur]*0.5');
	static $valeurs_defaut=array();
	
	static $descriptions=array('Couleur_debut'=>'Couleur du d&eacute;but du d&eacute;grad&eacute;', 
							   'Couleur_fin'=>'Couleur du fin du d&eacute;grad&eacute;',  
							   'Sens'=>'"Horizontal" (de gauche &agrave; droite) ou "Vertical" (de haut en bas)',  
							   'Pos_x_debut'=>'Marge du d&eacute;but du d&eacute;grad&eacute; par rapport &agrave; la gauche de la tranche',
							   'Pos_x_fin'=>'Marge de la fin du d&eacute;grad&eacute; par rapport &agrave; la gauche de la tranche',
							   'Pos_y_debut'=>'Marge du d&eacute;but du d&eacute;grad&eacute; par rapport au haut de la tranche',
							   'Pos_y_fin'=>'Marge de la fin du d&eacute;grad&eacute; par rapport au haut de la tranche');
	
	function Degrade($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;

		$this->options->Pos_x_debut=z(self::toTemplatedString($this->options->Pos_x_debut));
		$this->options->Pos_x_fin=z(self::toTemplatedString($this->options->Pos_x_fin));
		$this->options->Pos_y_debut=z(self::toTemplatedString($this->options->Pos_y_debut));
		$this->options->Pos_y_fin=z(self::toTemplatedString($this->options->Pos_y_fin));
		list($r1,$g1,$b1)=$this->getRGB(null, null, null, $this->options->Couleur_debut);
		list($r2,$g2,$b2)=$this->getRGB(null, null, null, $this->options->Couleur_fin);
		$couleur1=array($r1,$g1,$b1);
		$couleur2=array($r2,$g2,$b2);
		if ($this->options->Sens == 'Horizontal') {
			if ($this->options->Pos_x_debut < $this->options->Pos_x_fin) {
				$couleurs_inter=self::getMidColors($couleur1, $couleur2, abs($this->options->Pos_x_debut-$this->options->Pos_x_fin));
				foreach($couleurs_inter as $i=>$couleur) {
					list($rouge_inter,$vert_inter,$bleu_inter)=$couleur;
					$couleur_allouee=imagecolorallocate(Viewer::$image, $rouge_inter,$vert_inter,$bleu_inter);
					imageline(Viewer::$image, ($this->options->Pos_x_debut)+$i, $this->options->Pos_y_debut, ($this->options->Pos_x_debut)+$i, $this->options->Pos_y_fin, $couleur_allouee);
				}
			}
			else {
				$couleurs_inter=self::getMidColors($couleur1, $couleur2, abs($this->options->Pos_y_debut-$this->options->Pos_y_fin));
				foreach($couleurs_inter as $i=>$couleur) {
					list($rouge_inter,$vert_inter,$bleu_inter)=$couleur;
					$couleur_allouee=imagecolorallocate(Viewer::$image, $rouge_inter,$vert_inter,$bleu_inter);
					imageline(Viewer::$image, ($this->options->Pos_x_debut)-$i, $this->options->Pos_y_debut, ($this->options->$fin)-$i, $this->options->Pos_y_debut, $couleur_allouee);
				}
			}
		}
		else {
			$couleurs_inter=self::getMidColors($couleur1, $couleur2, abs($this->options->Pos_y_debut-$this->options->Pos_y_fin));
			if ($this->options->Pos_y_debut < $this->options->Pos_y_fin) {
				foreach($couleurs_inter as $i=>$couleur) {
					list($rouge_inter,$vert_inter,$bleu_inter)=$couleur;
					$couleur_allouee=imagecolorallocate(Viewer::$image, $rouge_inter,$vert_inter,$bleu_inter);
					imageline(Viewer::$image, $this->options->Pos_x_debut, ($this->options->Pos_y_debut)+$i, $this->options->Pos_x_fin, ($this->options->Pos_y_debut)+$i, $couleur_allouee);
				}
			}
			else {
				foreach($couleurs_inter as $i=>$couleur) {
					list($rouge_inter,$vert_inter,$bleu_inter)=$couleur;
					$couleur_allouee=imagecolorallocate(Viewer::$image, $rouge_inter,$vert_inter,$bleu_inter);
					if (false == imageline(Viewer::$image, $this->options->Pos_x_debut, ($this->options->Pos_y_fin)-$i, $this->options->Pos_x_fin, ($this->options->Pos_y_fin)-$i, $couleur_allouee)) {
						$a=1;
					}
				}
			}
		}
	}
	static function getMidColors($rgb1, $rgb2, $nb) {
		$rgb_mid=array();
		for ($j = 1; $j <= $nb; $j++) {
			$rgb_mid[$j]=array();
			for ($i = 0; $i < 3; $i++) {
				if ($rgb1[$i] < $rgb2[$i]) {
					$rgb_mid[$j][]= round(((max($rgb1[$i], $rgb2[$i]) - min($rgb1[$i], $rgb2[$i])) / ($nb + 1)) * $j + min($rgb1[$i], $rgb2[$i]));
				} else {
					$rgb_mid[$j][]= round(max($rgb1[$i], $rgb2[$i]) - ((max($rgb1[$i], $rgb2[$i]) - min($rgb1[$i], $rgb2[$i])) / ($nb + 1)) * $j);
				}
			}
		}
		return $rgb_mid;
	}
}

class DegradeTrancheAgrafee extends Fonction_executable {
	static $champs=array('Couleur'=>'couleur');
	static $valeurs_nouveau=array('Couleur'=>'D01721');
	static $valeurs_defaut=array();
	
	static $descriptions=array('Couleur'=>'Couleur de la tranche');
	
	function DegradeTrancheAgrafee($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;
		$coef_degrade=1.75;
		list($r1,$g1,$b1)=$this->getRGB(null, null, null, $this->options->Couleur);
		list($r2,$g2,$b2)=array(round($r1/$coef_degrade),round($g1/$coef_degrade),round($b1/$coef_degrade));
		$couleur1=array($r1,$g1,$b1);
		$couleur2=array($r2,$g2,$b2);
		$milieu=round(Viewer::$largeur/2);
		$couleurs_inter=Degrade::getMidColors($couleur1, $couleur2, $milieu);
		imageline(Viewer::$image, $milieu, 0, $milieu, Viewer::$hauteur, imagecolorallocate(Viewer::$image, $r1,$g1,$b1));
		foreach($couleurs_inter as $i=>$couleur) {
			list($rouge_inter,$vert_inter,$bleu_inter)=$couleur;
			$couleur_allouee=imagecolorallocate(Viewer::$image, $rouge_inter,$vert_inter,$bleu_inter);
			imageline(Viewer::$image, $milieu+$i, 0, $milieu+$i, Viewer::$hauteur, $couleur_allouee);
			imageline(Viewer::$image, $milieu-$i, 0, $milieu-$i, Viewer::$hauteur, $couleur_allouee);
		}
		$noir=imagecolorallocate(Viewer::$image, 0, 0, 0);
		imagefilledrectangle(Viewer::$image, $milieu -z(.25), Viewer::$hauteur*0.2, $milieu +z(.25), Viewer::$hauteur*0.2+Viewer::$hauteur*0.05, $noir);
		imagefilledrectangle(Viewer::$image, $milieu -z(.25), Viewer::$hauteur*0.8, $milieu +z(.25), Viewer::$hauteur*0.8+Viewer::$hauteur*0.05, $noir);
	}
}

class Rectangle extends Fonction_executable {
	static $champs=array('Couleur'=>'couleur','Pos_x_debut'=>'quantite','Pos_x_fin'=>'quantite','Pos_y_debut'=>'quantite','Pos_y_fin'=>'quantite','Rempli'=>'liste');
	static $valeurs_nouveau=array('Couleur'=>'D01721','Pos_x_debut'=>'3','Pos_x_fin'=>'[Largeur]-3','Pos_y_debut'=>'3','Pos_y_fin'=>'[Hauteur]*0.5','Rempli'=>'Non');
	static $valeurs_defaut=array();
	
	static $descriptions=array('Couleur'=>'Couleur du rectangle', 
							   'Pos_x_debut'=>'Marge du d&eacute;but du rectangle par rapport &agrave; la gauche de la tranche',
							   'Pos_x_fin'=>'Marge de la fin du rectangle par rapport &agrave; la gauche de la tranche',
							   'Pos_y_debut'=>'Marge du d&eacute;but du rectangle par rapport au haut de la tranche',
							   'Pos_y_fin'=>'Marge de la fin du rectangle par rapport au haut de la tranche',
							   'Rempli'=>'"Oui" pour dessiner un rectangle rempli, "Non" pour dessiner seulement le contour');
	
	function Rectangle($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;
		$this->options->Pos_x_debut=z(self::toTemplatedString($this->options->Pos_x_debut));
		$this->options->Pos_x_fin=z(self::toTemplatedString($this->options->Pos_x_fin));
		$this->options->Pos_y_debut=z(self::toTemplatedString($this->options->Pos_y_debut));
		$this->options->Pos_y_fin=z(self::toTemplatedString($this->options->Pos_y_fin));

		list($r,$g,$b)=$this->getRGB(null, null, null, $this->options->Couleur);
		$couleur=imagecolorallocate(Viewer::$image, $r, $g, $b);
		if ($this->options->Rempli=='Oui')
			imagefilledrectangle(Viewer::$image, $this->options->Pos_x_debut, $this->options->Pos_y_debut, $this->options->Pos_x_fin, $this->options->Pos_y_fin, $couleur);
		else
			imagerectangle(Viewer::$image, $this->options->Pos_x_debut, $this->options->Pos_y_debut, $this->options->Pos_x_fin, $this->options->Pos_y_fin, $couleur);
	}
}

class Arc_cercle extends Fonction_executable {
	static $champs=array('Couleur'=>'couleur','Pos_x_centre'=>'quantite','Pos_y_centre'=>'quantite','Largeur'=>'quantite','Hauteur'=>'quantite','Angle_debut'=>'quantite','Angle_fin'=>'quantite','Rempli'=>'liste');
	static $valeurs_nouveau=array('Couleur'=>'BBBBBB','Pos_x_centre'=>'10','Pos_y_centre'=>'50','Largeur'=>'10','Hauteur'=>'20','Angle_debut'=>'0','Angle_fin'=>'360','Rempli'=>'Non');
	static $valeurs_defaut=array();
	
	static $descriptions=array('Couleur'=>'Couleur de l\'arc de cercle', 
							   'Pos_x_centre'=>'Marge du centre de l\arc par rapport &agrave; la gauche de la tranche',
							   'Pos_y_centre'=>'Marge du centre de l\arc par rapport au haut de la tranche',
							   'Largeur'=>'Largeur de l\'arc de cercle<br />(Correspond au diam&egrave;tre pour un cercle complet)',
							   'Hauteur'=>'Hauteur de l\'arc de cercle<br />(Correspond au diam&egrave;tre pour un cercle complet)',
							   'Angle_debut'=>'Angle du d&eacute;but de l\'arc de cercle<br />(0 pour un cercle complet)',
							   'Angle_fin'=>'Angle de la fin de l\'arc de cercle<br />(360 pour un cercle complet)',
							   'Rempli'=>'"Oui" pour dessiner un arc de cercle rempli, "Non" pour dessiner seulement le trait');
	
	function Arc_cercle($options,$executer=true,$creation=false,$get_options_defaut=true) {
		parent::Fonction_executable($options,$creation,$get_options_defaut);
		if (!$executer)
			return;
		$this->options->Pos_x_centre=z(self::toTemplatedString($this->options->Pos_x_centre));
		$this->options->Pos_y_centre=z(self::toTemplatedString($this->options->Pos_y_centre));
		$this->options->Largeur=z(self::toTemplatedString($this->options->Largeur));
		$this->options->Hauteur=z(self::toTemplatedString($this->options->Hauteur));

		list($r,$g,$b)=$this->getRGB(null, null, null, $this->options->Couleur);
		$couleur=imagecolorallocate(Viewer::$image, $r, $g, $b);
		if ($this->options->Rempli=='Oui')
			imagefilledarc(Viewer::$image, $this->options->Pos_x_centre, $this->options->Pos_y_centre, $this->options->Largeur, $this->options->Hauteur, $this->options->Angle_debut, $this->options->Angle_fin, $couleur,IMG_ARC_PIE);
		else
			imagearc(Viewer::$image, $this->options->Pos_x_centre, $this->options->Pos_y_centre, $this->options->Largeur, $this->options->Hauteur, $this->options->Angle_debut, $this->options->Angle_fin, $couleur);
	}
}

class Dessiner_contour {
	function Dessiner_contour($dimensions) {
		if (is_null(Viewer::$image))
			Fonction_executable::erreur('Pas d\'infos sur cette tranche');
		else {
			$noir=imagecolorallocate(Viewer::$image, 0, 0, 0);
			for ($i=0;$i<z(0.15);$i++)
				imagerectangle(Viewer::$image, $i, $i, z($dimensions->Dimension_x)-1-$i, z($dimensions->Dimension_y)-1-$i, $noir);
		}
	}
}


function z($valeur) {
	return Viewer::$zoom*$valeur;
}

function est_dans_intervalle($numero,$intervalle) {
	if (is_null($numero))
		return true;
	if ($intervalle=='Tous')
		return true;
	if ($numero==$intervalle)
		return true;
	if (!isset(Modele_tranche::$numeros_dispos)) {
		$m=new Modele_tranche();
		Modele_tranche::$numeros_dispos=$m->get_numeros_disponibles(Modele_tranche::$pays,Modele_tranche::$magazine);
	}
	$numeros_dispos=Modele_tranche::$numeros_dispos;
	if (strpos($intervalle,'~')!==false) {
		$intervalles=explode(';',$intervalle);
		foreach($intervalles as $intervalle) {
			if (strpos($intervalle, '~') === false)
				list($numero_debut,$numero_fin)=array($intervalle,$intervalle);
			else
				list($numero_debut,$numero_fin)=explode('~',$intervalle);
			$numeros_debut[]=$numero_debut;
			$numeros_fin[]=$numero_fin;
		}
	}
	else
		list($numeros_debut,$numeros_fin)=array(explode(';',$intervalle),explode(';',$intervalle));
	
	foreach($numeros_debut as $i=>$numero_debut) {
		$numero_fin=$numeros_fin[$i];
		if ($numero_debut === $numero_fin) {
			if ($numero_debut === $numero)
				return true;
			else
				continue;
		}
		$numero_debut_trouve=false;
		foreach($numeros_dispos as $numero_dispo) {
			if ($numero_dispo==$numero_debut)
				$numero_debut_trouve=true;
			if ($numero_dispo==$numero && $numero_debut_trouve) {
				return true;
			}
			if ($numero_dispo==$numero_fin) 
				continue 2;
		}
	}
	return false;
}

function get_liste($fonction,$type,$arg=null) {
	$liste=array();
	switch($type) {
		case 'Police':
			$rep=BASEPATH.'fonts/';
			$dir = opendir($rep);
			while ($f = readdir($dir)) {
				if (strpos($f,'.ttf')===false)
					continue;
				if(is_file($rep.$f)) {
					$nom=substr($f,0,strlen($f)-strlen('.ttf'));
					$liste[$nom]=$nom;
				}
			}
		 break;
		 case 'Source':
		 	$pays=$arg;
			$rep=Fonction_executable::getCheminElements($pays).'/';
			$dir = opendir($rep);
			while ($f = readdir($dir)) {
				if (strpos($f,'.png')===false)
					continue;
				if(is_file($rep.$f)) {
					$nom=$f;
					$liste[$nom]=utf8_encode($nom);
				}
			}
		 break;
		 case 'Position':
			 $liste['bas']='bas';
			 $liste['haut']='haut';
		 break;
		 case 'Demi_hauteur':case 'Rempli':case 'Mesure_depuis_haut':
			 $liste['Oui']='Oui';
			 $liste['Non']='Non';
		 break;
		 case 'Sens':
			 $liste['Horizontal']='Horizontal';
			 $liste['Vertical']='Vertical';
		 break;
	}
	return $liste;
}


function rgb2hex($r, $g, $b) {
	$hex = "";
	$rgb = array($r, $g, $b);
	for ($i = 0; $i < 3; $i++) {
		if (($rgb[$i] > 255) || ($rgb[$i] < 0)) {
			echo "Error : input must be between 0 and 255";
			return 0;
		}
		$tmp = dechex($rgb[$i]);
		if (strlen($tmp) < 2)
			$hex .= "0" . $tmp;
		else
			$hex .= $tmp;
	}
	return strtoupper($hex);
}

function hex2rgb($color){
	if (strlen($color) != 6){
		return array(0,0,0);
	}
	$rgb = array();
	for ($x=0;$x<3;$x++){
		$rgb[$x] = hexdec(substr($color,(2*$x),2));
	}
	return $rgb;
}

function getNumerosDebutFinShort($intervalle=null) {
	if (is_null($intervalle))
		return array(self::$numero_debut,self::$numero_fin);
	$numero_debut_fin=explode('~',$intervalle);
	if (count($numero_debut_fin)==2)
		return explode('~',$intervalle);
	else
		return array($intervalle,$intervalle);
}

function decomposer_numero ($numero) {
	if ($numero=='Tous') return array('Tous','Tous');
	$regex_partie_numerique='#([A-Z]*)([0-9]*)#is';
	preg_match($regex_partie_numerique, $numero,$resultat_numero_debut);
	if (!array_key_exists(1, $resultat_numero_debut)) {
		$a=1;
	}
	return array($resultat_numero_debut[1],$resultat_numero_debut[2]);
}

function trier_intervalles($intervalle1,$intervalle2) {
	if (is_array($intervalle1)) {
		usort($intervalle1,'trier_intervalles');
		usort($intervalle2,'trier_intervalles');
		$intervalle1=$intervalle1[0];
		$intervalle2=$intervalle2[0];
	}
	list($numero_debut1,$numero_fin1)=getNumerosDebutFinShort($intervalle1);
	list($numero_debut2,$numero_fin2)=getNumerosDebutFinShort($intervalle2);
	list($partie_litterale_debut1,$partie_numerale_debut1)=decomposer_numero($numero_debut1);
	list($partie_litterale_debut2,$partie_numerale_debut2)=decomposer_numero($numero_debut2);
	if (($partie_litterale_debut1 < $partie_litterale_debut2) || ($partie_litterale_debut1 == $partie_litterale_debut2) && ($partie_numerale_debut1 < $partie_numerale_debut2))
	return -1;
	elseif (($partie_litterale_debut1 == $partie_litterale_debut2) && ($partie_numerale_debut1 == $partie_numerale_debut2))
	return 0;
	else
	return 1;
}

function imagettfbbox_t($size, $angle, $fontfile, $text){
	// compute size with a zero angle
	$coords = imagettfbbox($size, 0, $fontfile, $text);
	// convert angle to radians
	$a = deg2rad($angle);
	// compute some usefull values
	$ca = cos($a);
	$sa = sin($a);
	$ret = array();
	// perform transformations
	for($i = 0; $i < 7; $i += 2){
		$ret[$i] = round($coords[$i] * $ca + $coords[$i+1] * $sa);
		$ret[$i+1] = round($coords[$i+1] * $ca - $coords[$i] * $sa);
			}
			return $ret;
		}
	
		function calculateTextBox($text,$fontFile,$fontSize,$fontAngle) {
		  $rect = imagettfbbox_t($fontSize,$fontAngle,$fontFile,$text);
	
		  $minX = min(array($rect[0],$rect[2],$rect[4],$rect[6]));
		  $maxX = max(array($rect[0],$rect[2],$rect[4],$rect[6]));
		  $minY = min(array($rect[1],$rect[3],$rect[5],$rect[7]));
		  $maxY = max(array($rect[1],$rect[3],$rect[5],$rect[7]));
	
		  return array(
			"left"   => abs($minX),
			"top"	=> abs($minY),
			"width"  => $maxX - $minX,
			"height" => $maxY - $minY,
			"box"	=> $rect
		  );
		}
?>