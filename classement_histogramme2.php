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
        $retour=array('total'=>null,'possede'=>null,'total_pct'=>null,'possede_pct'=>null);
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
    /* Par magazine
    if (isset($_POST['init_chargement'])) {
        $liste_magazines=array();
        foreach($l->collection as $pays=>$numeros_pays) {
            foreach(array_keys($numeros_pays) as $magazine) {
                $liste_magazines[]=$pays.'/'.$magazine;
            }
        }
        header("X-JSON: " . json_encode($liste_magazines));
    }
    elseif (isset($_POST['element'])) {
        list($pays_courant,$magazine_courant)=explode('/',$_POST['element']);
        $retour=array();
        
        list($nom_complet_pays,$nom_complet_magazine)=DM_Core::$d->get_nom_complet_magazine($pays_courant, $magazine_courant);
        $regex_nb_numeros='#<li><a href="publication.php\?c='.$pays_courant.'/'.$magazine_courant.'">[^<]+</a>&nbsp;<i>\(([^ ]+)#';
        
        require_once('Inducks.class.php');
        list($numeros,$sous_titres)=Inducks::get_numeros($pays_courant,$magazine_courant);
        $nb=count($numeros);
        if ($nb==0) {
            $cpt=0;
            $retour['possede_pct']=0;
            $retour['total_pct']=0;
        }
        else {
            $cpt=count($l->collection[$pays_courant][$magazine_courant]);
            $retour['possede_pct']=round(100*($cpt/$nb));
            $retour['total_pct']=100-round(100*($cpt/$nb));
        }
        $retour['possede']=$cpt;
        $retour['total']=$nb;

        $retour['pays']=$nom_complet_pays;
        $retour['nom_magazine_court']=$magazine_courant;
        $retour['nom_magazine']=$nom_complet_magazine;
        header("X-JSON: " . json_encode($retour));
    }
 */
    elseif (isset($_POST['fin'])) {
        include_once ('OpenFlashChart/php-ofc-library/open-flash-chart.php');
        include_once ('locales/lang.php');
        $infos=json_decode($_POST['infos']);
        $donnees=array();
        foreach(json_decode($_POST['ids']) as $i=>$pays) {
            foreach($infos[$i]->total as $magazine=>$total) {
                list($pays_complet,$magazine_complet)=DM_Core::$d->get_nom_complet_magazine($pays,$magazine);
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
        $title = new title(utf8_encode(POSSESSION_NUMEROS));
        $title->set_style( "{font-size: 20px; color: #F24062; font-family:Tuffy; text-align: center;}" );

        $bar_stack = new bar_stack();
        //$bar_stack->set_colours(array('#FF8000','#04B404'));

        $bar_stack_pct = new bar_stack();
        $bar_stack_pct->set_colours(array('#FF8000','#04B404'));

        foreach ($donnees as $donnee) {
                $tmp = new bar_stack_value($donnee->possede,'#FF8000');
                $tmp2 = new bar_stack_value(intval($donnee->total)-$donnee->possede,'#04B404');
                $titre_infobulle=$donnee->pays.' : '.$donnee->nom_magazine;
                $tmp->set_tooltip($titre_infobulle.utf8_encode('<br>'.NUMEROS_POSSEDES.' : '.$donnee->possede.'<br>'.TOTAL.' : '.intval($donnee->total)));
                $tmp2->set_tooltip($titre_infobulle.utf8_encode('<br>'.NUMEROS_MANQUANTS.' : '.($donnee->total-$donnee->possede).'<br>'.TOTAL.' : #total#'));
                $bar_stack->append_stack(array($tmp,$tmp2));

                //$b->set_tooltip('a');
                //$bar_stack->append_stack(array($donnee->possede, intval($total[$index])));
        }


        foreach ($donnees as $donnee) {
                $tmp = new bar_stack_value($donnee->possede_pct,'#FF8000');
                $tmp2 = new bar_stack_value(intval($donnee->total_pct),'#04B404');
                $titre_infobulle=$donnee->pays.' : '.$donnee->nom_magazine;
                $tmp->set_tooltip($titre_infobulle.utf8_encode('<br>'.NUMEROS_POSSEDES).' : #val#%');
                $tmp2->set_tooltip($titre_infobulle.utf8_encode('<br>'.NUMEROS_MANQUANTS).' : '.(100-$donnee->possede_pct).'%');
                $bar_stack_pct->append_stack(array($tmp,$tmp2));
        }

        $supertotal=0;
        foreach($donnees as $donnee)
                if ($donnee->total+$donnee->possede>$supertotal)
                        $supertotal=$donnee->total;

        $bar_stack->set_keys(
        array(
                new bar_stack_key('#FF8000', utf8_encode(NUMEROS_POSSEDES), 13 ),
                new bar_stack_key('#04B404', utf8_encode(NUMEROS_REFERENCES), 13 )
        ));

        //$bar_stack->set_tooltip('#x_label# : #val# '.utf8_encode(NUMEROS__GRAPHIQUE')).'<br>'.TOTAL.' : #total# '.utf8_encode(REFERENCES);


        $bar_stack_pct->set_keys(
        array(
                new bar_stack_key('#FF8000', utf8_encode(NUMEROS_POSSEDES), 13 ),
                new bar_stack_key('#04B404', utf8_encode(NUMEROS_REFERENCES), 13 )
        ));

        //$bar_stack_pct->set_tooltip('#x_label# : #val# %' );

        $y = new y_axis();
        $y->set_range( 0, $supertotal, intval($supertotal/10) );

        $y_pct = new y_axis();
        $y_pct->set_range( 0, 100, 5 );

        $noms_magazines_courts=array();
        foreach($donnees as $donnee)
            $noms_magazines_courts[]=$donnee->nom_magazine_court;
        
        $x = new x_axis();
        $x->set_labels_from_array($noms_magazines_courts);

        $tooltip = new tooltip();
        $tooltip->set_hover();
        $chart = new open_flash_chart();
        $chart->set_title( $title );
        $chart->add_element( $bar_stack );
        $chart->set_x_axis( $x );
        $chart->add_y_axis( $y );
        $chart->set_tooltip( $tooltip );

        $chart_pct = new open_flash_chart();
        $chart_pct->set_title( $title );
        $chart_pct->add_element( $bar_stack_pct );
        $chart_pct->set_x_axis( $x );
        $chart_pct->add_y_axis( $y_pct );
        $chart_pct->set_tooltip( $tooltip );
        $taille_graphique=count($donnees)<=4?300:80+40*count($donnees);

        $retour=array();
        $retour['largeur_graphique']=$taille_graphique;
        $retour['data_1']=$chart->toPrettyString();
        $retour['data_2']=$chart_pct->toPrettyString();
        $retour['l10n_valeur_reelles']=AFFICHER_VALEURS_REELLES;
        $retour['l10n_pourcentages']=AFFICHER_POURCENTAGES;
        
        echo str_replace('\n','',json_encode($retour));
        //header("X-JSON: " . str_replace('\n','',json_encode($retour)));
    }
}
?>