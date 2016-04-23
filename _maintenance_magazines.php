<?php header('Content-type: text/html; charset=UTF-8');
class Resolution {
    static $numeros_doubles_JM= ['2411-12','2463-64','2479-80','2506-07','2515-16','2531-32','2558-59','2584-85','2610-11','2619-20','2636-37','2662-63','2671-72','2688-89','2715-16','2723-24','2767-68','2819-20','2828-29','2844-45','2871-72','2879-80','2896-97','2923-24','2932-33','2948-49','2975-76','2984-85'];
}

include_once('Util.class.php');
include_once('Database.class.php');
include_once('Inducks.class.php');
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title><?=MAINTENANCE?></title>
    </head>
    <body><?php

$l = DM_Core::$d->toList();
foreach($l->collection as $pays => $magazines) {
    echo $pays.' : <br />';
    $liste_magazines_inducks=Inducks::get_liste_magazines($pays);
    foreach($magazines as $magazine_dm=>$numeros_dm) {
        if (!array_key_exists($magazine_dm, $liste_magazines_inducks))
            echo $pays.'/'.$magazine_dm.' n\'existe plus<br />';
        else {
            list($numeros_inducks,$sous_titres)=Inducks::get_numeros($pays, $magazine_dm);
            foreach($numeros_dm as $numero_dm) {
                $num_dm=$numero_dm[0];
                if (!in_array($num_dm,$numeros_inducks)) {
                    echo $pays.'/'.$magazine_dm.' '.$num_dm.' n\'existe pas sur Inducks<br />';
                    if (false !== ($resolution=tentative_resolution_numero($pays,$magazine_dm,$num_dm))) {
                        list($pays_resolution,$magazine_resolution,$numero_resolution)=$resolution;
                        $requete_update='UPDATE numeros SET Pays=\''.$pays_resolution.'\', Magazine=\''.$magazine_resolution.'\', Numero=\''.$numero_resolution.'\' '
                                       .'WHERE (Pays = \''.$pays.'\' AND Magazine = \''.$magazine_dm.'\' AND Numero = \''.$num_dm.'\')';
                        echo $requete_update.'<br />';//DM_Core::$d->requete($requete_update);
                        DM_Core::$d->requete($requete_update);
                        echo '==&gt; R&eacute;solution propos&eacute;e : '.implode('-',$resolution).'<br />';
                    }
                }
            }
        }
    }
}

function tentative_resolution_numero($pays,$magazine,$num_dm) {
    switch($pays) {
        case 'fr':
            switch($magazine) {
                case 'JM':
                    $num_suivant=$num_dm+1;
                    $split=str_split($num_suivant);
                    $num_double_corresp=$num_dm.'-'.$split[2].$split[3];
                    if (in_array($num_double_corresp,  Resolution::$numeros_doubles_JM))
                        return [$pays,$magazine,$num_double_corresp];
                break;
                case 'JMS':
                    if ($num_dm==2500)
                        return [$pays,$magazine,'2500A'];
                break;
                case 'PMHS':
                    $split=str_split($num_dm);
                    $lettre=$split[0];
                    if ($lettre>='0' && $lettre <='9') {
                        $lettre='B';
                        $nombre=$num_dm;
                    }
                    else {
                        if ($split[1]>='A' && $split[1]<='Z') {
                            $nombre=12+($split[1]-'A');
                        }
                        else
                            $nombre=$split[1].$split[2];
                    }
                    switch($lettre) {
                        case 'B':
                            return [$pays,'JP',$nombre];
                        break;
                        case 'C':
                            return [$pays,'TP',$nombre+2];
                        break;
                    }
                break;
            }
        break;
    
    }
    return false;
}
?>
    </body>
</html>
