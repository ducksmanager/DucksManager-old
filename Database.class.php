<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Liste.class.php');
require_once('Inducks.class.php');
if (!isset($GLOBALS['firephp'])) {
	require_once('FirePHPCore/FirePHP.class.php');

	$GLOBALS['firephp'] = FirePHP::getInstance(true);
	$GLOBALS['firephp']->registerErrorHandler(
	            $throwErrorExceptions=true);
	$GLOBALS['firephp']->registerExceptionHandler();
	$GLOBALS['firephp']->registerAssertionHandler(
	            $convertAssertionErrorsToExceptions=true,
	            $throwAssertionExceptions=false);
	ob_start();
}
Database::$etats=array(		   'mauvais'=>array(MAUVAIS,'#FF0000'),
                                   'moyen'=>array(MOYEN,'#FF8000'),
                                   'bon'=>array(BON,'#2CA77B'),
                                   'indefini'=>array(INDEFINI,'#808080'));

Database::$etats_fr=array(	   'mauvais'=>array('Mauvais','#FF0000'),
                                                    'moyen'=>array('Moyen','#FF8000'),
                                                    'bon'=>array('Bon','#2CA77B'),
                                                    'indefini'=>array('Indéfini','#808080'));
class Database {
	public static $etats;
	public static $etats_fr;
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
		$requete_resultat=mysql_query($requete);
	}

	function liste_numeros($requete) {
		$requete_resultat=mysql_query($requete);
		while ($infos=mysql_fetch_array($requete_resultat)) {
			echo '!';
		}
	}

	function user_to_id($user) {
		if ((!isset($user) || empty($user)) && (isset($_COOKIE['user']) && isset($_COOKIE['pass']))) {
			$user=$_COOKIE['user'];
		}
		$requete='SELECT ID FROM users WHERE username LIKE \''.$user.'\'';
		$d=new Database();
		$resultat=$d->requete_select($requete);
		foreach ($resultat as $infos) {
			return $infos['ID'];
		}
	}

	function user_connects($user,$pass) {
		if (!$this->user_exists($user)) {
			//echo 'Ce nom d\'utilisateur n\'existe pas !';
			return false;
		}
		else {
			$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\') AND password LIKE(\''.$pass.'\')';
			$requete_resultat=mysql_query($requete);
			return (mysql_num_rows($requete_resultat)!=0);
		}
	}

	function user_exists($user) {
		$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\')';
		$requete_resultat=mysql_query($requete);
		return (mysql_num_rows($requete_resultat)!=0);
	}

    function user_is_beta() {
        if (isset($_SESSION['user'])) {
            $requete_beta_user='SELECT BetaUser FROM users WHERE username LIKE \''.$_SESSION['user'].'\'';
            $resultat_beta=$this->requete_select($requete_beta_user);
            return $resultat_beta[0]['BetaUser']==1;
        }
        return false;
    }

	function nouveau_user($user,$pass) {
		date_default_timezone_set('Europe/Paris');
		$requete='INSERT INTO users(username,password,DateInscription) VALUES(\''.$user.'\',\''.$pass.'\',\''.date('Y-m-d').'\')';
		echo $requete;
		if (false===mysql_query($requete)) {
			echo ERREUR_EXECUTION_REQUETE;
			return false;
		}
		return true;
	}
	/*
	function liste_acquisitions($requete) {
		$requete_resultat=mysql_query($requete);
		$cpt=0;
		echo '<table border="1">';
		while ($infos=mysql_fetch_array($requete_resultat)) {
			echo '<tr style="color:#'.$infos['Style_couleur'].';"><td>'.$infos['Date'].'</td><td>';
			$requete_numeros_acquisition='SELECT * FROM numeros WHERE (ID_Acquisition='.$infos['ID'].') ORDER BY Pays,Magazine,Numéro,Etat';
			$requete_numeros_acquisition_resultat=mysql_query($requete_numeros_acquisition);
			while($infos_numeros=mysql_fetch_array($requete_numeros_acquisition_resultat)) {
				echo '<u>'.$infos_numeros['Magazine'].'</u> '.$infos_numeros['Numéro'].'<br />';
			}
			echo '</td></tr>';
			$cpt++;
		}
		echo '</table>';
	}*/

    function get_nom_complet_magazine($pays,$magazine) {
        $requete_nom_magazine='SELECT NomComplet FROM magazines WHERE PaysAbrege LIKE \''.$pays.'\' AND NomAbrege LIKE \''.$magazine.'\'';
        $resultat_nom_magazine=$this->requete_select($requete_nom_magazine);
        $requete_nom_pays='SELECT NomComplet FROM pays WHERE NomAbrege LIKE \''.$pays.'\'';
        $resultat_nom_pays=$this->requete_select($requete_nom_pays);
        if (count($resultat_nom_magazine)==0 || count($resultat_nom_pays)==0) {
            Inducks::get_noms_complets_magazines($pays);
            $resultat_nom_magazine=$this->requete_select($requete_nom_magazine);
            $resultat_nom_pays=$this->requete_select($requete_nom_pays);
            if (count($resultat_nom_magazine)==0 || count($resultat_nom_pays)==0) {
                $requete_nom_magazine='INSERT INTO magazines(PaysAbrege,NomAbrege,NomComplet) VALUES ("'.$pays.'","'.$magazine.'","'.$magazine.'")';
                $this->requete($requete_nom_magazine);
                $requete_nom_pays='INSERT INTO pays(NomAbrege,NomComplet) VALUES ("'.$pays.'","'.$pays.'")';
                $this->requete($requete_nom_pays);
            }
        }
        return array($resultat_nom_pays[0]['NomComplet'],$resultat_nom_magazine[0]['NomComplet']);
    }

	function liste_etats() {
		$debut=true;
		foreach(self::$etats as $etat_court=>$infos_etat) {
			if ($etat_court!='indefini') {
				if (!$debut) {
					echo '~';
				}
				echo $infos_etat[0];
				$debut=false;
			}
		}
	}

	function liste_numeros_externes_dispos($id_user) {
		$requete_numeros_externes='SELECT Id_Utilisateur, Pays,Magazine,Numéro,Etat,AV FROM numeros WHERE (Id_Utilisateur<>'.$id_user.' AND AV=1) ORDER BY Id_Utilisateur, Pays, Magazine';
		$numeros_externes=$this->requete_select($requete_numeros_externes);
		if (count($numeros_externes)!=0) {
			$requete_pseudos_utilisateurs='SELECT ID, username FROM users';
			$liste_utilisateurs=array();
			$liste_utilisateurs_resultat=$this->requete_select($requete_pseudos_utilisateurs);
			foreach($liste_utilisateurs_resultat as $utilisateur) {
				$liste_utilisateurs[$utilisateur['ID']]=$utilisateur['username'];
			}
			$id_utilisateur='';
			$pays='';
			echo '<ul>';
			foreach($numeros_externes as $numero) {
				if ($numero['Pays']!=$pays)
					$liste_magazines=Inducks::get_noms_complets_magazines($numero['Pays']);
				$pays=$numero['Pays'];
				$requete_possede='SELECT Count(Numéro) AS c FROM numeros WHERE (ID_Utilisateur='
								 .$id_user.' AND Pays LIKE \''.$numero['Pays'].'\' AND Magazine LIKE \''
								 .$numero['Magazine'].'\' AND Numéro LIKE \''.$numero['Numéro'].'\')';
				$resultat_possede=$this->requete_select($requete_possede);
				if ($resultat_possede[0]['c']==0) {
					if ($id_utilisateur!=$numero['Id_Utilisateur'])
						echo '</ul><ul>'.$liste_utilisateurs[$numero['Id_Utilisateur']].' propose les num&eacute;ros suivants en vente :';
					echo '<li>'.$liste_magazines[$numero['Magazine']].' n&deg; '
						 .$numero['Numéro']
						 .' (Etat '.$numero['Etat'].')</li>';
					$id_utilisateur=$numero['Id_Utilisateur'];
				}
			}
		}
		else
			echo 'Pour l\'instant, vous poss&eacute;dez tous les num&eacute;ros propos&eacute;s &agrave; la vente par les autres utilisateurs.';
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
					$requete.='Numéro LIKE \''.$numero.'\'';
					$debut=false;
				}
				$requete.=')';
			break;
			default:

				$intitule=$etat=='non_marque'?'':self::$etats[$etat][0];
				$requete_insert='INSERT INTO numeros(Pays,Magazine,Numéro,';
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
				$this->requete($requete_insert);
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
					$requete_update.='Numéro LIKE \''.$numero.'\' ';
					$debut=false;
				}
				$requete_update.='))';
				$this->requete($requete_update);
				echo $requete_update;

		}
		if (isset($requete))
			mysql_query($requete);
	}

	function toList($id_user) {
		$requete='SELECT DISTINCT Pays,	Magazine,Numéro,Etat,ID_Acquisition,AV,ID_Utilisateur FROM numeros WHERE (ID_Utilisateur='.$id_user.') ORDER BY Pays, Magazine, Numéro';
		$d=new Database();
		$resultat=$d->requete_select($requete);
		$cpt=0;
		$l=new Liste();
		$numero=-1;
		foreach ($resultat as $infos) {
			if (array_key_exists($infos['Pays'],$l->collection)) {
				if (array_key_exists($infos['Magazine'],$l->collection[$infos['Pays']])) {
					array_push($l->collection[$infos['Pays']][$infos['Magazine']],array($infos['Numéro'],$infos['Etat'],$infos['AV'],$infos['ID_Acquisition']));
				}
				else {
					$l->collection[$infos['Pays']][$infos['Magazine']]=array(0=>array($infos['Numéro'],$infos['Etat'],$infos['AV'],$infos['ID_Acquisition']));
				}
			}
			else {
				$l->collection[$infos['Pays']]=array($infos['Magazine']=>0);
				$l->collection[$infos['Pays']][$infos['Magazine']]=array(0=>array($infos['Numéro'],$infos['Etat'],$infos['AV'],$infos['ID_Acquisition']));

			}
		}
		//echo '<pre>';print_r($l);echo '</pre>';
		return $l;
	}

	function ajouter_numero($requete) {
		mysql_query($requete);
	}

	function ajouter_auteur($id,$nom) {
		$id_user=$this->user_to_id($_SESSION['user']);
		$requete_auteur_existe='SELECT NomAuteurAbrege FROM auteurs_pseudos WHERE NomAuteurAbrege LIKE \''.$id.'\' AND DateStat LIKE \'0000-00-00\' AND ID_User='.$id_user;
		$resultat_auteur_existe=$this->requete_select($requete_auteur_existe);
		if (count($resultat_auteur_existe)>0) {
			echo AUTEUR_DEJA_DANS_LISTE.'<br />';
		}
		else {
			$requete_ajout_auteur='INSERT INTO auteurs_pseudos(NomAuteur, NomAuteurAbrege, ID_User,NbPossedes, DateStat) '
								 .'VALUES (\''.$nom.'\', \''.$id.'\','.$id_user.',0,\'0000-00-00\')';
			$this->requete($requete_ajout_auteur);
		}
	}

	function liste_auteurs_surveilles($auteurs_surveilles,$affiche_notation) {

		if (count($auteurs_surveilles)==0) {
			echo AUCUN_AUTEUR_SURVEILLE.'<br />';
		}
		else {
			if ($affiche_notation)
				echo '<form method="post" action="?action=agrandir&onglet=auteurs_favoris&onglet_auteur=preferences">';
			echo '<table style="width:100%">';
			foreach($auteurs_surveilles as $num=>$auteur) {
				echo '<tr><td>- '.$auteur['NomAuteur'].'</td>';
				if ($affiche_notation) {
					echo '<td id="pouces'.$num.'" onmouseout="vider_pouces()">';
					for ($i=1;$i<=10;$i++) {
						$orientation=$i<=5?'bas':'haut';
						$pouce_rempli=$auteur['Notation']>=$i;
						echo '<img id="pouce'.$num.'_'.$i.'" height="15" src="images/pouce_'.$orientation.($pouce_rempli?'':'_blanc').'.png" onclick="valider_note('.$num.')" onmouseover="hover('.$num.','.$i.')"/>';
					}
					echo '<input type="hidden" value="'.$auteur['NomAuteurAbrege'].'" name="auteur'.$num.'" />';
					echo '<input type="hidden" id="notation'.$num.'" name="notation'.$num.'" />';
					echo '</td>';
					echo '<td><input type="checkbox" '.($auteur['Notation']==-1?'checked="checked"':'')
						.' id="aucune_note'.$num.'" onclick="set_aucunenote('.$num.')" name="aucune_note'.$num.'" />&nbsp;'
						.AUCUNE_NOTE;
				}
				echo '<td><a href="javascript:void(0)" onclick="supprimer_auteur(\''.$auteur['NomAuteurAbrege'].'\')">'.SUPPRIMER.'</a></td>';
				echo '</tr>';
			}
			echo '</table>';
			if ($affiche_notation) {
				$d=new Database();
				$id_user=$d->user_to_id($_SESSION['user']);
				$requete_get_recommandations_liste_mags='SELECT RecommandationsListeMags FROM users WHERE ID='.$id_user;
				$resultat_get_recommandations_liste_mags=$d->requete_select($requete_get_recommandations_liste_mags);
				$recommandations_liste_mags=$resultat_get_recommandations_liste_mags[0]['RecommandationsListeMags'];
				echo EXPLICATION_NOTATION_AUTEURS1.'<br />'.EXPLICATION_NOTATION_AUTEURS2;
				echo '<br /><br />';
				echo '<input type="checkbox" '.($recommandations_liste_mags?'checked="checked" ':'')
					.'name="proposer_magazines_possedes">&nbsp;'.PROPOSER_MAGAZINES_POSSEDES.'<br />'
					.'<span style="font-size:12px">'.PROPOSER_MAGAZINES_POSSEDES_EXPLICATION.'</span>';
				echo '<br /><br /><input type="submit" class="valider" value="'.VALIDER_NOTATIONS.'" /></form>';
			}
		}
	}

	function liste_suggestions_magazines() {
		$d=new Database();
		$id_user=$d->user_to_id($_SESSION['user']);
		$requete_numeros_recommandes='SELECT Pays, Magazine, Numéro, Texte FROM numeros_recommandes '
									.'WHERE ID_Utilisateur='.$id_user.' ORDER BY Notation DESC';
		$resultat_numeros_recommandes=$d->requete_select($requete_numeros_recommandes);
		if (count($resultat_numeros_recommandes)!=0) {
			echo INTRO_NUMEROS_RECOMMANDES.'<br />';
			echo '<ul>';
			$pays_parcourus=array();
			$auteurs=array();

			foreach($resultat_numeros_recommandes as $numero) {
				$pays=$numero['Pays'];
				if (!array_key_exists($pays,$pays_parcourus))
					$pays_parcourus[$pays]=Inducks::get_noms_complets_magazines($pays);
				echo '<li>'.$pays_parcourus[$pays][$numero['Magazine']].' '.$numero['Numéro'].'<br />';
				$histoires=explode(',',$numero['Texte']);
				$debut=true;
				foreach ($histoires as $i=>$histoire) {
					list($auteur,$nb_histoires)=explode('=',$histoire);
					if (!array_key_exists($auteur,$auteurs))
						$auteurs[$auteur]=Inducks::get_auteur($auteur);
					if (!$debut) {
						if ($i==count($histoires)-1)
							echo ' '.ET.' ';
						else
							echo ', ';
					}
					if ($nb_histoires==1)
						echo (1).' '.HISTOIRE;
					else
						echo $nb_histoires.' '.HISTOIRES;
					echo ' '.DE.' '.$auteurs[$auteur];
					$debut=false;
				}
			}
			echo '</ul>';
		}
		else echo CALCULS_PAS_ENCORE_FAITS.'<br />';
	}

	function liste_auteurs_notes($auteurs_surveilles) {
		foreach($auteurs_surveilles as $auteur) {
			if ($auteur['Notation']>=1)
				echo '1_';
			else
				echo '0_';
		}
	}
}


