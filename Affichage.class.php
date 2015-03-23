<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
class Affichage {

	static function onglets($onglet_courant,$tab_onglets,$argument,$prefixe,$id=null) {
			$onmouseover='';
			$onmouseout='';
			$id=is_null($id) ? '':$id;
			?><ul <?=empty($id)?'':('id="'.$id.'"')?> class="tabnav"><?php
			foreach($tab_onglets as $nom_onglet=>$infos_lien) {
				$pays=$id=='onglets_pays' ? $infos_lien[0] : substr($infos_lien[0], 0,  strpos($infos_lien[0], '/'));
				$contenu_lien_onglet=$id=='onglets_magazines' ? $infos_lien[1] : $nom_onglet;
				?><li class="<?php
				if ($infos_lien[1]==AJOUTER_MAGAZINE) {
				   echo 'nouveau ';
				   $lien='?action=gerer&amp;onglet=ajout_suppr&amp;onglet_magazine=new';
				}
				else
					$lien=(empty($prefixe) || in_array($prefixe,array('?','.'))) ?'javascript:return false;':($prefixe.'&amp;'.$argument.'='.$infos_lien[0]);
				if ($infos_lien[0]==$onglet_courant)
				   echo 'active ';
				if (empty($prefixe)) {
					$nom = $pays;
				}
				else {
					switch($argument) {
						case 'onglet_magazine':
							$nom='magazine';
						break;
						default:
							if (in_array($argument,array('onglet_aide','onglet_type_param','previews')))
								$nom=$infos_lien[0];
							else
								$nom='';
						break;
					}
				}
				switch($prefixe) {
					case '':
						$onmouseout='';
						$onmouseover='montrer_magazines(\''.$pays.'\')';
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
				<?php
				if ($id=='onglets_pays' && $infos_lien[0]!='new') {
					?>
					<img src="images/flags/<?=$pays?>.png" alt="<?=$pays?>" /><span><?=$infos_lien[1]?></span>
					<?php
				}
				else {
					echo $contenu_lien_onglet;
				}
				?>
					</a></li>
				<?php
		}
		?></ul>
		<?php 
		if ($id!='onglets_pays') {
			?><br /><?php
		}
	}
	static function afficher_numeros($liste,$pays,$magazine,$numeros,$sous_titres) {
		$liste->nettoyer_collection();
		$nb_possedes=0;
		$numeros2=array();
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
		//print_r($liste->collection[$pays][$magazine]);
		?>
		<span id="pays" style="display:none"><?=$pays?></span>
		<span id="magazine" style="display:none"><?=$magazine?></span>
		<?php
		$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
		list($pays_complet,$nom_complet)=Inducks::get_nom_complet_magazine($pays, $magazine);
		?>
		<br />
		<table border="0" width="100%">
			<tr>
				<td rowspan="2">
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
		//echo '<pre>';print_r($liste);echo '</pre>';
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
                        if (!in_array($id_acquisition,array(-1,-2))) {
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

		list($pays_complets,$magazines_complets)=Inducks::get_noms_complets($evenements->publicationcodes);

		foreach($evenements->evenements as $evenements_date) {
			foreach($evenements_date as $type=>$evenements_type) {
				foreach($evenements_type as $evenement) {
					?><div class="evenement_<?=$type?>"><?php
					switch($type) {
						case 'inscriptions':
							?><b><?=$evenement->utilisateur?></b> <?=NEWS_S_EST_INSCRIT?>
						<?php 
						break;
						case 'bouquineries':
							?><b><?=utf8_decode($evenement->utilisateur)?></b> <?=NEWS_A_AJOUTE_BOUQUINERIE.' '
								   ?><i><a href="?action=bouquineries"><?=$evenement->nom_bouquinerie?></a></i>.
						<?php 
						break;
						case 'ajouts':
							$numero=$evenement->numero_exemple;
							if (!array_key_exists($numero->Pays.'/'.$numero->Magazine, $magazines_complets)) {
								$evenement->cpt++;
								continue;
							}	
							?><b><?=$evenement->utilisateur?></b> <?=NEWS_A_AJOUTE?> 
							<?=Affichage::afficher_texte_numero($numero->Pays,$magazines_complets[$numero->Pays.'/'.$numero->Magazine],$numero->Numero)?>
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
							$contributeurs = array_filter(array_unique($evenement->utilisateur));
                            $str_contributeurs = '<b>'.utf8_decode(implode('</b>, <b>', $contributeurs)).'</b>';
                            $str_contributeurs = str_replace_last(',', ' '.ET.' ', $str_contributeurs);

							?><?=$str_contributeurs?> <?=count($contributeurs) === 1 ? NEWS_A_CREE_TRANCHE : NEWS_ONT_CREE_TRANCHE?>
							<a href="javascript:void(0)" class="has_tooltip underlined">
								<?=Affichage::afficher_texte_numero($numero->Pays,$magazines_complets[$numero->Pays.'/'.$numero->Magazine],$numero->Numero)?>
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
	
	static function valider_formulaire_inscription($user, $pass, $pass2) {
		$erreur=null;
		if (isset($user)) {
			if (strlen($user) <3) {
				$erreur=UTILISATEUR_3_CHAR_ERREUR;
			}
			if (strlen($pass) <6) {
				$erreur=MOT_DE_PASSE_6_CHAR_ERREUR;
			}
			elseif ($pass !== $pass2) {
				$erreur=MOTS_DE_PASSE_DIFFERENTS;
			}
			else {
				if (DM_Core::$d->user_exists($user)) {
					$erreur=UTILISATEUR_EXISTANT;
				}
			}
		}
		return $erreur;
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
}

function str_replace_last($search, $replace, $str ) {
    if( ( $pos = strrpos( $str , $search ) ) !== false ) {
        $search_length  = strlen( $search );
        $str    = substr_replace( $str , $replace , $pos , $search_length );
    }
    return $str;
}
?>