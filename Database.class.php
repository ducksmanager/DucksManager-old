<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Liste.class.php');
require_once('Inducks.class.php');
require_once('ParametrageAjoutSuppr.class.php');

class Database {
	public static $etats;
	var $server;
	var $database;
	var $user;
	var $password;


	function __construct() {
			require_once('_priv/Database.priv.class.php');
			return DatabasePriv::connect();
	}

	function connect($user,$password) {
			$this->user=$user;
			$this->password=$password;
	}

	function requete_select($requete) {
		if ($_SERVER['SERVER_ADDR'] === DatabasePriv::$ip_serveur_virtuel && mysql_current_db() !== 'coa') {
			return Inducks::requete_select($requete,'db301759616','ducksmanager.net');
		}
		else {
			$requete_resultat=mysql_query($requete);
			if (!is_resource($requete_resultat))
				return array();
			$arr=array();
			while($arr_tmp=mysql_fetch_array($requete_resultat))
					array_push($arr,$arr_tmp);
			return $arr;
		}
	}

	function requete($requete) {
		require_once('Inducks.class.php');
		if ($_SERVER['SERVER_ADDR'] === DatabasePriv::$ip_serveur_virtuel) {
			return Inducks::requete_select($requete,'db301759616','ducksmanager.net');
		}
		else {
			return mysql_query($requete);
		}
	}
	
	static function get_remote_url($page) {
		return DatabasePriv::$url_serveur_virtuel.'/'.DatabasePriv::$root_serveur_virtuel.'/'.$page;
	}
	
	function user_to_id($user) {
			if ((!isset($user) || empty($user)) && (isset($_COOKIE['user']) && isset($_COOKIE['pass']))) {
					$user=$_COOKIE['user'];
			}
			$requete='SELECT ID FROM users WHERE username = \''.$user.'\'';
			$resultat=DM_Core::$d->requete_select($requete);
			return $resultat[0]['ID'];
	}

	function user_connects($user,$pass) {
			if (!$this->user_exists($user)) {
				return false;
			}
			else {
				$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\') AND password LIKE(sha1(\''.$pass.'\'))';
				return (count(DM_Core::$d->requete_select($requete))>0);
			}
	}

	function user_exists($user) {
			$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\')';
			return (count(DM_Core::$d->requete_select($requete))>0);

	}

	function user_is_beta() {
		if (isset($_SESSION['user'])) {
			$requete_beta_user='SELECT BetaUser FROM users WHERE username = \''.$_SESSION['user'].'\'';
			$resultat_beta=DM_Core::$d->requete_select($requete_beta_user);
			return $resultat_beta[0]['BetaUser']==1;
		}
		return false;
	}

	function user_afficher_video() {
		if (isset($_SESSION['user'])) {
			$requete_afficher_video='SELECT AfficherVideo FROM users WHERE username = \''.$_SESSION['user'].'\'';
			$resultat_afficher_video=DM_Core::$d->requete_select($requete_afficher_video);
			return $resultat_afficher_video[0]['AfficherVideo']==1;
		}
		return false;
	}

