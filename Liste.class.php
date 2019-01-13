<?php
require_once 'DucksManager_Core.class.php';
require_once 'Affichage.class.php';
require_once 'Inducks.class.php';
class Liste {
	var $contenu;
	var $nom_fichier;
	var $collection= [];
	var $very_max_centaines=1;
	var $database;
	static $types_listes= [];

	static function set_types_listes() {
		$rep = "Listes/";
		$dir = opendir($rep);
		$prefixe='Liste.';
		$suffixe='.class.php';
		while ($f = readdir($dir)) {
			if (strpos($f,'Debug')!==false) {
                continue;
            }
			if (is_file($rep . $f) && startswith($f, $prefixe) && endswith($f, $suffixe)) {
                $nom=substr($f,strlen($prefixe),strlen($f)-strlen($suffixe)-strlen($prefixe));

                include_once('Listes/Liste.'.$nom.'.class.php');
                $a=new ReflectionProperty($nom, 'titre');
                self::$types_listes[$nom]=$a->getValue();
            }
		}
		return self::$types_listes;
	}

	function __construct($texte=false) {
		if (!$texte) {
		    return;
        }
		$this->texte=$texte;
	}

	function ajouter($pays,$magazine,$numero) {
		if (!array_key_exists($pays, $this->collection)) {
            $this->collection[$pays] = [];
        }
		if (!array_key_exists($magazine,$this->collection[$pays])) {
            $this->collection[$pays][$magazine] = [];
        }
		if (in_array($numero, $this->collection[$pays][$magazine])) {
            return;
        }
		$this->collection[$pays][$magazine][]=$numero;
	}

	function ListeExemple() {
		$numeros_mp= [[2,'Excellent',false,-1], [273,'Bon',false,-1], [4,'Excellent',false,-1], [92,'Excellent',false,-1]];
		$numeros_mad= [[6,'Indefini',false,-1], [16,'Bon',false,-1]];
		$this->collection= ['fr'=> ['MP'=>$numeros_mp], 'us'=> ['MAD'=>$numeros_mad]];
	}

	function sous_liste($pays,$magazine=false) {
		$nouvelle_liste=new Liste();
		if (!$magazine) {
            $nouvelle_liste->collection= [$pays=> [$this->collection[$pays]]];
        }
		else {
		    if (isset($this->collection[$pays][$magazine])) {
                $nouvelle_liste->collection= [$pays=> [$magazine=>$this->collection[$pays][$magazine]]];
            }
        }
		return $nouvelle_liste;
	}

	function liste_pays() {
		$tab= [];
		$liste=Inducks::get_pays();
		foreach(array_keys($this->collection) as $pays) {
			$pays_trouve=array_key_exists($pays,$liste);
			$tab[$pays_trouve ? $liste[$pays] : $pays]= [$pays,$pays_trouve ? $liste[$pays] : $pays];
		}
		return $tab;
	}

	function get_publication_la_plus_possedee() {
	    $cpt_numeros = [];
        foreach($this->collection as $pays=>$numeros_pays) {
            foreach(array_keys($numeros_pays) as $magazine) {
                $cpt_numeros[$pays.'/'.$magazine] = count($numeros_pays[$magazine]);
            }
        }
        if (count($cpt_numeros) > 0) {
            arsort($cpt_numeros);
            return key($cpt_numeros);
        }
        return null;
    }

