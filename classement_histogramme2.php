<?php
@session_start();
if (isset($_GET['lang'])) {
    $_SESSION['lang']=$_GET['lang'];
}
require_once('DucksManager_Core.class.php');
$id_user=DM_Core::$d->user_to_id($_SESSION['user']);
$l=DM_Core::$d->toList($id_user);
if (isset($_POST['id'])) {
    if (isset($_POST['init_chargement'])) {
        header("X-JSON: " . json_encode(array_keys($l->collection)));
    }
    elseif (isset($_POST['element'])) {
        $pays=$_POST['element'];
        $retour= ['total'=>null,'possede'=>null,'total_pct'=>null,'possede_pct'=>null];
        require_once('Inducks.class.php');
        $nb_numeros_magazines=Inducks::get_nb_numeros_magazines_pays($pays);
        foreach(array_keys($l->collection[$pays]) as $magazine) {
            if (array_key_exists($magazine,$nb_numeros_magazines)) {
                $retour['total'][$magazine]=$nb_numeros_magazines[$magazine];
                $retour['possede'][$magazine]=count($l->collection[$pays][$magazine]);
                $retour['possede_pct'][$magazine]=round(100*($retour['possede'][$magazine]/$retour['total'][$magazine]));
                $retour['total_pct'][$magazine]=100-round(100*($retour['possede'][$magazine]/$retour['total'][$magazine]));
            }
            else {
                $retour['total'][$magazine]=0;
                $retour['possede'][$magazine]=0;
                $retour['possede_pct'][$magazine]=0;
                $retour['total_pct'][$magazine]=0;
            }
        }
        header("X-JSON: " . json_encode($retour));
    }
    elseif (isset($_POST['fin'])) {
        include_once ('OpenFlashChart/php-ofc-library/open-flash-chart.php');
        include_once ('locales/lang.php');
		foreach(array_keys($_POST) as $key)
			$_POST[$key] = str_replace('\\"','"',$_POST[$key]);
        $infos=json_decode($_POST['infos']);
        $donnees= [];
        
        $publication_codes= [];
        foreach(json_decode($_POST['ids']) as $i=>$pays) {
        	foreach(array_keys(get_object_vars($infos[$i]->total)) as $magazine) {
        		$publication_codes[]=$pays.'/'.$magazine;
        	}
        }
        list($noms_complets_pays,$noms_complets_magazines)=Inducks::get_noms_complets($publication_codes);
        
        foreach(json_decode($_POST['ids']) as $i=>$pays) {
            foreach($infos[$i]->total as $magazine=>$total) {
                $pays_complet = $noms_complets_pays[$pays];
                if (array_key_exists($pays.'/'.$magazine, $noms_complets_magazines)) {
                    $magazine_complet = $noms_complets_magazines[$pays.'/'.$magazine];
                }
                else { // Magazine ayant disparu d'Inducks
                    $magazine_complet = $magazine;
                }
                $donnee=new stdClass ();
                $donnee->nom_magazine_court=$magazine;
                $donnee->pays=$pays_complet;
                $donnee->nom_magazine=$magazine_complet;
                $donnee->total=$total;
                $donnee->possede=$infos[$i]->possede->$magazine;
                $donnee->total_pct=$infos[$i]->total_pct->$magazine;
                $donnee->possede_pct=$infos[$i]->possede_pct->$magazine;
                $donnees[]=$donnee;
            }
        }
        $title = utf8_encode(POSSESSION_NUMEROS);

        $possedes = [];
        $possedes_cpt = [];

        $totaux = [];
        $totaux_cpt = [];
        $colors = ['#FF8000','#04B404'];

        foreach ($donnees as $donnee) {
                $possedes[] = $donnee->possede;
                $totaux[] = intval($donnee->total)-$donnee->possede;

//                $tmp = new bar_stack_value($donnee->possede,'#FF8000');
//                $tmp2 = new bar_stack_value(intval($donnee->total)-$donnee->possede,'#04B404');
//                $titre_infobulle=$donnee->pays.' : '.$donnee->nom_magazine;
//                $tmp->set_tooltip($titre_infobulle.utf8_encode('<br>'.NUMEROS_POSSEDES.' : '.$donnee->possede.'<br>'.TOTAL.' : '.intval($donnee->total)));
//                $tmp2->set_tooltip($titre_infobulle.utf8_encode('<br>'.NUMEROS_MANQUANTS.' : '.($donnee->total-$donnee->possede).'<br>'.TOTAL.' : #total#'));
//                $bar_stack->append_stack([$tmp,$tmp2]);

                //$b->set_tooltip('a');
                //$bar_stack->append_stack(array($donnee->possede, intval($total[$index])));
        }


        foreach ($donnees as $donnee) {
            $possedes_cpt[] = $donnee->possede;
            $totaux_cpt[] = intval($donnee->total)-$donnee->possede;

//                $tmp = new bar_stack_value($donnee->possede_pct,'#FF8000');
//                $tmp2 = new bar_stack_value(intval($donnee->total_pct),'#04B404');
//                $titre_infobulle=$donnee->pays.' : '.$donnee->nom_magazine;
//                $tmp->set_tooltip($titre_infobulle.utf8_encode('<br>'.NUMEROS_POSSEDES).' : #val#%');
//                $tmp2->set_tooltip($titre_infobulle.utf8_encode('<br>'.NUMEROS_MANQUANTS).' : '.(100-$donnee->possede_pct).'%');
//                $bar_stack_pct->append_stack([$tmp,$tmp2]);
        }

        $supertotal=0;
        foreach($donnees as $donnee) {
            if ($donnee->total+$donnee->possede>$supertotal) {
                $supertotal=$donnee->total;
            }
        }
        
        $legend = [utf8_encode(NUMEROS_POSSEDES), utf8_encode(NUMEROS_REFERENCES)];

        $labels= [];
        foreach($donnees as $donnee) {
            $labels[]=$donnee->nom_magazine_court;
        }
        
        echo str_replace('\n','',json_encode($retour));
        //header("X-JSON: " . str_replace('\n','',json_encode($retour)));
    }
}