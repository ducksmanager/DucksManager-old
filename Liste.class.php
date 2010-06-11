<?php
$rep = "Listes/";
global $types_listes;
$types_listes=array();
$dir = opendir($rep);
$prefixe='Liste.';
$suffixe='.class.php';
/*while ($f = readdir($dir)) {
	if(is_file($rep.$f)) {
		if (startsWith($f,$prefixe) && endsWith($f,$suffixe)) {
			array_push($types_listes,substr($f,strlen($prefixe),strlen($f)-strlen($suffixe)-strlen($prefixe)));
			require_once($rep.$f);
		}
	}
}*/
require_once($rep.'Liste.Classique.class.php');

function types_listes() {
	global $types_listes;
	$str='';
	foreach($types_listes as $type) {
		$str.=$type.';';
	}
	echo substr($str,0,strlen($str)-1);
}

require_once('Database.class.php');
require_once('Affichage.class.php');
class Liste {
	var $contenu;
	var $nom_fichier;
	var $collection=array();
	var $very_max_centaines=1;
	var $database;

	function Liste($fichier=false) {
		if (!$fichier)
		return;
		$this->nom_fichier=$fichier;
		$this->lire();
	}

	function ListeExemple() {
		$numeros=array(array(2,'Excellent',false,-1),array(173,'Bon',false,-1),array(4,'Excellent',false,-1),array(92,'Excellent',false,-1));
		$this->collection=array('fr'=>(array('MP'=>$numeros)));
	}

	function fusionnerAvec($liste_autre) {
		/*echo 'Fusion de : ';
		echo '<pre>';print_r($this->collection);echo '</pre>';
		echo 'avec : ';
		echo '<pre>';print_r($liste_autre->collection);echo '</pre>';*/
		foreach($liste_autre->collection as $pays=>$numeros_pays) {
			foreach($numeros_pays as $magazine=>$numeros) {
				foreach($numeros as $numero_etat_av_date) {
					$numero=$numero_etat_av[0];
					$etat=$numero_etat_av[1];
					$av=$numero_etat_av[2];
					$date=$numero_etat_av[3];
					if (!array_key_exists($pays,$this->collection)) {
						$arr_temp=array($magazine=>array(0=>array($numero,$etat,$av,$date)));
						$this->collection[$pays]=$arr_temp;
					}
					else {
						if (!array_key_exists($magazine,$this->collection[$pays])) {
							$this->collection[$pays][$magazine]=array(0=>array($numero,$etat,$av,$date));
						}
						else
							if (!array_push($this->collection[$pays][$magazine],$numero_etat_av,$date))
								echo '<b>'.$magazine.$numero.'</b>';
					}
				}
			}
		}
	}


	function sous_liste($pays,$magazine=false) {
		$nouvelle_liste=new Liste();
		if (!$magazine)
		$nouvelle_liste->collection=array($pays=>array($this->collection[$pays]));
		else
		$nouvelle_liste->collection=array($pays=>array($magazine=>$this->collection[$pays][$magazine]));
		return $nouvelle_liste;
	}


	function liste_magazines() {
		$tab=array();
		foreach($this->collection as $pays=>$numeros_pays) {
			$liste_magazines=Inducks::get_noms_complets_magazines($pays);
			foreach($numeros_pays as $magazine=>$numeros) {
				$tab[$pays.'/'.$magazine]=array($pays.'/'.$magazine,$liste_magazines[$magazine]);
			}
		}
		return $tab;
	}

