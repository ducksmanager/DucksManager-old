<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Liste.class.php');
require_once('Inducks.class.php');

Database::$etats=array(
   'mauvais'=>array(MAUVAIS,'#FF0000'),
   'moyen'=>array(MOYEN,'#FF8000'),
   'bon'=>array(BON,'#2CA77B'),
   'indefini'=>array(INDEFINI,'#808080'));

class Database {
	public static $etats;
	var $server;
	var $database;
	var $user;
	var $password;


	function Database() {
			require_once('_priv/Database.priv.class.php');
			return DatabasePriv::connect();
	}

	function connect($user,$password) {
			$this->user=$user;
			$this->password=$password;
	}

	function requete_select($requete) {
			$requete_resultat=mysql_query($requete);
			$arr=array();
			while($arr_tmp=mysql_fetch_array($requete_resultat))
					array_push($arr,$arr_tmp);
			return $arr;
	}

	function requete($requete) {
			return mysql_query($requete);
	}
	
	function user_to_id($user) {
			if ((!isset($user) || empty($user)) && (isset($_COOKIE['user']) && isset($_COOKIE['pass']))) {
					$user=$_COOKIE['user'];
			}
			$requete='SELECT ID FROM users WHERE username LIKE \''.$user.'\'';
			$resultat=DM_Core::$d->requete_select($requete);
			return $resultat[0]['ID'];
	}

	function user_connects($user,$pass) {
			if (!$this->user_exists($user)) {
				return false;
			}
			else {
				$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\') AND password LIKE(\''.$pass.'\')';
				return (count(DM_Core::$d->requete_select($requete))>0);
			}
	}

	function user_exists($user) {
			$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\')';
			return (count(DM_Core::$d->requete_select($requete))>0);

	}

	function user_is_beta() {
		if (isset($_SESSION['user'])) {
			$requete_beta_user='SELECT BetaUser FROM users WHERE username LIKE \''.$_SESSION['user'].'\'';
			$resultat_beta=DM_Core::$d->requete_select($requete_beta_user);
			return $resultat_beta[0]['BetaUser']==1;
		}
		return false;
	}

	function user_afficher_video() {
		if (isset($_SESSION['user'])) {
			$requete_afficher_video='SELECT AfficherVideo FROM users WHERE username LIKE \''.$_SESSION['user'].'\'';
			$resultat_afficher_video=DM_Core::$d->requete_select($requete_afficher_video);
			return $resultat_afficher_video[0]['AfficherVideo']==1;
		}
		return false;
	}

	function nouveau_user($user,$email,$pass) {
			date_default_timezone_set('Europe/Paris');
			$requete='INSERT INTO users(username,password,EMail,DateInscription) VALUES(\''.$user.'\',\''.$pass.'\',\''.$email.'\',\''.date('Y-m-d').'\')';
			if (false===DM_Core::$d->requete($requete)) {
				echo ERREUR_EXECUTION_REQUETE;
				return false;
			}
			return true;
	}
	
	function maintenance_ordre_magazines($id_user) {
		$requete_get_max_ordre='SELECT MAX(Ordre) AS m FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user;
		$resultat_get_max_ordre=DM_Core::$d->requete_select($requete_get_max_ordre);
		$max=is_null($resultat_get_max_ordre[0]['m'])?-1:$resultat_get_max_ordre[0]['m'];
		$cpt=0;				 
		$l=DM_Core::$d->toList($id_user);
		foreach($l->collection as $pays=>$magazines) {
			foreach(array_keys($magazines) as $magazine) {
				$requete_verif_ordre_existe='SELECT Ordre FROM bibliotheque_ordre_magazines WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND ID_Utilisateur='.$id_user;
				$resultat_verif_ordre_existe=DM_Core::$d->requete_select($requete_verif_ordre_existe);
				$ordre_existe=count($resultat_verif_ordre_existe) > 0;
				if (!$ordre_existe) {
					$requete_set_ordre='INSERT INTO bibliotheque_ordre_magazines(Pays,Magazine,Ordre,ID_Utilisateur) '
									  .'VALUES (\''.$pays.'\',\''.$magazine.'\','.($max+1).','.$id_user.')';
					DM_Core::$d->requete($requete_set_ordre);
					$max++;
				}
				$cpt++;
			}
		}
		$requete_liste_ordres='SELECT Pays,Magazine,Ordre FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user;
		$resultat_liste_ordres=DM_Core::$d->requete_select($requete_liste_ordres);
		foreach($resultat_liste_ordres as $ordre) {
			$pays=$ordre['Pays'];
			$magazine=$ordre['Magazine'];
			if (!array_key_exists($pays, $l->collection) || !array_key_exists($magazine, $l->collection[$pays])) {
				$requete_suppr_ordre='DELETE FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user.' AND Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\'';
				DM_Core::$d->requete($requete_suppr_ordre);
			}
		}
	}

