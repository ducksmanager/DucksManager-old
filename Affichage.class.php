<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
class Affichage {

	static function onglets($onglet_courant,$tab_onglets,$argument,$prefixe) {

		$cpt=0;
		$nb_onglets=count($tab_onglets);
		$largeur_tab=intval(100/$nb_onglets);
        ?><ul class="tabnav"><?php
		foreach($tab_onglets as $nom_onglet=>$infos_lien) {
            $contenu_lien_onglet=$nom_onglet;
            ?><li class="<?php
            if ($infos_lien[1]==AJOUTER_MAGAZINE)
               echo 'nouveau ';
            if ($infos_lien[0]==$onglet_courant)
               echo 'active ';
            ?>"><a title="<?=$infos_lien[1]?>" href="<?=$prefixe?>&amp;<?=$argument?>=<?=$infos_lien[0]?>">
            <?php
            $pos_slash=strpos($nom_onglet,'/');
            if ($pos_slash!==false) {
                $pays=substr($nom_onglet,0,$pos_slash);
                $magazine=substr($nom_onglet,$pos_slash+1, strlen($nom_onglet));
                ?>
                <img src="images/flags/<?=$pays?>.png" alt="<?=$pays?>" /><span><?=$magazine?></span>
                <?php
            }
            else
                echo $contenu_lien_onglet;
            ?>
                </a></li>
            <?php
		}
        ?></ul><br /><?php
	}
	static function afficher_numeros($liste,$pays,$magazine,$numeros) {

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
		$d=new Database();
		if (!$d) {
			echo PROBLEME_BD;
			exit(-1);
		}
		$id_user=$d->user_to_id($_SESSION['user']);
                list($pays,$nom_complet)=$d->get_nom_complet_magazine($pays, $magazine);
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
                                <?=AFFICHER_NUMEROS_POSSEDES?>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">
                                <input type="checkbox" id="sel_numeros_manque" checked="checked" onclick="changer_affichage('manque')"/>
                            </td>
                            <td>
                                <?=AFFICHER_NUMEROS_MANQUANTS?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
		</table>
        <?php
		//echo '<pre>';print_r($liste);echo '</pre>';
		foreach($numeros as $numero) {
			?>
            <div class="num_<?php
			$possede=false;
			list($etat,$av,$id_acq)=$liste->est_possede_etat_av_idacq($pays,$magazine,$numero);
			if (''!=$etat) {
				$possede=true;
				$noms_etats=array();
				foreach(Database::$etats as $etat_court=>$infos_etat) {
					if ($etat==$infos_etat[0]) {
						$etat_class=$etat_court;
						$etat_nom_complet=$infos_etat[0];
						break;
					}
				}
				echo 'possede';
			}
			else
				echo 'manque';
			?>" id="n<?=($cpt)?>" title="<?=$numero?>">n&deg;<?=$numero?>
            <?php
			if ($possede) {
                if (!isset($etat_class)) {
                    ?><span class="num_indefini"></span><?php
                }
				else {
                    ?><span class="num_<?=$etat_class?>"><?=ETAT?> <?=$etat_class?></span><?php
                }
				if ($id_acq!=-1 && $id_acq!=-2) {
                    $requete_date_achat='SELECT Date FROM achats WHERE ID_Acquisition='.$id_acq.' AND ID_User='.$id_user;
                    $resultat_date=$d->requete_select($requete_date_achat);
                    if (count($resultat_date)>0) {
                        $regex_date='#([^-]+)-([^-]+)-(.+)#is';
                        $date=preg_replace($regex_date,'$3/$2/$1',$resultat_date[0]['Date']);
                        if (!is_null($date) && !empty($date)) {
                            ?>&nbsp;<?=ACHETE_LE?> <?=$date?><?php
                        }
                    }
				}
			}
			if ($av) {
				?><img height="16px" src="images/av.png" alt="AV"/><?php
            }
            ?>
			</div>
            <?php
			$cpt++;
		}
	}

	static function afficher_acquisitions($afficher_non_specifiee) {

		$d=new Database();
		if (!$d) {
			echo PROBLEME_BD;
			exit(-1);
		}
		$id_user=$d->user_to_id($_SESSION['user']);
		$requete_acquisition='SELECT ID_Acquisition, Date, Description FROM achats WHERE ID_User='.$id_user.' ORDER BY Date DESC';
		$liste_acquisitions=$d->requete_select($requete_acquisition);
		if (count($liste_acquisitions)==0) {
			?>
            <?=AUCUNE_DATE_ACQUISITION?><br />
			<?=SELECTIONNER_NOUVELLE_DATE_ACQUISITION?><br /><?php
		}
        ?>
		<select onchange="deselect_old(this)" multiple="multiple" id="date_acquisition">
            <?php
            if ($afficher_non_specifiee) {
                ?><option onmouseup="effacer_infos_acquisition()">[<?=DATE_NON_SPECIFIEE?>]</option><?php
            }
            if (count($liste_acquisitions)==0) {
                ?><option>[<?=AUCUNE_ACQUISITION?>]</option><?php
            }
            foreach($liste_acquisitions as $acquisition) {
                ?>
                <option label="<?=$acquisition['ID_Acquisition']?>"<?php
                if (!$afficher_non_specifiee) {
                    ?> onmouseup="modifier_acquisition(this.label, this.value)"<?php
                }
                else {
                    ?> onmouseup="effacer_infos_acquisition()"<?php
                }
                ?>
                >[<?=$acquisition['Date']?>] <?=$acquisition['Description']?></option><?php
            }
            ?>
            <option onmouseup="changer_date_acquisition(this,<?=($afficher_non_specifiee?'true':'false')?>)"><?=NOUVELLE_DATE_ACHAT?>...</option>
		</select>
       &nbsp;
       <span id="infos_liste_acquisition"></span>
       <?php
	}
}
?>