<?php
/*
 * Developpement : Bruno Perel (admin[at]ducksmanager[dot]net)
 * (c)2003-2012 Cette classe est soumise à copyright
 */
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
include_once ('Database.class.php');
include_once ('Magazine.class.php');
require_once('Format_liste.php');
class collectable extends Format_liste {
	static $titre='CollecTable';
	static $regex_numero_double='#([0-9]{2})([0-9]{2})\-([0-9]{2})#is';
	static $max_centaines=0;
	static $max_diz_et_unites=1;
	function collectable() {
		$this->les_plus=array(COLLECTABLE_PLUS_1,COLLECTABLE_PLUS_2,COLLECTABLE_PLUS_3);
		$this->les_moins=array(COLLECTABLE_MOINS_1,COLLECTABLE_MOINS_2);
		$this->description=COLLECTABLE_DESCRIPTION;
		$this->ajouter_parametres(array(
			'nb_numeros_ligne'=>new Parametre_valeurs('Nombre de num&eacute;ros par ligne',array(25,50,100),50,50)));
	}

	static function est_listable($numero) {
		return (is_numeric($numero) && $numero !=0) || est_double($numero);
	}
	
	function afficher($liste) {
		?>
			<style type="text/css">
				<!--
				table
				{
					color: black;
					font: 11px/15px verdana,arial,sans-serif;
				}
				
				table.collectable, table.legende {
				    width: 90%;
				}
				
				table.collectable {
				    border: solid 1px black;
					border-collapse: collapse;
				}
				
				/*table.collectable td
				{
				    text-align: left;
				    border: solid 1px black;
					height: 15px;
				}*/
				
				table.collectable td.libelle_ligne {
					
				}
				
				table.legende {
					border: 1px solid black;
				}
				
				table.noms_magazines {
					
				}
				
				-->
			</style>		
		<?php
		$nb_lignes=100/$this->p('nb_numeros_ligne');
			?><table class="collectable">
				<?php
				for ($i=1;$i<=$nb_lignes;$i++) {?>
					<tr>
						<td></td><?php
						for ($j=$this->p('nb_numeros_ligne')*($i-1)+1;$j<=$this->p('nb_numeros_ligne')*$i;$j++) {?>
							<td><?=$j?></td>
						<?php }
						if ($i==1) {?>
						<td rowspan="<?=$nb_lignes?>">
							<?=TOTAL?>
						</td>
						<?php } ?>
					</tr><?php
				}
			global $centaines_utilisees;
			$centaines_utilisees=array();
			ksort($liste);
			
			$publication_codes=array();
			foreach($liste as $pays=>$numeros_pays) {
				foreach(array_keys($numeros_pays) as $magazine) {
					$publication_codes[]=$pays.'/'.$magazine;
				}
			}
			$magazines_ne_paraissant_plus=Inducks::get_magazines_ne_paraissant_plus($publication_codes);
			foreach($liste as $pays=>$numeros_pays) {
				ksort($numeros_pays);
				foreach($numeros_pays as $magazine=>$numeros) {
				   $ne_parait_plus=in_array($pays.'/'.$magazine, $magazines_ne_paraissant_plus);
				   $montrer_numeros_inexistants=false;
				   if ($ne_parait_plus) {
						list($numeros_inducks,$sous_titres)=Inducks::get_numeros($pays,$magazine);
						$numeros_centaines=array_map('get_nb_centaines',$numeros_inducks);
						$numero_centaines_min=min($numeros_centaines);
						$numero_centaines_max=max($numeros_centaines);
						$montrer_numeros_inexistants=$numero_centaines_min==$numero_centaines_max;
						foreach($numeros_inducks as $numero_inducks)
							if (!(self::est_listable($numero_inducks)))
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
							preg_match(collectable::$regex_numero_double, $numero, $numero);
							$premier_numero = $numero[1] . $numero[2];
							$deuxieme_numero = $numero[1] . $numero[3];
							ajouter_a_liste($premier_numero, true);
							ajouter_a_liste($deuxieme_numero, true);
						}
						else {
							if (!(self::est_listable($numero))) {
								array_push($liste_non_numeriques, urldecode($numero));
								continue;
							}
							ajouter_a_liste($numero, false);
						}
					}
					$non_numeriques=count($liste_non_numeriques) > 0;
					?>
					<tr style="height: 15px">
						<td rowspan="<?=$nb_lignes+($non_numeriques?1:0)?>" valign="middle" align="center">
							<img alt="<?=$pays?>" src="images/flags/<?=$pays?>.png" />
							<br />
							<?=$magazine?>
							<br />
						</td>
					<?php

					for($i=1;$i<=100;$i++) {
						if ($i % ($this->p('nb_numeros_ligne')) == 1) {
							if ($i!=1) {
							?><tr style="height: 15px"><?php
								/*?><td></td><?php*/
							}
						}
						?><td<?php 
						if ($montrer_numeros_inexistants && !in_array($numero_centaines_min*$this->p('nb_numeros_ligne')+$i,$numeros_inducks)) {
							?> style="background-color:gray;"<?php
						}
						?>><?php
						$contenu='';
						for ($j=0;$j<=collectable::$max_centaines;$j++) {
							if (array_key_exists($j*100+$i,$liste_numeros)) {
								for ($k=0;$k<$liste_numeros[$j*100+$i];$k++) {
									if (array_key_exists($j*100+$i, $liste_numeros_doubles)) {
										if (array_key_exists($j*100+$i+1, $liste_numeros_doubles))
											$contenu.=number_to_letter($j).'&gt;';
										else
											$contenu.='&lt;'.number_to_letter($j);
									}
									else {
										$contenu.=number_to_letter($j);
									}
								}
							}
						}
						echo $contenu;
						?></td><?php
						if ($i % $this->p('nb_numeros_ligne') == 0) {
							if ($i == $this->p('nb_numeros_ligne')) {
								?><td style="text-align:center" rowspan="<?=$nb_lignes+($non_numeriques?1:0)?>"><?=$total_magazine?></td><?php
							}
							?>
							</tr><?php
						}
					}

					/*else {
							echo '<td>'.$total_magazine.'</td></tr><tr>';
					}*/
					if (count($liste_non_numeriques)>0) {
						?><tr>
							<td colspan="<?=$this->p('nb_numeros_ligne')?>">
								<?=NON_NUMERIQUES?> : <?=implode(', ',$liste_non_numeriques)?>
							</td>
						</tr><?php
					}
				}
			}