	function statistiques($onglet) {

		$counts=array();
		$total=0;
		foreach($this->collection as $pays=>$numeros_pays) {
			$counts[$pays]=array();
			foreach($numeros_pays as $magazine=>$numeros) {
				$counts[$pays][$magazine]=count($numeros);
			}
		}
		$onglets=array(MAGAZINES=>array('magazines',MAGAZINES_COURT),
                               CLASSEMENT=>array('classement',CLASSEMENT_COURT),
                               ETATS_NUMEROS=>array('etats',ETATS_NUMEROS_COURT),
                               AUTEURS=>array('auteurs',AUTEURS_COURT));
		Affichage::onglets($onglet,$onglets,'onglet','?action=stats',-1);

		if (count($counts)==0) {
			echo AUCUN_NUMERO_POSSEDE_1.'<a href="?action=gerer&onglet=ajout_suppr">'.ICI.'</a> '
                            .AUCUN_NUMERO_POSSEDE_2;
			return;
		}
		switch($onglet) {
			case 'magazines':
				echo '<iframe src="magazines_camembert.php" id="iframe_graphique" style="border:0px"></iframe>';
			break;
			case 'classement':
				echo '<iframe id="iframe_graphique" src="classement_histogramme2.php" style="border:0px"></iframe>';

				break;
			case 'etats':
				echo '<iframe id="iframe_graphique" src="etats_camembert.php" style="border:0px"></iframe>';

			break;
			case 'auteurs':
				$d=new Database();
				$id_user=$d->user_to_id($_SESSION['user']);
				if (isset($_POST['auteur_nom'])) {
					$d->ajouter_auteur($_POST['auteur_id'],$_POST['auteur_nom']);
				}
				$requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat LIKE \'0000-00-00\'';
				$resultat_auteurs_surveilles=$d->requete_select($requete_auteurs_surveilles);
				if (count($resultat_auteurs_surveilles)!=0) {
					$requete_calcul_effectue='SELECT Count(NomAuteurAbrege) AS cpt FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat NOT LIKE \'0000-00-00\'';
					$resultat_calcul_effectue=$d->requete_select($requete_calcul_effectue);
					if ($resultat_calcul_effectue[0]['cpt']==0) {
						echo CALCULS_PAS_ENCORE_FAITS.'<br />';
					}
					else {
						echo '<iframe id="iframe_graphique" src="auteurs_histogramme.php" style="border:0px"></iframe>';
					}
				}
				?><br /><br />
				<?php
				echo STATISTIQUES_AUTEURS_INTRO_1.'<br />';
				echo STATISTIQUES_AUTEURS_INTRO_2.'<br />';
				?>
				<!-- <u>Note : </u>Seuls les histoires publi&eacute;es en France seront compt&eacute;es dans les statistiques.<br /> -->
		        <form method="post" action="?action=stats&onglet=auteurs">
			        <input type="text" name="auteur_cherche" id="auteur_cherche" value="" />
			        <div class="update" id="liste_auteurs"></div>
			        <input type="hidden" id="auteur_nom" name="auteur_nom" />
			        <input type="hidden" id="auteur_id" name="auteur_id" />
			        <input type="submit" value="Ajouter" />
				</form>
				<div id="auteurs_ajoutes">
				<br /><br />
				<?php
				echo LISTE_AUTEURS_INTRO.'<br />';
				$d->liste_auteurs_surveilles($resultat_auteurs_surveilles,false);
				?>
				</div>
				<br />
				<?php
				echo STATISTIQUES_QUOTIDIENNES;
				if (count($resultat_auteurs_surveilles)>0) {
					echo LANCER_CALCUL_MANUELLEMENT.'<br />';
					?><button onclick="stats_auteur(<?php echo $id_user;?>)"><?php echo LANCER_CALCUL;?></button>
					<div id="resultat_stats"></div>
				<?php
				}
				echo '<br /><span style="color:red">'.NOUVEAU.'</span>&nbsp;'
					.ANNONCE_AGRANDIR_COLLECTION1.ANNONCE_AGRANDIR_COLLECTION2
					.' <a href="?action=agrandir&onglet=auteurs_favoris">'
					.ICI.'</a>';
				?>

				<div id="update_stats"></div>
				<span style="display:none" id="infos_maj"></span>
				<br />
				<?php
				$pays='fr';

			break;

			echo '<td>&nbsp;</td><td><table width="100%" border="1" cellspacing="2px" style="border-collapse:collapse;"><tr><td>'.NOMBRE_HISTOIRES.'</td></tr>';
			//foreach($counts as $pays=>$magazines) {
			echo '<tr><td colspan="2" width="300px"><u>'.$pays.'</u></td></tr>';
			$auteurs=array('Don+Rosa');//,'Don+Rosa','Romano+Scarpa','Al+Taliaferro','Bruce+Hamilton','Massimo+De+Vita','Tony+Strobl');
			foreach($auteurs as $auteur) {
				$adresse_auteur='http://coa.inducks.org/comp2.php?code=&keyw=&keywt=i&exactpg=&pg1=&pg2=&bro2=&bro3=&kind=0&rowsperpage=0&columnsperpage=0&hero=&xapp=&univ=&xa2=&creat='.$auteur.'&creat2=&plot=&plot2=&writ=&writ2=&art=&art2=&ink=&ink2=&pub1=&pub2=&part=&ser=&xref=&mref=&xrefd=&repabb=&repabbc=al&imgmode=0&vdesc2=on&vdesc=en&vfr=on&sort1=auto';
				$regex_code_histoire='#<A HREF="story.php\?c=[^"]+"><font courier>([^<]+)</font></A>#';
				$regex_histoire_code_personnages='#<tr[^>]+>.<td[^>]+><[^>]+><[^>]+><br>.<A[^>]+><[^>]+>([^<]+)</font></A> </td>.<td>[ ]*(?:<[^>]+>)?(<A [^<]+</A>[, ]*)*[^<]*(?:</small>)?(?:(?:(?:<i>)?[^<]*(?:<span[^<]*</span>[ ]*)*[^<]*</i>)?<br>)?.(?:.<i>(?:[^<]*)</i>)?(?:<br>.)?</td>.<td>(?:[^<]*<br>.)?<small>(?:[^<]*<br>)*[^<]*</small></td>.<td>(?:[^<]*<A [^>]+>(?:(?:<span [^>]+>)?[^<]*(?:</span>[ ]*)?)*</A>[()?*, ]*)+(?:<font [^<]+</font>)?[^<]*(?:<br>.)?(?:<font[^<]*</font><br>.)?</td>.<td>(([^<]*(<A [^<]*</A>[, ]*)*(?:<br>.?)?[^<]*)*)</td><td>(?:(?:[^<]*(?:<(?:A|i)[^<]*</(?:A|i)>)+[.()0-9a-zA-Z, ]*)*<br>.?)*#is';
				$regex_numero='#<A HREF="issue.php\?c=[^"]*">([^<]*)</A>#';


				list($nb_codes,$nb,$buffer,$codes,$histoirse)=liste_histoires($adresse_auteur,$regex_code_histoire,$regex_histoire_code_personnages);
				echo '<br /><u>'.$auteur.'</u> : <br />';
				echo 'Page 1 : '.$nb.'/'.$nb_codes.' total<br />';
				$page=1;
				$trouve=true;
				while ($trouve) {
					$adresse_auteur2='http://coa.inducks.org/comp2.php?imgmode=0&owned=&noowned=&pageDirecte='.$page.'&c2Direct=en&c3Direct=fr&queryDirect=';
					$regex_requete='#input type=hidden name=queryDirect value="([^"]*)"#is';
					$trouve=(preg_match($regex_requete,$buffer,$req)!=0);
					$adresse_auteur2.=urlencode(preg_replace($regex_requete,'$1',$req[0]));
					//echo $adresse_auteur2;
					list($nb_codes,$nb,$buffer,$codes,$histoires)=liste_histoires($adresse_auteur2,$regex_code_histoire,$regex_histoire_code_personnages);
					echo 'Page '.($page+1).' : '.$nb.'/'.$nb_codes.' total<br />';
					if ($page==1) {
						echo '<table border="1">';
						foreach($codes[0] as $i=>$code) {
							echo '<tr><td';
							$date_et_publications=preg_replace($regex_histoire_code_personnages,'$3',$histoires[0][$i]);
							//echo $date_et_publications;//echo preg_replace($regex_histoire_code_personnages,'<span style="background-color:#444499;">$1, $2, $3, $4, $5, $6, $7, $8, $9, $10</span>',$histoires[0][$i]);
							$nb_publications=preg_match_all($regex_numero,$date_et_publications,$publications);
							$liste_publications_texte='';
							foreach($publications[0] as $publication) {
								$magazine_numero=explode(' ',preg_replace($regex_numero,'$1',$publication));
								$magazine=$magazine_numero[0];
								$numero=$magazine_numero[1].$magazine_numero[2];
								if ($this->est_possede($pays,$magazine,$numero)) {
									echo ' style="background-color:#444444;"';
									$liste_publications_texte.='X ';
								}
								$liste_publications_texte.= $magazine.' '.$numero.'<br />';
							}
							echo '>'.$liste_publications_texte.'</td>';
							echo '<td>';
							echo preg_replace($regex_code_histoire,'<span style="background-color:#444499;">$1</span>',$code);
							echo '</td></tr>';
						}
						echo '</table>';
					}
					$page++;
				}
				continue;

			}
			echo '</td></tr></table>';
			break;
		}
	}

