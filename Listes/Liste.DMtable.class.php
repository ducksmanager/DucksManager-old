<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
include_once ('Database.class.php');
include_once ('Magazine.class.php');
require_once('Format_liste.php');
class DMtable extends Format_liste {
    static $regex_numero_double='#([0-9]{2})([0-9]{2})\-([0-9]{2})#is';
    static $max_centaines=0;
    static $max_diz_et_unites=1;
    function DMtable() {
        $this->les_plus=array(DMTABLE_PLUS_1,DMTABLE_PLUS_2,DMTABLE_PLUS_3);
        $this->les_moins=array(DMTABLE_MOINS_1,DMTABLE_MOINS_2);
        $this->description=DMTABLE_DESCRIPTION;
    }

    function afficher($liste) {
            ?><table rules="all" style="border:1px solid gray;color: black; font:11px/15px verdana,arial,sans-serif;">
                <tr>
                    <td></td><?php
            for ($i=1;$i<=50;$i++) {?>
                    <td><?=$i?></td>
            <?php }?>
                    <td rowspan="2">
                        <?=TOTAL?>
                    </td>
                </tr>
                <tr>
                    <td></td><?php
            for ($i=51;$i<=100;$i++) {?>
                    <td><?=$i?></td>
            <?php }?>
                </tr><?php
            global $centaines_utilisees;
            $centaines_utilisees=array();
            ksort($liste);
            foreach($liste as $pays=>$numeros_pays) {
                ksort($numeros_pays);
                    foreach($numeros_pays as $magazine=>$numeros) {
                       $requete_get_ne_parait_plus='SELECT NeParaitPlus FROM magazines WHERE PaysAbrege LIKE \''.$pays.'\' AND NomAbrege LIKE \''.$magazine.'\'';
                       $resultat_get_ne_parait_plus=DM_Core::$d->requete_select($requete_get_ne_parait_plus);
                       $ne_parait_plus=$resultat_get_ne_parait_plus[0]['NeParaitPlus']==1;
                       $montrer_numeros_inexistants=false;
                       if ($ne_parait_plus) {
                            list($numeros_inducks,$sous_titres)=Inducks::get_numeros($pays,$magazine);
                            $numeros_centaines=array_map('get_nb_centaines',$numeros_inducks);
                            $numero_centaines_min=min($numeros_centaines);
                            $numero_centaines_max=max($numeros_centaines);
                            $montrer_numeros_inexistants=$numero_centaines_min==$numero_centaines_max;
                            foreach($numeros_inducks as $numero_inducks)
                                if (!est_valide($numero_inducks))
                                    $montrer_numeros_inexistants=false;
                       }
                       $total_magazine = 0;
                       global $liste_numeros;
                       global $liste_numeros_doubles;
                       $liste_numeros = array();
                       $liste_numeros_doubles = array();
                       $liste_non_numeriques = array();
                       foreach($numeros as $numero_et_etat) {
                            $total_magazine++;
                            $numero = $numero_et_etat[0];
                            if (est_double($numero)) {
                                preg_match(DMtable::$regex_numero_double, $numero, $numero);
                                $premier_numero = $numero[1] . $numero[2];
                                $deuxieme_numero = $numero[1] . $numero[3];
                                ajouter_a_liste($premier_numero, true);
                                ajouter_a_liste($deuxieme_numero, true);
                            }
                            else {
                                if (!est_valide($numero)) {
                                    array_push($liste_non_numeriques, $numero);
                                    continue;
                                }
                                ajouter_a_liste($numero, false);
                            }
                        }
                        ?>
                        <tr>
                            <td rowspan="2" valign="middle" align="center">
                                <img alt="<?=$pays?>" src="images/flags/<?=$pays?>.png" />
                                <br />
                                <?=$magazine?>
                                <br />
                            </td>
                        <?php

                        for($i=1;$i<=100;$i++) {
                            ?><td style="<?php 
                            if ($montrer_numeros_inexistants && !in_array($numero_centaines_min*100+$i,$numeros_inducks)) {
                                ?>background-color:gray;<?php
                            }
                            ?>"><?php
                            for ($j=0;$j<=DMtable::$max_centaines;$j++) {
                                if (array_key_exists($j*100+$i,$liste_numeros)) {
                                    for ($k=0;$k<$liste_numeros[$j*100+$i];$k++) {
                                        echo number_to_letter($j);
                                        if (array_key_exists($j*100+$i, $liste_numeros_doubles)) {
                                            if (array_key_exists($j*100+$i+1, $liste_numeros_doubles))
                                                echo '&gt;';
                                            else
                                                echo '&lt;';
                                        }
                                    }
                                }
                            }
                            ?></td><?php
                            if ($i==50) {
                                ?><td rowspan="2"><?=$total_magazine.(($ne_parait_plus)?'!':'')?></td></tr><tr><?php
                            }
                        }

                        /*else {
                                echo '<td>'.$total_magazine.'</td></tr><tr>';
                        }*/
                        ?></tr><?php
                        if (count($liste_non_numeriques)>0) {
                            ?><tr>
                                <td></td>
                                <td colspan="51">
                                    <?=NON_NUMERIQUES?> : <?php
                            $debut=true;
                            foreach($liste_non_numeriques as $numero) {
                                if (!$debut)
                                        echo ' ; ';
                                echo $numero;
                                $debut=false;
                            }
                            ?>
                                </td>
                            </tr><?php
                        }

                    }
            }

            ?></table>
            <table style="width:100%">
                <tr>
                    <td><?php
            if (count($centaines_utilisees)>0) {
                        ?><table style="color:black;font:11px/15px verdana,arial,sans-serif">
                            <tr>
                                <td colspan="2" align="center">
                                    <u><?=LEGENDE_NUMEROS?></u>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align:top"><?php
                for ($i=0;$i<=DMtable::$max_centaines;$i++) {
                    if ($i==intval(DMtable::$max_centaines/2)+1) {
                                ?></td><td style="vertical-align:top"><?php
                    }
                    ?><?=number_to_letter($i)?>:<?=($i*100+1)?>-&gt;<?=(($i+1)*100)?><br /><?php
                }
                                ?></td></tr></table><?php
            }
            ?>
                    </td><?php
            include_once('Database.class.php');
            $d=new Database();
            $nb_magazines=0;
            foreach($liste as $pays=>$numeros_pays)
                $nb_magazines+=count($numeros_pays);

            if ($nb_magazines > 1) {
                ?>
                    <td align="right" style="vertical-align: top">
                        <table style="color:black;font:11px/15px verdana,arial,sans-serif"><?php
                foreach($liste as $pays=>$numeros_pays) {
                    ksort($numeros_pays);
                    foreach($numeros_pays as $magazine=>$numeros) {
                        list($nom_pays_complet,$nom_magazine_complet)=$d->get_nom_complet_magazine($pays, $magazine,true);
                        ?><tr>
                            <td><?=$magazine?></td>
                            <td><?=$nom_magazine_complet?></td>
                        </tr><?php
                    }
                }?>
                        </table>
                    </td>
      <?php }?>
                </tr>
            </table>
            <?php
    }
}
function number_to_letter($number) {
    global $centaines_utilisees;
    $centaines_utilisees[$number]=true;
    if ($number<26)
        return chr(97+$number);
    else
        return chr(65-26+$number);
}

function est_double($numero) {
    return preg_match(DMTable::$regex_numero_double, $numero) > 0;
}

function est_valide($numero) {
    return is_numeric($numero);
}

function ajouter_a_liste($numero,$est_double=false) {
    global $liste_numeros;global $liste_numeros_doubles;
    if (false!=(array_key_exists($numero,$liste_numeros)))
        $liste_numeros[$numero]++;
    else
        $liste_numeros[$numero]=1;

    $centaine=get_nb_centaines($numero);
    $diz_et_unites=$numero-100*$centaine;
    if ($diz_et_unites == 0) {
        $diz_et_unites = 100;
        $centaine--;
    }
    if ($centaine > DMtable::$max_centaines)
        DMtable::$max_centaines = $centaine;
    if ($diz_et_unites > DMtable::$max_diz_et_unites)
        DMtable::$max_diz_et_unites = $diz_et_unites;
    
    if ($est_double) {
        if (false!=(array_key_exists($numero,$liste_numeros_doubles)))
            $liste_numeros_doubles[$numero]++;
        else
            $liste_numeros_doubles[$numero]=1;
    }
}

function get_nb_centaines($numero) {
    return intval($numero/100);
}
?>