			?></table>
			<table class="legende">
				<tr>
					<td style="width: 50%"><?php
			if (count($centaines_utilisees)>0) {
						?><table>
							<tr>
								<td colspan="2" align="center">
									<u><?=LEGENDE_NUMEROS?></u>
								</td>
							</tr>
							<tr>
								<td style="vertical-align:top"><?php
				for ($i=0;$i<=collectable::$max_centaines;$i++) {
					if ($i==intval(collectable::$max_centaines/2)+1) {
						?></td><td style="vertical-align:top"><?php
					}
					?><?=number_to_letter($i)?>:<?=($i*100+1)?>-&gt;<?=(($i+1)*100)?><br /><?php
				}
								?></td>
							</tr>
						</table>
				<?php
			}
			?>
					</td><?php
			$nb_magazines=0;
			foreach($liste as $pays=>$numeros_pays)
				$nb_magazines+=count($numeros_pays);

			if ($nb_magazines > 1) {
				?>
					<td align="right" style="vertical-align: top;width: 50%"">
						<table class="noms_magazines"><?php
				$publication_codes=array();
				foreach($liste as $pays=>$numeros_pays) {
					foreach(array_keys($numeros_pays) as $magazine) {
						$publication_codes[]=$pays.'/'.$magazine;
					}
				}
				list($noms_pays,$noms_magazines) = Inducks::get_noms_complets($publication_codes);
				foreach($liste as $pays=>$numeros_pays) {
					ksort($numeros_pays);
					foreach($numeros_pays as $magazine=>$numeros) {
						?><tr>
							<td><img alt="<?=$pays?>" src="images/flags/<?=$pays?>.png" />&nbsp;<?=$magazine?></td>
							<td><?=$noms_magazines[$pays.'/'.$magazine]?></td>
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
	if (preg_match(collectable::$regex_numero_double, $numero, $numero) == 0)
		return false;
	return intval($numero[1].$numero[2])+1 == intval($numero[1].$numero[3]);
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
	if ($centaine > collectable::$max_centaines)
		collectable::$max_centaines = $centaine;
	if ($diz_et_unites > collectable::$max_diz_et_unites)
		collectable::$max_diz_et_unites = $diz_et_unites;
	
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