<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}

require_once 'remote/dm_client.php';
DmClient::init();

include_once 'locales/lang.php';
require_once'Liste.class.php';


require_once'DucksManager_Core.class.php';
require_once'Inducks.class.php';

Database::$etats=[
   'mauvais'=>[MAUVAIS,'#FF0000'],
   'moyen'=>[MOYEN,'#FF8000'],
   'bon'=>[BON,'#2CA77B'],
   'indefini'=>[INDEFINI,'#808080']];

class Database {
	public static $etats;

	function __construct() {
	    if (isset($_SESSION['user'])) {
            DmClient::setUserdata(['user' => $_SESSION['user'], 'pass' => $_SESSION['pass']]);
        }
	}

	function requete($requete, $parametres = [], $db = 'db_dm') {
        try {
		    $resultats = DmClient::get_query_results_from_dm_server($requete, $db, $parametres);
            if (is_array($resultats)) {
                return array_map(function($result) {
                    return (array) $result;
                }, $resultats);
            }
            return [];
        } catch (Exception $e) {
		    return [];
        }
	}

	function user_to_id($user) {
		if (isset($_COOKIE['user'], $_COOKIE['pass']) && empty($user)) {
            $user=$_COOKIE['user'];
		}
		$requete='SELECT ID FROM users WHERE username = \''.$user.'\'';
		$resultat=DM_Core::$d->requete($requete);
		if (count($resultat) === 0) {
			return null;
		}
		return $resultat[0]['ID'];
	}

	function user_connects($user,$pass) {
		if (!$this->user_exists($user)) {
			return false;
		}
		$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\') AND password LIKE(sha1(\''.$pass.'\'))';
		return (count(DM_Core::$d->requete($requete))>0);
	}

	function user_exists($user) {
		$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\')';
		return (count(DM_Core::$d->requete($requete))>0);

	}

