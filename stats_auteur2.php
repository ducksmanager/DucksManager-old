<?php
@session_start();
require_once ('Database.class.php');
require_once ('Util.class.php');
$debut = microtime(true);
global $regex_code_histoire;
$regex_code_histoire = '#<td valign="[^"]*" bgcolor="\#cbdced"><[^>]+><[^>]+><br/>[^<]*<a href="story.php\?c=[^"]+"><font courier>([^<]+)</font></a>#is';
global $regex_histoire_code_personnages;
$regex_histoire_code_personnages = '#<tr[^>]+>[^<]*<td valign="[^"]*" bgcolor="\#cbdced"><[^>]+><[^>]+><br/>[^<]*<a[^>]+><[^>]+>([^<]+)<\/font><\/a> <\/td>[^<]*<td>[ ]*(?:<[^>]+>)?(<a [^<]+<\/a>[, ]*)*[^<]*(?:<\/small>)?(?:(?:(?:<i>)?[^<]*(?:<span[^<]*<\/span>[ ]*)*[^<]*<\/i>)?<br/>)?.(?:[^<]*<i>[^<]*<\/i>)?(?:<br/>)?[^<]*(?:<img[^>]*>)?<\/td>[^<]*<td>(?:[^<]*<br/>.)?[^<]*(?:<br/>)?[^<]*<small>(?:[^<]*<br/>)*[^<]*<\/small><\/td>[^<]*<td>(?:[^<]*<a [^>]+>(?:(?:<span [^>]+>)?[^<]*(?:<\/span>[ ]*)?)*<\/a>[()?*, ]*)+(?:<font [^<]+<\/font>)?[^<]*(?:<br/>.)?(?:<font[^<]*<\/font><br/>.)?<\/td>[^<]*<td>(([^<]*(<a [^<]*<\/a>[, ]*)*(?:<br/>.?)?[^<]*)*)<\/td><td>(?:(?:[^<]*(?:<(?:A|i)[^<]*<\/(?:A|i)>)+[.()0-9a-zA-Z, ]*)*<br/>.?)*#is';
global $regex_numero;
$regex_numero = '#<a href="issue.php\?c=([^/]+)/[^"]*">([^ ]+)[ ]*([^<]+)</a>#';

global $notations_tous_users;
$notations_tous_users = array();

$auteurs = array();
$requete_auteurs = 'SELECT DISTINCT NomAuteurAbrege FROM auteurs_pseudos '
                   .'WHERE DateStat LIKE \'0000-00-00\'';
$resultat_auteurs = DM_Core::$d->requete_select($requete_auteurs);
foreach ($resultat_auteurs as $auteur)
    array_push($auteurs, $auteur['NomAuteurAbrege']);