if (isset($_POST['database'])) {
	@session_start();
	$d=new Database();
	if (!$d) {
		echo PROBLEME_BD;
		exit(-1);
	}
	if (isset($_POST['pass'])) {
		if (isset($_POST['connexion'])) {
			if (!$d->user_connects($_POST['user'],$_POST['pass']))
				echo 'Identifiants invalides!';
			else {
				$_SESSION['user']=$_POST['user'];
			}
		}
		else if (isset($_POST['inscription'])) {// Inscription
			if ($d->nouveau_user($_POST['user'],$_POST['pass'])) {
				$_SESSION['user']=$_POST['user'];
				echo $_SESSION['user'];
				echo 'OK';
			}
		}
		else if (isset($_POST['from_file'])) {
			$fichier=$_POST['from_file'];
			$l=new Liste($fichier);
			$l->lire();
			$cpt=$l->add_to_database($d,$d->user_to_id($_SESSION['user']));
			echo 'OK.'.$cpt.' '.NUMEROS_IMPORTES;
		}
	}
	else if (isset($_POST['from_file'])) { // Import avec un utilisateur existant
		$id_user=$d->user_to_id($_SESSION['user']);

		$fichier=$_POST['from_file'];
		$l=new Liste($fichier);
		$l->lire();
		$l->synchro_to_database($d,$id_user,$l);
	}

	else if (isset($_POST['update'])) {
		//print_r($_SESSION);
		$id_user=$d->user_to_id($_SESSION['user']);
		$l=$d->toList($id_user);
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
			$requete_id_acquisition='SELECT Count(ID_Acquisition) AS cpt, ID_Acquisition FROM achats WHERE ID_User='.$d->user_to_id($_SESSION['user']).' AND Date LIKE \''.$date_acquisition.'\' GROUP BY ID_Acquisition';
			$resultat_acqusitions=$d->requete_select($requete_id_acquisition);
			//echo $requete_id_acquisition;
			if ($resultat_acqusitions[0]['cpt'] ==0)
				$id_acquisition=-1;
			else
				$id_acquisition=$resultat_acqusitions[0]['ID_Acquisition'];
		}
		//$l->update_numeros($pays,$magazine,$etat,$liste,$id_acquisition);
		$d->update_numeros($pays,$magazine,$etat,$av,$liste,$id_acquisition);

	}
	else if (isset($_POST['affichage'])) {
		//print_r($_SESSION);
		$id_user=$d->user_to_id($_SESSION['user']);
		$l=$d->toList($id_user);
		$pays=$_POST['pays'];
		$magazine=$_POST['magazine'];
		if (false!=($numeros=Inducks::get_numeros($pays,$magazine))) {
			Affichage::afficher_numeros($l,$pays,$magazine,$numeros);

		}
		else
			echo AUCUN_NUMERO_IMPORTE_1.$magazine.' ('.PAYS_PUBLICATION.' : '.$pays.')';
	}
	else if (isset($_POST['acquisition'])) {
		//print_r($_SESSION);
		$id_user=$d->user_to_id($_SESSION['user']);

		/*Vérifier d'abord que les numéros à ajouter ne correspondent pas déjà à une date d'acquisition*/
		$compte_acquisition_date=$d->requete_select('SELECT Count(ID_Acquisition) as c FROM achats WHERE ID_User='.$id_user.' AND Date LIKE \''.$_POST['date_annee'].'-'.$_POST['date_mois'].'-'.$_POST['date_jour'].'\'');
		if ($compte_acquisition_date[0]['c']!=0) {
			echo 'Date';exit(0);
		}

		$d->requete('INSERT INTO achats(ID_User,Date,Description)'
				   .' VALUES ('.$id_user.',\''.$_POST['date_annee'].'-'.$_POST['date_mois'].'-'.$_POST['date_jour'].'\',\''.$_POST['description'].'\')');
		echo 'INSERT INTO achats(ID_User,Date,Description)'
				   .' VALUES ('.$id_user.',\''.$_POST['date_annee'].'-'.$_POST['date_mois'].'-'.$_POST['date_jour'].'\',\''.$_POST['description'].'\')';
		$requete_acquisition='SELECT Date, Description FROM achats WHERE ID_User='.$id_user.' ORDER BY Date DESC';
		$liste_acquisitions=$d->requete_select($requete_acquisition);
		$a=new Affichage();
		$a->afficher_acquisitions($_POST['afficher_non_defini']);

	}
	else if (isset($_POST['modif_acquisition'])) {
		$id_user=$d->user_to_id($_SESSION['user']);
		$d->requete('UPDATE achats SET Date=\''.$_POST['date'].'\',Description=\''.$_POST['description'].'\' WHERE ID_User='.$id_user.' AND ID_Acquisition=\''.$_POST['id_acquisition'].'\'');
	}
	else if(isset($_POST['supprimer_acquisition'])) {
		//print_r($_SESSION);
		$id_user=$d->user_to_id($_SESSION['user']);
		$d->requete('DELETE FROM achats WHERE ID_User='.$id_user.' AND ID_Acquisition=\''.$_POST['id_acquisition'].'\'');
	}
	else if (isset($_POST['liste_achats'])) {
		//print_r($_SESSION);acquisitions
		$id_user=$d->user_to_id($_SESSION['user']);
		$liste_achats=$d->requete_select('SELECT Date,Description FROM achats WHERE ID_User='.$id_user.' ORDER BY Date');
		foreach ($liste_achats as $achat) {
			echo $achat['Description'].'~'.$achat['Date'].'_';
		}
	}
	else if (isset($_POST['liste_etats'])) {
		$d->liste_etats();
	}
	else if (isset($_POST['liste_notations'])) {
		$id_user=$d->user_to_id($_SESSION['user']);
		$resultat_notations=$d->requete_select('SELECT Notation FROM auteurs_pseudos WHERE ID_user='.$id_user.' AND DateStat LIKE \'0000-00-00\'');
		echo $d->liste_auteurs_notes($resultat_notations);
	}
	else if (isset($_POST['supprimer_auteur'])) {
		$id_user=$d->user_to_id($_SESSION['user']);
		$d->requete('DELETE FROM auteurs_pseudos '
				   .'WHERE ID_user='.$id_user.' AND NomAuteurAbrege LIKE \''.$_POST['nom_auteur'].'\'');
	}
	else { // Vérification de l'utilisateur
		if ($d->user_exists($_POST['user']))
			echo UTILISATEUR_EXISTANT;
		else
			echo 'OK, '.UTILISATEUR_VALIDE;
	}
}
?>