	function get_nom_complet_magazine($pays,$magazine,$encode=false) {
		return Inducks::get_nom_complet_magazine($pays,$magazine);
	}

	function get_noms_complets_pays() {
		return Inducks::get_pays();
	}

	function liste_etats() {
		$etats=array();
		foreach(self::$etats as $etat_court=>$infos_etat) {
			if ($etat_court!='indefini') {
				$etats[]=$infos_etat[0];
			}
		}
		echo implode('~', $etats);
	}

	function liste_numeros_externes_dispos($id_user) {
		$requete_numeros_externes = 'SELECT Id_Utilisateur, Pays,Magazine,Numero,Etat,AV FROM numeros WHERE (Id_Utilisateur<>'.$id_user.' AND AV=1) ORDER BY Id_Utilisateur, Pays, Magazine';
		$numeros_externes = DM_Core::$d->requete_select($requete_numeros_externes);
		if (count($numeros_externes) != 0) {
			$requete_pseudos_utilisateurs = 'SELECT ID, username FROM users';
			$liste_utilisateurs = array();
			$liste_utilisateurs_resultat = DM_Core::$d->requete_select($requete_pseudos_utilisateurs);
			foreach ($liste_utilisateurs_resultat as $utilisateur) {
				$liste_utilisateurs[$utilisateur['ID']] = $utilisateur['username'];
			}
			$id_utilisateur = '';
			$pays = '';
			foreach ($numeros_externes as $numero) {
				if ($numero['Pays'] != $pays)
					$liste_magazines = Inducks::get_liste_magazines($numero['Pays']);
				$pays = $numero['Pays'];
				$requete_possede = 'SELECT Count(Numero) AS c FROM numeros WHERE (ID_Utilisateur='
								  .$id_user.' AND Pays LIKE \'' .$numero['Pays'].'\' AND Magazine LIKE \''
								  .$numero['Magazine'].'\' AND Numero LIKE \''.$numero['Numero'].'\')';
				$resultat_possede = DM_Core::$d->requete_select($requete_possede);
				if ($resultat_possede[0]['c'] == 0) {
					if ($id_utilisateur != $numero['Id_Utilisateur']) {
						if (!empty($id_utilisateur)) {
							?></ul><br /><?php
						}
						?><ul><b><?=utf8_decode($liste_utilisateurs[$numero['Id_Utilisateur']])?></b> <?=PROPOSE_LES_NUMEROS?>
					<?php } 
					list($nom_complet_pays,$nom_complet_magazine)=DM_Core::$d->get_nom_complet_magazine($numero['Pays'],$numero['Magazine']);
					?>
					<li>
						<?=$nom_complet_magazine?> (<?=$nom_complet_pays?>) n&deg;<?=$numero['Numero']?>
					</li><?php
					$id_utilisateur = $numero['Id_Utilisateur'];
				}
			}
		}
		else
			echo AUCUN_NUMERO_PROPOSE;
	}

