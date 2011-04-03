<?php
require_once('DucksManager_Core.class.php');
require_once('Affichage.class.php');
require_once('Inducks.class.php');
class Liste {
	var $contenu;
	var $nom_fichier;
	var $collection=array();
	var $very_max_centaines=1;
	var $database;
        static $types_listes=array();

        static function set_types_listes() {
            $rep = "Listes/";
            $dir = opendir($rep);
            $prefixe='Liste.';
            $suffixe='.class.php';
            while ($f = readdir($dir)) {
                if (strpos($f,'Debug')!==false)
                    continue;
                if(is_file($rep.$f)) {
                    if (startsWith($f,$prefixe) && endsWith($f,$suffixe)) {
                        $nom=substr($f,strlen($prefixe),strlen($f)-strlen($suffixe)-strlen($prefixe));
                        
                        include_once('Listes/Liste.'.$nom.'.class.php');
                        $a=new ReflectionProperty($nom, 'titre');
                        Liste::$types_listes[$nom]=$a->getValue();
                    }
                }
            }
            return Liste::$types_listes;
        }
	function Liste($texte=false) {
		if (!$texte)
                    return;
		$this->texte=$texte;
		$this->lire();
	}

        function ajouter($pays,$magazine,$numero) {
            if (!array_key_exists($pays, $this->collection))
                $this->collection[$pays]=array();
            if (!array_key_exists($magazine,$this->collection[$pays]))
                $this->collection[$pays][$magazine]=array();
            if (in_array($numero, $this->collection[$pays][$magazine]))
                return;
            $this->collection[$pays][$magazine][]=$numero;
        }

	function ListeExemple() {
		$numeros_mp=array(array(2,'Excellent',false,-1),array(273,'Bon',false,-1),array(4,'Excellent',false,-1),array(92,'Excellent',false,-1));
		$numeros_mad=array(array(6,'Indefini',false,-1),array(16,'Bon',false,-1));
		$this->collection=array('fr'=>(array('MP'=>$numeros_mp)), 'us'=>array('MAD'=>$numeros_mad));
	}

