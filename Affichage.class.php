<?php
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
class Affichage {

	function onglets($selected,$tab_onglets,$argument,$prefixe,$jump_lines) {

		echo '<ul class="tabnav">';
		$cpt=0;
		$nb_onglets=count($tab_onglets);
		$largeur_tab=intval(100/$nb_onglets);
		foreach($tab_onglets as $nom_onglet=>$infos_lien) {
                    $contenu_lien_onglet=$nom_onglet;
                    $pos_slash=strpos($nom_onglet,'/');
                    if ($pos_slash!==false) {
                        $pays=substr($nom_onglet,0,$pos_slash);
                        $image_pays='images/flags/'.$pays.'.png';
                        $magazine=substr($nom_onglet,$pos_slash+1, strlen($nom_onglet));
                        $contenu_lien_onglet='<img src="'.$image_pays.'" alt="'.$pays.'" /><span>'.$magazine.'</span>';
                    }
                    //if ($jump_lines!=-1&&$cpt++%$jump_lines==0) echo '</tr><tr>';
                    echo '<li class="';
                    if ($infos_lien[1]==AJOUTER_MAGAZINE)
                        echo 'nouveau ';
                    if ($infos_lien[0]==$selected)
                        echo 'active ';
                    echo '"><a title="'.$infos_lien[1].'" href="'.$prefixe.'&amp;'.$argument.'='.$infos_lien[0].'">'.$contenu_lien_onglet.'</a></li>';
                    /*echo '<b class="top"><b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b></b>';
                            echo '<span class="boxcontent"><p><a title="'.$nom_onglet.'" href="'.$prefixe.'&amp;'.$argument.'='.$infos_lien[0].'">'.$infos_lien[1].'</a></p></span>';
                                    echo '<b class="bottom"><b class="b4b"></b><b class="b3b"></b><b class="b2b"></b><b class="b1b"></b></b>';
                            echo '</span>
                              </td>';*/
		}
                echo '</ul><br />';
		//echo '</tr>';
		//echo '</table>';
	}
	function afficher_numeros($liste,$pays,$magazine,$numeros) {

		$etats=array('manque'=>ETAT_MANQUANTS,
                             'mauvais'=>ETAT_MAUVAIS,
                             'moyen'=>ETAT_MOYEN,
                             'bon'=>ETAT_BON,
                             'excellent'=>ETAT_EXCELLENT,
                             'indefini'=>ETAT_INDEFINI);
		$cpt=0;
		//print_r($liste->collection[$pays][$magazine]);
		echo '<span id="pays" style="display:none">'.$pays.'</span>';
		echo '<span id="magazine" style="display:none">'.$magazine.'</span>';
		$d=new Database();
		if (!$d) {
			echo PROBLEME_BD;
			exit(-1);
		}
		$id_user=$d->user_to_id($_SESSION['user']);
		$requete_nom_complet_magazine='SELECT NomComplet FROM magazines WHERE (PaysAbrege LIKE "'.$pays.'" AND NomAbrege LIKE "'.$magazine.'")';
		$requete_nom_complet_magazine_resultat=$d->requete_select($requete_nom_complet_magazine);
		$nom_complet=utf8_encode($requete_nom_complet_magazine_resultat[0]['NomComplet']);
		echo '<br />';
		echo '<table border="0" width="100%">';
		echo '<tr><td rowspan="2"><span style="font-size:15pt;font-weight:bold;">'.$nom_complet.'</span></td>';
		echo '<td align="right"><table>';
		echo '<tr><td><input type="checkbox" id="sel_numeros_possede" checked="checked" onclick="changer_affichage(\'possede\')"/></td>'
			 .'<td>'.AFFICHER_NUMEROS_POSSEDES.'</td></tr>';
		echo '<tr><td align="right"><input type="checkbox" id="sel_numeros_manque" checked="checked" onclick="changer_affichage(\'manque\')"/></td>'
			 .'<td>'.AFFICHER_NUMEROS_MANQUANTS.'</td></tr>';
		echo '</table></td></tr>';
		echo '</table>';
		//echo '<pre>';print_r($liste);echo '</pre>';
		foreach($numeros as $numero) {
			echo '<div class="num_';
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
			echo '" id="n'.($cpt).'" title="'.$numero.'">';
			echo 'n&deg;'.$numero;
			if ($possede) {
                                if (is_null($etat_class))
                                    echo ' <span class="num_indefini"></span>';
				else
                                    echo ' <span class="num_'.$etat_class.'">'.ETAT.' '.$etat_class.'</span>';
				if ($id_acq!=-1 && $id_acq!=-2) {
					$requete_date_achat='SELECT Date FROM achats WHERE ID_Acquisition='.$id_acq.' AND ID_User='.$id_user;
					$resultat_date=$d->requete_select($requete_date_achat);
					$regex_date='#([^-]+)-([^-]+)-(.+)#is';
					$date=preg_replace($regex_date,'$3/$2/$1',$resultat_date[0]['Date']);
					if (!is_null($date) && !empty($date))
                                            echo '&nbsp;'.ACHETE_LE.' '.$date;
				}
			}
			if ($av)
				echo '<img height="16px" src="images/av.png" alt="AV"/>';
			echo '</div>';
			$cpt++;
		}
	}
	function afficher_etiquettes() {
		echo '<ol>';
		echo '<li>'.TEXTE_SELECTIONNER_NUMEROS1.'<br />'
				   .TEXTE_SELECTIONNER_NUMEROS2.'</li><br />';
		echo '<li>'.TEXTE_SELECTIONNER_NUMEROS3;

		echo '</ol><br />';
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
			echo AUCUNE_DATE_ACQUISITION.'<br />'
				.SELECTIONNER_NOUVELLE_DATE_ACQUISITION.'<br />';
		}

		echo '<select onchange="deselect_old(this)" multiple="multiple" id="date_acquisition">';
		if ($afficher_non_specifiee)
			echo '<option onmouseup="effacer_infos_acquisition()">['.DATE_NON_SPECIFIEE.']</option>';
		if (count($liste_acquisitions)==0) {
			echo '<option>['.AUCUNE_ACQUISITION.']</option>';
		}
		foreach($liste_acquisitions as $acquisition) {
			echo '<option label="'.$acquisition['ID_Acquisition'].'"';
			if (!$afficher_non_specifiee) {
				echo ' onmouseup="modifier_acquisition(this.label, this.value)"';
			}
			else
				echo ' onmouseup="effacer_infos_acquisition()"';
			echo '>['.$acquisition['Date'].'] '.$acquisition['Description'].'</option>';
		}
		echo '<option onmouseup="changer_date_acquisition(this,'.($afficher_non_specifiee?'true':'false').')">'.NOUVELLE_DATE_ACHAT.'...</option>';
		echo '</select>&nbsp;<span id="infos_liste_acquisition"></span>';
	}
}

?>