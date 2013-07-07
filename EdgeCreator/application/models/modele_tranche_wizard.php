<?php
include_once(BASEPATH.'/../../Inducks.class.php');
include_once(BASEPATH.'/../application/models/modele_tranche.php');
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
										   .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND username = \''.$username.'\'';
			$user_possede_modele = $this->db->query($requete_modele_magazine_existe)->first_row()->cpt > 0;
		}
		return $user_possede_modele;
	}
	
	function get_tranches_en_cours($id=null,$pays=null,$magazine=null,$numero=null) {
		$requete='SELECT ID, Pays, Magazine, Numero '
				.'FROM tranches_en_cours_modeles '
				.'WHERE username=\''.mysql_real_escape_string(self::$username).'\' AND Active=1';
		if (!is_null($id)) {
			$requete.=' AND ID='.$id;
		}
		elseif (!is_null($pays)) {
			$requete.=' AND Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Numero=\''.$numero.'\'';
		}
		
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
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' '
				.'AND username = \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\' ';
		if (!is_null($ordre))
			$requete.='AND Ordre='.$ordre.' ';
		$requete.='ORDER BY Ordre';
		$query = $this->db->query($requete);
		$resultats=$query->result();
		foreach($resultats as $resultat)
			$resultats_o[]=new Modele_tranche ($resultat);
		return $resultats_o;
	}

	function get_ordres($pays,$magazine,$numero=null,$toutes_colonnes=false) {
		$resultats_ordres=array();
		$requete=' SELECT DISTINCT '.($toutes_colonnes?'*':'Ordre, Numero')
				.' FROM tranches_en_cours_modeles_vue'
			    .' WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\'';
		if (!is_null($numero)) {
			$requete.=' AND Numero=\''.$numero.'\'';
		}
		$requete.=' AND username = \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\''
				 .' ORDER BY Ordre'; 
		$query = $this->db->query($requete);
		$resultats=$query->result();
		foreach($resultats as $resultat) {
			$resultats_ordres[]=$toutes_colonnes?$resultat:$resultat->Ordre;
		}
		if (!$toutes_colonnes) {
			$resultats_ordres=array_unique($resultats_ordres);
		}
		return $resultats_ordres;
	}

	function get_nb_etapes($pays,$magazine) {
		$resultats_etapes=array();
		$requete='SELECT Count(Nom_fonction) AS cpt '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs ON edgecreator_modeles2.ID = edgecreator_valeurs.ID_Option '
			    .'INNER JOIN edgecreator_intervalles ON edgecreator_valeurs.ID = edgecreator_intervalles.ID_Valeur '
			    .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Option_nom IS NULL '
				.'AND username = \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\'';
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
			    .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero = \''.$numero.'\' '
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
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Ordre='.$ordre.' '
				.'AND username = \''.self::$username.'\' '
				.'AND Numero=\''.$numero.'\'';
		
		$resultat = $this->db->query($requete)->row();
		return count($resultat) == 0 ? null : new Fonction($resultat);
	}

	function get_options($pays,$magazine,$ordre,$numero=null,$creation=false,$inclure_infos_options=false, $nouvelle_etape=false, $nom_option=null) {
		$creation=false;
		$resultats_options=new stdClass();
		$requete='SELECT '.implode(', ', self::$content_fields).' '
				.'FROM tranches_en_cours_modeles_vue '
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero = \''.$numero.'\' AND Ordre='.$ordre.' AND Option_nom IS NOT NULL '
				.'AND username = \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\' ';
		if (!is_null($nom_option))
			$requete.='AND Option_nom = \''.$nom_option.'\' ';
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

	function decaler_etapes_a_partir_de($id_modele,$etape_debut, $inclure_cette_etape) {
		$decalages=array();
		$requete_select='SELECT DISTINCT Ordre '
					   .'FROM tranches_en_cours_valeurs '
					   .'WHERE ID_Modele = '.$id_modele.' '
					     .'AND Ordre'.($inclure_cette_etape ? '>=' : '>').$etape_debut.' '
					   .'ORDER BY Ordre DESC';
		$resultats=$this->db->query($requete_select)->result();
		foreach($resultats as $resultat) {
			$etape=intval($resultat->Ordre);
			$decalages[]=array('old'=>$etape, 'new'=>$etape+1);
		}
		$requete='UPDATE tranches_en_cours_valeurs '
				.'SET Ordre=Ordre+1 ' 
				.'WHERE ID_Modele = '.$id_modele.' '
				  .'AND Ordre'.($inclure_cette_etape ? '>=' : '>').$etape_debut;
		//echo $requete."\n";
		$this->db->query($requete);
		return $decalages;
	}
	
	function valeur_existe($id_valeur) {
		$requete='SELECT ID FROM edgecreator_valeurs WHERE ID='.$id_valeur;
		return $this->db->query($requete)->num_rows() > 0;
	}
	
	function insert($id_modele,$ordre,$nom_fonction,$option_nom,$option_valeur) {
		$option_nom=is_null($option_nom) ? 'NULL' : '\''.preg_replace("#([^\\\\])'#","$1\\'",$option_nom).'\'';
		$option_valeur=is_null($option_valeur) ? 'NULL' : '\''.preg_replace("#([^\\\\])'#","$1\\'",$option_valeur).'\'';
		
		$requete='INSERT INTO tranches_en_cours_valeurs (ID_Modele,Ordre,Nom_fonction,Option_nom,Option_valeur) VALUES '
				.'('.$id_modele.','.$ordre.',\''.$nom_fonction.'\','.$option_nom.','.$option_valeur.') ';
		//echo $requete."\n";
		$this->db->query($requete);
	}
	
	function getIdModele($pays,$magazine,$numero,$username) {
		$requete='SELECT ID FROM tranches_en_cours_modeles '
				.'WHERE Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Numero=\''.$numero.'\' AND username=\''.$username.'\' AND Active=1';
		$resultat=$this->db->query($requete)->row(0);
		return $resultat->ID;
		
	}
	
	function getNomFonction($id_modele,$ordre) {
		$requete='SELECT Nom_fonction FROM tranches_en_cours_valeurs '
				.'WHERE ID_Modele='.$id_modele.' AND Ordre='.$ordre;
		$resultat=$this->db->query($requete)->row(0);
		return $resultat->Nom_fonction;
	}
	
	function creer_modele($pays, $magazine, $numero) {
		$requete='INSERT INTO tranches_en_cours_modeles (Pays, Magazine, Numero, username, Active) '
				.'VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$numero.'\',\''.self::$username.'\', 1)';
		$this->db->query($requete);
		echo $requete."\n";
	}
	
	function get_photo_principale($pays,$magazine,$numero) {
		$requete='SELECT NomPhotoPrincipale FROM tranches_en_cours_modeles '
				.'WHERE Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Numero=\''.$numero.'\'';
		$resultat=$this->db->query($requete)->row(0);
		return $resultat->NomPhotoPrincipale;
	}

	function insert_etape($pays,$magazine,$numero,$pos,$etape,$nom_fonction) {
		$inclure_avant = $pos==='avant' || $pos==='_';
		$id_modele=$this->getIdModele($pays,$magazine,$numero,self::$username);
		$infos=new stdClass();
		
		$infos->decalages=$this->decaler_etapes_a_partir_de($id_modele,$etape, $inclure_avant);
		
		$nouvelle_fonction=new $nom_fonction(false, null, true);
		$numero_etape=$inclure_avant ? $etape : $etape+1;
		foreach($nouvelle_fonction->options as $nom=>$valeur) {
			$this->insert($id_modele,$numero_etape,$nom_fonction,$nom,$valeur);			
		}
		$infos->numero_etape=$numero_etape;
		return $infos;
	}

	function update_etape($pays,$magazine,$numero,$etape,$parametrage) {
		$id_modele=$this->getIdModele($pays,$magazine,$numero,self::$username);
		$nom_fonction=$this->getNomFonction($id_modele,$etape);
		
		$requete_suppr='DELETE valeurs FROM tranches_en_cours_valeurs AS valeurs '
					  .'WHERE ID_Modele='.$id_modele.' AND Ordre='.$etape;
		$this->db->query($requete_suppr);
		echo $requete_suppr."\n";
		
		foreach($parametrage as $parametre=>$valeur) {
			$this->insert($id_modele,$etape,$nom_fonction,$parametre,$valeur);			
		}
	}
	
	function update_photo_principale($pays,$magazine,$numero,$nom_photo_principale) {
		$id_modele=$this->getIdModele($pays,$magazine,$numero,self::$username);
		
		$requete_maj='UPDATE tranches_en_cours_modeles '
					.'SET NomPhotoPrincipale=\''.$nom_photo_principale.'\' '
					.'WHERE ID='.$id_modele;
		$this->db->query($requete_maj);
		echo $requete_maj."\n";
	}

	function cloner_etape($pays,$magazine,$etape_courante,$etape) {
		$requete='SELECT '.implode(', ', self::$fields).' '
				.'FROM edgecreator_modeles2 '
				.'INNER JOIN edgecreator_valeurs AS valeurs ON edgecreator_modeles2.ID = valeurs.ID_Option '
			    .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Ordre='.$etape_courante.' AND username = \''.self::$username.'\'';
		$resultats=$this->db->query($requete)->result();
		foreach($resultats as $resultat) {
			$resultat->Ordre=$etape;
		}
		$this->db->insert_batch('edgecreator_modeles2',$resultats);
	}

	function supprimer_etape($pays,$magazine,$numero,$etape) {
		$requete_suppr='DELETE FROM tranches_en_cours_valeurs '
					  .'WHERE ID_Modele=(SELECT m.ID FROM tranches_en_cours_modeles m '
					  				   .'WHERE m.Pays = \''.$pays.'\' AND m.Magazine = \''.$magazine.'\' AND m.Numero = \''.$numero.'\' AND m.Active=1) '
					  	.'AND Ordre = \''.$etape.'\'';
		$this->db->query($requete_suppr);
		echo $requete_suppr."\n";
	}

	function delete_option($pays,$magazine,$etape,$nom_option) {
		if ($nom_option=='Actif')
			$requete_suppr_option='DELETE modeles, valeurs, intervalles FROM edgecreator_modeles2 modeles '
								  .'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
							      .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
							      .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' '
								  .'AND Ordre='.$etape.' AND Option_nom IS NULL AND username = \''.self::$username.'\'';
		else
			$requete_suppr_option='DELETE modeles, valeurs, intervalles FROM edgecreator_modeles2 modeles '
								  .'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
							      .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
							      .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' '
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
		$requete_ajout_modele='INSERT INTO tranches_en_cours_modeles (Pays, Magazine, Numero, username, Active) '
							 .'VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$nouveau_numero.'\','
							 .'\''.mysql_real_escape_string(self::$username).'\', 1)';
		$this->db->query($requete_ajout_modele);
		$id_modele=$this->get_id_modele_tranche_en_cours_max();
		
		
		$requete_get_options='SELECT '.implode(', ', self::$fields).',username '
						    .'FROM edgecreator_modeles2 AS modeles '
							.'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
					        .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
							.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' '
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
	
	function get_tranches_non_pretes() {
		$username = $this->session->userdata('user');
		$requete=" SELECT ID, Pays,Magazine,Numero"
				." FROM numeros"
				." WHERE ID_Utilisateur=(SELECT ID FROM users WHERE username='$username')"
				."   AND CONCAT(Pays,'/',Magazine,' ',Numero) NOT IN"
				."    (SELECT CONCAT(publicationcode,' ',issuenumber)"
				."   FROM tranches_pretes)"
				." ORDER BY Pays, Magazine, Numero";

		$resultats = Inducks::requete_select($requete, DatabasePriv::$nom_db_DM,'ducksmanager.net');
		
		$publication_codes=array();
		foreach($resultats as $resultat) {
			$publication_codes[]=$resultat['Pays'].'/'.$resultat['Magazine'];
		}
		list($noms_pays,$noms_magazines) = Inducks::get_noms_complets($publication_codes);
		
		foreach($resultats as $i=>$resultat) {
			$resultats[$i]['Magazine_complet'] = $noms_magazines[$resultat['Pays'].'/'.$resultat['Magazine']]
												.' ('.$noms_pays[$resultat['Pays']].')';
		}
		
		return $resultats;
	}
	
	function desactiver_modele($pays,$magazine,$numero) {
		$id_modele=$this->getIdModele($pays,$magazine,$numero,self::$username);
		
		$requete_maj=' UPDATE tranches_en_cours_modeles '
					.' SET Active=0'
					.' WHERE ID='.$id_modele;
		$this->db->query($requete_maj);
		echo $requete_maj."\n";
		
	}
	
	function setNumero($numero) {
		self::$numero=$numero;
	}
}
Modele_tranche_Wizard::$content_fields=array('Ordre', 'Nom_fonction', 'Option_nom', 'Option_valeur');
?>