	function liste_magazines($pays_magazine_supplementaire=null,$tri_noms_complets=true) {
		$publication_codes= [];
		foreach($this->collection as $pays=>$numeros_pays) {
			foreach(array_keys($numeros_pays) as $magazine) {
				$publication_codes[]=$pays.'/'.$magazine;
			}
		}
		if (!is_null($pays_magazine_supplementaire)) {
			$publication_codes[]=$pays_magazine_supplementaire;
		}
		$noms_pays = Inducks::get_noms_complets_pays($publication_codes);
		$noms_magazines = Inducks::get_noms_complets_magazines($publication_codes);
		foreach(array_keys($noms_pays) as $nom_abrege) {
			$noms_pays[$noms_pays[$nom_abrege]]= [$nom_abrege, $noms_pays[$nom_abrege]];
			unset($noms_pays[$nom_abrege]);
		}
		foreach(array_keys($noms_magazines) as $nom_abrege) {
			$noms_magazines[$nom_abrege]= [$nom_abrege, $noms_magazines[$nom_abrege]];
		}

		if ($tri_noms_complets) {
			uasort($noms_magazines, function($a,$b) {
				if ($a[1] === $b[1]) {
					return 0;
				}
				return ($a[1] < $b[1]) ? -1 : 1;
			});
		}
		return [$noms_pays,$noms_magazines];
	}