	function update_numeros($pays,$magazine,$etat,$av,$liste,$id_acquisition) {
		if ($etat=='possede') $etat='indefini';
		switch($etat) {
			case 'non_possede':
				$requete='DELETE FROM numeros WHERE (ID_Utilisateur=\''.$this->user_to_id($_SESSION['user']).'\') AND (';
				$debut=true;
				foreach($liste as $numero) {
					if (!$debut)
						$requete.=' OR ';
					$requete.='Numero LIKE \''.$numero.'\'';
					$debut=false;
				}
				$requete.=')';
			break;
			default:

				$intitule=$etat=='non_marque'?'':$etat;
				$requete_insert='INSERT INTO numeros(Pays,Magazine,Numero,';
				$arr=array($etat=>array('non_marque','Etat'),
						   $id_acquisition=>array(-2,'ID_Acquisition'),
						   $av=>array(-1,'AV')
						  );
				$debut=true;
				foreach($arr as $indice=>$valeur) {
					if (!($debut)) {
						if ($etat=='non_marque') {
							$debut=false;
							continue;
						}
						else
							$requete_insert.=',';
					}
					$requete_insert.=$valeur[1];
					$debut=false;
				}
				$requete_insert.=',ID_Utilisateur) VALUES ';
				$debut=true;
				$l=new Liste();
				$liste_user=$this->toList($this->user_to_id($_SESSION['user']));
				$liste_deja_possedes=array();
				foreach($liste as $numero) {
					if ($liste_user->est_possede($pays,$magazine,$numero)) {
						array_push($liste_deja_possedes,$numero);
						continue;
					}
					if (!$debut)
						$requete_insert.=', ';

					$requete_insert.='(\''.$pays.'\',\''.$magazine.'\',\''.$numero.'\',';

					$arr=array($etat=>array('non_marque','\''.$intitule.'\''),
							   $id_acquisition=>array(-2,$id_acquisition),
							   $av=>array(-1,$av)
							  );
					$debut=true;
					foreach($arr as $indice=>$valeur) {
						if (!($debut)) {
							if ($etat=='non_marque') {
								$debut=false;
								continue;
							}
							else
								$requete_insert.=',';
						}
						$requete_insert.=$valeur[1];
						$debut=false;
					}
					$requete_insert.=','.$this->user_to_id($_SESSION['user']).')';
					$debut=false;
				}
				//echo $requete_insert;
				DM_Core::$d->requete($requete_insert);
				$requete_update='UPDATE numeros SET ';

				$arr=array($etat=>array('non_marque','Etat=\''.$intitule.'\''),
						   $id_acquisition=>array(-2,'ID_Acquisition='.$id_acquisition),
						   $av=>array(-1,'AV='.$av)
						  );
				$debut=true;
				foreach($arr as $indice=>$valeur) {
					if ($indice!=$valeur[0]) {
						if (!$debut)
							$requete_update.=',';
						$requete_update.=$valeur[1];
						$debut=false;
					}
				}

				$requete_update.=' WHERE (Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND ID_Utilisateur='.$this->user_to_id($_SESSION['user']).' AND (';
				$debut=true;
				foreach($liste_deja_possedes as $numero) {
					if (!$debut)
						$requete_update.='OR ';
					$requete_update.='Numero LIKE \''.$numero.'\' ';
					$debut=false;
				}
				$requete_update.='))';
				DM_Core::$d->requete($requete_update);
				echo $requete_update;

		}
		if (isset($requete))
			mysql_query($requete);
	}

	function toList($id_user=false) {
		
			$requete='SELECT DISTINCT Pays, Magazine,Numero,Etat,ID_Acquisition,AV,ID_Utilisateur FROM numeros ';
			if ($id_user!==false) 
				$requete.='WHERE (ID_Utilisateur='.$id_user.') ';
			$requete.='ORDER BY Pays, Magazine, Numero';
			$resultat=DM_Core::$d->requete_select($requete);
			$cpt=0;
			$l=new Liste();
			$numero=-1;
			foreach ($resultat as $infos) {
				if (array_key_exists($infos['Pays'],$l->collection)) {
						if (array_key_exists($infos['Magazine'],$l->collection[$infos['Pays']])) {
							array_push($l->collection[$infos['Pays']][$infos['Magazine']],array($infos['Numero'],$infos['Etat'],$infos['AV'],$infos['ID_Acquisition']));
						}
						else {
							$l->collection[$infos['Pays']][$infos['Magazine']]=array(0=>array($infos['Numero'],$infos['Etat'],$infos['AV'],$infos['ID_Acquisition']));
						}
				}
				else {
					$l->collection[$infos['Pays']]=array($infos['Magazine']=>0);
					$l->collection[$infos['Pays']][$infos['Magazine']]=array(0=>array($infos['Numero'],$infos['Etat'],$infos['AV'],$infos['ID_Acquisition']));

				}
			}
			//echo '<pre>';print_r($l);echo '</pre>';
			return $l;
	}
		