	function nouveau_user($user,$email,$pass) {
			date_default_timezone_set('Europe/Paris');
			$requete='INSERT INTO users(username,password,Email,DateInscription) VALUES(\''.$user.'\',\''.$pass.'\',\''.$email.'\',\''.date('Y-m-d').'\')';
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
				$requete_verif_ordre_existe='SELECT Ordre FROM bibliotheque_ordre_magazines WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND ID_Utilisateur='.$id_user;
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
				$requete_suppr_ordre='DELETE FROM bibliotheque_ordre_magazines WHERE ID_Utilisateur='.$id_user.' AND Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\'';
				DM_Core::$d->requete($requete_suppr_ordre);
			}
		}
	}

	function get_noms_complets_pays() {
		return Inducks::get_pays();
	}

	function liste_etats() {
		$etats=array();
		foreach(Etats::$instance->getListe() as $etat_court=>$etat) {
			if (!in_array($etat_court, 'indefini', 'non_possede')) {
				$etats[]=$etat->libelle;
			}
		}
		echo implode('~', $etats);
	}

	function liste_numeros_externes_dispos($id_user) {
		$resultat_email=DM_Core::$d->requete_select('SELECT Email FROM users WHERE ID='.$id_user);
		
		$requete_ventes_utilisateurs = 'SELECT users.ID, users.username, numeros.Pays, numeros.Magazine, numeros.Numero '
									  .'FROM users '
									  .'INNER JOIN numeros ON users.ID = numeros.ID_Utilisateur '
									  .'WHERE numeros.AV=1 '
									    .'AND NOT EXISTS (SELECT 1 FROM numeros numeros_user_courant '
									    				.'WHERE numeros_user_courant.ID_Utilisateur='.$id_user.' '
									    				  .'AND numeros.Pays = numeros_user_courant.Pays '
									    				  .'AND numeros.Magazine = numeros_user_courant.Magazine '
									    				  .'AND numeros.Numero = numeros_user_courant.Numero) '
  										.'AND numeros.ID_Utilisateur <> '.$id_user.' '
  										.'AND users.Email <> \'\' '
										.'ORDER BY users.username, numeros.Pays, numeros.Magazine, numeros.Numero';
		$resultat_ventes_utilisateurs = DM_Core::$d->requete_select($requete_ventes_utilisateurs);
		if (count($resultat_ventes_utilisateurs) > 0) {
			if (empty($resultat_email[0]['Email'])) {
				?><br />
				<span class="warning">
					<?=ATTENTION_EMAIL_VIDE_ACHAT?>
                  	<a target="_blank" href="?action=gerer&amp;onglet=compte"><?=GESTION_COMPTE_COURT?></a>.
            	</span><?php
			}
			$publication_codes = array();
			foreach ($resultat_ventes_utilisateurs as $vente) {
				$publication_codes[]=$vente['Pays'].'/'.$vente['Magazine'];
			}
			list($liste_pays,$liste_magazines)=$noms_complets_magazines=Inducks::get_noms_complets($publication_codes);
			$username_courant = '';
			foreach ($resultat_ventes_utilisateurs as $vente) {
				$publicationcode=$vente['Pays'].'/'.$vente['Magazine'];
				if ($username_courant != $vente['username']) {
					if (!empty($username_courant)) {
						$this->bloc_envoi_message_achat_vente($username_courant);?>
						</ul><br /><?php
					}
					$username_courant=utf8_decode($vente['username']);
					?><ul><b><?=$username_courant?></b> <?=PROPOSE_LES_NUMEROS?>
				<?php }
				?>
				<li>
					<img src="images/flags/<?=$vente['Pays']?>.png" />&nbsp;<?=$liste_magazines[$publicationcode]?> n&deg;<?=$vente['Numero']?>
				</li>
				<?php
			}
			$this->bloc_envoi_message_achat_vente($username_courant);
		}
		else
			echo AUCUN_NUMERO_PROPOSE;
	}
	
	function bloc_envoi_message_achat_vente($username) {
		date_default_timezone_set('Europe/Paris');
		
		$requete_message_envoye_aujourdhui='SELECT 1 FROM emails_ventes WHERE username_achat=\''.$_SESSION['user'].'\' AND username_vente=\''.$username.'\' AND date=\''.date('Y-m-d',mktime(0,0)).'\'';
		$message_deja_envoye=count(DM_Core::$d->requete_select($requete_message_envoye_aujourdhui)) > 0;
		if (isset($_GET['contact']) && $_GET['contact'] === $username) {
			if ($message_deja_envoye) {?>
				<span class="confirmation">
					<?=CONFIRMATION_ENVOI_MESSAGE.$username?>
				</span><?php
			}
			else {
				$requete_emails='SELECT username, Email FROM users WHERE username IN (\''.$_SESSION['user'].'\',\''.$username.'\') AND Email <> ""';
				$resultat_emails=DM_Core::$d->requete_select($requete_emails);
				if (count($resultat_emails) != 2) {
					?><span class="warning"><?=ENVOI_EMAIL_ECHEC?></span><?php
				}
				else {
					foreach($resultat_emails as $resultat) {
						if ($resultat['username'] == $_SESSION['user'])
							$email_acheteur=$resultat['Email'];
						else
							$email_vendeur=$resultat['Email'];	
					}
					
					$entete = "MIME-Version: 1.0\r\n";
					$entete .= "Content-type: text/html; charset=iso-8859-1\r\n";
					$entete .= "To: ".$username." <".$email_vendeur.">\r\n";
					$entete .= "From: ".$_SESSION['user']." ".DE." DucksManager <".$email_acheteur.">\r\n";
					$contenu_mail=
                         sprintf(SALUTATION,$_SESSION['user'])
                        .'<br /><br />'.sprintf(EMAIL_ACHAT_VENTE_2,$_SESSION['user'])
                        .'<br /><br />'.EMAIL_ACHAT_VENTE_3
                        .'<br /><br /><br />'.EMAIL_ACHAT_VENTE_3
                        .'<br /><br />'.EMAIL_SIGNATURE;
					if (mail($email_vendeur, EMAIL_ACHAT_VENTE_TITRE, $contenu_mail,$entete)) {
						?><span class="confirmation"><?=CONFIRMATION_ENVOI_MESSAGE.$username?></span><?php
						$requete_ajout_message='INSERT INTO emails_ventes (username_achat, username_vente, date) VALUES (\''.$_SESSION['user'].'\', \''.$username.'\', \''.date('Y-m-d',mktime(0,0)).'\')';
						DM_Core::$d->requete($requete_ajout_message);
					}
					else {
						?><span class="warning"><?=ENVOI_EMAIL_ECHEC?></span><?php
					}
				}
			}
		}
		else {
			?><br /><?php
			if ($message_deja_envoye) {?>
				<span class="confirmation">
					<?=CONFIRMATION_ENVOI_MESSAGE.$username?>
				</span><?php
			}
			else {?>
				<span style="border: 1px solid white;margin-left: 10px;">
					&gt; <a href="?action=agrandir&contact=<?=$username?>"><?=ENVOYER_MESSAGE_A.$username?></a>
				</span><?php
			}
		}
	}
	
	function update_numeros($parametres) {
        $liste=explode(',',$parametres['list_to_update']);

		if ($parametres['Etat'] === 'possede') {
            $parametres['Etat']='indefini';
        }

		switch($parametres['Etat']) {
			case 'non_possede':
				$numeros = array();
				foreach($liste as $numero) {
                    $numeros[] = '\''.$numero.'\'';
				}
                $requete='DELETE FROM numeros '
                        .'WHERE ID_Utilisateur=\''.$this->user_to_id($_SESSION['user']).'\' AND Numero IN ('.implode(',', $numeros).')';

                DM_Core::$d->requete($requete);
			break;
			default:
                // Ignorer les paramètres ayant la valeur "conserver"
                $parametres = array_filter($parametres, function($valeur) {
                    return $valeur !== ParametreAjoutSuppr::$nomParametreConserver;
                });

                $liste_user=$this->toList($this->user_to_id($_SESSION['user']));

                $champs_fixes = array('Pays', 'Magazine', 'Numero', 'ID_Utilisateur');

                $champs_variables = array_intersect(
                    array(Etats::$instance->nom, EtatsAchats::$instance->nom, EtatsAVendre::$instance->nom),
                    array_flip($parametres)
                );
                $champs = array_merge($champs_fixes, $champs_variables);

                $inserts = array();
                $updates = array();
                foreach($liste as $numero) {
                    $parametres = array_intersect_key($parametres, array_flip($champs));
                    $parametres['Numero'] = $numero;

                    if ($liste_user->est_possede($parametres['Pays'],$parametres['Magazine'],$parametres['Numero'])) {
                        array_push($updates, $parametres);
                    }
                    else {
                        array_push($inserts, $parametres);
                    }
                }

                foreach($inserts as $insert) {
                    $values = array_intersect_key($insert, array_flip($champs));
                    $requete_insert='INSERT INTO numeros('.implode(',', array_keys($values)).') VALUES (\''.implode("','", $values).'\')';
                    DM_Core::$d->requete($requete_insert);
                }

                foreach($updates as $update) {
                    $values = array();
                    foreach($champs_variables as $champ) {
                        $values[]=$champ."='".$update[$champ]."'";
                    }

                    $criteria = array();
                    foreach($champs_fixes as $champ) {
                        $criteria[]=$champ."='".$update[$champ]."'";
                    }

                    $requete_update=' UPDATE numeros'
                                   .' SET '.implode(',', $values).' WHERE ('.implode(' AND ', $criteria).')';
                    DM_Core::$d->requete($requete_update);
                }
        }
	}

	function toList($id_user=false) {
		
			$requete='SELECT DISTINCT Pays, Magazine,Numero,Etat,ID_Acquisition,AV,ID_Utilisateur FROM numeros ';
			if ($id_user!==false) 
				$requete.='WHERE (ID_Utilisateur='.$id_user.') ';
			$requete.='ORDER BY Pays, Magazine, Numero';
			$resultat=DM_Core::$d->requete_select($requete);
			$l=new Liste();
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
		$requete_auteur_existe='SELECT NomAuteurComplet FROM auteurs WHERE NomAuteurAbrege = \''.$nom_abrege.'\'';
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
		$requete_auteur_existe='SELECT NomAuteurAbrege FROM auteurs_pseudos WHERE NomAuteurAbrege = \''.$id.'\' AND DateStat = \'0000-00-00\' AND ID_User='.$id_user;
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
						if ($i==count($auteurs__nbs)-1) {
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
		return $this->requete_select('SELECT NomAuteurAbrege, NomAuteur, Notation FROM auteurs_pseudos WHERE ID_user='.$id_user.' AND DateStat = \'0000-00-00\'');
	}

	function sous_liste($pays,$magazine) {
        $id_user=$this->user_to_id($_SESSION['user']);
        $l=DM_Core::$d->toList($id_user);

		$l_magazine=new Liste();
		if (isset($l->collection[$pays][$magazine])) {
			foreach($l->collection[$pays][$magazine] as $numero) {
				$l_magazine->ajouter($pays, $magazine, $numero);
			}
		}
		return $l_magazine;
	}
	
	function est_utilisateur_vendeur_sans_email() {
		$id_user=$this->user_to_id($_SESSION['user']);
		$requete='SELECT 1 FROM users '
				.'WHERE ID='.$id_user.' AND (Email IS NULL OR Email=\'\') '
				  .'AND (SELECT COUNT(Numero) FROM numeros WHERE ID_Utilisateur='.$id_user.' AND AV=1) > 0';
		return count($this->requete_select($requete)) == 1; 
	}
	
	function get_niveaux() {
		$requete_nb_photographies ='SELECT COUNT(issuenumber) AS cpt FROM tranches_pretes '
								  .'WHERE photographes REGEXP \'(^|,)('.$_SESSION['user'].')($|,)\' '
								  .'ORDER BY publicationcode';
		$resultat_nb_photographies=DM_Core::$d->requete_select($requete_nb_photographies);

		$requete_nb_creations =	   'SELECT COUNT(issuenumber) AS cpt FROM tranches_pretes '
								  .'WHERE createurs REGEXP \'(^|,)('.$_SESSION['user'].')($|,)\' '
								  .'ORDER BY createurs';
		$resultat_nb_creations=DM_Core::$d->requete_select($requete_nb_creations);

		$id_user=$this->user_to_id($_SESSION['user']);
		$requete_nb_bouquineries='SELECT COUNT(Nom) AS cpt FROM bouquineries WHERE Actif=1 AND ID_Utilisateur='.$id_user;
		$resultat_nb_bouquineries=DM_Core::$d->requete_select($requete_nb_bouquineries);
		$nb = array('Photographe'=> $resultat_nb_photographies[0]['cpt'],
					'Concepteur'	=> $resultat_nb_creations[0]['cpt'],
					'Duckhunter'	=> $resultat_nb_bouquineries[0]['cpt']);

		$limites=array('Photographe'=>array('Avance' => 50, 'Intermediaire' => 10, 'Debutant' => 1),
					   'Concepteur'	=>array('Avance' => 10, 'Intermediaire' => 3,  'Debutant' => 1),
					   'Duckhunter' =>array('Avance' =>  5, 'Intermediaire' => 3,  'Debutant' => 1));
		$cpt_et_niveaux=array();
		foreach($nb as $type=>$cpt) {
			$cpt_et_niveaux[$type]=null;
			foreach ($limites[$type] as $niveau=>$cpt_min) {
				if ($cpt >= $cpt_min) {
					$cpt_et_niveaux[$type]=array('Niveau'=>$niveau,'Cpt'=>$cpt);
					break;
				}
			}
		}	
		return $cpt_et_niveaux;
	}
	
	function get_evenements_recents() {
		$limite_evenements = 20;

		$evenements = new stdClass();
		$evenements->evenements = array();

		/* Inscriptions */
		$requete_inscriptions='SELECT users.ID, users.username, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(DateInscription)) AS DiffSecondes '
							 .'FROM users '
							 .'WHERE DateInscription > date_add(now(), interval -1 month) AND users.username NOT LIKE "test%"';

		$resultat_inscriptions = DM_Core::$d->requete_select($requete_inscriptions);
		foreach($resultat_inscriptions as $inscription) {
			ajouter_evenement(
				$evenements->evenements,
				$inscription['DiffSecondes'],
				'inscriptions',
				$inscription['username']);
		}
		
		/* Ajouts aux collections */
		$evenements->publicationcodes = array();
		$requete='SELECT users.ID, users.username,
				  	     (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(DateAjout)) AS DiffSecondes, COUNT(Numero) AS cpt,
				  		 (SELECT CONCAT(Pays,\'/\',Magazine,\'/\',Numero)
						  FROM numeros n
						  WHERE n.ID=numeros.ID
						  LIMIT 1) AS NumeroExemple
				  FROM numeros
				  INNER JOIN users ON numeros.ID_Utilisateur=users.ID
				  WHERE DateAjout > DATE_ADD(NOW(), INTERVAL -1 MONTH) AND users.username<>"demo" AND users.username NOT LIKE "test%"
				  GROUP BY users.ID, DATE(DateAjout)
				  HAVING COUNT(Numero) > 0
				  ORDER BY DateAjout DESC';
		$resultat_derniers_ajouts = DM_Core::$d->requete_select($requete);
		foreach($resultat_derniers_ajouts as $ajout) {
			preg_match('#([^/]+/[^/]+)#', $ajout['NumeroExemple'], $publicationcode);
			$evenements->publicationcodes[]=$publicationcode[0];
			
			list($pays,$magazine,$numero)=explode('/',$ajout['NumeroExemple']);
			$numero_exemple=array('Pays'=>$pays, 'Magazine'=>$magazine, 'Numero'=>$numero);
			
			$evenement = array('numero_exemple'=>$numero_exemple,
							   'cpt'		   =>intval($ajout['cpt'])-1);
			
			ajouter_evenement(
				$evenements->evenements,
				$ajout['DiffSecondes'],
				'ajouts',
				$ajout['username'],
				$evenement);
		}
		
		/* Propositions de bouquineries */
		$requete_bouquineries='SELECT users.ID, users.username, bouquineries.Nom AS Nom, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(DateAjout)) AS DiffSecondes
							   FROM bouquineries INNER JOIN users ON bouquineries.ID_Utilisateur=users.ID
							   WHERE Actif=1 AND DateAjout > date_add(now(), interval -1 month)';
		

		$resultat_bouquineries = DM_Core::$d->requete_select($requete_bouquineries);
		foreach($resultat_bouquineries as $bouquinerie) {
			$evenement = array('nom_bouquinerie'=>$bouquinerie['Nom']);
			ajouter_evenement(
					$evenements->evenements,
					$bouquinerie['DiffSecondes'],
					'bouquineries',
					$bouquinerie['username'],
					$evenement);
		}
		
		/* Ajouts de tranches */
		$requete_tranches="SELECT publicationcode, issuenumber, photographes, createurs, DATE(dateajout) DateAjout,
                                  (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(dateajout)) AS DiffSecondes, COUNT(issuenumber) AS cpt,
                                  (
                                    SELECT CONCAT(publicationcode,'/',issuenumber)
                                    FROM tranches_pretes tp
                                    WHERE tp.publicationcode =tranches_pretes.publicationcode AND tp.issuenumber =tranches_pretes.issuenumber
                                    LIMIT 1
                                  ) AS NumeroExemple
                          FROM tranches_pretes
                          WHERE DateAjout > DATE_ADD(NOW(), INTERVAL -1 MONTH)
                            AND NOT (publicationcode = 'fr/JM' AND issuenumber REGEXP '^[0-9]+$')
                          GROUP BY CONCAT(photographes, createurs), DATE(DateAjout)
                          HAVING COUNT(issuenumber) > 0
                          ORDER BY DateAjout DESC";
		
		$resultat_tranches = DM_Core::$d->requete_select($requete_tranches);
		foreach($resultat_tranches as $tranche_prete) {
			$publicationcode = $tranche_prete['publicationcode'];
			$evenements->publicationcodes[]=$publicationcode;

            list($pays,$magazine,$numero)=explode('/',$tranche_prete['NumeroExemple']);
            $numero_exemple=array('Pays'=>$pays, 'Magazine'=>$magazine, 'Numero'=>$numero);

            $evenement = array('numero_exemple'=>$numero_exemple,
                               'cpt'		   =>intval($tranche_prete['cpt'])-1);

			ajouter_evenement(
					$evenements->evenements,
					$tranche_prete['DiffSecondes'],
					'tranches pretes',
					array_merge(explode(',',$tranche_prete['photographes']),
								explode(',',$tranche_prete['createurs'])),
					$evenement);
		}
		
		$evenements->publicationcodes = array_unique($evenements->publicationcodes);
		ksort($evenements->evenements);
		
		$evenements_slice=array();
		$cpt=0;
		
		// Filtre : les 20 plus récents seulement
		foreach($evenements->evenements as $diff_secondes=>$evenements_types) {
			$evenements_slice[$diff_secondes]=new stdClass();
			foreach($evenements_types as $type=>$evenements_type) {
				$evenements_slice_type=array();
				foreach($evenements_type as $evenement) {
					if ($cpt >= $limite_evenements) {
						$evenements_slice[$diff_secondes]->$type=$evenements_slice_type;
						break 3;
					}
					$evenements_slice_type[]=$evenement;
					$cpt++;
				}
				$evenements_slice[$diff_secondes]->$type=$evenements_slice_type;
			}	
		}
		$evenements->evenements=$evenements_slice;
		return $evenements;
	}

    public function get_tranches_collection_ajoutees($id_user, $depuis_derniere_visite_seulement)
    {
        $requete_tranches_collection_ajoutees =
            'SELECT tp.publicationcode, tp.issuenumber, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(tp.dateajout)) AS DiffSecondes
             FROM tranches_pretes tp, numeros n
             WHERE n.ID_Utilisateur = '.$id_user.'
             AND CONCAT(publicationcode,\'/\',issuenumber) = CONCAT(n.Pays,\'/\',n.Magazine,\'/\',n.Numero)
             AND tp.dateajout > \'2013-07-01\'';

        if ($depuis_derniere_visite_seulement) {
            $requete_tranches_collection_ajoutees.='
             AND tranches_pretes.dateajout>(
               SELECT DernierAcces
               FROM users
               WHERE ID='.$id_user.' AND DernierAcces > \'0000-00-00\')';
        }
        else {
            $requete_tranches_collection_ajoutees.='
             ORDER BY DiffSecondes ASC
             LIMIT 5';
        }
        return DM_Core::$d->requete_select($requete_tranches_collection_ajoutees);
    }

    /**
     * @param $id_user
     * @return array
     */
    function get_liste_achats($id_user)
    {
        return DM_Core::$d->requete_select('SELECT ID_Acquisition, Date, Description FROM achats WHERE ID_User=' . $id_user . ' ORDER BY Date DESC');
    }
}

function mysql_current_db() {
	$r = mysql_query("SELECT DATABASE()") or die(mysql_error());
	return mysql_result($r,0);
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
        $parametres = array_merge($_POST, array(
            'ID_Utilisateur'=>DM_Core::$d->user_to_id($_SESSION['user']))
        );
        $id_acquisition = $parametres['ID_Acquisition'];
        if ($id_acquisition >= 0) {
            $requete_acquisition_existe='SELECT 1 FROM achats WHERE ID_Acquisition='.$id_acquisition.' AND ID_User='.$parametres['ID_Utilisateur'];
            $resultat_acquisition_existe = DM_Core::$d->requete_select($requete_acquisition_existe);
            if (count($resultat_acquisition_existe) === 0) {
                $parametres['ID_Acquisition'] = -1;
            }
        }
		DM_Core::$d->update_numeros($parametres);
	}
	else if (isset($_POST['evenements_recents'])) {	
		Affichage::afficher_evenements_recents(DM_Core::$d->get_evenements_recents());
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
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);

        $format_date = str_replace('%','',_FORMAT_DATE);
        $date = date($format_date, strtotime(mysql_real_escape_string($_POST['date'])));

        $description = mysql_real_escape_string($_POST['description']);

		/*Vérifier d'abord que les numéros à ajouter ne correspondent pas déjà à une date d'acquisition*/
		$requete_acquisition_existe='SELECT Count(ID_Acquisition) as c '
										   .'FROM achats '
										   .'WHERE ID_User='.$id_user.' AND Date = \''.$date.'\' AND Description = \''.$description.'\'';
        $compte_acquisition_date=DM_Core::$d->requete_select($requete_acquisition_existe);
		if ($compte_acquisition_date[0]['c']!=0) {
			echo 'Date';exit(0);
		}

		DM_Core::$d->requete('INSERT INTO achats(ID_User,Date,Description)'
                           .' VALUES ('.$id_user.',\''.$date.'\',\''.$description.'\')');
		$requete_acquisition='SELECT Date, Description FROM achats WHERE ID_User='.$id_user.' ORDER BY Date DESC';
		$liste_acquisitions=DM_Core::$d->requete_select($requete_acquisition);

	}
	else if (isset($_POST['modif_acquisition'])) {
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		DM_Core::$d->requete('UPDATE achats SET Date=\''.$_POST['date'].'\',Description=\''.$_POST['description'].'\' WHERE ID_User='.$id_user.' AND ID_Acquisition=\''.$_POST['id_acquisition'].'\'');
	}
	else if(isset($_POST['supprimer_acquisition'])) {
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		$requete='DELETE FROM achats WHERE ID_User='.$id_user.' AND ID_Acquisition='.$_POST['supprimer_acquisition'];
		DM_Core::$d->requete($requete);
	}
	else if (isset($_POST['liste_achats'])) {
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
        $liste_achats = DM_Core::$d->get_liste_achats($id_user);
        $tab_achats=array();
		$cpt_strlen=0;
		foreach ($liste_achats as $achat) {
			$id_achat=$achat['ID_Acquisition'];
			if ($_POST['continue'] != -1) {
			 	if ($_POST['continue']==$id_achat) {
					$_POST['continue'] = -1;
				}
				else continue;
			}
			$o_achat=new stdClass();
			$o_achat->id=$id_achat;
			$o_achat->description=$achat['Description'];
			$o_achat->date=$achat['Date'];
			$tab_achats[]=$o_achat;
			$cpt_strlen+=strlen(json_encode($o_achat));
		}
		echo json_encode($tab_achats);
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
				   .'WHERE ID_user='.$id_user.' AND NomAuteurAbrege = \''.$_POST['nom_auteur'].'\'');
	}
elseif (isset($_POST['liste_bouquineries'])) {
			$requete_bouquineries='SELECT Nom, CONCAT(Adresse, \'<br />\',CodePostal, \' \',Ville) AS Adresse, Pays, Commentaire, CoordX, CoordY, CONCAT(\''.SIGNALE_PAR.'\',IFNULL(username,\'un visiteur anonyme\')) AS Signature FROM bouquineries '
								 .'LEFT JOIN users ON bouquineries.ID_Utilisateur=users.ID '
                                 .'WHERE Actif=1 '
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
	else { // Vérification de l'utilisateur
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


function ajouter_evenement(&$evenements, $diff_secondes, $type_evenement, $utilisateur, $evenement=array()) {
	$evenement['diffsecondes'] = $diff_secondes;
	$evenement['utilisateur'] = $utilisateur;
	if (!array_key_exists($diff_secondes, $evenements)) {
		$evenements[$diff_secondes]=new stdClass();
	}
	if (!array_key_exists($type_evenement, $evenements[$diff_secondes])) {
		$evenements[$diff_secondes]->$type_evenement=array();
	}
	$evenements_type=$evenements[$diff_secondes]->$type_evenement;
	$evenements_type[]=json_decode(json_encode($evenement));
	
	$evenements[$diff_secondes]->$type_evenement = $evenements_type;
}
?>