	function statistiques($onglet) {
		$id_user=$_SESSION['id_user'];

		$counts= [];
		foreach($this->collection as $pays=>$numeros_pays) {
			$counts[$pays]= [];
			foreach($numeros_pays as $magazine=>$numeros) {
				$counts[$pays][$magazine]=count($numeros);
			}
		}
		$onglets= [PUBLICATIONS=> ['magazines',PUBLICATIONS_COURT],
							   POSSESSIONS=> ['possessions',POSSESSIONS_COURT],
							   ETATS_NUMEROS=> ['etats',ETATS_NUMEROS_COURT],
							   ACHATS=> ['achats',ACHATS_COURT],
							   AUTEURS=> ['auteurs',AUTEURS_COURT]];
		Affichage::onglets($onglet,$onglets,'onglet','?action=stats');

		if (count($counts)===0) {
            ?><div class="alert alert-info">
                <?=AUCUN_NUMERO_POSSEDE_1?>
                <a href="?action=gerer&onglet=ajout_suppr"><?=ICI?></a>
                <?=AUCUN_NUMERO_POSSEDE_2?></div>
            <?php
			return;
		}
		switch($onglet) {
			case 'magazines':
				?>
				<div id="canvas-holder">
					<canvas id="graph_publications"></canvas>
				</div><?php
			break;
			case 'possessions':
				$types = ['abs' => AFFICHER_VALEURS_REELLES, 'cpt'=> AFFICHER_POURCENTAGES]; ?>

				<span id="chargement_classement_termine"><?=CHARGEMENT?></span><br />
				<div id="barre_pct_classement">
					<div id="pct_classement">&nbsp;</div>
				</div>
				<span id="prefixe_message_classement">
					<?=CALCUL?>
				</span>
				<span id="message_classement"></span>
				<div id="fin_classement" class="hidden">
                    <div class="btn-group" data-toggle="buttons">
                        <?php foreach($types as $type=>$label) {?>
                            <label class="btn btn-default graph_type <?=$type==='abs' ? 'active': ''?>" onclick="toggleGraphs(this, 'possessions')">
                                <input type="radio" name="options_graph" autocomplete="off" /> <?=$label?>
                            </label><?php
                        }?>
                    </div>
				</div>
				<br />
				<script type="text/javascript">
					initProgressBar('classement','Stats.class.php', {graph: 'true', possessions: 'true'}, afficher_histogramme_possessions);
				</script>
				<div id="resultat_classement"></div>

				<div id="canvas-holder" class="hidden">
					<?php foreach($types as $type=>$label) {
						?><canvas class="graph_possessions <?=$type?> <?=$type==='cpt' ? 'hidden' : ''?>"
								  width="100%" height="500px"></canvas><?php
					}?>
				</div>
				<?php
				break;
			case 'etats':
				?>
				<div id="canvas-holder">
					<canvas id="graph_conditions"></canvas>
				</div><?php
			break;

			case 'achats':
				$types = ['nouv' => AFFICHER_NOUVELLES_ACQUISITIONS, 'tot'=> AFFICHER_POSSESSIONS_TOTALES]; ?>

				<div id="message_achats"><?=CHARGEMENT?></div>

				<div id="fin_achats" class="hidden"></div>

				<br />

				<div id="canvas-holder" class="hidden" style="background: whitesmoke">
					<?php foreach($types as $type=>$label) {
						?><canvas class="graph_achats <?=$type?> <?=$type==='tot' ? 'hidden' : ''?>"
								  width="100%" height="500px"></canvas><?php
					}?>
				</div>
				<?php
			break;
			case 'auteurs':
				$requete_auteurs_surveilles='SELECT NomAuteur FROM auteurs_pseudos WHERE ID_User='.$id_user;
				$resultat_auteurs_surveilles=DM_Core::$d->requete($requete_auteurs_surveilles);
				if (count($resultat_auteurs_surveilles) > 0) {
					$requete_calcul_effectue='SELECT count(NomAuteur) AS cpt FROM auteurs_pseudos WHERE ID_User='.$id_user;
					$resultat_calcul_effectue=DM_Core::$d->requete($requete_calcul_effectue);
					if (count($resultat_calcul_effectue) === 0) {
						echo AUCUN_AUTEUR_SURVEILLE;
					}
					else {
						$types = ['abs' => AFFICHER_VALEURS_REELLES, 'pct'=> AFFICHER_POURCENTAGES]; ?>

						<div id="aucun_resultat_stats_auteur" class="hidden">
							<?=CALCULS_PAS_ENCORE_FAITS?>
						</div>
						<div id="chargement_stats_auteur">
							<?=CHARGEMENT?>
						</div>
						<div id="fin_stats_auteur" class="hidden">
                            <div class="btn-group" data-toggle="buttons">
                                <?php foreach($types as $type=>$label) {?>
                                <label class="btn btn-default graph_type <?=$type==='abs' ? 'active': ''?>" onclick="toggleGraphs(this, 'auteurs')">
                                    <input type="radio" name="options_graph" autocomplete="off" /> <?=$label?>
                                    </label><?php
                                }?>
                            </div>
						</div>
                        <br />
                        <div id="canvas-holder" class="hidden">
                            <?php foreach($types as $type=>$label) {
                                ?><canvas class="graph_auteurs <?=$type?> <?=$type==='pct' ? 'hidden' : ''?>"></canvas><?php
                            }?>
                        </div>
						<?php
					}
				}
				?><br /><br />
				<?=STATISTIQUES_QUOTIDIENNES?>
				<br /><br />
				<hr /><?php
				if (isset($_POST['auteur_nom'])) {
					DM_Core::$d->ajouter_auteur($_POST['auteur_nom']);
				}
				?>
				<br /><br />
				<?=AUTEURS_FAVORIS_INTRO_1?>
				<a target="_blank" href="?action=agrandir&onglet=auteurs_favoris"><?=AUTEURS_FAVORIS_INTRO_2?></a>
				<br />
				<?=STATISTIQUES_AUTEURS_INTRO?>
				<br /><br />
                <div class="form-group">
                    <form method="post" action="?action=stats&amp;onglet=auteurs">
                        <label for="auteur_nom" class="col-sm-1 control-label" style="white-space: nowrap"><?=AUTEUR?> :</label>
                        <div class="col-sm-4">
                            <input class="form-control" autocomplete="off" type="text" name="auteur_nom" id="auteur_nom" value="" />
                        </div>
                        <input type="hidden" id="auteur_id" name="auteur_id" />
                        <div class="col-sm-4">
                            <input class="btn btn-default center" type="submit" value="<?=AJOUTER?>" />
                        </div>
                    </form>
                </div>
				<div style="clear: both">
					<br /><br />
					<?=LISTE_AUTEURS_INTRO?>
					<?php
					$requete_auteurs_surveilles="
						SELECT NomAuteur, Notation
						FROM auteurs_pseudos
						WHERE ID_User=$id_user";
					DM_Core::$d->afficher_liste_auteurs_surveilles(DM_Core::$d->requete($requete_auteurs_surveilles));
					?>
				</div><?php
				break;
			break;
		}
	}