	function get_auteur($nom_abrege) {
		$requete_auteur_existe='SELECT NomAuteurComplet FROM auteurs WHERE NomAuteurAbrege LIKE \''.$nom_abrege.'\'';
		$resultat_auteur_existe=DM_Core::$d->requete_select($requete_auteur_existe);
		if (count($resultat_auteur_existe)>0) {
			return $resultat_auteur_existe[0]['NomAuteurComplet'];
		}
		else {
			$nom_auteur_complet=Inducks::get_auteur($nom_abrege);
			$requete_ajout_auteur='INSERT INTO auteurs(NomAuteurAbrege,NomAuteurComplet) VALUES (\''.$nom_abrege.'\',\''.$nom_auteur_complet.'\')';
			DM_Core::$d->requete($requete_ajout_auteur);
			return $nom_auteur_complet;
		}
	}
	
function ajouter_auteur($id,$nom) {
		$id_user=$this->user_to_id($_SESSION['user']);
		$requete_auteur_existe='SELECT NomAuteurAbrege FROM auteurs_pseudos WHERE NomAuteurAbrege LIKE \''.$id.'\' AND DateStat LIKE \'0000-00-00\' AND ID_User='.$id_user;
		$resultat_auteur_existe=DM_Core::$d->requete_select($requete_auteur_existe);
		if (count($resultat_auteur_existe)>0) {
			echo AUTEUR_DEJA_DANS_LISTE.'<br />';
		}
		else {
			$requete_ajout_auteur='INSERT INTO auteurs_pseudos(NomAuteur, NomAuteurAbrege, ID_User,NbPossedes, DateStat) '
								 .'VALUES (\''.$nom.'\', \''.$id.'\','.$id_user.',0,\'0000-00-00\')';
			DM_Core::$d->requete($requete_ajout_auteur);
		}
	}

	function liste_auteurs_surveilles($auteurs_surveilles,$affiche_notation) {
		
		if (count($auteurs_surveilles)==0) {
			echo AUCUN_AUTEUR_SURVEILLE;
			?><br /><?php
		}
		else {
			if ($affiche_notation) {
				?><form method="post" action="?action=agrandir&onglet=auteurs_favoris&onglet_auteur=preferences"><?php
			}
			?><table style="width:100%"><?php
			foreach($auteurs_surveilles as $num=>$auteur) {
				?><tr><td>- <?=$auteur['NomAuteur']?></td><?php
				if ($affiche_notation) {
					?><td id="pouces<?=$num?>" onmouseout="vider_pouces()"><?php
                                        echo note_to_pouces($num,$auteur['Notation']);
					?><input type="hidden" value="<?=$auteur['NomAuteurAbrege']?>" name="auteur<?=$num?>" />
					<input type="hidden" id="notation<?=$num?>" name="notation<?=$num?>" />
					</td>
					<td><input type="checkbox" <?=($auteur['Notation']==-1?'checked="checked"':'')?> id="aucune_note<?=$num?>" onclick="set_aucunenote(<?=$num?>)" name="aucune_note<?=$num?>" />&nbsp;<?=AUCUNE_NOTE?>
					<?php
				}
				?><td><a href="javascript:void(0)" onclick="supprimer_auteur('<?=$auteur['NomAuteurAbrege']?>')"><?=SUPPRIMER?></a></td>
				</tr><?php
			}
			?></table><?php
			if ($affiche_notation) {
				$id_user=$this->user_to_id($_SESSION['user']);
				$requete_get_recommandations_liste_mags='SELECT RecommandationsListeMags FROM users WHERE ID='.$id_user;
				$resultat_get_recommandations_liste_mags=DM_Core::$d->requete_select($requete_get_recommandations_liste_mags);
				$recommandations_liste_mags=$resultat_get_recommandations_liste_mags[0]['RecommandationsListeMags'];
				echo EXPLICATION_NOTATION_AUTEURS1;
				?><br /><?php 
				echo EXPLICATION_NOTATION_AUTEURS2;
				?><br /><br />
				<input type="checkbox" <?=($recommandations_liste_mags?'checked="checked" ':'')?>
					   name="proposer_magazines_possedes" />&nbsp;<?=PROPOSER_MAGAZINES_POSSEDES?><br />
				<span style="font-size:10px"><?=PROPOSER_MAGAZINES_POSSEDES_EXPLICATION?></span>
				<br /><br /><input type="submit" class="valider" value="<?=VALIDER_NOTATIONS?>" /></form>
				<?php
			}
		}
	}