	function add_to_database($d,$id_user) {
		$cpt=0;
		foreach($this->collection as $pays=>$numeros_pays) {
			foreach($numeros_pays as $magazine=>$numeros) {
				foreach($numeros as $numero) {
					$num_final='';
					//for($i=0;$i<6-strlen($numero);$i++)
					//	$num_final.='0';
					$num_final.=$numero;
					$requete='INSERT INTO numeros VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$num_final.'\',\'Indéfini\',-1,0,'.$id_user.')';
					$d->ajouter_numero($requete);
					$cpt++;
				}
			}
		}
		return $cpt;
	}

	function synchro_to_database($d,$id_user,$nouvelle_liste) {
		$l_ducksmanager=$d->toList($id_user);
		$l_ducksmanager->compareWith($nouvelle_liste,$id_user);

	}
	function update_numeros($pays,$magazine,$etat,$av,$liste,$id_acquisition) {

		$liste_origine=$this->collection[$pays][$magazine];
		foreach($liste as $numero) {
			switch($etat) {
				case 'manque':
					if (($pos=array_search($numero,$liste_origine))!=-1) {
						unset($liste_origine[$pos]);
					}
					break;
				default:
					if (!array_key_exists($pays,$this->collection)) {
						$arr_temp=array($magazine=>array($numero,$id_acquisition));
						$this->collection[$pays]=$arr_temp;
						$liste_origine=$this->collection[$pays][$magazine];
					}
					if (!array_key_exists($magazine,$this->collection[$pays])) {
						$this->collection[$pays][$magazine]=array($numero,$id_acquisition);
						$liste_origine=$this->collection[$pays][$magazine];
						continue;
					}
					if (!in_array($numero,$liste_origine)) {
						array_push($liste_origine,array($numero,$id_acquisition));
					}
					break;
			}
		}
		$this->collection[$pays][$magazine]=$liste_origine;
	}