	function user_afficher_video() {
		if (isset($_SESSION['user'])) {
			$requete_afficher_video="SELECT AfficherVideo FROM users WHERE username = '{$_SESSION['user']}'";
			$resultat_afficher_video=DM_Core::$d->requete($requete_afficher_video);
			return $resultat_afficher_video[0]['AfficherVideo'] === '1';
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

	function get_noms_complets_pays() {
		return Inducks::get_pays();
	}

	function update_numeros($pays,$magazine,$etat,$av,$liste,$id_acquisition) {
		$id_user=$this->user_to_id($_SESSION['user']);

		if ($etat === '_non_possede') {
            DM_Core::$d->requete('
              DELETE FROM numeros
              WHERE ID_Utilisateur=?
                AND Numero IN (' . implode(',', array_fill(0, count($liste), '?')) . ')', array_merge([$id_user], $liste)
            );
        }
        else {
            $liste_user=$this->toList($id_user);

            $id_acquisition = (int) $id_acquisition;
            $id_acquisition_insert=$id_acquisition==='do_not_change' ? -1 : $id_acquisition;
            $av_insert=$av==='do_not_change' ? 0 : $av;

            $numeros_insert = [];
            $liste_deja_possedes=[];
            foreach($liste as $numero) {
                if (!is_null($liste_user->get_etat_numero_possede($pays,$magazine,$numero))) {
                    $liste_deja_possedes[] = $numero;
                }
                else {
                    $numeros_insert[] = [$pays,$magazine,$numero,$etat,$id_acquisition_insert,$av_insert,$id_user];
                }
            }

            if (count($numeros_insert) > 0) {
                $champs = ['Pays', 'Magazine', 'Numero', 'Etat', 'ID_Acquisition', 'AV', 'ID_Utilisateur'];
                DM_Core::$d->requete('
                  INSERT INTO numeros(' . implode(',', $champs) . ')
                  VALUES ' . implode(',', array_map(function ($data_numero) {
                        return '(' . implode(',', array_fill(0, count($data_numero), '?')) . ')';
                    }, $numeros_insert)), flatten($numeros_insert)
                );
            }

            $changements = [];

            if ($etat !== 'do_not_change') {
                $changements['Etat'] = $etat;
            }

            if ($id_acquisition !== 'do_not_change') {
                $changements['ID_Acquisition'] = $id_acquisition;
            }

            if ($av !== 'do_not_change') {
                $changements['AV'] = $av;
            }

            if (count($liste_deja_possedes) > 0) {
                DM_Core::$d->requete('
                  UPDATE numeros
                  SET ' . implode(',', array_map(function ($champ) {
                        return "$champ=?";
                    }, array_keys($changements))) . ' 
                  WHERE Pays=?
                    AND Magazine=?
                    AND ID_Utilisateur=?
                    AND Numero IN (' . implode(',', array_fill(0, count($liste_deja_possedes), '?')) . ')', array_merge(array_values($changements), [$pays, $magazine, $id_user], $liste_deja_possedes)
                );
            }
		}
	}

	function toList($id_user=false) {
        $resultats_numeros = Dm_Core::$d->requete('
            SELECT Pays, Magazine, REGEXP_REPLACE(Numero, \'[ ]+\', \' \') AS Numero, Etat, AV, achats.ID_Acquisition AS ID_Acquisition, achats.Date AS Date_Acquisition, achats.Description AS Description_Acquisition
            FROM numeros
            LEFT JOIN achats ON numeros.ID_Acquisition=achats.ID_Acquisition
            WHERE ID_Utilisateur=?
            ORDER BY Pays, Magazine, Numero',
            [$id_user]
        );
        $l=new Liste();
        foreach ($resultats_numeros as $resultat) {
            $resultat_val = array_values($resultat);
            if (isset($l->collection[$resultat['Pays']])) {
                if (!isset($l->collection[$resultat['Pays']][$resultat['Magazine']])) {
                    $l->collection[$resultat['Pays']][$resultat['Magazine']]=[];
                }
                $l->collection[$resultat['Pays']][$resultat['Magazine']][$resultat['Numero']] = $resultat_val;
            }
            else {
                $l->collection[$resultat['Pays']]=[$resultat['Magazine']=>[$resultat_val]];
            }
        }
        return $l;
	}

	function ajouter_auteur($nomAuteurAbrege) {
		$id_user=$this->user_to_id($_SESSION['user']);
		$requete_nb_auteurs_surveilles="
            SELECT NomAuteurAbrege
            FROM auteurs_pseudos
            WHERE ID_User=$id_user";
		$resultat_nb_auteurs_surveilles=DM_Core::$d->requete($requete_nb_auteurs_surveilles);
		if (count($resultat_nb_auteurs_surveilles) >= 5) {
			?><div class="alert alert-danger"><?=MAX_AUTEURS_SURVEILLES_ATTEINT?></div><?php
		}
		else {
            if (Inducks::is_auteur($nomAuteurAbrege)) {
                $requete_auteur_existe = $requete_nb_auteurs_surveilles." AND NomAuteurAbrege = '$nomAuteurAbrege'";
                $resultat_auteur_existe=DM_Core::$d->requete($requete_auteur_existe);
                if (count($resultat_auteur_existe) > 0) {
                    ?><div class="alert alert-danger"><?=AUTEUR_DEJA_DANS_LISTE?></div><?php
                }
                else {
                    $requete_ajout_auteur= '
                        INSERT INTO auteurs_pseudos(NomAuteurAbrege, ID_User, Notation)
                        VALUES (:nomAuteurAbrege, :idUser, :notation)';
                    DM_Core::$d->requete($requete_ajout_auteur, ['nomAuteurAbrege' => $nomAuteurAbrege, 'idUser' => $id_user, 'notation' => -1]);
                }
            }
        }
	}

	function afficher_liste_auteurs_surveilles($auteurs_surveilles) {
		if (count($auteurs_surveilles)===0) {
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
		$notesAuteurs = $this->requete('SELECT NomAuteurAbrege, Notation FROM auteurs_pseudos WHERE ID_user='.$id_user);
		$codesAuteurs = array_map(function($noteAuteur) {
		    return $noteAuteur['NomAuteurAbrege'];
        }, $notesAuteurs);
		$nomsAuteurs = Inducks::requete('
          SELECT personcode, fullname
          from inducks_person
          where personcode IN ('.implode(',', array_fill(0, count($codesAuteurs), '?')).')',
            $codesAuteurs
        );
		array_walk($notesAuteurs, function(&$noteAuteur) use ($nomsAuteurs) {
		    $noteAuteur['NomAuteur'] = array_values(array_filter($nomsAuteurs, function($codeAuteur) use ($noteAuteur) {
		        return $codeAuteur['personcode'] === $noteAuteur['NomAuteurAbrege'];
		    }))[0]['fullname'];
        });
		return $notesAuteurs;
	}

	function modifier_note_auteur($nomAuteurAbrege, $note) {
        $id_user=$this->user_to_id($_SESSION['user']);

        $requete_notation="
          UPDATE auteurs_pseudos
          SET Notation=$note
          WHERE NomAuteurAbrege = :auteur
            AND ID_user=:id_user";
        DM_Core::$d->requete($requete_notation, [':auteur' => $nomAuteurAbrege, ':id_user' => $id_user]);
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
		return count($this->requete($requete)) === 1;
	}

	function get_niveaux() {
		$id_user=$this->user_to_id($_SESSION['user']);

		$requete_nb_photographies ="
            SELECT NbPoints AS cpt FROM users_points up
            WHERE up.TypeContribution = 'photographe' AND up.ID_Utilisateur = $id_user";
		$resultat_nb_photographies=DM_Core::$d->requete($requete_nb_photographies);

		$requete_nb_creations =	"
            SELECT NbPoints AS cpt FROM users_points up
            WHERE up.TypeContribution = 'createur' AND up.ID_Utilisateur = $id_user";
		$resultat_nb_creations=DM_Core::$d->requete($requete_nb_creations);

		$requete_nb_bouquineries='SELECT COUNT(Nom) AS cpt FROM bouquineries WHERE Actif=1 AND ID_Utilisateur='.$id_user;
		$resultat_nb_bouquineries=DM_Core::$d->requete($requete_nb_bouquineries);

		return Affichage::get_medailles([
            'Photographe'=> (int) ($resultat_nb_photographies[0] ?? ['cpt' => 0])['cpt'],
            'Concepteur' => (int) ($resultat_nb_creations[0] ?? ['cpt' => 0])['cpt'],
            'Duckhunter' => (int) ($resultat_nb_bouquineries[0] ?? ['cpt' => 0])['cpt']
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

		$resultat_inscriptions = DM_Core::$d->requete($requete_inscriptions);
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
		$resultat_derniers_ajouts = DM_Core::$d->requete($requete);
		foreach($resultat_derniers_ajouts as $ajout) {
			preg_match('#([^/]+/[^/]+)#', $ajout['NumeroExemple'], $publicationcode);
			$evenements->publicationcodes[]=$publicationcode[0];

			[$pays,$magazine,$numero] =explode('/',$ajout['NumeroExemple']);
			$numero_complet=['Pays'=>$pays, 'Magazine'=>$magazine, 'Numero'=>$numero];

			$evenement = [
			        'numero_exemple'=>$numero_complet,
			        'cpt'		    =>(int) $ajout['cpt']-1
            ];

			ajouter_evenement(
				$evenements->evenements, $evenement, $ajout['DiffSecondes'], 'ajouts', $ajout['ID_Utilisateur']);
		}

		/* Propositions de bouquineries */
		$requete_bouquineries='SELECT bouquineries.ID_Utilisateur, bouquineries.Nom AS Nom, (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(DateAjout)) AS DiffSecondes FROM bouquineries
							   WHERE Actif=1 AND DateAjout > date_add(now(), interval -1 month)';

		$resultat_bouquineries = DM_Core::$d->requete($requete_bouquineries);
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

		$resultat_tranches = DM_Core::$d->requete($requete_tranches);
        $groupe_precedent = null;
        $evenement = null;
		foreach($resultat_tranches as $tranche_prete) {
			$publicationcode = $tranche_prete['publicationcode'];
			$evenements->publicationcodes[]=$publicationcode;

            [$pays,$magazine,$numero] =explode('/',$tranche_prete['Numero']);
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
    public function get_tranches_collection_ajoutees($id_user, $depuis_derniere_visite = false) {
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

        return DM_Core::$d->requete($requete_tranches_collection_ajoutees);
    }

    public function get_details_collections($idsUtilisateurs) {
	    $requete_details_collections = "
            SELECT 
                users.ID AS ID_Utilisateur, users.username AS Username, users.AccepterPartage,
                COUNT(DISTINCT numeros.Pays) AS NbPays,
                COUNT(DISTINCT numeros.Pays, numeros.Magazine) AS NbMagazines,
                COUNT(numeros.Numero) AS NbNumeros,
                (SELECT IFNULL((
                 SELECT NbPoints FROM users_points users_points_photographe
                 WHERE users_points_photographe.ID_Utilisateur = users.ID
                   AND users_points_photographe.TypeContribution = 'photographe'
                ), 0)) AS NbPointsPhotographe,
                (SELECT IFNULL((
                 SELECT NbPoints FROM users_points users_points_createur
                 WHERE users_points_createur.ID_Utilisateur = users.ID
                   AND users_points_createur.TypeContribution = 'createur'
                ), 0)) AS NbPointsCreateur,
                (
                 SELECT COUNT(bouquineries.Nom) FROM bouquineries
                 WHERE bouquineries.ID_Utilisateur=users.ID AND bouquineries.Actif=1
                ) AS NbBouquineries
            FROM users            
            LEFT JOIN numeros ON users.ID = numeros.ID_Utilisateur
            WHERE users.ID IN (" . implode(',', array_fill(0, count($idsUtilisateurs), '?')) . ")
            GROUP BY users.ID";

	    $resultats = DM_Core::$d->requete($requete_details_collections, $idsUtilisateurs);
	    return array_combine(array_map(function($resultat) {
	        return $resultat['ID_Utilisateur'];
	    }, $resultats), array_values($resultats));
    }

    public function get_points_courants($id_user){
        $requete_points_courants = "
            SELECT
                TypeContribution,
                NbPoints
            FROM users_points
            WHERE ID_Utilisateur=$id_user";

        $resultats = DM_Core::$d->requete($requete_points_courants);
        $points = ['photographe' => 0, 'createur' => 0];
        foreach($resultats as $resultat) {
            $points[$resultat['TypeContribution']] = (int) $resultat['NbPoints'];
        }
        return $points;
    }

}

if (isset($_POST['database'])) {
	@session_start();
	if (isset($_POST['pass'])) {
		if (isset($_POST['connexion'])) {
			if (!DM_Core::$d->user_connects($_POST['user'],$_POST['pass'])) {
			    echo 'Identifiants invalides!';
			}
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
        $av=$_POST['av'];
		$id_acquisition=$_POST['id_acquisition'];

		if ($id_acquisition!==-1 && $id_acquisition!=='do_not_change') {
			$requete_id_acquisition="SELECT Count(*) AS cpt, ID_Acquisition FROM achats WHERE ID_User='$id_user' AND ID_Acquisition = '$id_acquisition'";
			$resultat_acqusitions=DM_Core::$d->requete($requete_id_acquisition);
			if ($resultat_acqusitions[0]['cpt'] === 0) {
			    $id_acquisition=-1;
			}
		}
		DM_Core::$d->update_numeros($pays,$magazine,$etat,$av,$liste,$id_acquisition);
	}
	else if (isset($_POST['evenements_recents'])) {
        Affichage::afficher_evenements_recents(DM_Core::$d->get_evenements_recents());
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

		//Vérifier d'abord que la date d'acquisition n'existe pas déjà
		$requete_acquisition_existe='SELECT ID_Acquisition '
								   .'FROM achats '
								   .'WHERE ID_User='.$id_user.' AND Date = \''.$_POST['date'].'\' AND Description = \''.$_POST['description'].'\'';
		$compte_acquisition_date=DM_Core::$d->requete($requete_acquisition_existe);
		if (count($compte_acquisition_date) > 0) {
			echo 'Date';
		}
		else {
            DM_Core::$d->requete('INSERT INTO achats(ID_User,Date,Description)'
                . ' VALUES (' . $id_user . ',\'' . $_POST['date'] . '\',\'' . $_POST['description'] . '\')');
		}
	}
	else if(isset($_POST['supprimer_acquisition'])) {
		$id_user=$_SESSION['id_user'];
		$requete='DELETE FROM achats WHERE ID_User='.$id_user.' AND ID_Acquisition='.$_POST['supprimer_acquisition'];
		echo $requete;
		DM_Core::$d->requete($requete);
	}
	else if (isset($_POST['liste_achats'])) {
		$id_user=$_SESSION['id_user'];
		$liste_achats=DM_Core::$d->requete("SELECT ID_Acquisition, Date, Description FROM achats WHERE ID_User=$id_user ORDER BY Date DESC");
		$tab_achats=array_map(function($achat) {
		    return [
                'id' => $achat['ID_Acquisition'],
                'description' => $achat['Description'],
                'date' => $achat['Date']
            ];
        }, $liste_achats);
        header('Content-Type: application/json');
		echo json_encode($tab_achats);
	}
	else if (isset($_POST['liste_auteurs'])) {
        $resultats_auteur = [];
        $requete_auteur='
          SELECT personcode, fullname FROM inducks_person
          WHERE LOWER(fullname) LIKE :fullname';
        $resultats_auteur = DM_Core::$d->requete($requete_auteur, [':fullname' => '%'.strtolower($_POST['value']).'%'], 'db_coa');

        header('Content-Type: application/json');
        echo json_encode(array_map(function($auteur) {
            return ['id' => $auteur['personcode'], 'name' => $auteur['fullname']];
        }, $resultats_auteur));
	}
	else if (isset($_POST['liste_notations'])) {
		$id_user=$_SESSION['id_user'];
		$resultat_notations=DM_Core::$d->get_notes_auteurs($id_user);

        header('Content-Type: application/json');
		echo json_encode($resultat_notations);
	}
	else if (isset($_POST['changer_notation'])) {
		DM_Core::$d->modifier_note_auteur($_POST['auteur'], $_POST['notation']);
	}
	else if (isset($_POST['supprimer_auteur'])) {
		$id_user=$_SESSION['id_user'];
		DM_Core::$d->requete('DELETE FROM auteurs_pseudos '
            . 'WHERE ID_user=' . $id_user . ' AND NomAuteurAbrege = \'' . $_POST['auteur'] . '\'');
	}
	else if (isset($_POST['liste_bouquineries'])) {
		$requete_bouquineries='SELECT Nom, AdresseComplete AS Adresse, Commentaire, CoordX, CoordY, CONCAT(\''.SIGNALE_PAR.'\',IFNULL(username,\'un visiteur anonyme\')) AS Signature FROM bouquineries '
							 .'LEFT JOIN users ON bouquineries.ID_Utilisateur=users.ID '
							 .'WHERE Actif=1';
		$resultat_bouquineries=DM_Core::$d->requete($requete_bouquineries);
        header('Content-type: application/json');
		echo json_encode($resultat_bouquineries);
	}
    else if (isset($_POST['get_points'])) {
		$id_user=$_SESSION['id_user'];
	    $niveauxMedaillesPhotographe = Affichage::$niveaux_medailles['Photographe'];
	    $pointsActuels = DM_Core::$d->get_points_courants($id_user);
        header('Content-type: application/json');
        echo json_encode([
            'niveaux_medailles' => Affichage::$niveaux_medailles['Photographe'],
            'points' => $pointsActuels['photographe']
        ]);
    }
	else { // Vérification de l'utilisateur
		if (DM_Core::$d->user_exists($_POST['user'])) {
		    echo UTILISATEUR_EXISTANT;
		}
		else {
		    echo 'OK, '.UTILISATEUR_VALIDE;
		}
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

function flatten($array) {
    $return = [];
    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
    return $return;
}
?>
