<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
class Affichage {

	static function onglets($onglet_courant,$tab_onglets,$argument,$prefixe,$drapeaux=false) {
			$onmouseover='';
			$onmouseout='';
			$cpt=0;
			$nb_onglets=count($tab_onglets);
			$largeur_tab=intval(100/($nb_onglets==0 ? 1 : $nb_onglets));
			?><ul class="tabnav"><?php
			foreach($tab_onglets as $nom_onglet=>$infos_lien) {
				$pays=$drapeaux ? $infos_lien[0] : substr($infos_lien[0], 0,  strpos($infos_lien[0], '/'));
				$contenu_lien_onglet=$nom_onglet;
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
					default:
						if (strpos($prefixe,'ajout_suppr')) {
							$onmouseover='montrer_nom_magazine(this)';
							$onmouseout='cacher_nom_magazine()';
						}
				}
				?>"><a id="<?=$infos_lien[1]?>"
					   name="<?=$nom?>"
					   onmouseover="<?=$onmouseover?>"
					   onmouseout="<?=$onmouseout?>"
					   <?=(isset($onclick)?'onclick="'.$onclick.'"':'')?>
					   href="<?=$lien?>">
				<?php
				if ($drapeaux && $infos_lien[0]!='new') {
					?>
					<img src="images/flags/<?=$pays?>.png" alt="<?=$pays?>" /><span><?=$infos_lien[1]?></span>
					<?php
				}
				else {
					if ($argument=='onglet_magazine')
						echo substr ($contenu_lien_onglet, strpos ($contenu_lien_onglet, '/')+1,strlen($contenu_lien_onglet));
					else
						echo $contenu_lien_onglet;
				}
				?>
					</a></li>
				<?php
		}
		?></ul><br /><?php
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
						
						
		$etats=array('manque'=>ETAT_MANQUANTS,
					 'mauvais'=>ETAT_MAUVAIS,
					 'moyen'=>ETAT_MOYEN,
					 'bon'=>ETAT_BON,
					 'excellent'=>ETAT_EXCELLENT,
					 'indefini'=>ETAT_INDEFINI);
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
				 id="n<?=($cpt)?>" title="<?=$numero?>"><img class="preview" src="images/icones/view.png" /><span class="num">n&deg;<?=$numero?>&nbsp;<span class="soustitre"><?=$sous_titre?></span></span>
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
}
?>