	function lire() {
		$inF = fopen($this->nom_fichier,"r");
		if ($inF) {
			$debut=true;$this->collection=array();
			while (!feof($inF)) {
				$buffer = fgets($inF, 4096);
				$little_array=explode('^',$buffer);
				$country=$little_array[0];
				$regex='#^([^ ]*)[ ]+(.*)$#';
				preg_match($regex,$little_array[1],$magazine_numero);
				$magazine=$magazine_numero[1];
				$numero=$magazine_numero[2];
				if (!array_key_exists($country,$this->collection)) {
					$arr_temp=array($magazine=>array(0=>$numero));
					$this->collection[$country]=$arr_temp;
				}
				else {
					if (!array_key_exists($magazine,$this->collection[$country])) {
						$this->collection[$country][$magazine]=array($numero);
					}
					else
						if (!array_push($this->collection[$country][$magazine],$numero))
							echo '<b>'.$magazine.$numero.'</b>';
				}
			}
			fclose($inF);
		}
	}

	function compareWith($other_list,$id_user) {
		$numeros_a_ajouter=$numeros_a_supprimer=$numeros_communs=0;
		$texte_a_ajouter=$texte_a_supprimer='';
		if (!($id_user===false)) $d=new Database();
		$noms_magazines=array();
		foreach($this->collection as $pays=>$numeros_pays) {
			if (!array_key_exists($pays,$noms_magazines))
				$noms_magazines[$pays]=Inducks::get_noms_complets_magazines($pays);
			foreach($numeros_pays as $magazine=>$numeros) {
				$magazine_affiche=false;
				foreach($numeros as $numero) {
					if (array_key_exists($pays,$other_list->collection)
					&& array_key_exists($magazine,$other_list->collection[$pays])
					&& in_array($numero[0],$other_list->collection[$pays][$magazine]))
						$numeros_communs++;
					else {
						if (!($id_user===false)) {
							$requete_supprimer='DELETE FROM numeros WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND ID_Utilisateur='.$id_user.' AND Numéro LIKE \''.$numero[0].'\'';
							$d->requete($requete_supprimer);
						}
						if (!$magazine_affiche) $texte_a_supprimer.='<br /><u>'.$noms_magazines[$pays][$magazine].' :</u> ';
						$magazine_affiche=true;
						$texte_a_supprimer.=$numero[0].' ';
						$numeros_a_supprimer++;
					}
				}
			}
		}
		foreach($other_list->collection as $pays=>$numeros_pays) {
			if (!array_key_exists($pays,$noms_magazines))
				$noms_magazines[$pays]=Inducks::get_noms_complets_magazines($pays);
			foreach($numeros_pays as $magazine=>$numeros) {
				$magazine_affiche=false;
				foreach($numeros as $numero) {
					if (array_key_exists($pays,$this->collection)
					&& array_key_exists($magazine,$this->collection[$pays])) {
						$trouve=false;
						$numeros_possedes_magazine=count($this->collection[$pays][$magazine]);
						for ($i=0;$i<$numeros_possedes_magazine;$i++)
							if ($numero==$this->collection[$pays][$magazine][$i][0])
								$trouve=true;
						if (!$trouve) {
							if (!$magazine_affiche) $texte_a_ajouter.='<br /><u>'.$noms_magazines[$pays][$magazine].' :</u> ';
							$magazine_affiche=true;
							$texte_a_ajouter.=$numero.' ';
							if (!($id_user===false)) {
								$requete_ajouter='INSERT INTO numeros VALUES(\''.$pays.'\',\''.$magazine.'\','
												  .'\''.$numero.'\',\'Indéfini\',-1,0,'.$id_user.')';
								$d->requete($requete_ajouter);
							}
							$numeros_a_ajouter++;
						}
					}
				}
			}
		}
		if ($id_user===false) {
			echo '<ul>';
			echo '<li>'.$numeros_a_ajouter.' '.NUMEROS_A_AJOUTER;
			echo $texte_a_ajouter;
			echo '</li>';
			echo '<li>'.$numeros_a_supprimer.' '.NUMEROS_A_SUPPRIMER;
			echo $texte_a_supprimer;
			echo '</li>';
			echo '<li>'.$numeros_communs.' '.NUMEROS_COMMUNS.'</li>';
			echo '</ul>';
			return $numeros_a_ajouter!=0 && $numeros_a_supprimer!=0;
		}
		else echo OPERATIONS_EXCECUTEES;
	}