	function add_to_database($id_user) {
		$cpt=0;
		foreach($this->collection as $pays=>$numeros_pays) {
			if ($pays!=='country') {
                foreach($numeros_pays as $magazine=>$numeros) {
                    foreach($numeros as $numero) {
                        $requete='INSERT INTO numeros (Pays, Magazine, Numero, Etat, ID_Acquisition, AV, ID_Utilisateur) '
                                .'VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$numero.'\',\'indefini\',-1,0,'.$id_user.')';
                        DM_Core::$d->requete($requete);
                        $cpt++;
                    }
                }
            }
		}
		return $cpt;
	}

	function remove_from_database($id_user) {
		$cpt=0;
		foreach($this->collection as $pays=>$numeros_pays) {
			if ($pays!=='country') {
                foreach($numeros_pays as $magazine=>$numeros) {
                    foreach($numeros as $numero) {
                        $num_final=is_array($numero) && array_key_exists(0,$numero) ? $numero[0] : $numero;
                        $requete='DELETE FROM numeros WHERE (ID_Utilisateur ='.$id_user.' AND PAYS = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero = \''.$num_final.'\')';
                        DM_Core::$d->requete($requete);
                        $cpt++;
                    }
                }
            }
		}
		return $cpt;
	}

	function synchro_to_database($ajouter_numeros=true,$supprimer_numeros=false) {
			$id_user=$_SESSION['id_user'];
			$l_ducksmanager=DM_Core::$d->toList($id_user);
			$l_ducksmanager->compareWith($this,$ajouter_numeros,$supprimer_numeros);
	}

	function compareWith($other_list,$ajouter_numeros=false,$supprimer_numeros=false) {
		$id_user=$_SESSION['id_user'];
		$numeros_a_ajouter=$numeros_a_supprimer=$numeros_communs=0;

		$liste_a_supprimer=new Liste();

		foreach($this->collection as $pays=>$numeros_pays) {
			foreach($numeros_pays as $magazine=>$numeros) {
				sort($numeros);
				foreach($numeros as $numero) {
					if (array_key_exists($pays, $other_list->collection)
					 && array_key_exists($magazine, $other_list->collection[$pays])
					 && in_array($numero[0], $other_list->collection[$pays][$magazine])) {
                        $numeros_communs++;
                    }
					else {
						$liste_a_supprimer->ajouter($pays, $magazine, $numero[0]);
						$numeros_a_supprimer++;
					 }
				}
			}
		}
		if ($supprimer_numeros) {
            $liste_a_supprimer->remove_from_database($id_user);
        }
		$liste_a_ajouter=new Liste();
		foreach($other_list->collection as $pays=>$numeros_pays) {
			foreach($numeros_pays as $magazine=>$numeros) {
				if ($pays!=='country') {
                    foreach($numeros as $numero) {
                        $trouve=false;
                        if (array_key_exists($pays,$this->collection)
                        && array_key_exists($magazine,$this->collection[$pays])) {
                            $numeros_possedes_magazine=count($this->collection[$pays][$magazine]);
                            for ($i=0;$i<$numeros_possedes_magazine;$i++) {
                                if ($numero === $this->collection[$pays][$magazine][$i][0]) {
                                    $trouve = true;
                                }
                            }
                        }
                        if (!$trouve) {
                            $liste_a_ajouter->ajouter($pays, $magazine, $numero);
                            $numeros_a_ajouter++;
                        }
                    }
                }
			}
		}
		if ($ajouter_numeros) {
            $liste_a_ajouter->add_to_database($id_user);
        }
		if (!$ajouter_numeros && !$supprimer_numeros) {
			?>
			<ul>
				<li style="margin-top:10px"><?=$numeros_a_ajouter?> <?=NUMEROS_A_AJOUTER?> :
					<?php $liste_a_ajouter->afficher('Classique'); ?>
				</li>
				<li style="margin-top:10px"><?=$numeros_a_supprimer?> <?=NUMEROS_A_SUPPRIMER?> :
					<?php $liste_a_supprimer->afficher('Classique'); ?>
				</li>
				<li style="margin-top:10px"><?=$numeros_communs?> <?=NUMEROS_COMMUNS?>
				</li>
			</ul>
			<?php
			return [$numeros_a_ajouter, $numeros_a_supprimer];
		}
        echo OPERATIONS_EXECUTEES.' <br />';
    }

