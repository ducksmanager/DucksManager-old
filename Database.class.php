<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}

require_once('ServeurDb.class.php');
if (!array_key_exists('SERVER_ADDR', $_SERVER)) { // Stub CLI mode
    $_SERVER['SERVER_ADDR'] = ServeurDb::getIpServeurVirtuel();
}
else {
	include_once ('locales/lang.php');
	require_once('Liste.class.php');
}
require_once('DucksManager_Core.class.php');
require_once('Inducks.class.php');

Database::$etats=[
   'mauvais'=>[MAUVAIS,'#FF0000'],
   'moyen'=>[MOYEN,'#FF8000'],
   'bon'=>[BON,'#2CA77B'],
   'indefini'=>[INDEFINI,'#808080']];

class Database {
	public static $etats;
	var $server;
	var $database;
	var $user;
	var $password;

	/** @var $handle mysqli  */
	public static $handle = null;

	public static function escape($string) {
        return self::$handle->real_escape_string($string);
	}


	function __construct() {
			return ServeurDb::connect();
	}

	function connect($user,$password) {
			$this->user=$user;
			$this->password=$password;
	}

	function requete_select($requete) {
		if (ServeurDb::isServeurVirtuel() && get_current_db() !== 'coa') {
			return Inducks::requete_select($requete,ServeurDb::$nom_db_DM,'ducksmanager.net');
		}
		else {
			$requete_resultat=self::$handle->query($requete);
			if ($requete_resultat === false)
				return [];
			$arr=[];
			while($arr_tmp=$requete_resultat->fetch_array(MYSQLI_ASSOC))
					array_push($arr,$arr_tmp);
			return $arr;
		}
	}

	function requete($requete) {
		require_once('Inducks.class.php');
		if (ServeurDb::isServeurVirtuel()) {
			return Inducks::requete_select($requete,ServeurDb::$nom_db_DM,'ducksmanager.net');
		}
		else {
			return self::$handle->query($requete);
		}
	}

