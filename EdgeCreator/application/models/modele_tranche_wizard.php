<?php
include_once(BASEPATH.'/../../Inducks.class.php');
include_once(BASEPATH.'/../application/models/modele_tranche.php');
Inducks::$use_local_db=true;//strpos($_SERVER['SERVER_ADDR'],'localhost') === false && strpos($_SERVER['SERVER_ADDR'],'127.0.0.1') === false;
		
class Modele_tranche_Wizard extends Modele_tranche {
	static $content_fields;
	static $numero;

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
	
	function get_id_modele($pays,$magazine,$numero,$username=null) {
		if (is_null($username)) {
			$username = self::$username;
		}
		$requete='SELECT ID FROM tranches_en_cours_modeles '
				.'WHERE Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Numero=\''.$numero.'\'';
		if (!is_null($username)) {
			$requete.=' AND username=\''.$username.'\' AND Active=1';
		}
		$resultat=$this->db->query($requete)->row(0);
		return $resultat->ID;
	}
	
	function get_nom_fonction($id_modele,$ordre) {
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
		$id_modele=$this->get_id_modele($pays,$magazine,$numero,self::$username);
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
		$id_modele=$this->get_id_modele($pays,$magazine,$numero,self::$username);
		$nom_fonction=$this->get_nom_fonction($id_modele,$etape);
		
		$requete_suppr='DELETE valeurs FROM tranches_en_cours_valeurs AS valeurs '
					  .'WHERE ID_Modele='.$id_modele.' AND Ordre='.$etape;
		$this->db->query($requete_suppr);
		echo $requete_suppr."\n";
		
		foreach($parametrage as $parametre=>$valeur) {
			$this->insert($id_modele,$etape,$nom_fonction,$parametre,$valeur);			
		}
	}
	
	function update_photo_principale($pays,$magazine,$numero,$nom_photo_principale) {
		$id_modele=$this->get_id_modele($pays,$magazine,$numero,self::$username);
		
		$requete_maj='UPDATE tranches_en_cours_modeles '
					.'SET NomPhotoPrincipale=\''.$nom_photo_principale.'\' '
					.'WHERE ID='.$id_modele;
		$this->db->query($requete_maj);
		echo $requete_maj."\n";
	}