	function afficher($type,$parametres=null) {
		$type=strtolower($type);
		if (@require_once 'Listes/Liste.'.$type.'.class.php') {
            /** @var Liste $o */
            $o=new $type();
			if (!is_null($parametres)) {
				foreach($parametres as $nom_parametre=>$parametre) {
                    $o->parametres->$nom_parametre = $parametre;
                }
			}
			$o->afficher($this->collection);
		}
		else {
            echo ERREUR_TYPE_LISTE_INVALIDE;
        }
	}

	function get_etat_numero_possede($pays, $magazine, $numero) {
		if (array_key_exists($pays, $this->collection)
         && array_key_exists($magazine, $this->collection[$pays])) {
            foreach($this->collection[$pays][$magazine] as $numero_liste) {
                if (nettoyer_numero($numero_liste[0])===$numero) {
                    return $numero_liste[1];
                }
            }
        }
		return null;
	}
	function infos_numero($pays,$magazine,$numero) {
		if (array_key_exists($pays, $this->collection)
         && array_key_exists($magazine, $this->collection[$pays])) {
            foreach($this->collection[$pays][$magazine] as $numero_liste) {
                if ($numero_liste[0]===$numero) {
                    return [
                        $numero_liste[0],
                         array_key_exists($numero_liste[1],Database::$etats) ? $numero_liste[1] : 'indefini',
                        $numero_liste[2],
                        $numero_liste[3]
                    ];
                }
            }
        }
		return null;
	}

	function nettoyer_collection() {
		$collection2= [];
		foreach($this->collection as $pays=>$liste_magazines) {
			$collection2[$pays]= [];
			foreach($liste_magazines as $magazine=>$liste_numeros) {
				$collection2[$pays][$magazine]= [];
				foreach($liste_numeros as $numero_liste) {
					$collection2[$pays][$magazine][]= [nettoyer_numero($numero_liste[0]),$numero_liste[1],$numero_liste[2],$numero_liste[3]];
				}
			}
		}
		$this->collection=$collection2;
	}

	static function import($liste_texte) {
		$l=new Liste($liste_texte);
		$ajouts = 0;
		$suppressions = 0;
		if ($l->collection === []) {
			echo AUCUN_NUMERO_INDUCKS;
			return [false,0,0];
		}

        if (isset($_SESSION['user'])) {
            $id_user = $_SESSION['id_user'];
            $l_ducksmanager = DM_Core::$d->toList($id_user);
            list($ajouts,$suppressions) = $l_ducksmanager->compareWith($l);
            if ($ajouts===0 && $suppressions===0) {
                echo LISTES_IDENTIQUES;
                return [true,0,0];
            }
        }
        else {
            echo RESULTAT_NUMEROS_INDUCKS;
            $l->afficher('Classique');
        }
        return [true,$ajouts, $suppressions];
    }
}
if (isset($_POST['parametres'])) {
    $_POST['parametres'] = str_replace('\"', '"', $_POST['parametres']);
}

function startswith($hay, $needle) { // From http://sunfox.org/blog/2007/03/21/startswith-et-endswith-en-php/
	return $needle === $hay or strpos($hay, $needle) === 0;
}

function endswith($hay, $needle) {
	return $needle === $hay or strpos(strrev($hay), strrev($needle)) === 0;
}
?>