foreach ($auteurs as $auteur) {
    if (empty($auteur))
        continue;

    if (isset($_POST['id_user'])) {
        echo $auteur . '<br />';
        $requete_auteurs = 'SELECT ID_User FROM auteurs_pseudos '
            . 'WHERE ID_User=' . $_POST['id_user'] . ' AND NomAuteurAbrege LIKE \'' . $auteur . '\' AND DateStat LIKE \'0000-00-00\'';
        $resultat_auteurs = DM_Core::$d->requete_select($requete_auteurs);
        $requete_users = 'SELECT RecommandationsListeMags FROM users '
            . 'WHERE ID=' . $_POST['id_user'];
        $resultat_users = DM_Core::$d->requete_select($requete_users);
        if (count($resultat_auteurs) > 0) {
            $users = array($_POST['id_user']);
            foreach ($resultat_users as $user) {
                if ($user['RecommandationsListeMags'] == 1) {
                    $l = DM_Core::$d->toList($_POST['id_user']);
                    $liste_magazines = $l->liste_magazines();
                }
                else
                    $liste_magazines=array('vide' => 'vide');
                $users = array($_POST['id_user'] => $liste_magazines);
            }
        }
        else
            $users=array();
    }
    else {
        $users = array();
        $requete_users = 'SELECT ID_User, RecommandationsListeMags FROM auteurs_pseudos '
            . 'WHERE NomAuteurAbrege LIKE \'' . $auteur . '\' AND DateStat LIKE \'0000-00-00\'';
        $resultat_users = DM_Core::$d->requete_select($requete_users);
        foreach ($resultat_users as $user) {
            if ($user['RecommandationsListeMags'] === 1) {
                $l = DM_Core::$d->toList($user['ID_User']);
                $liste_magazines = $l->liste_magazines();
            }
            else
                $liste_magazines=array('vide' => 'vide');
            $users = array($user['ID_User'] => $liste_magazines);
        }
    }
    if (count($users) != 0) {
        $adresse_nom_auteur = 'http://coa.inducks.org/creator.php?c=' . urldecode($auteur);
        $adresse_nom_auteur = str_replace(' ', '+', $adresse_nom_auteur);
        echo $adresse_nom_auteur;
        $page = Util::get_page($adresse_nom_auteur);
        $regex_nom_auteur = '#<img class="transparent" src="img/coafoot.png" width="50" height="37" alt="">([^<]+)</h1>#is';
        preg_match($regex_nom_auteur, $page, $nom_auteur);
        $nom_auteur = $nom_auteur[1];
    }

    global $liste_magazines;
    foreach ($users as $id_user => $liste_magazines) {
        if (!array_key_exists($id_user, $notations_tous_users))
            $notations_tous_users[$id_user] = array();
        global $notations_magazines;
        $notations_magazines = array();
        $requete_notation = 'SELECT Notation FROM auteurs_pseudos WHERE '
            . 'NomAuteurAbrege LIKE \'' . $auteur . '\' AND ID_user=' . $id_user . ' AND DateStat LIKE \'0000-00-00\'';
        $resultat_notation = DM_Core::$d->requete_select($requete_notation);
        global $notation_auteur;
        $notation_auteur = $resultat_notation[0]['Notation'] - 5;
        global $l;
        $l = DM_Core::$d->toList($id_user);

        global $total_codes;
        $total_codes = 0;
        global $possedes;
        $possedes = 0;
        global $publie_france_non_possede;
        $publie_france_non_possede = 0;
        /* $adresse_compte_auteur='http://coa.inducks.org/comp.php?qu=SELECT%20COUNT(sv.storycode)%20FROM%20inducks_storyversion%20sv%20WHERE%20sv.what=%27s%27%20AND%20((sv.plotsummary%20LIKE%20%27%,'.$auteur.',%27)%20OR%20(sv.writsummary%20LIKE%20%27%,'.$auteur.',%%27)%20OR%20(sv.artsummary%20LIKE%27%,'.$auteur.',%%27)%20OR%20(sv.inksummary%20LIKE%20%27,'.$auteur.',%%27))%20ORDER%20BY%20sv.storycode&gd=0&mode=3';

          $page_compte=Util::get_page($adresse_compte_auteur);
          $regex_compte='#<b>COUNT\(sv\.storycode\)\^</b>[^0-9]*([0-9]+)\^</pre>#is';
          $a=preg_match($regex_compte,$page_compte,$nb_codes_total);
          $cpt_total=$nb_codes_total[1];
         */

        /*
          $cpt_total=1;
          echo $cpt_total; */

        $adresse_auteur = 'http://coa.inducks.org/comp2.php?code=&keyw=&keywt=i&exactpg=&pg1=&pg2=&bro2=&bro3=&kind=0&rowsperpage=0&columnsperpage=0&hero=&xapp=&univ=&xa2=&creat=' . $auteur . '&creat2=&plot=&plot2=&writ=&writ2=&art=&art2=&ink=&ink2=&pub1=&pub2=&part=&ser=&xref=&mref=&xrefd=&repabb=&repabbc=al&imgmode=0&vdesc2=on&vdesc=en&vfr=on&sort1=auto';

        list($nb_codes, $nb, $trouve, $requete_url, $codes, $histoires) = liste_histoires(1, $adresse_auteur);
        traiter_possessions($codes, $histoires, $auteur);
        $page_en_cours = 1;
        while ($trouve) {
            echo 'Page ' . $page_en_cours . '<br />';
            $adresse_auteur2 = 'http://coa.inducks.org/comp2.php?imgmode=0&owned=&noowned=&pageDirecte=' . $page_en_cours . '&c2Direct=en&c3Direct=fr&queryDirect='
                . urlencode($requete_url);
            list($nb_codes, $nb, $trouve, $requete_url, $codes, $histoires) = liste_histoires($page_en_cours + 1, $adresse_auteur2);
            traiter_possessions($codes, $histoires, $auteur);
            //Util::update_pct($event,100*(($page_en_cours+1)*200)/$cpt_total);

            $page_en_cours++;
        }
        echo $possedes . ' poss&eacute;d&eacute;s sur ' . $total_codes . ' (' . $publie_france_non_possede . ' publi&eacute;s en France mais non poss&eacute;d&eacute;s)<br />';
        $publie_etranger_non_possede = $total_codes - $publie_france_non_possede - $possedes;

        date_default_timezone_set('Europe/Paris');
        $requete_suppr_stats_existe = 'DELETE FROM auteurs_pseudos '
            . 'WHERE NomAuteurAbrege LIKE \'' . $auteur . '\' AND ID_User=' . $id_user . ' AND DateStat LIKE \'' . date('Y-m-d') . '\'';
        DM_Core::$d->requete($requete_suppr_stats_existe);
        $requete_stats = 'INSERT INTO auteurs_pseudos (NomAuteur, NomAuteurAbrege, ID_User, NbNonPossedesFrance, NbNonPossedesEtranger, NbPossedes, DateStat) '
            . 'VALUES (\'' . $nom_auteur . '\',\'' . $auteur . '\',' . $id_user . ',' . $publie_france_non_possede . ',' . $publie_etranger_non_possede . ',' . $possedes . ',\'' . date('Y-m-d') . '\')';
        DM_Core::$d->requete($requete_stats);

        foreach ($notations_magazines as $numero => $score_auteurs) {
            $score_magazine = $score_auteurs['Score'];
            $auteurs = $score_auteurs['Auteurs'];
            if (!array_key_exists($numero, $notations_tous_users[$id_user]))
                $notations_tous_users[$id_user][$numero] = array('Numero' => $numero, 'Score' => 0, 'Auteurs' => array());
            $notations_tous_users[$id_user][$numero]['Score']+=$score_magazine;
            foreach ($auteurs as $auteur => $nb_histoires_auteur)
                $notations_tous_users[$id_user][$numero]['Auteurs'][$auteur] = $nb_histoires_auteur;
        }

        //echo '<pre>';print_r($notations_magazines);echo '</pre>';for ($k=0;$k<50;$k++) echo "\n";
    }
}
$notations_user2 = array();
foreach ($notations_tous_users as $user => $notations_user) {
    usort($notations_user, 'tri_notations');
    $notations_user2[$user] = $notations_user;
    $requete_supprime_recommandation = 'DELETE FROM numeros_recommandes WHERE ID_Utilisateur=' . $id_user;
    DM_Core::$d->requete($requete_supprime_recommandation);
    for ($i = count($notations_user2[$user]) - 20; $i < count($notations_user2[$user]); $i++) {
        list($pays, $magazine_numero) = explode('/', $notations_user2[$user][$i]['Numero']);
        list($magazine, $numero) = explode(' ', $magazine_numero);
        $notation = $notations_user2[$user][$i]['Score'];
        $texte = '';
        if (isset($notations_user2[$user][$i]['Auteurs'])) {
            foreach ($notations_user2[$user][$i]['Auteurs'] as $auteur => $nb_histoires) {
                $texte.= ( $debut ? '' : ',') . $auteur . '=' . $nb_histoires;
            }
        }
        $requete_ajout_recommandation = 'INSERT INTO numeros_recommandes(Pays,Magazine,Numero,Notation,ID_Utilisateur,Texte) '
            . 'VALUES (\'' . $pays . '\',\'' . $magazine . '\',\'' . $numero . '\',' . $notation . ',' . $user . ',\'' . $texte . '\')';
        DM_Core::$d->requete($requete_ajout_recommandation);
    }
}
echo '<pre>';
print_r($notations_user2);
echo '</pre>';