	function user_to_id($user) {
		if ((!isset($user) || empty($user)) && (isset($_COOKIE['user']) && isset($_COOKIE['pass']))) {
				$user=$_COOKIE['user'];
		}
		$requete='SELECT ID FROM users WHERE username = \''.$user.'\'';
		$resultat=DM_Core::$d->requete_select($requete);
		if (count($resultat) === 0) {
			return null;
		}
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
		$etats=[];
		foreach(self::$etats as $etat_court=>$infos_etat) {
			if ($etat_court!='indefini') {
				$etats[]=$infos_etat[0];
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
				<span class="alert alert-warning">
					<?=ATTENTION_EMAIL_VIDE_ACHAT?>
                  	<a target="_blank" href="?action=gerer&amp;onglet=compte"><?=GESTION_COMPTE_COURT?></a>.
            	</span><?php
			}
			$publication_codes = [];
			foreach ($resultat_ventes_utilisateurs as $vente) {
				$publication_codes[]=$vente['Pays'].'/'.$vente['Magazine'];
			}
			$liste_magazines=Inducks::get_noms_complets_magazines($publication_codes);
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

		// TODO Use DM server service
		$requete_message_envoye_aujourdhui='SELECT 1 FROM emails_ventes WHERE username_achat=\''.$_SESSION['user'].'\' AND username_vente=\''.$username.'\' AND date=\''.date('Y-m-d',mktime(0,0)).'\'';
		$message_deja_envoye=count(DM_Core::$d->requete_select($requete_message_envoye_aujourdhui)) > 0;
		if (isset($_GET['contact']) && $_GET['contact'] === $username) {
			if ($message_deja_envoye) {?>
				<span class="alert alert-success">
					<?=CONFIRMATION_ENVOI_MESSAGE.$username?>
				</span><?php
			}
			else {
				$requete_emails='SELECT username, Email FROM users WHERE username IN (\''.$_SESSION['user'].'\',\''.$username.'\') AND Email <> ""';
				$resultat_emails=DM_Core::$d->requete_select($requete_emails);
				if (count($resultat_emails) != 2) {
					?><span class="alert alert-danger"><?=ENVOI_EMAIL_ECHEC?></span><?php
				}
				else {
					foreach($resultat_emails as $resultat) {
						if ($resultat['username'] === $_SESSION['user'])
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
						?><span class="alert alert-success"><?=CONFIRMATION_ENVOI_MESSAGE.$username?></span><?php
						// TODO Use DM server service
						$requete_ajout_message='INSERT INTO emails_ventes (username_achat, username_vente, date) VALUES (\''.$_SESSION['user'].'\', \''.$username.'\', \''.date('Y-m-d',mktime(0,0)).'\')';
						DM_Core::$d->requete($requete_ajout_message);
					}
					else {
						?><span class="alert alert-danger"><?=ENVOI_EMAIL_ECHEC?></span><?php
					}
				}
			}
		}
		else {
			?><br /><?php
			if ($message_deja_envoye) {?>
				<span class="alert alert-success">
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
	
	function update_numeros($pays,$magazine,$etat,$av,$liste,$id_acquisition) {
		if ($etat==='possede') $etat='indefini';

		$id_user=$this->user_to_id($_SESSION['user']);

		switch($etat) {
			case 'non_possede':
			    $liste_str = array_map(function($numero) {
                    return DM_Core::$d->escape($numero);
			    }, $liste);

		        self::$handle->query("
                  DELETE FROM numeros
                  WHERE ID_Utilisateur=$id_user
                    AND Numero IN (".implode(',', $liste_str).")"
                );
			break;
			default:
				$champs = ['Pays', 'Magazine', 'Numero', 'ID_Acquisition', 'AV', 'ID_Utilisateur'];
                if ($etat !== 'non_marque') {
                    $champs[] = 'Etat';
                }
				$liste_user=$this->toList($id_user);

                $valeurs = [];
				$liste_deja_possedes=[];
				foreach($liste as $numero) {
					if (!is_null($liste_user->get_etat_numero_possede($pays,$magazine,$numero))) {
						$liste_deja_possedes[] = $numero;
					}
					else {
                        $data_numero = [$pays,$magazine,$numero,$id_acquisition,$av,$id_user];
                        if ($etat !== 'non_marque') {
                            $data_numero[] = $etat;
                        }

                        $valeurs[] = array_map(function($valeur) {
                            return "'".DM_Core::$d->escape($valeur)."'";
                        }, $data_numero);
                    }
				}

				$valeurs_str = array_map(function($data_numero) {
				    return '('.implode(',', $data_numero).')';
				}, $valeurs);

				DM_Core::$d->requete("
                  INSERT INTO numeros(".implode(',',$champs).")
                  VALUES ".implode(',', $valeurs_str)
                );

				$changements = [];

				if ($etat !== 'non_marque') {
				    $changements[] = "Etat='$etat'";
				}

				if ($id_acquisition !== -2) {
				    $changements[] = "ID_Acquisition='$id_acquisition'";
				}

				if ($av !== -1) {
				    $changements[] = "AV='$av'";
				}

				$numeros_update = array_map(function($numero) {
                    return "'".DM_Core::$d->escape($numero)."'";
				}, $liste_deja_possedes);

				if (count($numeros_update) > 0) {
                    DM_Core::$d->requete("
                      UPDATE numeros
                      SET ".implode(',', $changements)."
                      WHERE Pays='$pays'
                        AND Magazine='$magazine'
                        AND ID_Utilisateur=$id_user
                        AND Numero IN (".implode(',', $numeros_update).")"
                    );
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
						array_push($l->collection[$infos['Pays']][$infos['Magazine']],[$infos['Numero'],$infos['Etat'],$infos['AV'],$infos['ID_Acquisition']]);
					}
					else {
						$l->collection[$infos['Pays']][$infos['Magazine']]=[0=>[$infos['Numero'],$infos['Etat'],$infos['AV'],$infos['ID_Acquisition']]];
					}
				}
				else {
					$l->collection[$infos['Pays']]=[$infos['Magazine']=>0];
					$l->collection[$infos['Pays']][$infos['Magazine']]=[0=>[$infos['Numero'],$infos['Etat'],$infos['AV'],$infos['ID_Acquisition']]];

				}
			}
			return $l;
	}
	
	function ajouter_auteur($idAuteur,$nomAuteur) {
		$id_user=$this->user_to_id($_SESSION['user']);
		$requete_nb_auteurs_surveilles="
            SELECT COUNT(NomAuteurAbrege) AS cpt
            FROM auteurs_pseudos
            WHERE DateStat = '0000-00-00' AND ID_User=$id_user";
		$resultat_nb_auteurs_surveilles=DM_Core::$d->requete_select($requete_nb_auteurs_surveilles);
		if (count($resultat_nb_auteurs_surveilles) > 0 && $resultat_nb_auteurs_surveilles[0]['cpt'] >= 5) {
			?><div class="alert alert-danger"><?=MAX_AUTEURS_SURVEILLES_ATTEINT?></div><?php
		}
		else {
            if (!is_null(Inducks::get_auteur($idAuteur))) {
                $requete_auteur_existe = $requete_nb_auteurs_surveilles." AND NomAuteurAbrege = '$idAuteur'";
                $resultat_auteur_existe=DM_Core::$d->requete_select($requete_auteur_existe);
                if (count($resultat_auteur_existe) > 0 && (int)$resultat_auteur_existe[0]['cpt'] > 0) {
                    ?><div class="alert alert-danger"><?=AUTEUR_DEJA_DANS_LISTE?></div><?php
                }
                else {
                    $requete_ajout_auteur="
                        INSERT INTO auteurs_pseudos(NomAuteur, NomAuteurAbrege, ID_User,NbPossedes, DateStat)
                        VALUES ('$nomAuteur', '$idAuteur', $id_user, 0, '0000-00-00')";
                    DM_Core::$d->requete($requete_ajout_auteur);
                }
            }
        }
	}

	function afficher_liste_auteurs_surveilles($auteurs_surveilles) {
		if (count($auteurs_surveilles)==0) {
			echo AUCUN_AUTEUR_SURVEILLE;
			?><br /><?php
		}
		else {
            ?><ul id="liste_notations">
                <li class="notation template">
                    <div class="nom_auteur"></div>
                    <div class="notation_auteur"></div>
                    <div class="supprimer_auteur">
                        <a href="javascript:void(0)"><?=SUPPRIMER?></a>
                    </div>
                </li>
            </ul><?php
		}
	}
	
	function get_notes_auteurs($id_user) {
		return $this->requete_select('SELECT NomAuteurAbrege, NomAuteur, Notation FROM auteurs_pseudos WHERE ID_user='.$id_user.' AND DateStat = \'0000-00-00\'');
	}
	
	function modifier_note_auteur($auteur, $note) {
        $id_user=$this->user_to_id($_SESSION['user']);
        
        $requete_notation="
          UPDATE auteurs_pseudos
          SET Notation=$note
          WHERE DateStat = '0000-00-00' 
            AND NomAuteurAbrege = '$auteur'
            AND ID_user=$id_user";
        DM_Core::$d->requete($requete_notation);
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
		$id_user=$this->user_to_id($_SESSION['user']);

		$requete_nb_photographies ="
            SELECT COUNT(issuenumber) AS cpt FROM tranches_pretes_contributeurs 
            WHERE contribution = 'photographe' AND contributeur = $id_user";
		$resultat_nb_photographies=DM_Core::$d->requete_select($requete_nb_photographies);

		$requete_nb_creations =	"
            SELECT COUNT(issuenumber) AS cpt FROM tranches_pretes_contributeurs 
            WHERE contribution = 'createur' AND contributeur = $id_user";
		$resultat_nb_creations=DM_Core::$d->requete_select($requete_nb_creations);

		$requete_nb_bouquineries='SELECT COUNT(Nom) AS cpt FROM bouquineries WHERE Actif=1 AND ID_Utilisateur='.$id_user;
		$resultat_nb_bouquineries=DM_Core::$d->requete_select($requete_nb_bouquineries);

		return Affichage::get_medailles([
            'Photographe'=> $resultat_nb_photographies[0]['cpt'],
            'Concepteur' => $resultat_nb_creations[0]['cpt'],
            'Duckhunter' => $resultat_nb_bouquineries[0]['cpt']
        ]);
	}
	
	function get_evenements_recents() {
		$limite_evenements = 20;

		$evenements = new stdClass();
		$evenements->evenements = [];

		/* Inscriptions */
		$requete_inscriptions="
          SELECT users.ID, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(DateInscription)) AS DiffSecondes
          FROM users
          WHERE EXISTS(
            SELECT 1 FROM numeros WHERE users.ID = numeros.ID_Utilisateur
          )
            AND DateInscription > date_add(now(), interval -1 month) AND users.username NOT LIKE 'test%'
        ";

		$resultat_inscriptions = DM_Core::$d->requete_select($requete_inscriptions);
		foreach($resultat_inscriptions as $inscription) {
			ajouter_evenement(
				$evenements->evenements, [], $inscription['DiffSecondes'], 'inscriptions', $inscription['ID']);
		}
		
		/* Ajouts aux collections */
		$evenements->publicationcodes = [];
		$requete='SELECT users.ID AS ID_Utilisateur,
				  	     (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(DateAjout)) AS DiffSecondes, COUNT(Numero) AS cpt,
				  		 (SELECT CONCAT(Pays,\'/\',Magazine,\'/\',Numero)
						  FROM numeros n
						  WHERE n.ID=numeros.ID
						  LIMIT 1) AS NumeroExemple
				  FROM numeros
				  INNER JOIN users ON numeros.ID_Utilisateur=users.ID
				  WHERE DateAjout > DATE_ADD(NOW(), INTERVAL -1 MONTH) AND users.username<>\'demo\' AND users.username NOT LIKE \'test%\'
				  GROUP BY users.ID, DATE(DateAjout)
				  HAVING COUNT(Numero) > 0
				  ORDER BY DateAjout DESC';
		$resultat_derniers_ajouts = DM_Core::$d->requete_select($requete);
		foreach($resultat_derniers_ajouts as $ajout) {
			preg_match('#([^/]+/[^/]+)#', $ajout['NumeroExemple'], $publicationcode);
			$evenements->publicationcodes[]=$publicationcode[0];
			
			list($pays,$magazine,$numero)=explode('/',$ajout['NumeroExemple']);
			$numero_complet=['Pays'=>$pays, 'Magazine'=>$magazine, 'Numero'=>$numero];
			
			$evenement = ['numero_exemple'=>$numero_complet,
							   'cpt'		   =>intval($ajout['cpt'])-1];
			
			ajouter_evenement(
				$evenements->evenements, $evenement, $ajout['DiffSecondes'], 'ajouts', $ajout['ID_Utilisateur']);
		}
		
		/* Propositions de bouquineries */
		$requete_bouquineries='SELECT bouquineries.ID_Utilisateur, bouquineries.Nom AS Nom, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(DateAjout)) AS DiffSecondes FROM bouquineries
							   WHERE Actif=1 AND DateAjout > date_add(now(), interval -1 month)';

		$resultat_bouquineries = DM_Core::$d->requete_select($requete_bouquineries);
		foreach($resultat_bouquineries as $bouquinerie) {
			$evenement = ['nom_bouquinerie'=>$bouquinerie['Nom']];
			ajouter_evenement(
					$evenements->evenements, $evenement, $bouquinerie['DiffSecondes'], 'bouquineries', $bouquinerie['ID_Utilisateur']);
		}
		
		/* Ajouts de tranches */
		$requete_tranches= "
            SELECT publicationcode, issuenumber, GROUP_CONCAT(contributeur) AS collaborateurs, DATE(dateajout) DateAjout,
               (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(dateajout)) AS DiffSecondes,
               CONCAT(publicationcode,'/',issuenumber) AS Numero
            FROM tranches_pretes
            INNER JOIN tranches_pretes_contributeurs USING (publicationcode, issuenumber)
            WHERE DateAjout > DATE_ADD(NOW(), INTERVAL -1 MONTH)
              AND NOT (publicationcode = 'fr/JM' AND issuenumber REGEXP '^[0-9]+$')
            GROUP BY publicationcode, issuenumber
            ORDER BY DateAjout DESC, collaborateurs";

		$resultat_tranches = DM_Core::$d->requete_select($requete_tranches);
        $groupe_precedent = null;
        $evenement = null;
		foreach($resultat_tranches as $tranche_prete) {
			$publicationcode = $tranche_prete['publicationcode'];
			$evenements->publicationcodes[]=$publicationcode;

            list($pays,$magazine,$numero)=explode('/',$tranche_prete['Numero']);
            $numero_complet=['Pays'=>$pays, 'Magazine'=>$magazine, 'Numero'=>$numero];

            $collaborateurs = explode(',', preg_replace('#(,{2,})#',',', trim($tranche_prete['collaborateurs'],',')));
            $groupe_courant = ['DiffSecondes' => $tranche_prete['DiffSecondes'], 'Collaborateurs' => $collaborateurs];

            if (!is_null($groupe_precedent) &&
                ($groupe_precedent['Collaborateurs'] === $groupe_courant['Collaborateurs']
              && round($groupe_precedent['DiffSecondes'] / 24 / 3600) === round($groupe_courant['DiffSecondes'] / 24 / 3600))) {
                $evenement['numeros'][] = $numero_complet;
            }
            else {
                if (!is_null($evenement)) {
                    ajouter_evenement(
                        $evenements->evenements, $evenement, $groupe_precedent['DiffSecondes'], 'tranches_pretes', null, $groupe_precedent['Collaborateurs']);
                }
                $evenement = ['numeros' => [$numero_complet]];
            }
            $groupe_precedent = $groupe_courant;
		}

        if (count($resultat_tranches) > 0) {
            ajouter_evenement(
                $evenements->evenements, $evenement, $groupe_courant['DiffSecondes'], 'tranches_pretes', null,$groupe_courant['Collaborateurs']);
        }

		$evenements->publicationcodes = array_unique($evenements->publicationcodes);
		ksort($evenements->evenements);
		
		$evenements_slice=[];
		$cpt=0;

		$tous_id_utilisateurs = [];

		// Filtre : les 20 plus récents seulement
		foreach($evenements->evenements as $diff_secondes=>$evenements_types) {
			$evenements_slice[$diff_secondes]=new stdClass();
			foreach($evenements_types as $type=>$evenements_type) {
				$evenements_slice_type=[];
				foreach($evenements_type as $evenement) {
					if ($cpt >= $limite_evenements) {
						$evenements_slice[$diff_secondes]->$type=$evenements_slice_type;
						break 3;
					}
					$evenements_slice_type[]=$evenement;
					if (!is_null($evenement->id_utilisateur)) {
					    $tous_id_utilisateurs[]= $evenement->id_utilisateur;
                    }
					if (!is_null($evenement->ids_utilisateurs)) {
					    $tous_id_utilisateurs = array_merge($tous_id_utilisateurs, $evenement->ids_utilisateurs);
                    }
					$cpt++;
				}
				$evenements_slice[$diff_secondes]->$type=$evenements_slice_type;
			}	
		}

		$evenements->ids_utilisateurs=$tous_id_utilisateurs;
		$evenements->evenements=$evenements_slice;
		return $evenements;
	}

    /**
     * @param $id_user
     * @param boolean $depuis_derniere_visite
     * @return array
    */
    public function get_tranches_collection_ajoutees($id_user, $depuis_derniere_visite = false)
    {
        $derniere_visite = null;
        if ($depuis_derniere_visite) {
            $derniere_visite = Util::get_derniere_visite_utilisateur();
            if (is_null($derniere_visite)) {
                return [];
            }
        }
        $requete_tranches_collection_ajoutees =
            "SELECT tp.publicationcode, tp.issuenumber, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(tp.dateajout)) AS DiffSecondes
             FROM tranches_pretes tp, numeros n
             WHERE n.ID_Utilisateur = '$id_user'
             AND CONCAT(publicationcode,'/',issuenumber) = CONCAT(n.Pays,'/',n.Magazine,'/',n.Numero)
             AND DATEDIFF(NOW(), tp.dateajout) < 90";

        if (!is_null($derniere_visite)) {
            $requete_tranches_collection_ajoutees.="
                AND tp.dateajout>'{$derniere_visite->format('Y-m-d H:i:s')}'";
        }
        $requete_tranches_collection_ajoutees.="
            ORDER BY DiffSecondes ASC
            LIMIT 5";

        return DM_Core::$d->requete_select($requete_tranches_collection_ajoutees);
    }

    function get_id_user_partage_bibliotheque($user, $cle) {
        $id_user=DM_Core::$d->user_to_id($user);
        if (is_null($id_user)) {
            return null;
        }

    	// TODO Use DM server service
        $requete_verifier_lien_partage = 'SELECT 1 FROM bibliotheque_acces_externes
                                          WHERE ID_Utilisateur = '.mysqli_real_escape_string(Database::$handle, $id_user).' 
                                          AND Cle=\''.mysqli_real_escape_string(Database::$handle, $cle).'\'';
        if (count(DM_Core::$d->requete_select($requete_verifier_lien_partage)) > 0) {
            return $id_user;
        }
        else {
            return null;
        }
    }

    public function get_details_collections($idsUtilisateurs) {
	    $concat_utilisateurs = implode(',', $idsUtilisateurs);
	    $requete_details_collections = "
            SELECT 
                users.ID AS ID_Utilisateur, users.username AS Username, 
                COUNT(DISTINCT numeros.Pays) AS NbPays, COUNT(DISTINCT numeros.Pays, numeros.Magazine) AS NbMagazines, COUNT(numeros.Numero) AS NbNumeros,
                (
                 SELECT COUNT(issuenumber) AS cpt FROM tranches_pretes_contributeurs
                 WHERE contributeur = users.ID AND contribution='photographe'
                ) AS NbPhotographies,
                (
                 SELECT COUNT(issuenumber) AS cpt FROM tranches_pretes_contributeurs
                 WHERE contributeur = users.ID AND contribution='createur'
                ) AS NbCreations,
                (
                 SELECT COUNT(bouquineries.Nom) FROM bouquineries
                 WHERE bouquineries.ID_Utilisateur=users.ID AND bouquineries.Actif=1
                ) AS NbBouquineries
            FROM users
            
            LEFT JOIN numeros ON users.ID = numeros.ID_Utilisateur
            WHERE users.ID IN ($concat_utilisateurs)
            GROUP BY users.ID";

	    $resultats = DM_Core::$d->requete_select($requete_details_collections);
	    return array_combine(array_map(function($resultat) {
	        return $resultat['ID_Utilisateur'];
	    }, $resultats), array_values($resultats));
    }

    public function get_points_courants($id_user){
        $requete_points_courants = "
            SELECT
            contributions.type_contribution,
            sum(contributions.Popularite) AS points
            FROM (
               SELECT
                 tp.*,
                 tpc.contributeur,
                 tpc.contribution AS type_contribution,
                 (
                   SELECT COUNT(*) AS Popularite
                   FROM numeros n
                   INNER JOIN users u ON n.ID_Utilisateur = u.ID
                   WHERE
                     n.Pays = SUBSTRING_INDEX(tp.publicationcode, '/', 1) AND
                     n.Magazine = SUBSTRING_INDEX(tp.publicationcode, '/', -1) AND
                     n.Numero = tp.issuenumber AND
                     u.username NOT LIKE 'test%' AND
                     n.DateAjout < DATE_SUB(tp.dateajout, INTERVAL -1 MONTH)
                   GROUP BY n.Pays, n.Magazine, n.Numero
                 ) AS Popularite
        
               FROM tranches_pretes tp
               INNER JOIN tranches_pretes_contributeurs tpc USING (publicationcode, issuenumber)
             ) contributions
            WHERE contributions.contributeur=$id_user
            GROUP BY contributions.type_contribution";

        return DM_Core::$d->requete_select($requete_points_courants);
    }

    public function get_points_tranche($pays,$magazine,$numero){
        $requete_points_tranche = "
            SELECT np.Popularite
            FROM numeros_popularite np
            WHERE
                np.Pays = '$pays' AND
                np.Magazine = '$magazine' AND
                np.Numero = '$numero'
        ";

        $resultats_points_tranche = DM_Core::$d->requete_select($requete_points_tranche);
        if (count($resultats_points_tranche) === 0) {
            return 0;
        }
        else {
            return intval($resultats_points_tranche[0]['Popularite']);
        }
    }

}

function get_current_db() {
	$result = Database::$handle->query("SELECT DATABASE()") or die(Database::$handle->error);
    if ($row=$result->fetch_array(MYSQLI_NUM)) {
        return $row[0][0];
    }
    else {
        return null;
    }
}

if (isset($_POST['database'])) {
	@session_start();
	if (isset($_POST['pass'])) {
		if (isset($_POST['connexion'])) {
			if (!DM_Core::$d->user_connects($_POST['user'],$_POST['pass']))
				echo 'Identifiants invalides!';
			else {
				$_SESSION['user']=$_POST['user'];
			    $_SESSION['id_user']=DM_Core::$d->user_to_id($_SESSION['user']);
			}
		}
	}

	else if (isset($_POST['update'])) {
		$id_user=$_SESSION['id_user'];
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
			$requete_id_acquisition="SELECT Count(ID_Acquisition) AS cpt, ID_Acquisition FROM achats WHERE ID_User='$id_user' AND Date = '$date_acquisition' GROUP BY ID_Acquisition";
			$resultat_acqusitions=DM_Core::$d->requete_select($requete_id_acquisition);
			if ($resultat_acqusitions[0]['cpt'] ==0)
				$id_acquisition=-1;
			else
				$id_acquisition=$resultat_acqusitions[0]['ID_Acquisition'];
		}
		DM_Core::$d->update_numeros($pays,$magazine,$etat,$av,$liste,$id_acquisition);
	}
	else if (isset($_POST['evenements_recents'])) {
		if (Inducks::connexion_ok()) {
			Affichage::afficher_evenements_recents(DM_Core::$d->get_evenements_recents());
		}
	}
	else if (isset($_POST['affichage'])) {
		$id_user=$_SESSION['id_user'];
		$l=DM_Core::$d->toList($id_user);
		$pays=$_POST['pays'];
		$magazine=$_POST['magazine'];

		Affichage::afficher_numeros($l, $pays, $magazine);
	}
	else if (isset($_POST['acquisition'])) {
		$id_user=$_SESSION['id_user'];

		/*Vérifier d'abord que les numéros à ajouter ne correspondent pas déjà à une date d'acquisition*/
		$requete_acquisition_existe='SELECT Count(ID_Acquisition) as c '
								   .'FROM achats '
								   .'WHERE ID_User='.$id_user.' AND Date = \''.$_POST['date_annee'].'-'.$_POST['date_mois'].'-'.$_POST['date_jour'].'\' AND Description = \''.$_POST['description'].'\'';
		$compte_acquisition_date=DM_Core::$d->requete_select($requete_acquisition_existe);
		if ($compte_acquisition_date[0]['c']!=0) {
			echo 'Date';exit(0);
		}

		DM_Core::$d->requete('INSERT INTO achats(ID_User,Date,Description)'
				   			.' VALUES ('.$id_user.',\''.$_POST['date_annee'].'-'.$_POST['date_mois'].'-'.$_POST['date_jour'].'\',\''.$_POST['description'].'\')');
		$requete_acquisition='SELECT Date, Description FROM achats WHERE ID_User='.$id_user.' ORDER BY Date DESC';
		$liste_acquisitions=DM_Core::$d->requete_select($requete_acquisition);

	}
	else if(isset($_POST['supprimer_acquisition'])) {
		$id_user=$_SESSION['id_user'];
		$requete='DELETE FROM achats WHERE ID_User='.$id_user.' AND ID_Acquisition='.$_POST['supprimer_acquisition'];
		echo $requete;
		DM_Core::$d->requete($requete);
	}
	else if (isset($_POST['liste_achats'])) {
		$id_user=$_SESSION['id_user'];
		$liste_achats=DM_Core::$d->requete_select('SELECT ID_Acquisition, Date,Description FROM achats WHERE ID_User='.$id_user.' ORDER BY Date DESC');
		$tab_achats=[];
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
		$id_user=$_SESSION['id_user'];
		$resultat_notations=DM_Core::$d->get_notes_auteurs($id_user);
		
        header('Content-Type: application/json');
		echo json_encode($resultat_notations);
	}
	else if (isset($_POST['changer_notation'])) {
		DM_Core::$d->modifier_note_auteur(
		    mysqli_real_escape_string(Database::$handle, $_POST['auteur']),
		    mysqli_real_escape_string(Database::$handle, $_POST['notation'])
        );
	}
	else if (isset($_POST['supprimer_auteur'])) {
		$id_user=$_SESSION['id_user'];
		DM_Core::$d->requete('DELETE FROM auteurs_pseudos '
				   .'WHERE ID_user='.$id_user.' AND NomAuteurAbrege = \''.$_POST['nom_auteur'].'\'');
	}
	elseif (isset($_POST['liste_bouquineries'])) {
		$requete_bouquineries='SELECT Nom, AdresseComplete AS Adresse, Commentaire, CoordX, CoordY, CONCAT(\''.SIGNALE_PAR.'\',IFNULL(username,\'un visiteur anonyme\')) AS Signature FROM bouquineries '
							 .'LEFT JOIN users ON bouquineries.ID_Utilisateur=users.ID '
							 .'WHERE Actif=1';
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

function ajouter_evenement(&$evenements, $evenement, $diff_secondes, $type_evenement, $id_utilisateur = null, $noms_utilisateurs = null) {
	$evenement['diffsecondes'] = $diff_secondes;
	$evenement['id_utilisateur'] = $id_utilisateur;
	$evenement['ids_utilisateurs'] = $noms_utilisateurs;
	if (!array_key_exists($diff_secondes, $evenements)) {
		$evenements[$diff_secondes]=new stdClass();
	}
	if (!array_key_exists($type_evenement, $evenements[$diff_secondes])) {
		$evenements[$diff_secondes]->$type_evenement=[];
	}
	$evenements_type=$evenements[$diff_secondes]->$type_evenement;
	$evenements_type[]=json_decode(json_encode($evenement));
	
	$evenements[$diff_secondes]->$type_evenement = $evenements_type;
}
?>