	function afficher($type) {
		$o=new $type();
		$o->afficher($this->collection);
	}

	function est_possede($pays,$magazine,$numero) {
		if (array_key_exists($pays,$this->collection)) {
			if (array_key_exists($magazine,$this->collection[$pays])) {
				foreach($this->collection[$pays][$magazine] as $id=>$numero_liste) {
					if ($numero_liste[0]==$numero) {
						return true;
					}
				}
			}
		}
		return false;
	}
	function est_possede_etat_av_idacq($pays,$magazine,$numero) {
		if (array_key_exists($pays,$this->collection)) {
			if (array_key_exists($magazine,$this->collection[$pays])) {
				foreach($this->collection[$pays][$magazine] as $id=>$numero_liste) {
					if ($numero_liste[0]==$numero) {
						return array($numero_liste[1],$numero_liste[2],$numero_liste[3]);
					}
				}
			}
		}
		return array('','','');
	}
}
if (isset($_POST['liste'])) {
	if (isset($_POST['import'])) {

		$l=new Liste($_POST['liste']);
		if ($l->collection==array())
			echo AUCUN_NUMERO_INDUCKS;
		else {
			if (isset($_SESSION['user'])) {
				echo IMPORT_UTILISATEUR_EXISTANT1.'<br />';
				echo '<ul>';
				echo '<li>'.IMPORT_UTILISATEUR_EXISTANT2_1.'</li>';
				echo '<li>'.IMPORT_UTILISATEUR_EXISTANT2_2.'</li>';
				echo '<li>'.IMPORT_UTILISATEUR_EXISTANT2_3.'</li>';
				echo '</ul><br />';
				$d=new Database();
				$id_user=$d->user_to_id($_SESSION['user']);
				$l_ducksmanager=$d->toList($id_user);
				$operations_a_effectuer=$l_ducksmanager->compareWith($l,false);
				if (!$operations_a_effectuer) {
					echo LISTES_IDENTIQUES;
					return;
				}
			}
			else {
				echo RESULTAT_NUMEROS_INDUCKS;
			}

		  	echo '<br />'.QUESTION_EXECUTER_OPS_INDUCKS
		  		.'<button onclick="importer(true,'.(isset($_SESSION['user'])?'true':'false').')">'
		  		.OUI.'</button> '
		  		.'<button onclick="importer(false,'.(isset($_SESSION['user'])?'true':'false').')">'
		  		.NON.'</button><br />';
		}
	}
}
elseif (isset($_POST['types_liste'])) {
	types_listes();
}
elseif(isset($_POST['sous_liste'])) {
	@session_start();
	$pays=$_POST['pays'];
	$magazine=$_POST['magazine'];
	$type_liste=$_POST['type_liste'];
	$d=new Database();
	if (!$d) {
		echo PROBLEME_BD;
		exit(-1);
	}
	$id_user=$d->user_to_id($_SESSION['user']);
	$l=$d->toList($id_user);
	$sous_liste=$l->sous_liste($pays,$magazine);
	if (isset($_GET['fusions'])) {
		$fusions=explode(';',$_GET['fusions']);
		foreach($fusions as $fusion) {
			$pays_et_magazine_fusion=explode(',',$fusion);
			$sous_liste->fusionnerAvec($l->sous_liste($pays_et_magazine_fusion[0],$pays_et_magazine_fusion[1]));
		}
	}
	$sous_liste->afficher($type_liste);
}
elseif(isset($_GET['liste_exemple'])) {
	$l=new Liste();
	$l->ListeExemple();
	$objet =new $_GET['type_liste']();
	$objet->afficher($l->collection).'</font>';
}

