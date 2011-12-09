<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
include_once ('locales/lang.php');
require_once('Format_liste.php');
class DMspiral extends Format_liste {
	function DMspiral() {
		$this->les_plus=array(DMSPIRAL_PLUS_1,DMSPIRAL_PLUS_2,DMSPIRAL_PLUS_3);
		$this->les_moins=array(DMSPIRAL_MOINS_1,DMSPIRAL_MOINS_2,DMSPIRAL_MOINS_3);
		$this->description=DMSPIRAL_DESCRIPTION;
	}

	function afficher($liste) {
            foreach($liste as $pays=>$numeros_pays) {
                foreach($numeros_pays as $magazine=>$numeros) {
                    $chaine='';
                    $premier=true;
                    foreach($numeros as $numero_et_etat) {
                        if (!$premier)
                            $chaine.=',';
                        if (is_string($numero_et_etat) || count($numero_et_etat) <2)
                                continue;
                        $numero=$numero_et_etat[0];
                        $etat=$numero_et_etat[1];
                        $chaine.=$magazine.'!'.
                                 $numero.'!'.
                                 $etat.'!'.
                                 '2005-00-00'.'!'.
                                 'a';
                        $premier=false;
                    }
                    //echo '<div id="mon_image"><table border="1"><tr><td>';
                    echo '<img src="image.php?chaine='.$chaine.'&amp;mag='.$magazine.'" />';
                    //echo '</td><td>'.$magazine.'('.$pays.')</td></tr></table></div>';
                }
            }
	}
}
?>