	function cloner_etape($pays,$magazine,$numero,$pos,$etape_courante) {
		$inclure_avant = $pos==='avant' || $pos==='_';
		$id_modele=$this->get_id_modele($pays,$magazine,$numero,self::$username);
		$infos=new stdClass();
		
		$infos->decalages=$this->decaler_etapes_a_partir_de($id_modele,$etape_courante, $inclure_avant);
		
		$nouvelle_etape=$inclure_avant ? $etape_courante : $etape_courante+1;		
		$requete=' SELECT Nom_fonction, Option_nom, Option_valeur, ID_Modele'
				.' FROM tranches_en_cours_valeurs '
				.' WHERE ID_Modele='.$id_modele.' AND Ordre='.$etape_courante;
		$resultats=$this->db->query($requete)->result();
		foreach($resultats as $i=>$resultat) {
			$resultat->Ordre=$nouvelle_etape;
			$infos->nom_fonction=$resultat->Nom_fonction;
			$resultats[$i]=(array) $resultats[$i];
		}
		$this->db->insert_batch('tranches_en_cours_valeurs',$resultats);
		
		$infos->numero_etape=$nouvelle_etape;
		return $infos;
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
	
	function get_id_modele_tranche_en_cours_max() {
		$requete='SELECT MAX(ID) AS Max FROM tranches_en_cours_modeles';
		return $this->db->query($requete)->first_row()->Max;
	}
	
	function get_id_valeur_max() {
		$requete='SELECT MAX(ID) AS Max FROM edgecreator_valeurs';
		return $this->db->query($requete)->first_row()->Max;
	}

	function etendre_numero ($pays,$magazine,$numero,$nouveau_numero) {

		$options = $this->get_valeurs_options($pays,$magazine, array($numero));
		
		if (count($options) > 0) {
			echo 'Aucune option d\'étape pour '.$pays.'/'.$magazine.' '.$numero;
			return;
		}
		
		$requete_ajout_modele='INSERT INTO tranches_en_cours_modeles (Pays, Magazine, Numero, username, Active) '
							 .'VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$nouveau_numero.'\','
							 .'\''.mysql_real_escape_string(self::$username).'\', 1)';
		$this->db->query($requete_ajout_modele);
		$id_modele=$this->get_id_modele_tranche_en_cours_max();
		
		foreach($options[$numero] as $option) {
			$requete_ajout_valeur=' INSERT INTO tranches_en_cours_valeurs (ID_Modele, Ordre, Nom_fonction, Option_nom, Option_valeur)'
								 .' VALUES ('.$id_modele .',\''.$option->Ordre.'\',\''.$option->Nom_fonction.'\','
								 .' '.$option->Option_nom.','.$option->Option_valeur.')';
			$this->db->query($requete_ajout_valeur);
		}
		
		// Suppression des étapes incomplètes = étapes dont le nombre d'options est différent de celui défini
		
		foreach(self::$noms_fonctions as $nom_fonction) {
			$champs_obligatoires = array_diff(array_keys($nom_fonction::$champs), array_keys($nom_fonction::$valeurs_defaut));
			
			$requete_nettoyage = ' SELECT Ordre, Option_nom'
								.' FROM tranches_en_cours_modeles_vue'
								.' WHERE ID_Modele='.$id_modele.' AND Nom_fonction=\''.$nom_fonction.'\''
								.' ORDER BY Ordre';
			$resultats=$this->db->query($requete_nettoyage)->result();
			$etapes_et_options=array();
			foreach($resultats as $resultat) {
				if (!array_key_exists($resultat->Ordre, $etapes_et_options)) {
					$etapes_et_options[$resultat->Ordre]=array();
				}
				$etapes_et_options[$resultat->Ordre][]=$resultat->Option_nom;
				echo "Etape ".$resultat->Ordre.', option '.$resultat->Option_nom."\n";
			}
			
			foreach($etapes_et_options as $etape=>$options) {
				$champs_obligatoires_manquants = array_diff($champs_obligatoires, $options);
				if (count($champs_obligatoires_manquants) > 0) {
					echo utf8_encode("\nEtape $etape : l'étape sera supprimée car les champs suivants ne sont pas renseignés : "
									 .implode(', ', $champs_obligatoires_manquants)."\n");
					$requete_suppression_etape=' DELETE FROM tranches_en_cours_valeurs'
											  .' WHERE ID_Modele='.$id_modele.' AND Ordre='.$etape;
					$this->db->query($requete_suppression_etape);
				}
			}
		}		
	}
	
	function get_tranches_non_pretes() {
		$username = $this->session->userdata('user');
		$id_user = $this->username_to_id($username);
		$requete=" SELECT ID, Pays,Magazine,Numero"
				." FROM numeros"
				." WHERE ID_Utilisateur=".$id_user
				."   AND CONCAT(Pays,'/',Magazine,' ',Numero) NOT IN"
				."    (SELECT CONCAT(publicationcode,' ',issuenumber)"
				."   FROM tranches_pretes)"
				." ORDER BY Pays, Magazine, Numero";

		$resultats = $this->requete_select_dm($requete);
		
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
		$id_modele=$this->get_id_modele($pays,$magazine,$numero,self::$username);
		
		$requete_maj=' UPDATE tranches_en_cours_modeles '
					.' SET Active=0'
					.' WHERE ID='.$id_modele;
		$this->db->query($requete_maj);
		echo $requete_maj."\n";
	}
	
	function get_couleurs_frequentes($id_modele) {
		$couleurs=array();
		$requete= ' SELECT DISTINCT Option_valeur'
				 .' FROM tranches_en_cours_modeles_vue'
				 .' WHERE ID_Modele='.$id_modele.' AND Option_nom LIKE \'Couleur%\'';
		$resultats=$this->db->query($requete)->result();
		foreach($resultats as $i=>$resultat) {
			$couleurs[]=$resultat->Option_valeur;
		}
		return $couleurs;
	}
	
	function get_couleur_point_photo($pays,$magazine,$numero,$frac_x,$frac_y) {
		$id_modele=$this->Modele_tranche->get_id_modele($pays,$magazine,$numero);
		$requete_nom_photo = ' SELECT NomPhotoPrincipale'
							.' FROM tranches_en_cours_modeles'
							.' WHERE ID='.$id_modele;
		$resultat_nom_photo = $this->db->query($requete_nom_photo)->row();
		
		$chemin_photos = Fonction_executable::getCheminPhotos($pays);
		$chemin_photo_tranche = $chemin_photos.'/'.$resultat_nom_photo->NomPhotoPrincipale;
		$image = imagecreatefromjpeg($chemin_photo_tranche);
		list($width, $height) = getimagesize($chemin_photo_tranche);
		
		$rgb = imagecolorat($image, $frac_x*$width, $frac_y*$height);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		return rgb2hex($r,$g,$b);
	}
	
	function setNumero($numero) {
		self::$numero=$numero;
	}
}
Modele_tranche_Wizard::$content_fields=array('Ordre', 'Nom_fonction', 'Option_nom', 'Option_valeur');
?>