	function liste_suggestions_magazines() {
		$id_user=$this->user_to_id($_SESSION['user']);
		$requete_numeros_recommandes='SELECT Pays, Magazine, Numero, Texte, Notation FROM numeros_recommandes '
                                            .'WHERE ID_Utilisateur='.$id_user.' ORDER BY Notation DESC';
		$resultat_numeros_recommandes=DM_Core::$d->requete_select($requete_numeros_recommandes);
		if (count($resultat_numeros_recommandes)!=0) {
			echo INTRO_NUMEROS_RECOMMANDES;?>
			<br />
			<ol><?php
			$pays_parcourus=array();
			$auteurs=array();

			foreach($resultat_numeros_recommandes as $numero) {
				$pays=$numero['Pays'];
				if (!array_key_exists($pays,$pays_parcourus))
					$pays_parcourus[$pays]=Inducks::get_liste_magazines($pays);
				?>
				<li><?=$pays_parcourus[$pays][$numero['Magazine']]?> <?=$numero['Numero']?><br />
				
				<?php
				$auteurs__nbs=explode(',',$numero['Texte']);

				echo '<b>'.$numero['Notation'].' points : </b>';
				$debut=true;
				foreach ($auteurs__nbs as $i=>$histoire) {
					list($auteur,$nb_histoires)=explode('=',$histoire);
					if (!array_key_exists($auteur,$auteurs))
					$auteurs[$auteur]=DM_Core::$d->get_auteur($auteur);
					if (!$debut) {
						if ($i==count($auteur__nb)-1) {
							?> <?=ET?> <?php
						}
						else {
							?>, <?php
						}
					}
					if ($nb_histoires==1) {
						?>1 <?=HISTOIRE?><?php
					}
					else {
						?><?=$nb_histoires?> <?=HISTOIRES?><?php
					}
					?> <?=DE?> <?=$auteurs[$auteur]?><?php
					$debut=false;
				}
			}
			?>
		</ol><?php
		}
		else {
                    ?><?=CALCULS_PAS_ENCORE_FAITS?><br /><?php
                }
	}

	function liste_auteurs_notes($auteurs_surveilles) {
		foreach($auteurs_surveilles as $auteur) {
			if ($auteur['Notation']>=1)
				echo '1_';
			else
				echo '0_';
		}
	}
	
	function get_notes_auteurs($id_user) {
		return $this->requete_select('SELECT NomAuteurAbrege, NomAuteur, Notation FROM auteurs_pseudos WHERE ID_user='.$id_user.' AND DateStat LIKE \'0000-00-00\'');
	}

	function sous_liste($pays,$magazine) {
		$l_magazine=new Liste();
		if (isset($l->collection[$pays][$magazine])) {
			foreach($l->collection[$pays][$magazine] as $numero) {
				$l_magazine->ajouter($pays, $magazine, $numero);
			}
		}
		return $l_magazine;
	}
}
require_once('DucksManager_Core.class.php');

