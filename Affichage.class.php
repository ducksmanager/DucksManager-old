<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
class Affichage {

    static $niveaux_medailles=[
        'Photographe' => [1 => 1, 2 => 10, 3 => 50],
        'Concepteur'  => [1 => 1, 2 => 3,  3 => 10],
        'Duckhunter'  => [1 => 1, 2 => 3,  3 =>  5]
    ];

    static function onglets_magazines($onglets_pays,$onglets_magazines) {
        $magazine_courant = isset($_GET['onglet_magazine']) ? $_GET['onglet_magazine'] : null;
        $pays_courant = is_null($magazine_courant) ? null : explode('/', $magazine_courant)[0];
        ?>
        <nav class="magazines_possedes navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#"><?=LISTE_MAGAZINES?></a>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <?php foreach($onglets_pays as $nom_pays => $details_pays) {
                            $nom_pays_abrege=$details_pays[0];
                            ?><li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <img class="flag" src="images/flags/<?=$nom_pays_abrege?>.png" />
                                    <span class="<?=$pays_courant === $nom_pays_abrege ? 'bold' : '' ?>">
                                        <?=$nom_pays?>
                                    </span>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach(array_filter($onglets_magazines, function($nom_magazine_abrege) use($nom_pays_abrege) {
                                        return explode('/', $nom_magazine_abrege)[0] === $nom_pays_abrege;
                                    }, ARRAY_FILTER_USE_KEY) as $magazine) { ?>
                                        <li>
                                            <a href="?action=gerer&amp;onglet=ajout_suppr&onglet_magazine=<?=$magazine[0]?>">
                                                <span class="<?=$magazine[0] === $magazine_courant ? 'bold' : '' ?>">
                                                    <?=$magazine[1]?>
                                                </span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="?action=gerer&amp;onglet=ajout_suppr&amp;onglet_magazine=new" role="button">
                                <?=NOUVEAU_MAGAZINE?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <?php
    }

	static function onglets($onglet_courant, $tab_onglets, $argument, $prefixe) {
			$onmouseover='';
			$onmouseout='';
			?><ul <?=empty($id)?'':('id="'.$id.'"')?> class="tabnav"><?php
			foreach($tab_onglets as $nom_onglet=>$infos_lien) {
				?><li class="<?php

                $lien=(empty($prefixe) || in_array($prefixe, ['?','.'])) ?'javascript:return false;':($prefixe.'&amp;'.$argument.'='.$infos_lien[0]);
				if ($infos_lien[0]==$onglet_courant)
				   echo 'active ';
				if (empty($prefixe)) {
					$nom = substr($infos_lien[0], 0,  strpos($infos_lien[0], '/'));
				}
				else {
                    if (in_array($argument, ['onglet_aide','onglet_type_param','previews']))
                        $nom=$infos_lien[0];
                    else
                        $nom='';
				}
				switch($prefixe) {
					case '':
						$onmouseout='';
					break;
					case '?';
						$onclick='toggle_item_menu(this)';
					break;
				}
				?>"><a id="<?=$infos_lien[1]?>"
					   name="<?=$nom?>"
					   onmouseover="<?=$onmouseover?>"
					   onmouseout="<?=$onmouseout?>"
					   <?=(isset($onclick)?'onclick="'.$onclick.'"':'')?>
					   href="<?=$lien?>">
				        <?=$nom_onglet?>
					</a>
                </li>
				<?php
		}
		?></ul>
		<?php
	}

    /**
     * @param Liste $liste
     * @param $pays
     * @param $magazine
     * @param $numeros
     * @param $sous_titres
     */
    static function afficher_numeros($liste,$pays,$magazine,$numeros,$sous_titres) {
		$liste->nettoyer_collection();
		$nb_possedes=0;
		$numeros2= [];
		foreach($numeros as $i=>$numero) {
			$infos_numero=$liste->infos_numero($pays,$magazine,$numero);
			$o=new stdClass();
			$o->est_possede=false;
			if (!is_null($infos_numero)) {
				$nb_possedes++;
				$o->est_possede=true;
			}
			$o->etat=$infos_numero[1];
			$o->av=$infos_numero[2];
			$o->id_acquisition=$infos_numero[3];
			$o->sous_titre=$sous_titres[$i];
			$numeros2[$numero]=$o;
		}
		$nb_non_possedes=count($numeros)-$nb_possedes;
						
		
		$cpt=0;
		?>
		<span id="pays" style="display:none"><?=$pays?></span>
		<span id="magazine" style="display:none"><?=$magazine?></span>
		<?php
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		$nom_complet=Inducks::get_nom_complet_magazine($pays, $magazine);
		?>
		<br />
		<table border="0" width="100%">
			<tr>
				<td rowspan="2">
                    <img class="flag" src="images/flags/<?=$pays?>.png" />
					<span style="font-size:15pt;font-weight:bold;"><?=$nom_complet?></span>
				</td>
				<td align="right">
					<table>
						<tr>
							<td>
								<input type="checkbox" id="sel_numeros_possede" checked="checked" onclick="changer_affichage('possede')"/>
							</td>
							<td>
								<?=AFFICHER_NUMEROS_POSSEDES?> (<?=$nb_possedes?>)
							</td>
						</tr>
						<tr>
							<td align="right">
								<input type="checkbox" id="sel_numeros_manque" checked="checked" onclick="changer_affichage('manque')"/>
							</td>
							<td>
								<?=AFFICHER_NUMEROS_MANQUANTS?> (<?=$nb_non_possedes?>)
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<?php
		foreach($numeros2 as $numero=>$infos) {
			$etat=$infos->etat;
			$id_acquisition=$infos->id_acquisition;
			$av=$infos->av;
			$sous_titre=$infos->sous_titre;
			$possede=$infos->est_possede;
			?>
			<div class="num_<?=$possede ? 'possede' : 'manque'?>" 
				 id="n<?=($cpt)?>" title="<?=$numero?>">
                <a name="<?=$numero?>"></a>
                <img class="preview" src="images/icones/view.png" />
                <span class="num">n&deg;<?=$numero?>&nbsp;
                    <span class="soustitre"><?=$sous_titre?></span>
                </span>
                    <?php

                    if ($possede) {
                        ?><div class="bloc_details">
                            <div class="details_numero num_<?=$etat?> detail_<?=$etat?>" title="<?=get_constant('ETAT_'.strtoupper($etat))?>">
                        </div><?php
                        if (!in_array($id_acquisition, [-1,-2])) {
                            $requete_date_achat='SELECT ID_Acquisition, Date FROM achats WHERE ID_Acquisition='.$id_acquisition.' AND ID_User='.$id_user;
                            $resultat_date=DM_Core::$d->requete_select($requete_date_achat);
                            if (count($resultat_date)>0) {
                                $regex_date='#([^-]+)-([^-]+)-(.+)#is';
                                $date=preg_replace($regex_date,'$3/$2/$1',$resultat_date[0]['Date']);
                                $id=$resultat_date[0]['ID_Acquisition'];
                                if (!is_null($date) && !empty($date)) {
                                    ?>
                                        <div class="details_numero detail_date" class="achat_<?=$id?>">
                                            <img src="images/page_date.png" title="<?=ACHETE_LE.' '.$date?>"/>
                                        </div><?php
                                }
                            }
                        }
                        else { ?>
                            <div class="details_numero detail_date"></div><?php
                        }
                        ?><div class="details_numero detail_a_vendre"><?php
                        if ($av) {
                            ?><img height="16px" src="images/av_<?=$_SESSION['lang']?>_petit.png" alt="AV" title="<?A_VENDRE?>"/><?php
                        }
                        ?></div>
                     </div><?php
                    }
                ?>
                </div>
            </div>
            <?php
            $cpt++;
		}
	}
	
	static function afficher_evenements_recents($evenements) {
		include_once('Edge.class.php');
		Edge::$sans_etageres = true;
		Edge::$grossissement_defaut = 1;

		$magazines_complets=Inducks::get_noms_complets_magazines($evenements->publicationcodes);
		$details_collections=DM_Core::$d->get_details_collections($evenements->ids_utilisateurs);

		foreach($evenements->evenements as $evenements_date) {
			foreach($evenements_date as $type=>$evenements_type) {
				foreach($evenements_type as $evenement) {
					?><div class="evenement_<?=$type?>"><?php
					switch($type) {
						case 'inscriptions':
							Affichage::afficher_texte_utilisateur($details_collections[$evenement->id_utilisateur]);
							?><?=NEWS_S_EST_INSCRIT?>
						<?php 
						break;
						case 'bouquineries':
                            Affichage::afficher_texte_utilisateur($details_collections[$evenement->id_utilisateur]);?>
                            <?=NEWS_A_AJOUTE_BOUQUINERIE.' ' ?>
                            <i><a href="?action=bouquineries"><?=$evenement->nom_bouquinerie?></a></i>.
						<?php 
						break;
						case 'ajouts':
							$numero=$evenement->numero_exemple;
							if (!array_key_exists($numero->Pays.'/'.$numero->Magazine, $magazines_complets)) {
								$evenement->cpt++;
								continue;
							}
                            Affichage::afficher_texte_utilisateur($details_collections[$evenement->id_utilisateur]);
							?><?=NEWS_A_AJOUTE?>
							<?php Affichage::afficher_texte_numero($numero->Pays,$magazines_complets[$numero->Pays.'/'.$numero->Magazine],$numero->Numero); ?>
							<?php 
							if ($evenement->cpt > 0) {
								?>
								<?=ET?> <?=$evenement->cpt?> 
								<?=$evenement->cpt === 1 ? NEWS_AUTRE_NUMERO : NEWS_AUTRES_NUMEROS?>
							<?php } ?>
							<?=NEWS_A_SA_COLLECTION?><?php
						break;
						case 'tranches_pretes':
							$numero=$evenement->numeros[0];
							if (!array_key_exists($numero->Pays.'/'.$numero->Magazine, $magazines_complets)) {
								$evenement->cpt++;
								continue;
							}
							$contributeurs = array_filter(array_unique($evenement->noms_utilisateurs));
                            $str_contributeurs = '<b>'.utf8_decode(implode('</b>, <b>', $contributeurs)).'</b>';
                            $str_contributeurs = str_replace_last(',', ' '.ET.' ', $str_contributeurs);

							?><?=$str_contributeurs?> <?=count($contributeurs) === 1 ? NEWS_A_CREE_TRANCHE : NEWS_ONT_CREE_TRANCHE?>
							<a href="javascript:void(0)" class="has_tooltip underlined">
								<?php Affichage::afficher_texte_numero($numero->Pays,$magazines_complets[$numero->Pays.'/'.$numero->Magazine],$numero->Numero);?>
								<?php
								$nb_autres_numeros = count($evenement->numeros) - 1;
								if ($nb_autres_numeros > 0) {
									?>
										<?=ET?> <?=($nb_autres_numeros)?>
										<?=$nb_autres_numeros === 1 ? NEWS_AUTRE_TRANCHE : NEWS_AUTRES_TRANCHES?>
								<?php } ?>
							</a>
							<span class="cache tooltip_content">
								<?php
								foreach($evenement->numeros as $numero) {
									$e=new Edge($numero->Pays, $numero->Magazine, $numero->Numero, $numero->Numero);
									echo $e->html;
								}
								echo Edge::getEtagereHTML(true);
								foreach($evenement->numeros as $numero) {
									Affichage::afficher_texte_numero($numero->Pays,$magazines_complets[$numero->Pays.'/'.$numero->Magazine],$numero->Numero);
									?><br /><?php
								}
								?>
							</span>
							<?=NEWS_ONT_CREE_TRANCHE_2?>
							<?php 
						break;
					}
                    Affichage::afficher_temps_passe($evenement->diffsecondes);
					?></div><?php
				}
			}
		}
	}

    static function afficher_temps_passe($diff_secondes) {
        ?><span class="date">&nbsp;<?=NEWS_IL_Y_A_PREFIXE?>

        <?php
        if ($diff_secondes < 60) {
            ?><?=$diff_secondes.' '.NEWS_TEMPS_SECONDE.($diff_secondes == 1 ? '':'s')?><?php
        }
        else {
            $diff_secondes=intval($diff_secondes/60);
            if ($diff_secondes < 60) {
                ?><?=$diff_secondes.' '.NEWS_TEMPS_MINUTE.($diff_secondes == 1 ? '':'s')?><?php
            }
            else {
                $diff_secondes=intval($diff_secondes/60);
                if ($diff_secondes < 24) {
                    ?><?=$diff_secondes.' '.NEWS_TEMPS_HEURE.($diff_secondes == 1 ? '':'s')?><?php
                }
                else {
                    $diff_secondes=intval($diff_secondes/24);
                    ?><?=$diff_secondes.' '.NEWS_TEMPS_JOUR.(intval($diff_secondes) == 1 ? '':'s')?><?php
                }
            }
        }
		?><?=NEWS_IL_Y_A_SUFFIXE?></span><?php
    }
	
	static function afficher_texte_numero($pays, $magazine, $numero) {
		?><span class="nowrap"><img src="images/flags/<?=$pays?>.png" />&nbsp;<?=$magazine.' '.$numero?></span><?php
	}

	static function afficher_texte_utilisateur($infos_utilisateur) {
        $nom_utilisateur = utf8_decode($infos_utilisateur['Username']);
        ?><a href="javascript:void(0)" class="has_tooltip underlined"><b><?=$nom_utilisateur?></b></a>
        <div class="cache tooltip_content">
            <h4><?=$nom_utilisateur?></h4>
            <div>
                <?php Affichage::afficher_stats_collection($infos_utilisateur['NbPays'], $infos_utilisateur['NbMagazines'], $infos_utilisateur['NbNumeros'], $infos_utilisateur['NbPhotographies'], $infos_utilisateur['NbCreations'], $infos_utilisateur['NbBouquineries']) ?>
            </div>
        </div><?php
    }

	static function afficher_texte_histoire($code, $title, $comment)
	{
		if (empty($title)) {
			$title = SANS_TITRE.($comment ? ' ('.$comment.') ' : '');
		}
		?><?=$title?>&nbsp;<a target="_blank" href="https://coa.inducks.org/story.php?c=<?=urlencode($code)?>&search=">
			<?=VOIR_PLUS?>
		</a><?php
	}
	
	static function valider_formulaire_inscription($user, $pass, $pass2) {
		$erreur=null;
		if (isset($user)) {
			if (preg_match('#^[-_A-Za-z0-9]{3,15}$#', $user) === 0) {
				return UTILISATEUR_INVALIDE;
			}
			if (strlen($pass) <6) {
				return MOT_DE_PASSE_6_CHAR_ERREUR;
			}
			elseif ($pass !== $pass2) {
				return MOTS_DE_PASSE_DIFFERENTS;
			}
			else {
				if (DM_Core::$d->user_exists($user)) {
					return UTILISATEUR_EXISTANT;
				}
			}
		}
		else {
			return UTILISATEUR_INVALIDE;
		}
		return null;
	}

    static function partager_page() {
        $id_user=DM_Core::$d->user_to_id($_SESSION['user']);
        $cle = Util::get_random_string();
        $requete_ajout_acces = 'INSERT INTO bibliotheque_acces_externes(ID_Utilisateur, Cle) VALUES ('.$id_user.', \''.$cle.'\')';
        DM_Core::$d->requete($requete_ajout_acces);
        ?><div class="a2a_kit a2a_kit_size_32 a2a_default_style"
               data-a2a-url="http://www.ducksmanager.net/?action=bibliotheque&user=<?=$_SESSION['user']?>&key=<?=$cle?>"
               data-a2a-title="Ma bibliothèque DucksManager">
            <a class="noborder a2a_button_email"></a>
            <a class="noborder a2a_button_facebook"></a>
            <a class="noborder a2a_button_twitter"></a>
            <a class="noborder a2a_button_google_plus"></a>
        </div><?php
    }

    public static function afficher_stats_collection_court($nb_pays, $nb_magazines, $nb_numeros) {
        echo $nb_numeros.' '.NUMEROS . '<br />'
            . POSSESSION_MAGAZINES_2 . ' ' . $nb_magazines . ' '
            . POSSESSION_MAGAZINES_3 . ' ' . $nb_pays . ' ' .  PAYS. '.';
    }

    public static function get_medailles($nbPhotographiesCreationsBouquineries) {
        $cpt_et_niveaux=[];
        foreach($nbPhotographiesCreationsBouquineries as $type=>$cpt) {
            $cpt_et_niveaux[$type]=null;
            foreach (Affichage::$niveaux_medailles[$type] as $niveau=> $cpt_min) {
                if ($cpt >= $cpt_min) {
                    $cpt_et_niveaux[$type]=['Niveau'=>$niveau,'Cpt'=>$cpt];
                }
            }
        }
        return $cpt_et_niveaux;
    }

    public static function afficher_stats_collection($nb_pays, $nb_magazines, $nb_numeros, $nbPhotographies, $nbCreations, $nbBouquineries)
    {
        $medailles = Affichage::get_medailles([
            'Photographe'=> $nbPhotographies,
            'Concepteur' => $nbCreations,
            'Duckhunter' => $nbBouquineries
        ]);
        foreach($medailles as $type=>$cpt_et_niveau) {
            if (!is_null($cpt_et_niveau)) {
                $niveau=$cpt_et_niveau['Niveau'];
                ?>
                <div class="medaille_profil">
                    <img src="images/medailles/<?=$type?>_<?=$niveau?>_fond.png" /><br />
                    <b><?=constant('TITRE_MEDAILLE_'.strtoupper($type))?><br /><?=NIVEAU?> <?=str_replace('avance', 'avancé', $niveau)?></b>
                </div><?php
            }
        }
        ?>
        <div class="clear"><?php
            if ($nb_numeros > 0) {
                echo $nb_numeros.' '.NUMEROS . '<br />'
                   . $nb_magazines . ' ' . MAGAZINES . '<br />'
                   . $nb_pays . ' ' .  PAYS;
            }
        ?></div><?php
    }
}

function str_replace_last($search, $replace, $str ) {
    if( ( $pos = strrpos( $str , $search ) ) !== false ) {
        $search_length  = strlen( $search );
        $str    = substr_replace( $str , $replace , $pos , $search_length );
    }
    return $str;
}
?>