$fin = microtime(true);
echo "\nTemps total : " . ($fin - $debut) . ' ms';

function liste_histoires($num_page, $adresse_auteur) {
    global $regex_histoire_code_personnages;
    global $regex_code_histoire;
    $nb_codes = $nb = 0;
    $codes = array();
    $histoires = array();
    $requete_url = '';
    $page = Util::get_page($adresse_auteur);
    $nb_codes = preg_match_all($regex_code_histoire, $page, $codes);
    $nb = preg_match_all($regex_histoire_code_personnages, $page, $histoires, PREG_PATTERN_ORDER);
    //echo '<span style="display:none">';print_r($codes);echo '</pre></span>';
    echo 'Page ' . $num_page . ' : ' . $nb . '/' . $nb_codes . ' total<br />';
    $regex_requete = '#input type=hidden name=queryDirect value="([^"]*)"#is';
    $trouve = (preg_match($regex_requete, $page, $req) != 0);
    if ($trouve)
        $requete_url = preg_replace($regex_requete, '$1', $req[0]);
    return array($nb_codes, $nb, $trouve, $requete_url, $codes, $histoires);
}

function traiter_possessions($codes, $histoires, $auteur) {
    global $liste_magazines;
    global $notations_magazines;
    global $notation_auteur;
    global $regex_histoire_code_personnages;
    global $regex_numero;
    global $id_user;
    global $l;
    global $total_codes;
    global $possedes;
    global $publie_france_non_possede;
    $total_codes+=count($codes[0]);
    foreach ($codes[0] as $i => $code) {
        $est_possede = false;
        $publie_france = false;
        echo '<br />' . $code . ' : ';
        /* Util::start_log('histoires');
          print_r($codes);
          Util::stop_log(); */
        $date_et_publications = preg_replace($regex_histoire_code_personnages, '$3', $histoires[0][$i]);
        //echo $date_et_publications;//echo preg_replace($regex_histoire_code_personnages,'<span style="background-color:#444499;">$1, $2, $3, $4, $5, $6, $7, $8, $9, $10</span>',$histoires[0][$i]);
        $nb_publications = preg_match_all($regex_numero, $date_et_publications, $publications);
        $liste_publications_texte = '';
        //echo '<pre>';print_r($code);echo '</pre>';
        foreach ($publications[0] as $publication) {
            $pays = 'fr';
            list($pays_numero, $magazine, $numero) = explode(';', preg_replace($regex_numero, '$1;$2;$3', $publication));
            if ($pays_numero == 'fr')
                $publie_france = true;
            if ($l->est_possede($pays, $magazine, $numero)) {
                $est_possede = true;
                echo '<b>' . $magazine . ' ' . $numero . '</b> ';
                $liste_publications_texte.= $magazine . ' ' . $numero . '<br />';
            } else {
                echo $magazine . ' ' . $numero . ' ';
            }
        }
        if ($est_possede)
            $possedes++;
        else {
            if ($publie_france) {
                $publie_france_non_possede++;
                if ($notation_auteur + 5 != -1) {
                    foreach ($publications[0] as $publication) {
                        list($pays, $magazine, $numero) = explode(';', preg_replace($regex_numero, '$1;$2;$3', $publication));
                        if (!array_key_exists('vide', $liste_magazines)
                            && !array_key_exists($pays . '/' . $magazine, $liste_magazines))
                            continue;
                        /*
                          if (!array_key_exists($pays,$notations_magazines)) {
                          $notations_magazines=array($pays=>array());
                          }
                          if (!array_key_exists($magazine,$notations_magazines[$pays])) {
                          $notations_magazines[$pays]=array($magazine=>array());
                          }
                          if (!array_key_exists($numero,$notations_magazines[$pays][$magazine])) {
                          $notations_magazines[$pays][$magazine]=array($numero=>0);
                          } */
                        if (!array_key_exists($pays . '/' . $magazine . ' ' . $numero, $notations_magazines))
                            $notations_magazines[$pays . '/' . $magazine . ' ' . $numero] = array('Score' => 0, 'Auteurs' => array());
                        if (!array_key_exists($auteur, $notations_magazines[$pays . '/' . $magazine . ' ' . $numero]['Auteurs']))
                            $notations_magazines[$pays . '/' . $magazine . ' ' . $numero]['Auteurs'][$auteur] = 0;
                        $notations_magazines[$pays . '/' . $magazine . ' ' . $numero]['Score']+=$notation_auteur;
                        $notations_magazines[$pays . '/' . $magazine . ' ' . $numero]['Auteurs'][$auteur]++;
                    }
                }
            }
        }
    }
}

function tri_notations($numero1, $numero2) {
    if ($numero1['Score'] == $numero2['Score'])
        return 0;
    return ($numero1['Score'] < $numero2['Score']) ? -1 : 1;
}