if (isset($_POST['database'])) {
	@session_start();
	if (isset($_POST['pass'])) {
		if (isset($_POST['connexion'])) {
			if (!DM_Core::$d->user_connects($_POST['user'],$_POST['pass']))
				echo 'Identifiants invalides!';
			else {
				$_SESSION['user']=$_POST['user'];
			}
		}
	}

	else if (isset($_POST['update'])) {
		//print_r($_SESSION);
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		$l=DM_Core::$d->toList($id_user);
		$liste=explode(',',$_POST['list_to_update']);
		$pays=$_POST['pays'];
		$magazine=$_POST['magazine'];
		$etat=$_POST['etat'];
		if ($_POST['av']=='true'||$_POST['av']=='-1')
			$av=($_POST['av']=='true')?1:0;
		else
			$av=$_POST['av'];
		$date_acquisition=$_POST['date_acquisition'];
		$id_acquisition=$date_acquisition;
		if ($date_acquisition!=-1 && $date_acquisition!=-2) {
			$requete_id_acquisition='SELECT Count(ID_Acquisition) AS cpt, ID_Acquisition FROM achats WHERE ID_User='.DM_Core::$d->user_to_id($_SESSION['user']).' AND Date LIKE \''.$date_acquisition.'\' GROUP BY ID_Acquisition';
			$resultat_acqusitions=DM_Core::$d->requete_select($requete_id_acquisition);
			//echo $requete_id_acquisition;
			if ($resultat_acqusitions[0]['cpt'] ==0)
				$id_acquisition=-1;
			else
				$id_acquisition=$resultat_acqusitions[0]['ID_Acquisition'];
		}
		//$l->update_numeros($pays,$magazine,$etat,$liste,$id_acquisition);
		DM_Core::$d->update_numeros($pays,$magazine,$etat,$av,$liste,$id_acquisition);

	}
	else if (isset($_POST['affichage'])) {
		//print_r($_SESSION);
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		$l=DM_Core::$d->toList($id_user);
		$pays=$_POST['pays'];
		$magazine=$_POST['magazine'];
		list($numeros,$sous_titres)=Inducks::get_numeros($pays,$magazine);
				if ($numeros!=false) {
					Affichage::afficher_numeros($l,$pays,$magazine,$numeros,$sous_titres);
				}
		else {
					echo AUCUN_NUMERO_IMPORTE.$magazine.' ('.PAYS_PUBLICATION.' : '.$pays.')';
					?><br /><br /><?php
					echo QUESTION_SUPPRIMER_MAGAZINE;
					$l_magazine=$l->sous_liste($pays,$magazine);
					
					$l_magazine->afficher('Classique');
					?><br />
					<a href="?action=gerer&supprimer_magazine=<?=$pays.'.'.$magazine?>"><?=OUI?></a>&nbsp;
					<a href="?action=gerer"><?=NON?></a><?php
					if (!Util::isLocalHost())
						@mail('admin@ducksmanager.net', 'Erreur de recuperation de numeros', AUCUN_NUMERO_IMPORTE.$magazine.' ('.PAYS_PUBLICATION.' : '.$pays.')');
				}
	}
	else if (isset($_POST['acquisition'])) {
		//print_r($_SESSION);
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);

		/*V�rifier d'abord que les num�ros � ajouter ne correspondent pas d�j� � une date d'acquisition*/
		$requete_acquisition_existe='SELECT Count(ID_Acquisition) as c '
										   .'FROM achats '
										   .'WHERE ID_User='.$id_user.' AND Date LIKE \''.$_POST['date_annee'].'-'.$_POST['date_mois'].'-'.$_POST['date_jour'].'\' AND Description LIKE \''.$_POST['description'].'\'';
				$compte_acquisition_date=DM_Core::$d->requete_select($requete_acquisition_existe);
		if ($compte_acquisition_date[0]['c']!=0) {
			echo 'Date';exit(0);
		}

		DM_Core::$d->requete('INSERT INTO achats(ID_User,Date,Description)'
				   .' VALUES ('.$id_user.',\''.$_POST['date_annee'].'-'.$_POST['date_mois'].'-'.$_POST['date_jour'].'\',\''.$_POST['description'].'\')');
		$requete_acquisition='SELECT Date, Description FROM achats WHERE ID_User='.$id_user.' ORDER BY Date DESC';
		$liste_acquisitions=DM_Core::$d->requete_select($requete_acquisition);

	}
	else if (isset($_POST['modif_acquisition'])) {
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		DM_Core::$d->requete('UPDATE achats SET Date=\''.$_POST['date'].'\',Description=\''.$_POST['description'].'\' WHERE ID_User='.$id_user.' AND ID_Acquisition=\''.$_POST['id_acquisition'].'\'');
	}
	else if(isset($_POST['supprimer_acquisition'])) {
		//print_r($_SESSION);
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		$requete='DELETE FROM achats WHERE ID_User='.$id_user.' AND ID_Acquisition='.$_POST['supprimer_acquisition'];
		echo $requete;
		DM_Core::$d->requete($requete);
	}
	else if (isset($_POST['liste_achats'])) {
		//print_r($_SESSION);acquisitions
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		$liste_achats=DM_Core::$d->requete_select('SELECT ID_Acquisition, Date,Description FROM achats WHERE ID_User='.$id_user.' ORDER BY Date');
		$tab_achats=array();
		foreach ($liste_achats as $achat) {
			$o_achat=new stdClass();
			$o_achat->id=$achat['ID_Acquisition'];
			$o_achat->description=$achat['Description'];
			$o_achat->date=$achat['Date'];
			$tab_achats[]=$o_achat;
		}
		echo header("X-JSON: " . json_encode($tab_achats));
	}
	else if (isset($_POST['liste_etats'])) {
		DM_Core::$d->liste_etats();
	}
	else if (isset($_POST['liste_notations'])) {
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		$resultat_notations=DM_Core::$d->get_notes_auteurs($id_user);
		$json=json_encode(DM_Core::$d->liste_auteurs_notes($resultat_notations));
		echo header("X-JSON: " . $json);
	}
	else if (isset($_POST['changer_notation'])) {
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		DM_Core::$d->update_notation($_POST['auteur_abrege'],$_POST['notation']);
		echo NOTATION_MODIFIEE;
	}
	else if (isset($_POST['supprimer_auteur'])) {
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		DM_Core::$d->requete('DELETE FROM auteurs_pseudos '
				   .'WHERE ID_user='.$id_user.' AND NomAuteurAbrege LIKE \''.$_POST['nom_auteur'].'\'');
	}
