<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Format_liste.php');
class DMtable extends Format_liste {
	function DMtable() {
		$this->les_plus=array(DMTABLE_PLUS_1,DMTABLE_PLUS_2,DMTABLE_PLUS_3);
		$this->les_moins=array(DMTABLE_MOINS_1,DMTABLE_MOINS_2);
		$this->description=DMTABLE_DESCRIPTION;
	}

	function afficher($liste) {
		$max_centaines=0;
		$max_diz_et_unites=1;
		?><table rules="all" style="border:1;color: gray; font:11px/15px verdana,arial,sans-serif;">
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
				$total_magazine=0;
				$liste_numeros=array();
				$liste_non_numeriques=array();
				foreach($numeros as $numero_et_etat) {
					$total_magazine++;
					$numero=$numero_et_etat[0];
					if (false!=(array_key_exists($numero,$liste_numeros)))
						$liste_numeros[$numero]++;
					else
						$liste_numeros[$numero]=1;
					if (!is_numeric($numero) && $numero!='-') {
						array_push($liste_non_numeriques,$numero);
						continue;
					}
					//print_r ($numero);
					$centaine=intval($numero/100);
					$diz_et_unites=$numero-100*$centaine;
					if ($diz_et_unites==0) {
						$diz_et_unites=100;
						$centaine--;
					}
					if ($centaine>$max_centaines)
						$max_centaines=$centaine;
					if ($diz_et_unites>$max_diz_et_unites)
						$max_diz_et_unites=$diz_et_unites;
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
				$array_values_number=array_count_values($liste_numeros);

				for($i=1;$i<=50;$i++) {
                                    ?><td><?php
                                    for ($j=0;$j<=$max_centaines;$j++) {
                                        if (!array_key_exists($j*100+$i,$liste_numeros))
                                            continue;
                                        for ($k=0;$k<$liste_numeros[$j*100+$i];$k++) {
                                            echo number_to_letter($j);
                                        }
                                    }
                                    ?></td><?php
				}
                                ?><td rowspan="2"><?=$total_magazine?></td></tr><tr><?php
                                for($i=51;$i<=100;$i++) {
                                    ?><td><?php
                                    for ($j=0;$j<=$max_centaines;$j++) {
                                        if (!array_key_exists($j*100+$i,$liste_numeros))
                                            continue;
                                        for ($k=0;$k<$liste_numeros[$j*100+$i];$k++) {
                                                echo number_to_letter($j);
                                        }
                                    }
                                    ?></td><?php
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
                            ?><table style="color:gray;font:11px/15px verdana,arial,sans-serif">
                                <tr>
                                    <td colspan="2" align="center">
                                        <u><?=LEGENDE_NUMEROS?></u>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="vertical-align:top"><?php
                    for ($i=0;$i<=$max_centaines;$i++) {
                        if ($i==intval($max_centaines/2)+1) {
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
                            <table style="color:gray;font:11px/15px verdana,arial,sans-serif"><?php
                    foreach($liste as $pays=>$numeros_pays) {
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
?>