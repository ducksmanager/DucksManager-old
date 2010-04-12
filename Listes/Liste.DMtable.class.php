<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Format_liste.php');
class DMtable extends Format_liste {
	function DMtable() {
		$this->les_plus=array(L::_('dmtable_plus_1'),
							  L::_('dmtable_plus_2'),
							  L::_('dmtable_plus_3'));
		$this->les_moins=array(L::_('dmtable_moins_1'),
							   L::_('dmtable_moins_2'));
		$this->description=L::_('dmtable_description');
	}
	
	function afficher($liste) {
		$max_centaines=0;
		$max_diz_et_unites=1;
		echo '<table rules="all" border="1">';
		echo '<tr><td></td>';
		for ($i=1;$i<=50;$i++)
			echo '<td>'.$i.'</td>';
		echo '</tr>';
		echo '<tr><td />';
		for ($i=51;$i<=100;$i++)
			echo '<td>'.$i.'</td>';
		echo '</tr>';
		foreach($liste as $pays=>$numeros_pays) {
			foreach($numeros_pays as $magazine=>$numeros) {
				$total_magazine=0;
				$liste_numeros=array();
				$liste_non_numeriques=array();
				foreach($numeros as $numero_et_etat) {
					$total_magazine++;
					$numero=$numero_et_etat[0];
					if (false!=(array_search($numero,$liste_numeros)))
						$liste_numeros[$numero]++; 
					else
						$liste_numeros[$numero]=1;
					if (!is_numeric($numero)) {
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
				
				echo '<tr><td rowspan="2" valign="middle">'.$magazine.'<br />('.$pays.')</td>';
				
				$array_values_number=array_count_values($liste_numeros);	
				
				for($i=1;$i<=50;$i++) {
					echo '<td>';
					for ($j=0;$j<=$max_centaines;$j++)
						for ($k=0;$k<$liste_numeros[$j*100+$i];$k++) {
							echo number_to_letter($j);
						}
					echo '</td>';
				}
					echo '<td rowspan="2">'.$total_magazine.'</td></tr><tr>';				
					for($i=51;$i<=100;$i++) {
						echo '<td>';
						for ($j=0;$j<=$max_centaines;$j++) {
							for ($k=0;$k<$liste_numeros[$j*100+$i];$k++) {
								echo number_to_letter($j);
							}
						}
						echo '</td>';
					}
				/*else {
					echo '<td>'.$total_magazine.'</td></tr><tr>';
				}*/
				echo '</tr>';
				if (count($liste_non_numeriques)>0) {
					echo '<tr><td></td><td colspan="51">'.L::_('non_numeriques').' : ';
					$debut=true;
					foreach($liste_non_numeriques as $numero) {
						if (!$debut)
							echo ' ; ';
						echo $numero;
						$debut=false;
					}
					echo '</td></tr>';
				}
			
			}
		}
		
		echo '</table><table><tr><td colspan="2" align="center"><u>'.L::_('legende_numeros').'</u></td></tr><tr><td>';
		for ($i=0;$i<=$max_centaines;$i++) {
			if ($i==intval($max_centaines/2)+1) echo '</td><td>';
			echo number_to_letter($i);
			echo ':'.($i*100+1).'-&gt;'.(($i+1)*100).'<br />';
		}
		echo '</td></tr></table>';
	}
}
function number_to_letter($number) {
	if ($number<26)
		return chr(97+$number);
	else
		return chr(65-26+$number);
}
?>