elseif (isset($_POST['liste_bouquineries'])) {
			$requete_bouquineries='SELECT Nom, CONCAT(Adresse, \'<br />\',CodePostal, \' \',Ville) AS Adresse, Pays, Commentaire, CoordX, CoordY, CONCAT(\''.SIGNALE_PAR.'\',IFNULL(username,\'un visiteur anonyme\')) AS Signature FROM bouquineries '
								 .'LEFT JOIN users ON bouquineries.ID_Utilisateur=users.ID '
								 .'ORDER BY Pays, CodePostal, Ville';
			$resultat_bouquineries=DM_Core::$d->requete_select($requete_bouquineries);
			foreach($resultat_bouquineries as &$bouquinerie) {
				$i=0;
				while (array_key_exists($i, $bouquinerie)) {
					unset ($bouquinerie[$i]);
					$i++;
				}

			}
			$json=json_encode($resultat_bouquineries);
			echo $json;
		}
	else { // V�rification de l'utilisateur
			if (DM_Core::$d->user_exists($_POST['user']))
					echo UTILISATEUR_EXISTANT;
			else
					echo 'OK, '.UTILISATEUR_VALIDE;
	}
}
elseif (isset($_GET['test_bd_inducks'])) {
	$requete_alias = 'SELECT charactercode, charactername FROM inducks_characteralias';
	$resultat_alias = DM_Core::$d->requete_select($requete_alias);
	?><table><tr><td>Code</td><td>Name</td></tr><?php
	foreach($resultat_alias as $resultat) {
		?><tr><td><?=$resultat['charactercode']?></td><td><?=$resultat['charactername']?></td></tr><?php
	}
	?></table><?php
}
function note_to_pouces($num,$note) {
	ob_start();
	for ($i=1;$i<=10;$i++) {
		$orientation=$i<=5?'bas':'haut';
		$pouce_rempli=$note>=$i;
		?><img alt="+" id="pouce<?=$num?>_<?=$i?>" height="15" src="images/pouce_<?=$orientation.($pouce_rempli?'':'_blanc')?>.png" onclick="valider_note(<?=$num?>)" onmouseover="hover(<?=$num?>,<?=$i?>)"/><?php
	}
	ob_end_flush();
}

?>