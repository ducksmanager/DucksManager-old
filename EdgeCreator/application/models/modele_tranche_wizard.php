<?php
include_once(BASEPATH.'/../../Inducks.class.php');
include_once(BASEPATH.'/../application/models/modele_tranche.php');
Inducks::$use_db=true;
Inducks::$use_local_db=true;//strpos($_SERVER['SERVER_ADDR'],'localhost') === false && strpos($_SERVER['SERVER_ADDR'],'127.0.0.1') === false;
		
class Modele_tranche_Wizard extends Modele_tranche {
	static $content_fields;
	static $numero;

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
	
	function get_tranches_en_cours() {
		$requete='SELECT Pays, Magazine, Numero '
				.'FROM tranches_en_cours_modeles '
				.'WHERE username=\''.mysql_real_escape_string(self::$username).'\'';
		
		$query = $this->db->query($requete);
		$resultats=$query->result();
		$liste_pays=array();
		foreach($resultats as $resultat) {
			$resultat->Pays_complet='';
			$resultat->Magazine_complet='';
			if (!in_array($resultat->Pays,$liste_pays))
				$liste_pays[]=$resultat->Pays;
		}
		
		$noms_pays = Inducks::get_pays();
		foreach($liste_pays as $pays) {
			$noms_magazines=Inducks::get_liste_magazines($pays);
			foreach($resultats as $resultat) {
				if ($resultat->Pays == $pays) {
					$resultat->Pays_complet=$noms_pays[$resultat->Pays];
					$resultat->Magazine_complet=$noms_magazines[$resultat->Magazine];
				}
			}
			
		}
		return $resultats;
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

	function get_etapes_simple($pays,$magazine,$numero,$num_etape=null) {
		$resultats_etapes=array();
		$username=($this->user_possede_modele() ? self::$username : 'brunoperel');
		$requete='SELECT '.implode(', ', self::$content_fields).' '
				.'FROM tranches_en_cours_modeles_vue '
			    .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero = \''.$numero.'\' AND Option_nom IS NULL '
				.'AND username = \''.self::$username.'\' ';
		if (!is_null($num_etape))
			$requete.='AND Ordre='.$num_etape.' ';
		$requete.=' GROUP BY Ordre'
				 .' ORDER BY Ordre ';
		$resultats = $this->db->query($requete)->result();
		return $resultats;
	}

	function get_fonction($pays,$magazine,$ordre,$numero) {
		$resultats_fonctions=array();
		$requete='SELECT '.implode(', ', self::$content_fields).' '
				.'FROM tranches_en_cours_modeles_vue '
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Ordre='.$ordre.' AND Option_nom IS NULL '
				.'AND username = \''.self::$username.'\' '
				.'AND Numero=\''.$numero.'\'';
		
		$query = $this->db->query($requete);
		$resultats=$query->result();
		if (count($resultats) == 0) {
			return null;
		}
		return new Fonction($resultats[0]);
	}

	function get_options($pays,$magazine,$ordre,$numero=null,$creation=false,$inclure_infos_options=false, $nouvelle_etape=false, $nom_option=null) {
		$creation=false;
		$resultats_options=new stdClass();
		$requete='SELECT '.implode(', ', self::$content_fields).' '
				.'FROM tranches_en_cours_modeles_vue '
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero = \''.$numero.'\' AND Ordre='.$ordre.' AND Option_nom IS NOT NULL '
				.'AND username LIKE \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\' ';
		if (!is_null($nom_option))
			$requete.='AND Option_nom LIKE \''.$nom_option.'\' ';
		$requete.='ORDER BY Option_nom ASC';
		
		$resultats=$this->db->query($requete)->result();
		$resultats_options=new stdClass();
		foreach($resultats as $resultat) {
			$nom_fonction=$resultat->Nom_fonction;
			$option_nom=$resultat->Option_nom;
			$valeur=$resultat->Option_valeur;
			$resultats_options->$option_nom=$valeur;
		}
		$f=new $nom_fonction($resultats_options,false,$creation,!$nouvelle_etape); // Ajout des champs avec valeurs par défaut
		if ($inclure_infos_options) {
			$prop_champs=new ReflectionProperty(get_class($f), 'champs');
			$champs=$prop_champs->getValue();
			$prop_valeurs_defaut=new ReflectionProperty(get_class($f), 'valeurs_defaut');
			$valeurs_defaut=$prop_valeurs_defaut->getValue();
			$prop_descriptions=new ReflectionProperty(get_class($f), 'descriptions');
			$descriptions=$prop_descriptions->getValue();
			foreach($f->options as $nom_option=>$option) {
				$intervalles_option=array();
				$intervalles_option['valeur']=$f->options->$nom_option;
				$intervalles_option['type']=$champs[$nom_option];
				$intervalles_option['description']=isset($descriptions[$nom_option]) ? $descriptions[$nom_option] : '';
				if (array_key_exists($nom_option, $valeurs_defaut))
					$intervalles_option['valeur_defaut']=$valeurs_defaut[$nom_option];
				$f->options->$nom_option=$intervalles_option;
			}
		}
		return $f->options;
	}

	function has_no_option($pays,$magazine,$etape) {
		$requete='SELECT Option_nom '
				.'FROM tranches_en_cours_modeles_vue '
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Option_nom IS NOT NULL '
				.'AND username = \''.self::$username.'\'';
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
	
	function valeur_existe($id_valeur) {
		$requete='SELECT ID FROM edgecreator_valeurs WHERE ID='.$id_valeur;
		return $this->db->query($requete)->num_rows() > 0;
	}
	
	function insert($id_modele,$ordre,$option_nom,$option_valeur) {
		$option_nom=is_null($option_nom) ? 'NULL' : '\''.preg_replace("#([^\\\\])'#","$1\\'",$option_nom).'\'';
		$option_valeur=is_null($option_valeur) ? 'NULL' : '\''.preg_replace("#([^\\\\])'#","$1\\'",$option_valeur).'\'';
		
		$requete='INSERT INTO tranches_en_cours_valeurs (ID_Modele,Ordre,Nom_fonction,Option_nom,Option_valeur) VALUES '
				.'('.$id_modele.','.$ordre.',\''.$nom_fonction.'\',\''.$option_nom.'\',\''.$option_valeur.'\') ';
		echo $requete."\n";
		$this->db->query($requete);
			
	}
	
	function getIdModele($pays,$magazine,$numero,$username) {
		$requete='SELECT ID FROM tranches_en_cours_modeles '
				.'WHERE Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Numero=\''.$numero.'\' AND username=\''.$username.'\'';
		$resultat=$this->db->query($requete)->row(0);
		return $resultat->ID;
		
	}
	
	function getNomFonction($id_modele,$ordre) {
		$requete='SELECT Nom_fonction FROM tranches_en_cours_valeurs '
				.'WHERE ID_Modele='.$id_modele.' AND Ordre='.$ordre;
		$resultat=$this->db->query($requete)->row(0);
		return $resultat->Nom_fonction;
	}

	function update_ordre($pays,$magazine,$numero,$ordre,$parametrage) {
		$id_modele=$this->getIdModele($pays,$magazine,$numero,self::$username);
		$nom_fonction=$this->getNomFonction($id_modele,$ordre);
		
		$requete_suppr='DELETE valeurs FROM tranches_en_cours_valeurs AS valeurs '
					  .'WHERE ID_Modele='.$id_modele;
		$this->db->query($requete_suppr);
		echo $requete_suppr."\n";
		
		
		foreach($parametrage as $option_nom_intervalle=>$option_valeur) {
			$option_valeur=str_replace("'","\'",$option_valeur);
			list($option_nom,$intervalle)=explode('.',$option_nom_intervalle);
			list($numero_debut,$numero_fin)=explode('~',$intervalle);
			
			$this->insert($id_modele,$ordre,$option_nom,$option_valeur);
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
								  .'AND Ordre='.$etape.' AND Option_nom IS NULL AND username = \''.self::$username.'\'';
		else
			$requete_suppr_option='DELETE modeles, valeurs, intervalles FROM edgecreator_modeles2 modeles '
								  .'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
							      .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
							      .'WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' '
								  .'AND Ordre='.$etape.' AND Option_nom = \''.$nom_option.'\' AND username = \''.self::$username.'\'';
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
	
	function get_id_modele_tranche_en_cours_max() {
		$requete='SELECT MAX(ID) AS Max FROM tranches_en_cours_modeles';
		return $this->db->query($requete)->first_row()->Max;
	}
	
	function get_id_valeur_max() {
		$requete='SELECT MAX(ID) AS Max FROM edgecreator_valeurs';
		return $this->db->query($requete)->first_row()->Max;
	}

	function etendre_numero ($pays,$magazine,$numero,$nouveau_numero) {
		$requete_ajout_modele='INSERT INTO tranches_en_cours_modeles (Pays, Magazine, Numero, username) '
							 .'VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$nouveau_numero.'\','
							 .'\''.mysql_real_escape_string(self::$username).'\')';
		$this->db->query($requete_ajout_modele);
		$id_modele=$this->get_id_modele_tranche_en_cours_max();
		
		
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
			$option_nom=is_null($resultat->Option_nom) ? 'NULL' : ('\''.mysql_real_escape_string($resultat->Option_nom).'\'');
			$option_valeur=is_null($resultat->Option_valeur) ? 'NULL' : ('\''.mysql_real_escape_string($resultat->Option_valeur).'\'');
			$intervalle=$this->getIntervalleShort($this->getIntervalle($resultat->Numero_debut, $resultat->Numero_fin));
			if (est_dans_intervalle($numero,$intervalle)) {
				$requete_ajout_valeur='INSERT INTO tranches_en_cours_valeurs (ID_Modele, Ordre, Nom_fonction, Option_nom, Option_valeur) '
									 .'VALUES ('.$id_modele.',\''.$resultat->Ordre.'\',\''.$resultat->Nom_fonction.'\', '
											  .(is_null($resultat->Option_nom) ? 'NULL' : '\''.mysql_real_escape_string($resultat->Option_nom).'\'').','
											  .(is_null($resultat->Option_valeur) ? 'NULL' : '\''.mysql_real_escape_string($resultat->Option_valeur).'\'').')';
				$this->db->query($requete_ajout_valeur);
			}
		}
	}
	
	function setNumero($numero) {
		self::$numero=$numero;
	}
}
Modele_tranche_Wizard::$fields=array('username', 'Pays', 'Magazine', 'Numero', 'Ordre', 'Nom_fonction', 'Option_nom', 'Option_valeur');
Modele_tranche_Wizard::$content_fields=array('Ordre', 'Nom_fonction', 'Option_nom', 'Option_valeur');
?>