/*
 echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
 <html><head><title>DucksManager 3</title></head><body>';
 $l=new Liste("collection.txt");
 $d=new Database();
 if (!$d) {
 echo 'Probl&egrave;me avec la base de donn&eacute;es !';
 exit(-1);
 }
 $d->liste_numeros('SELECT * FROM users');
 $d->liste_acquisitions('SELECT * FROM achats');
 //$l->add_to_database($d);
 if (isset($_GET['type']))
 $l->afficher($_GET['type']);
 else
 $l->afficher('DMtable');
 echo '</body></html>';*/

function startswith($hay, $needle) { // From http://sunfox.org/blog/2007/03/21/startswith-et-endswith-en-php/
	return $needle === $hay or strpos($hay, $needle) === 0;
}

function endswith($hay, $needle) {
	return $needle === $hay or strpos(strrev($hay), strrev($needle)) === 0;
}

/*function liste_histoires($adresse_auteur,$regex_code_histoire,$regex_histoire_code_personnages) {
 $nb_codes=$nb=0;$buffer="";$codes=array();$histoires=array();
 $handle = @fopen($adresse_auteur, "r");
 if ($handle) {
 $buffer="";
 while (!feof($handle)) {
 $buffer.= fgets($handle, 4096);
 }
 fclose($handle);
 $nb_codes=preg_match_all($regex_code_histoire,$buffer,$codes);
 $nb=preg_match_all($regex_histoire_code_personnages,$buffer,$histoires,PREG_PATTERN_ORDER);
 }
 else {
 echo 'Erreur de connexion &agrave; Inducks!';
 }
 return array($nb_codes,$nb,$buffer,$codes,$histoires);
 }*/
?>