	function fusionnerAvec($liste_autre) {
		/*echo 'Fusion de : ';
		echo '<pre>';print_r($this->collection);echo '</pre>';
		echo 'avec : ';
		echo '<pre>';print_r($liste_autre->collection);echo '</pre>';*/
		foreach($liste_autre->collection as $pays=>$numeros_pays) {
			foreach($numeros_pays as $magazine=>$numeros) {
				foreach($numeros as $numero_etat_av_date) {
                                    $numero=$numero_etat_av_date[0];
					$etat=$numero_etat_av_date[1];
					$av=$numero_etat_av_date[2];
					$date=$numero_etat_av_date[3];
					if (!array_key_exists($pays,$this->collection)) {
						$arr_temp=array($magazine=>array(0=>array($numero,$etat,$av,$date)));
						$this->collection[$pays]=$arr_temp;
					}
					else {
						if (!array_key_exists($magazine,$this->collection[$pays])) {
							$this->collection[$pays][$magazine]=array(0=>array($numero,$etat,$av,$date));
						}
						else
							if (!array_push($this->collection[$pays][$magazine],array($numero,$etat,$av,$date)))
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

        function liste_pays() {
            $tab=array();
            foreach(array_keys($this->collection) as $pays) {
                $requete_nom_complet_pays='SELECT NomComplet FROM pays WHERE NomAbrege LIKE \''.$pays.'\' AND L10n LIKE \''.$_SESSION['lang'].'\'';
                $resultat_nom_complet_pays=DM_Core::$d->requete_select($requete_nom_complet_pays);
                $tab[$resultat_nom_complet_pays[0]['NomComplet']]=array($pays,$resultat_nom_complet_pays[0]['NomComplet']);
            }
            return $tab;
	}
	function liste_magazines() {
            $tab=array();
            foreach($this->collection as $pays=>$numeros_pays) {
                    foreach($numeros_pays as $magazine=>$numeros) {
                        list($nom_complet_pays,$nom_complet_magazine)=DM_Core::$d->get_nom_complet_magazine($pays, $magazine);
                        $tab[$pays.'/'.$magazine]=array($pays.'/'.$magazine,$nom_complet_magazine);
                    }
            }
            return $tab;
	}

	function statistiques($onglet) {
                $id_user=DM_Core::$d->user_to_id($_SESSION['user']);

		$counts=array();
		$total=0;
		foreach($this->collection as $pays=>$numeros_pays) {
			$counts[$pays]=array();
			foreach($numeros_pays as $magazine=>$numeros) {
				$counts[$pays][$magazine]=count($numeros);
			}
		}
		$onglets=array(MAGAZINES=>array('magazines',MAGAZINES_COURT),
                               POSSESSIONS=>array('possessions',POSSESSIONS_COURT),
                               ETATS_NUMEROS=>array('etats',ETATS_NUMEROS_COURT),
                               ACHATS=>array('achats',ACHATS_COURT),
                               AUTEURS=>array('auteurs',AUTEURS_COURT));
		Affichage::onglets($onglet,$onglets,'onglet','?action=stats');

		if (count($counts)==0) {
			echo AUCUN_NUMERO_POSSEDE_1.'<a href="?action=gerer&onglet=ajout_suppr">'.ICI.'</a> '
                            .AUCUN_NUMERO_POSSEDE_2;
			return;
		}
		switch($onglet) {
			case 'magazines':
				?><iframe src="magazines_camembert.php" id="iframe_graphique" style="border:0px"></iframe><?php
			break;
			case 'possessions':
			include_once('Chargement.class.php');	
                            ?>
                                
                                <span id="chargement_classement_termine"><?=CHARGEMENT?>...</span><br />
                                <div id="barre_pct_classement" style="border: 1px solid white; width: 200px;">
                                    <div id="pct_classement" style="width: 0%; background-color: red;">&nbsp;</div>
                                </div>
                                Calcul <span id="message_classement">initialis&eacute;</span>
                                <br /><br />
                                <script type="text/javascript">
                                    initProgressBar('classement','classement_histogramme2.php');
                                </script>
                                <div id="resultat_classement" style="border:0px"></div>

                                
                                <?php
				break;
			case 'etats':
				?><iframe id="iframe_graphique" src="etats_camembert.php" style="border:0px"></iframe><?php
			break;

                        case 'achats':
                            $requete_achat_existe='SELECT Count(Date) AS cpt FROM achats WHERE ID_User='.$id_user;
                            $resultat_achat_existe=DM_Core::$d->requete_select($requete_achat_existe);
                            if ($resultat_achat_existe[0]['cpt']==0) {
                                echo AUCUNE_DATE_ACQUISITION;
                            }
                            else {
                                ?><iframe id="iframe_graphique" src="achats_histogramme.php" style="border:0px"></iframe><?php
                            }
                        break;
			case 'auteurs':
				if (isset($_POST['auteur_nom'])) {
					DM_Core::$d->ajouter_auteur($_POST['auteur_id'],$_POST['auteur_nom']);
				}
				$requete_auteurs_surveilles='SELECT NomAuteur, NomAuteurAbrege FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat LIKE \'0000-00-00\'';
				$resultat_auteurs_surveilles=DM_Core::$d->requete_select($requete_auteurs_surveilles);
				if (count($resultat_auteurs_surveilles)!=0) {
					$requete_calcul_effectue='SELECT Count(NomAuteurAbrege) AS cpt FROM auteurs_pseudos WHERE ID_User='.$id_user.' AND DateStat NOT LIKE \'0000-00-00\'';
					$resultat_calcul_effectue=DM_Core::$d->requete_select($requete_calcul_effectue);
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
			        <input type="text" name="auteur_cherche" id="auteur_cherche" value="" size="40"/>
			        <div class="update" id="liste_auteurs"></div>
			        <input type="hidden" id="auteur_nom" name="auteur_nom" />
			        <input type="hidden" id="auteur_id" name="auteur_id" />
                                <img alt="Loading" id="loading_auteurs" src="loading.gif" style="display:none"/>
			        <input type="submit" value="Ajouter" />
				</form>
				<div id="auteurs_ajoutes">
				<br /><br />
				<?php
				echo LISTE_AUTEURS_INTRO.'<br />';
				DM_Core::$d->liste_auteurs_surveilles($resultat_auteurs_surveilles,false);
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
		}
	}

	function add_to_database($d,$id_user) {
            $cpt=0;
            foreach($this->collection as $pays=>$numeros_pays) {
                if ($pays=='country')
                    continue;
                foreach($numeros_pays as $magazine=>$numeros) {
                    foreach($numeros as $numero) {
                        $num_final='';
                        //for($i=0;$i<6-strlen($numero);$i++)
                        //	$num_final.='0';
                        $num_final.=$numero;
                        $requete='INSERT INTO numeros VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$num_final.'\',\'Indefini\',-1,0,'.$id_user.')';
                        DM_Core::$d->requete($requete);
                        $cpt++;
                    }
                }
            }
            return $cpt;
	}

        function remove_from_database($d,$id_user) {
            $cpt=0;
            foreach($this->collection as $pays=>$numeros_pays) {
                if ($pays=='country')
                    continue;
                foreach($numeros_pays as $magazine=>$numeros) {
                    foreach($numeros as $numero) {
                        $num_final=$numero[0];
                        $requete='DELETE FROM numeros WHERE (ID_Utilisateur ='.$id_user.' AND PAYS LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Numero LIKE \''.$num_final.'\')';
                        DM_Core::$d->requete($requete);
                        $cpt++;
                    }
                }
            }
            return $cpt;
	}

	function synchro_to_database($d,$ajouter_numeros=true,$supprimer_numeros=false) {
            $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
            $l_ducksmanager=DM_Core::$d->toList($id_user);
            $l_ducksmanager->compareWith($this,$ajouter_numeros,$supprimer_numeros);
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
            $lignes=explode("\r\n",$this->texte);
            foreach($lignes as $ligne) {
                $infos_ligne=explode('^',$ligne);
                if (count($infos_ligne)>=3) {
                    $country=$infos_ligne[0];
                    if ($country=='cou')
                        xdebug_break();
                    $regex='#^([^ ]*)[ ]+(.*)$#';
                    preg_match($regex,$infos_ligne[1],$magazine_numero);
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
            }
	}

	function compareWith($other_list,$ajouter_numeros=false,$supprimer_numeros=false) {
           if ($ajouter_numeros || $supprimer_numeros) {
                $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
           }
            $numeros_a_ajouter=$numeros_a_supprimer=$numeros_communs=0;

            $noms_magazines=array();
            $liste_a_supprimer=new Liste();

            foreach($this->collection as $pays=>$numeros_pays) {
                if (!array_key_exists($pays,$noms_magazines))
                    $noms_magazines[$pays]=array();
                foreach($numeros_pays as $magazine=>$numeros) {
                    $noms_magazines[$pays][$magazine]=DM_Core::$d->get_nom_complet_magazine($pays, $magazine,true);
                    $magazine_affiche=false;
                    sort($numeros);
                    foreach($numeros as $numero) {
                        if (array_key_exists($pays, $other_list->collection)
                         && array_key_exists($magazine, $other_list->collection[$pays])
                         && in_array($numero[0], $other_list->collection[$pays][$magazine]))
                            $numeros_communs++;
                        else {
                            $liste_a_supprimer->ajouter($pays, $magazine, $numero[0]);
                            $magazine_affiche = true;
                            $numeros_a_supprimer++;
                         }
                    }
                }
            }
            if (supprimer_numeros)
                $liste_a_supprimer->remove_from_database (DM_Core::$d, $id_user);
            $liste_a_ajouter=new Liste();
            foreach($other_list->collection as $pays=>$numeros_pays) {
                if (!array_key_exists($pays,$noms_magazines))
                    $noms_magazines[$pays]=array();
                foreach($numeros_pays as $magazine=>$numeros) {
                    if ($pays=='country')
                        continue;
                    list($nom_complet_pays,$noms_magazines[$pays][$magazine])=DM_Core::$d->get_nom_complet_magazine($pays, $magazine,true);
                    $magazine_affiche=false;
                    foreach($numeros as $numero) {
                        $trouve=false;
                        if (array_key_exists($pays,$this->collection)
                        && array_key_exists($magazine,$this->collection[$pays])) {
                            $numeros_possedes_magazine=count($this->collection[$pays][$magazine]);
                            for ($i=0;$i<$numeros_possedes_magazine;$i++)
                                if ($numero==$this->collection[$pays][$magazine][$i][0])
                                    $trouve=true;
                        }
                        if (!$trouve) {
                            $liste_a_ajouter->ajouter($pays, $magazine, $numero);
                            $magazine_affiche=true;
                            $numeros_a_ajouter++;
                        }
                    }
                }
            }
            if ($ajouter_numeros)
                $liste_a_ajouter->add_to_database (DM_Core::$d, $id_user);
            if (!$ajouter_numeros && !$supprimer_numeros) {
                ?>
                <ul>
                    <li style="margin-top:10px"><?=$numeros_a_ajouter?> <?=NUMEROS_A_AJOUTER?> :
                        <?=$liste_a_ajouter->afficher('Classique')?>
                    </li>
                    <li style="margin-top:10px"><?=$numeros_a_supprimer?> <?=NUMEROS_A_SUPPRIMER?> :
                        <?=$liste_a_supprimer->afficher('Classique')?>
                    </li>
                    <li style="margin-top:10px"><?=$numeros_communs?> <?=NUMEROS_COMMUNS?>
                    </li>
                </ul>
                <?php
                return array($numeros_a_ajouter, $numeros_a_supprimer);
            }
            else {
                echo OPERATIONS_EXECUTEES.' <br />';
            }
	}

	function afficher($type,$parametres=null) {
		$type=strtolower($type);
            if (@require_once('Listes/Liste.'.$type.'.class.php')) {
                $o=new $type();
                if (!is_null($parametres)) {
                    foreach($parametres as $nom_parametre=>$parametre)
                        $o->parametres->$nom_parametre=$parametre;
                }
                $o->afficher($this->collection);
            }
            else
                echo ERREUR_TYPE_LISTE_INVALIDE;
	}

	function est_possede($pays,$magazine,$numero) {
		if (array_key_exists($pays,$this->collection)) {
			if (array_key_exists($magazine,$this->collection[$pays])) {
				foreach($this->collection[$pays][$magazine] as $id=>$numero_liste) {
					if (nettoyer_numero($numero_liste[0])==$numero) {
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
					if (nettoyer_numero($numero_liste[0])==$numero) {
						return array($numero_liste[1],$numero_liste[2],$numero_liste[3]);
					}
				}
			}
		}
		return array('','','');
	}
        
        function get_liste_auto($pays,$magazine) {
            $nb_non_numeriques=0;
            self::set_types_listes();
            foreach($this->collection[$pays][$magazine] as $numero_et_etat) {
                $numero = $numero_et_etat[0];
                if (!(collectable::est_listable($numero)))
                    $nb_non_numeriques++;
            }
            return 'dmspiral';
        }

        static function import($liste_texte) {
            $l=new Liste($liste_texte);
            if ($l->collection == array()) {
                echo AUCUN_NUMERO_INDUCKS;
                return array(false,0,0);
            }
            else {
                if (isset($_SESSION['user'])) {
                    $id_user = DM_Core::$d->user_to_id($_SESSION['user']);
                    $l_ducksmanager = DM_Core::$d->toList($id_user);
                    list($ajouts,$suppressions) = $l_ducksmanager->compareWith($l);
                    if ($ajouts==0 && $suppressions==0) {
                        echo LISTES_IDENTIQUES;
                        return array(true,0,0);
                    }
                }
                else {
                    echo RESULTAT_NUMEROS_INDUCKS;
                    $l->afficher('Classique');
                }
                return array(true,$ajouts, $suppressions);
            }
        }
        
        static function init_parametres_boite($pays,$magazine,$type_liste,$position_liste) {
            @session_start();
            $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
            if (file_exists('Listes/Liste.'.$type_liste.'.class.php'))
                include_once('Listes/Liste.'.$type_liste.'.class.php');

            $o_tmp=new $type_liste;
            if ($o_tmp->getListeParametresModifiables() == new stdClass()) {
                $requete_ajouter_boite='INSERT INTO parametres_listes(`ID_Utilisateur`,`Pays`,`Magazine`,`Type_Liste`,`Position_Liste`,`Parametre`,`Valeur`) VALUES '
                                      .'('.$id_user.',\''.$pays.'\',\''.$magazine.'\',\''.$type_liste.'\','.$position_liste.',NULL,NULL)';
                DM_Core::$d->requete($requete_ajouter_boite);
            }
            else {
                foreach($o_tmp->getListeParametresModifiables() as $nom_parametre=>$parametre) {
                    $requete_ajouter_boite='INSERT INTO parametres_listes(`ID_Utilisateur`,`Pays`,`Magazine`,`Type_Liste`,`Position_Liste`,`Parametre`,`Valeur`) VALUES '
                                          .'('.$id_user.',\''.$pays.'\',\''.$magazine.'\',\''.$type_liste.'\','.$position_liste.',\''.$nom_parametre.'\',\''.$parametre->valeur_defaut.'\')';
                    DM_Core::$d->requete($requete_ajouter_boite);
                }
            }
        }
}
if (isset($_POST['parametres']))
    $_POST['parametres'] = str_replace('\"', '"', $_POST['parametres']);
if (isset($_POST['types_listes'])) {
    header("X-JSON: " . json_encode(Liste::set_types_listes()));
}
elseif(isset($_POST['sous_liste'])) {
    @session_start();
    $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
    $l=DM_Core::$d->toList($id_user);
    if (isset($_POST['pays'])) {
        $pays=$_POST['pays'];
        $magazine=$_POST['magazine'];
        $type_liste=$_POST['type_liste'];
        $sous_liste=$l->sous_liste($pays,$magazine);
    }
    else
        $sous_liste=new Liste();
    if (isset($_POST['parametres']))
        $parametres=json_decode($_POST['parametres']);
    else
        $parametres=new stdClass();
    if (isset($_POST['type_liste'])) {
        if (isset($_POST['fusions'])) {
            $fusions=explode('-',$_POST['fusions']);
            foreach($fusions as $fusion) {
                $pays_et_magazine_fusion=explode('_',$fusion);
                $requete_get_type_liste='SELECT Type_Liste FROM parametres_listes WHERE Pays LIKE \''.$pays_et_magazine_fusion[0].'\' AND Magazine LIKE \''.$pays_et_magazine_fusion[1].'\' AND ID_Utilisateur='.$id_user;
                $resultat_get_type_liste=DM_Core::$d->requete_select($requete_get_type_liste);
                if (count($resultat_get_type_liste) > 0) {
                    $type_liste=$resultat_get_type_liste[0]['Type_Liste'];

                    if (isset($_POST['type_liste']) && $_POST['type_liste'] != $type_liste) {
                        if (isset($_POST['confirmation_remplacement'])) {
                            $requete_effacer_parametres_courants='DELETE FROM parametres_listes WHERE Pays LIKE \''.$pays_et_magazine_fusion[0].'\' AND Magazine LIKE \''.$pays_et_magazine_fusion[1].'\' AND ID_Utilisateur='.$id_user;
                            DM_Core::$d->requete($requete_effacer_parametres_courants);
                        }
                        else {    
                            header("X-JSON: " . json_encode(array('message'=>'Les parametres de la boite seront reinitialises si vous changez son type d\'affichage. Confirmer ?')));
                            exit(0);
                        }
                    }
                }
            }
            $type_liste=$_POST['type_liste'];
            $parametres=array();
        }
    }
    else {
        $requete_get_type_liste='SELECT Type_Liste FROM parametres_listes WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\'';
        $resultat_get_type_liste=DM_Core::$d->requete_select($requete_get_type_liste);
        if (count($resultat_get_type_liste) > 0) {
            $type_liste=$resultat_get_type_liste[0]['Type_Liste'];
        }
        else 
            $type_liste='dmspiral';
    }
    if (isset($_POST['fusions'])) {
        $fusions=explode('-',$_POST['fusions']);
        foreach($fusions as $fusion) {
            $pays_et_magazine_fusion=explode('_',$fusion);
            $sous_liste->fusionnerAvec($l->sous_liste($pays_et_magazine_fusion[0],$pays_et_magazine_fusion[1]));
        }
    }
    $sous_liste->afficher($type_liste,$parametres);
}
elseif(isset($_GET['liste_exemple'])) {
	$l=new Liste();
	$l->ListeExemple();
        if (file_exists('Listes/Liste.'.$_GET['type_liste'].'.class.php'))
            include_once('Listes/Liste.'.$_GET['type_liste'].'.class.php');
	$objet =new $_GET['type_liste']();
        ?>
        <html>
            <head>
                <meta content="text/html; charset=ISO-8859-1"
                      http-equiv="content-type">
                <title><?php echo TITRE;?></title>
                <link rel="stylesheet" type="text/css" href="style.css">
            </head>
            <body>
            <?php $objet->afficher($l->collection).'</font>';?>
            </body>
        </html>
        <?php
}
elseif (isset($_POST['get_description'])) {
     if (file_exists('Listes/Liste.'.$_POST['type_liste'].'.class.php'))
        include_once('Listes/Liste.'.$_POST['type_liste'].'.class.php');
    $a=new ReflectionProperty($_POST['type_liste'], 'titre');
    $b=new $_POST['type_liste'];
    header("X-JSON: " . json_encode(array('titre'=>$a->getValue(),'contenu'=>$b->description)));
}
elseif (isset($_POST['update_list'])) {
    @session_start();
    $parametres=json_decode($_POST['parametres']);
    list($pays,$magazine)=explode('_',$_POST['pays_magazine']);
    $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
    $l = DM_Core::$d->toList($id_user);
    foreach($parametres as $parametre=>$valeur) {
        $requete_modifier_parametre='UPDATE parametres_listes SET Valeur=\''.$valeur.'\' '
                                   .'WHERE ID_Utilisateur='.$id_user.' AND Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Parametre LIKE \''.$parametre.'\'';
        DM_Core::$d->requete($requete_modifier_parametre);
    }
    
    $sous_liste = new Liste();
    $sous_liste = $l->sous_liste($pays, $magazine);
    echo $sous_liste->afficher($_POST['type_liste'],$parametres);
}
elseif (isset($_POST['update_parametres_generaux'])) {
    @session_start();
    $parametres=json_decode($_POST['parametres']);
    $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
    foreach($parametres as $parametre=>$valeur) {
        $requete_modifier_parametre='UPDATE parametres_listes SET Valeur=\''.$valeur.'\' '
                                   .'WHERE ID_Utilisateur='.$id_user.' AND Position_Liste=-1 AND Parametre LIKE \''.$parametre.'\'';
        DM_Core::$d->requete($requete_modifier_parametre);
    }
}
elseif (isset($_POST['parametres'])) {
    @session_start();
    $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
    list($pays,$magazine)=explode('_',$_POST['id_magazine']);
    $requete_get_parametres='SELECT Type_Liste,Parametre,Valeur FROM parametres_listes WHERE Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND ID_Utilisateur='.$id_user;
    $resultat_get_parametres=DM_Core::$d->requete_select($requete_get_parametres);
    if (count($resultat_get_parametres) == 0) {
        $type_liste=$_POST['type_liste'];
        $position_liste=$_POST['position_liste'];
        Liste::init_parametres_boite($pays, $magazine, $type_liste,$position_liste);
        $resultat_get_parametres=DM_Core::$d->requete_select($requete_get_parametres);
    }
    $type_liste=$resultat_get_parametres[0]['Type_Liste'];
    
    $parametres=new stdClass();
    foreach($resultat_get_parametres as $parametre) {
        $nom_parametre=$parametre['Parametre'];
        $parametres->$nom_parametre=$parametre['Valeur'];
    }
    if (file_exists('Listes/Liste.'.$type_liste.'.class.php'))
        include_once('Listes/Liste.'.$type_liste.'.class.php');
    $liste_courante=new $type_liste;
    if (count($resultat_get_parametres) > 0) {
        foreach($parametres as $nom_parametre=>$parametre)
            $liste_courante->parametres->$nom_parametre->valeur=$parametre;
    }
    foreach($parametres as $parametre=>$valeur) {
        $requete_modifier_parametre='UPDATE parametres_listes SET Valeur=\''.$valeur.'\' '
                                   .'WHERE ID_Utilisateur='.$id_user.' AND Pays LIKE \''.$pays.'\' AND Magazine LIKE \''.$magazine.'\' AND Parametre LIKE \''.$parametre.'\'';
        DM_Core::$d->requete($requete_modifier_parametre);
    }
    header("X-JSON: " . json_encode($liste_courante->getListeParametresModifiables()));
}

function startswith($hay, $needle) { // From http://sunfox.org/blog/2007/03/21/startswith-et-endswith-en-php/
	return $needle === $hay or strpos($hay, $needle) === 0;
}

function endswith($hay, $needle) {
	return $needle === $hay or strpos(strrev($hay), strrev($needle)) === 0;
}
?>